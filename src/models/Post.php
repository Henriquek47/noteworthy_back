<?php
// src/Models/Post.php

namespace App\Models;

use App\models\Address;

class Post {
    public $id; 
    public $pictures;
    public string $authorId; // Referência ao usuário
    public string $title;
    public string $description;
    public string $instrument;
    public float $price;
    public string $shipping;
    public bool $status;
    public ?Address $address;
    public \DateTime $createdAt;

    public function __construct(?string $id, string $title, $pictures, string $description, string $authorId, string $instrument, float $price, string $shipping, bool $status, ?Address $address
        ) {
        $this->id = $id;
        $this->title = $title;
        $this->pictures = $pictures;
        $this->description = $description;
        $this->authorId = $authorId;
        $this->instrument = $instrument;
        $this->price = $price;
        $this->shipping = $shipping;
        $this->status = $status;
        $this->address = $address ?? null;
        $this->createdAt = new \DateTime();
    }
}
