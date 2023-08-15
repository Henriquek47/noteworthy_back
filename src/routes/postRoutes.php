<?php

use App\models\Address;
use App\models\Post;
use App\repository\PostRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

function postRoutes($app) {

    function resizeAndSaveImage($sourcePath, $targetPath, $maxWidth, $maxHeight) {
        list($sourceWidth, $sourceHeight, $sourceType) = getimagesize($sourcePath);
        
        // Define o tamanho do lado do quadrado
        $squareSize = min($maxWidth, $maxHeight, $sourceWidth, $sourceHeight);
        
        // Define a posição inicial x e y na imagem original para começar o corte
        if ($sourceWidth > $sourceHeight) {
            $src_x = ($sourceWidth - $sourceHeight) / 2;
            $src_y = 0;
            $sourceWidth = $sourceHeight;
        } else {
            $src_x = 0;
            $src_y = ($sourceHeight - $sourceWidth) / 2;
            $sourceHeight = $sourceWidth;
        }
        
        $targetImage = imagecreatetruecolor($squareSize, $squareSize);
        
        if ($sourceType === IMAGETYPE_JPEG) {
            $sourceImage = imagecreatefromjpeg($sourcePath);
        } elseif ($sourceType === IMAGETYPE_PNG) {
            $sourceImage = imagecreatefrompng($sourcePath);
            imagealphablending($targetImage, false);
            imagesavealpha($targetImage, true);
        } elseif ($sourceType === IMAGETYPE_GIF) {
            $sourceImage = imagecreatefromgif($sourcePath);
        } else {
            return false;
        }
        
        imagecopyresampled(
            $targetImage, $sourceImage,
            0, 0, $src_x, $src_y,
            $squareSize, $squareSize, $sourceWidth, $sourceHeight
        );
        
        $success = imagejpeg($targetImage, $targetPath, 85);
    
        imagedestroy($sourceImage);
        imagedestroy($targetImage);
        
        return $success;
    }
    
    

    $app->post('/post', function (Request $request, Response $response, array $args) {
        $data = $request->getParsedBody();
    
        $uploadedFiles = $request->getUploadedFiles();
        
        // 2. Verificar se o profilePicture foi enviado
        $picturesFiles = $uploadedFiles['pictures'] ?? null;
        
        // 3. Processar e salvar o arquivo
        $profilePicturePath = null;
        if ($picturesFiles && $picturesFiles->getError() === UPLOAD_ERR_OK) {
            $uploadDirectory = "C:/xampp/htdocs/noteworthy_back/src/upload"; // Altere isso para o diretório de upload desejado
            $basename = bin2hex(random_bytes(8)); // Gerar um nome de arquivo aleatório
            $filename = $basename . "." . pathinfo($picturesFiles->getClientFilename(), PATHINFO_EXTENSION);
            
            $fullFilePath = $uploadDirectory . DIRECTORY_SEPARATOR . $filename;
            $picturesFiles->moveTo($fullFilePath);
            
            // Redimensionar imagem para formato quadrado de 200x200 pixels
            resizeAndSaveImage($fullFilePath, $fullFilePath, 200, 200);
    
            $profilePicturePath = $fullFilePath;
        }
        
        $addressData = $data['address'] ?? null;
        if ($addressData) {
            $addressData = new Address(
                $data['address']['street'],
                $data['address']['city'],
                $data['address']['state'],
                $data['address']['neighborhood'],
                $data['address']['number'],
                $data['address']['complement']
            );
        }
        $post = new Post(
            null,
            $data['title'],
            $profilePicturePath,
            $data['description'],
            $data['authorId'],
            $data['instrument'],
            $data['price'],
            $data['shipping'],
            $data['status'],
            $addressData,
        );
    
        $postRepository = new PostRepository();
        $post = $postRepository->create($post);
    
        $response->getBody()->write(json_encode(['message' => 'Post created successfully!']));
        return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus(200);
    });

    $app->get('/post/{id}/image', function (Request $request, Response $response, array $args) {
        $id = $args['id'];
    
        $postRepository = new PostRepository();
        $post = $postRepository->findById($id);
    
        if ($post && $post->pictures && file_exists($post->pictures)) {
            return $response->withHeader('Content-Type', mime_content_type($post->pictures))
                            ->withBody(new \Slim\Psr7\Stream(fopen($post->pictures, 'rb')))
                            ->withStatus(200);
        }
    
        $response->getBody()->write(json_encode(['error' => 'Image not found']));
        return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus(404);
    });

    $app->get('/post/list', function (Request $request, Response $response, array $args) {
        $postRepository = new PostRepository();
        $post = $postRepository->findAll();

        if ($post) {
            $response->getBody()->write(json_encode($post));
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(200);
        }

        $response->getBody()->write(json_encode(['error' => 'Post not found']));
        return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus(404);
    });

    $app->get('/post/{id}', function (Request $request, Response $response, array $args) {
        $id = $args['id'];
        $postRepository = new PostRepository();
        $post = $postRepository->findById($id);

        if ($post) {
            $response->getBody()->write(json_encode($post));
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(200);
        }

        $response->getBody()->write(json_encode(['error' => 'Post not found']));
        return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus(404);
    });

    $app->delete('/post/{id}', function (Request $request, Response $response, array $args) {
        $id = $args['id'];

        $postRepository = new PostRepository();
        $postRepository->delete($id);

        $response->getBody()->write(json_encode(['message' => 'Post deleted successfully!']));
        return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus(200);
    });

}
