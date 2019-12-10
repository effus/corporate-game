<?php

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/db.php";

class Controller {

    private $config;
    private $db;
    private $error;

    public function __construct()
    {
        session_start();
        $page = $_GET['view'] ?? 'main';
        try {
            $method = 'action' . ucfirst($page);
            if (!method_exists($this, $method)) {
                throw new Exception('Page not found');
            }
            $this->config = new Config();
            $this->db = new Db($this->config->getDb());
            $this->$method();

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

    public function actionAdmin()
    {
        if (!isset($_SESSION['is_admin'])) {
            header('Location: /?view=login');
            die();
        }
        $view = 'admin';
        include __DIR__ . "/view/layout.php";
    }

    public function actionLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $login = $_POST['login'];
            $pass = $_POST['pass'];
            if (md5($login.$pass) === $this->config->getAdminHash()) {
                $_SESSION['is_admin'] = true;
                header('Location: /?view=admin');
            } else {
                throw new Exception('Incorrect identity');
            }
        } else {
            $view = 'login';
            include __DIR__ . "/view/layout.php";
        }
    }

    public function actionError()
    {
        $error = $this->error;
        $view = 'error';
        include __DIR__ . "/view/layout.php";
    }
}