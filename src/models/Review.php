<?php
// src/Models/Review.php

namespace App\Models;

class Review {
    public string $id;
    public string $reviewerId; // Referência ao usuário
    public string $reviewedUserId; // O usuário sendo revisado
    public float $rating; // Por exemplo, de 1 a 5
    public \DateTime $reviewedAt;

    public function __construct(string $reviewerId, string $reviewedUserId, float $rating) {
        $this->reviewerId = $reviewerId;
        $this->reviewedUserId = $reviewedUserId;
        $this->rating = $rating;
        $this->reviewedAt = new \DateTime();
    }
}
