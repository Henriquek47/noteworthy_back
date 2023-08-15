<?php
// src/Repositories/UserRepository.php

namespace App\repository;

use App\database\Connection;
use App\Models\Review;

class ReviewRepository {
    private $collection;

    public function __construct() {
        
        $connection = Connection::getInstance();
        $this->collection = $connection->getDatabase()->selectCollection('reviews');
    }

    public function findByPk(string $id): ?Review {
        $document = $this->collection->findOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
        
        if ($document) {
            $review = new Review($document['reviewerId'], $document['reviewedUserId'], $document['rating']);
            $review->id = (string) $document['_id'];
            $review->reviewedAt = new \DateTime($document['reviewedAt']->toDateTime()->format('Y-m-d H:i:s'));
            return $review;
        }

        return null;
    }

    public function create(Review $review): Review {
        $document = [
            'reviewerId' => $review->reviewerId,
            'reviewedUserId' => $review->reviewedUserId,
            'rating' => $review->rating,
            'reviewedAt' => $review->reviewedAt
        ];
        
        $insertResult = $this->collection->insertOne($document);
        
        if ($insertResult->getInsertedCount() === 1) {
            $review->id = (string) $insertResult->getInsertedId();
        }
        
        return $review;
    }

    
}
