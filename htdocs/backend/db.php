<?php

class Db {
    private $connection;

    public function __constructor($db) {
        $this->connection = new PDO('mysql:host=' . $db['server'].';dbname=' . $db['database'], $db['user'], $db['password']);
    }
}