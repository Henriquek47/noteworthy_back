<?php

use App\models\Address;
use App\models\User;
use App\repository\UserRepository;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

function userRoutes($app) {

    $app->post('/login', function (Request $request, Response $response, array $args) {
        $data = $request->getParsedBody();
    
        // 1. Obter os arquivos enviados
        $uploadedFiles = $request->getUploadedFiles();
    
        // 2. Verificar se o profilePicture foi enviado
        $profilePictureFile = $uploadedFiles['profilePicture'] ?? null;
    
        // 3. Processar e salvar o arquivo
        $profilePicturePath = null;
        if ($profilePictureFile && $profilePictureFile->getError() === UPLOAD_ERR_OK) {
            $uploadDirectory = "C:/xampp/htdocs/noteworthy_back/src/upload"; // Altere isso para o diretório de upload desejado
            $basename = bin2hex(random_bytes(8)); // Gerar um nome de arquivo aleatório
            $filename = $basename . "." . pathinfo($profilePictureFile->getClientFilename(), PATHINFO_EXTENSION);
            $profilePictureFile->moveTo($uploadDirectory . DIRECTORY_SEPARATOR . $filename);
            $profilePicturePath = $uploadDirectory . DIRECTORY_SEPARATOR . $filename;
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
    
        // 4. Use a variável $profilePicturePath ao criar o objeto User
        $user = new User(
            null,
            $data['username'],
            $profilePicturePath,
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['favoritePosts'] ?? [],
            $addressData
        );
    
        $userRepository = new UserRepository();
        $user = $userRepository->save($user);
    
        $token = bin2hex(random_bytes(16));
    
        $userRepository->storeUserToken($user, $token);
    
        $response = $response->withAddedHeader('Set-Cookie', "user_auth={$token}; expires=" . (time() + 3600) . "; path=/");
    
        $response->getBody()->write(json_encode(['message' => 'User created successfully!']));
        return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus(200);
    });
    
    $app->post('/logout', function (Request $request, Response $response) {
        $token = $request->getCookieParams()['user_auth'] ?? null;

        if ($token) {
            $userRepository = new UserRepository();
            $userRepository->invalidateUserToken($token);

            $response = $response->withAddedHeader('Set-Cookie', "user_auth={$token}; expires=" . (time() + 3600) . "; path=/");
        }

        $response->getBody()->write(json_encode(['message' => 'Logged out successfully']));
        return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus(200);
    });

    $app->get('/user/{id}/image', function (Request $request, Response $response, array $args) {
        $id = $args['id'];
    
        $userRepository = new UserRepository();
        $user = $userRepository->findById($id);
    
        if ($user && $user->profilePicture && file_exists($user->profilePicture)) {
            return $response->withHeader('Content-Type', mime_content_type($user->profilePicture))
                            ->withBody(new \Slim\Psr7\Stream(fopen($user->profilePicture, 'rb')))
                            ->withStatus(200);
        }
    
        $response->getBody()->write(json_encode(['error' => 'Image not found']));
        return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus(404);
    });

    $app->get('/user/{id}', function (Request $request, Response $response, array $args) {
        $id = $args['id'];

        $userRepository = new UserRepository();
        $user = $userRepository->findById($id);

        if ($user) {
            $response->getBody()->write(json_encode($user));
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(200);
        }

        $response->getBody()->write(json_encode(['error' => 'User not found']));
        return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus(404);
    });

    $app->delete('/user/{id}', function (Request $request, Response $response, array $args) {
        $id = $args['id'];

        $userRepository = new UserRepository();
        $userRepository->delete($id);

        $response->getBody()->write(json_encode(['message' => 'User deleted successfully!']));
        return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus(200);
    });

    $app->put('/user/{id}', function (Request $request, Response $response, array $args) {
        $id = $args['id'];
        $data = $request->getParsedBody();

        $userRepository = new UserRepository();
        $user = $userRepository->findById($id);

        if (!$user) {
            $response->getBody()->write(json_encode(['error' => 'User not found']));
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(404);
        }

        $addressData = $data['address'] ?? null;
        if ($addressData) {
            $address = new Address(
                $addressData['street'],
                $addressData['city'],
                $addressData['state'],
                $addressData['neighborhood'],
                $addressData['number'],
                $addressData['complement']
            );
            $user->address = $address;
        }

        $user->username = $data['username'];
        $user->profilePicture = $data['profilePicture'];
        $user->email = $data['email'];
        $user->favoritePosts = $data['favoritePosts'];

        $userRepository->update($user);

        $response->getBody()->write(json_encode(['message' => 'User updated successfully!']));
        return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus(200);
    });
}
