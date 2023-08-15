<?php

use App\models\Payment;
use App\repository\PaymentRepository;
use App\repository\PostRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

function paymentRoutes($app) {

    $app->post('/payment/{post_id}', function (Request $request, Response $response, array $args) {
        $data = $request->getParsedBody();
        $postId = $args['post_id'];
        $payment = new Payment(
            null,
            $data['fromUserId'],
            $data['toUserId'],
            $data['amount'],
            $data['method'],
        );

        $paymentRepository = new PaymentRepository();
        $postRepository = new PostRepository();
        $payment = $paymentRepository->create($payment);
        $postRepository->delete($postId);

        $response->getBody()->write(json_encode(['message' => 'Payment created successfully!']));
        return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus(200);
    });

    $app->get('/payment/list', function (Request $request, Response $response, array $args) {
        $paymentRepository = new PaymentRepository();
        $payment = $paymentRepository->findAll();

        if ($payment) {
            $response->getBody()->write(json_encode($payment));
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(200);
        }

        $response->getBody()->write(json_encode(['error' => 'Payment not found']));
        return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus(404);
    });

}
