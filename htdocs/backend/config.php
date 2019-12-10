<?php

class Config {
    private $db;
    private $domain;
    private $admin;

    public function __construct() {
        if (!file_exists(__DIR__ . '/config.json')) {
            throw new Exception('Config not found');
        }
        $config = json_decode(file_get_contents(__DIR__ . '/config.json'), true);
        $this->db = $config['db'];
        $this->domain = $config['domain'];
        $this->admin = $config['admin'];
    }

    public function getDb() {
        return $this->db;
    }

    public function getDomain() {
        return $this->domain;
    }

    public function getAdminHash() {
        return $this->admin['hash'];
    }
}