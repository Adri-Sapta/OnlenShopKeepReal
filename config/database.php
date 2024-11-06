<?php

require_once __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;

class Database {
    private $client;
    private $database;

    public function __construct() {
        $this->client = new Client("mongodb://localhost:27017");
        $this->database = $this->client->KeepReal; // Sesuaikan nama database di sini
    }

    public function getDatabase() {
        return $this->database;
    }

    // Fungsi untuk mendapatkan koleksi
    public function getCollection($collectionName) {
        return $this->database->$collectionName;
    }

    // Fungsi untuk mengambil data dari koleksi
    public function getData($collectionName, $filter = [], $options = []) {
        $collection = $this->getCollection($collectionName);
        return $collection->find($filter, $options);
    }
}

?>