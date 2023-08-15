<?php

use App\Models\Review;
use App\Repositories\ReviewRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

function reviewRoutes($app) {

    $app->post('/review', function (Request $request, Response $response, array $args) {
        $data = $request->getParsedBody();
        
        if (isset($data['reviewerId'], $data['reviewedUserId'], $data['rating'])) {
            $review = new Review($data['reviewerId'], $data['reviewedUserId'], floatval($data['rating']));
            
            $repository = new ReviewRepository();
            $savedReview = $repository->create($review);
            
            // Respond with the saved review details or however you'd like to format the response.
            $response->getBody()->write(json_encode($savedReview));
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(200);
        }
        
        // Return error if required data isn't present.
        return $response->withHeader('Content-Type', 'application/json')
        ->withStatus(404);
    });

    $app->get('/review/{id}', function (Request $request, Response $response, array $args) {
        $id = $args['id'];
        
        $repository = new ReviewRepository();
        $review = $repository->findByPk($id);
        
        if ($review) {
            $response->getBody()->write(json_encode($review));
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(200);
        }
        
        // Return error if review not found.
        return $response->withHeader('Content-Type', 'application/json')
        ->withStatus(404);
    });

}
