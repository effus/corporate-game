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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $gamer = $_POST['gamer'];
            header('Location: /?view=team');
            return;
        }
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

    public function actionScreen()
    {
        $view = 'screen';
        include __DIR__ . "/view/layout.php";
    }

    public function actionAnswer()
    {
        $view = 'answer';
        include __DIR__ . "/view/layout.php";
    }

    public function actionTeam()
    {
        $view = 'team';
        include __DIR__ . "/view/layout.php";
    }

    public function actionExit()
    {
        header('Location: /');
    }

    public function actionGetMonitorScores()
    {
        $states = [
            'Вопрос',
            'Ждем ответ',
            'Отвечает'
        ];

        $result = null;
        $state_i = rand(0,2);
        $state = $states[$state_i];
        if ($state_i === 2 && rand(0,1) === 1) {
            $result = rand(0,1) === 1;
        }

        echo json_encode([
            'game' => 'Игра 1',
            'round' => '1',
            'state' => $state,
            'hasAnswer' => $state_i === 2 ? true : false,
            'team' => 'team 1',
            'result' => $result
        ]);
    }

    public function actionGetTeamState()
    {
        $ready = rand(0,5) === 3;
        echo json_encode([
            'teamCount' => rand(0,20),
            'ready' => $ready
        ]);
    }

    public function actionGetAnswerState()
    {
        $ready = rand(0,5) === 3;
        echo json_encode([
            'canAnswer' => rand(0,1) === 1,
            'hash' => md5(time())
        ]);
    }

    public function actionError()
    {
        $error = $this->error;
        $view = 'error';
        include __DIR__ . "/view/layout.php";
    }
}