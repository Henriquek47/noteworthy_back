<?php
namespace App\repository;

use MongoDB\Client;
use MongoDB\Collection;
use App\Models\Post;
use MongoDB\BSON\ObjectId;
use App\database\Connection;
use App\models\Address;

class PostRepository {

    private Collection $collection;
    private $gridFS;

    public function __construct() {
        $connection = Connection::getInstance();
        $this->collection = $connection->getDatabase()->selectCollection('posts');
        $this->gridFS = $connection->getDatabase()->selectGridFSBucket();

    }
    // CREATE
    public function create(Post $post): void {
        $document = [
            'title' => $post->title,
            'pictures' => $post->pictures,
            'description' => $post->description,
            'authorId' => $post->authorId,
            'instrument' => $post->instrument,
            'price' => $post->price,
            'shipping' => $post->shipping,
            'status' => $post->status,
            'address' => $post->address,
            'createdAt' => $post->createdAt->format('Y-m-d H:i:s')
        ];
        $this->collection->insertOne($document);
    }

    // READ by ID
    public function findById(string $id): ?Post {
        $document = $this->collection->findOne(['_id' => new ObjectId($id)]);
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
        return new Post(
            (string) $document['_id'],
            $document['title'],
            $document['pictures'],
            $document['description'],
            $document['authorId'],
            $document['instrument'],
            $document['price'],
            $document['shipping'],
            $document['status'],
            $address,

        );
    }

    public function findAll(): array {
        $cursor = $this->collection->find([], [
            'sort' => ['createdAt' => -1] // -1 indica ordenação decrescente
        ]);
    
        $posts = [];
        foreach ($cursor as $document) {
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
    
    
            $posts[] = new Post(
                (string) $document['_id'],
                $document['title'],
                $document['pictures'],
                $document['description'],
                $document['authorId'],
                $document['instrument'],
                $document['price'],
                $document['shipping'],
                $document['status'],
                $address
            );
        }
        
        return $posts;
    }
    

    // UPDATE
    public function update(Post $post): void {
        $document = [
            'title' => $post->title,
            'pictures' => $post->pictures,
            'description' => $post->description,
            'authorId' => $post->authorId,
            'instrument' => $post->instrument,
            'price' => $post->price,
            'shipping' => $post->shipping,
            'status' => $post->status,
            'createdAt' => $post->createdAt->format('Y-m-d H:i:s')
        ];
        $this->collection->updateOne(['_id' => new ObjectId($post->id)], ['$set' => $document]);
    }

    // DELETE
    public function delete(string $id): void {
        $this->collection->deleteOne(['_id' => new ObjectId($id)]);
    }

}
