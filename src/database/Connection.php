<?php
namespace App\database;
require 'vendor/autoload.php';
use MongoDB\Client;

class Connection {
    private static $instance = null;
    private $client;
    private $database;

    private function __construct() {
        $this->client = new Client("mongodb://localhost:27017");
        $this->database = $this->client->teste;
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getDatabase() {
        return $this->database;
    }
}
