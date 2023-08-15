<?php
// src/Models/User.php

namespace App\models;

class User {
    public ?string $id = null;
    public $profilePicture;
    public string $username;
    public string $email;
    public string $password; // Lembre-se de armazenar hashes, não senhas puras!
    public array $favoritePosts; // Lista de IDs de posts favoritos
    public ?Address $address;
    public \DateTime $createdAt;

    public function __construct(?string $id, string $username, $profilePicture ,string $email, string $password, array $favoritePosts, ?Address $address) {
        $this->id = $id;
        $this->profilePicture = $profilePicture ?? null;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password; // Lembre-se de hashear antes de armazenar!
        $this->favoritePosts = $favoritePosts ?? [];
        $this->address = $address ?? null;
        $this->createdAt = new \DateTime();
    }
}
