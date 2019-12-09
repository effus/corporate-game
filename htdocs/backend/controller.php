<?php

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/db.php";

class Controller {

    private $view = [
        'default' => 'actionMain'
    ];

    private $config;
    private $db;
    private $error;

    public function __construct()
    {
        $page = $_GET['view'] ?? 'default';
        if (!isset($this->view[$page])) {
            throw new Exception('Page not found');
        }
        try {
            $this->config = new Config();
            $this->db = new Db($this->config->getDb());
            $action = $this->view[$page];
            $this->$action();
        } catch (Exception $e) {
            $this->error = $e;
            $this->actionError();
        }
        
    }

    public function actionMain()
    {
        $view = 'main';
        include __DIR__ . "/view/layout.php";
    }

    public function actionError()
    {
        var_dump($this->error);
    }
}