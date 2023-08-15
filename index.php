<?php

require 'vendor/autoload.php';
require __DIR__ . '/src/routes/userRoutes.php';
require __DIR__ . '/src/routes/postRoutes.php';
require __DIR__ . '/src/routes/paymentRoutes.php';
require __DIR__ . '/src/routes/reviewRoutes.php';

use App\repository\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Factory\AppFactory;

$app = AppFactory::create();

// CORS Middleware
$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$corsMiddleware = function ($request, $handler) {
    $response = $handler->handle($request);
    
    // Add CORS headers
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
};

$middlewareJSON = function ($request, $handler) {
    $contentType = $request->getHeaderLine('Content-Type');

    if (strstr($contentType, 'application/json')) {
        $contents = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $request = $request->withParsedBody($contents);
        }
    }

    return $handler->handle($request);
};

$authenticationMiddleware = function ($request, $handler) {
    if (isset($_COOKIE['user_auth'])) {
        $token = $_COOKIE['user_auth'];

        $userRepository = new UserRepository();
        $user = $userRepository->findByToken($token);

        if ($user) {
            $request = $request->withAttribute('user', $user);
            return $handler->handle($request);
        }
    }

    $response = new \Slim\Psr7\Response();

    $response->getBody()->write(json_encode(['error' => 'Not authenticated']));
    return $response->withStatus(401)
        ->withHeader('Content-Type', 'application/json');
};

$app->add($corsMiddleware); // Adicione o middleware CORS
$app->add($middlewareJSON);
//$app->add($authenticationMiddleware);

userRoutes($app);
postRoutes($app);
paymentRoutes($app);
reviewRoutes($app);

$app->run();
