<?php
// src/Repositories/UserRepository.php

namespace App\repository;

use App\models\User;
use App\models\Address;
use App\database\Connection;
use Path\To\UTCDateTime;
use DateTime;

class UserRepository
{
    private $collection;
    private $collectionToken;

    public function __construct()
    {
        $connection = Connection::getInstance();
        $this->collection = $connection->getDatabase()->selectCollection('users');
        $this->collectionToken = $connection->getDatabase()->selectCollection('users_auth');
    }

    public function save(User $user): User
    {
        $document = [
            'profilePicture' => $user->profilePicture,
            'username' => $user->username,
            'email' => $user->email,
            'password' => $user->password, // Lembre-se de hashear a senha antes de salvar!
            'favoritePosts' => $user->favoritePosts,
            'address' => $user->address ? $user->address->toArray() : null,
            'createdAt' => $user->createdAt->format('Y-m-d H:i:s'),
        ];
        $result = $this->collection->insertOne($document);

        $user->id = $result->getInsertedId();

        return $user;
    }

    public function delete(string $id): void
    {
        $objectId = new \MongoDB\BSON\ObjectId($id);
        $this->collection->deleteOne(['_id' => $objectId]);
    }


    public function findById(string $id): ?User
{
    $objectId = new \MongoDB\BSON\ObjectId($id);
    
    $document = $this->collection->findOne(['_id' => $objectId]);
    if (!$document) {
        return null;
    }

    if (isset($document['address'])) {
        $address = new Address(
            $document['address']['street'] ?? null, 
            $document['address']['city'] ?? null, 
            $document['address']['state'] ?? null,
            $document['address']['neighborhood'] ?? null, 
            $document['address']['number'] ?? null, 
            $document['address']['complement'] ?? null
        );
    } else {
        $address = null;
    }

    // Convertendo BSONArray para array nativo
    $favoritePostsArray = isset($document['favoritePosts']) ? $document['favoritePosts']->getArrayCopy() : [];

    $user = new User(
        $document['_id'],
        $document['username'],
        $document['profilePicture'],
        $document['email'],
        $document['password'],
        $favoritePostsArray,
        $address
    );

    return $user;
}


    public function update(User $user): void
    {
        $document = [
            'username' => $user->username,
            'profilePicture' => $user->profilePicture,
            'email' => $user->email,
            'password' => $user->password,
            'favoritePosts' => $user->favoritePosts,
            'address' => $user->address->toArray(), // Converta o Address para um array
            'createdAt' => $user->createdAt->format('Y-m-d H:i:s'),
        ];

        $objectId = new \MongoDB\BSON\ObjectId($user->id);

        $this->collection->updateOne(
            ['_id' => $objectId], // filtro
            ['$set' => $document] // operação de atualização
        );
    }

    public function storeUserToken($user, $token) {
        $expiresAt = (new DateTime('+1 hour'))->format('Y-m-d H:i:s'); // O token expira em 1 hora
        
        $document = [
            'user_id' => new \MongoDB\BSON\ObjectId($user->id),
            'token' => $token,
            'expires_at' => $expiresAt
        ];
    
        // Aqui, usaremos uma operação upsert para inserir um novo documento ou atualizar um existente
        $this->collectionToken->updateOne(
            ['user_id' => new \MongoDB\BSON\ObjectId($user->id)], 
            ['$set' => $document],
            ['upsert' => true]
        );
    }
    

    public function invalidateUserToken(string $token) {
        $user = $this->findByToken($token);
        $this->collectionToken->deleteOne(['user_id' => new \MongoDB\BSON\ObjectId($user->id)]);
    }

    public function findByToken($token) {
        $tokenDocument = $this->collectionToken->findOne(['token' => $token]);
    
        if (!$tokenDocument) {
            return null;
        }
    
        return $this->findById((string) $tokenDocument['user_id']);
    }
    
    public function isTokenExpired($tokenDocument) {
        $now = new DateTime();
        $expiresAt = DateTime::createFromFormat('Y-m-d H:i:s', $tokenDocument['expires_at']);
    
        return $now > $expiresAt;
    }
    
}
