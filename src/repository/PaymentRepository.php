<?php
// src/Repositories/UserRepository.php

namespace App\repository;

use App\models\User;
use App\database\Connection;
use App\Models\Payment;

class PaymentRepository {
    private $collection;

    public function __construct() {
        
        $connection = Connection::getInstance();
        $this->collection = $connection->getDatabase()->selectCollection('payments');
    }

    public function create(Payment $payment): void {
        $document = [
            'fromUserId' => $payment->fromUserId,
            'toUserId' => $payment->toUserId,
            'amount' => $payment->amount,
            'method' => $payment->method,
        ];
        $this->collection->insertOne($document);
    }

    // Read
    public function findAll(): array {
        $cursor = $this->collection->find();
        $payments = [];
    
        foreach ($cursor as $document) {
            $payment = new Payment(
                (string) $document['_id'],
                $document['from_user_id'],
                $document['to_user_id'],
                $document['amount'],
                $document['method']
            );
            $payments[] = $payment;
        }
    
        return $payments;
    }
    
}
