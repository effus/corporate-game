<?php

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/db.php";

class Controller {

    const ROUND_CREATED = 0;
    const ROUND_PLAYED = 1;
    const ROUND_HAS_ANSWER = 2;
    const ROUND_FINISHED = 3;
    
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
        $game = $this->db->getCurrentGame();
        if (!$game) {
            throw new Exception('Ни одной игры пока не начали, попробуйте позже');
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $gamer = mb_substr(trim($_POST['gamer']), 0, 100);
            $userId = $this->db->registerGamer($gamer, $game['id']);
            $_SESSION['is_gamer'] = true;
            $_SESSION['id'] = $userId;
            $_SESSION['team'] = null;
            $_SESSION['game'] = $game['id'];
            header('Location: /?view=team');
            return;
            
        }
        $view = 'main';
        include __DIR__ . "/view/layout.php";
    }
    
    /**
     * страница сбора команд
     */
    public function actionTeam()
    {
        if (!isset($_SESSION['is_gamer'])) {
            header('Location: /');
            die();
        }
        $game = $this->db->getCurrentGame();
        if (!$game) {
            header('Location: /');
            die();
        }
        $_SESSION['game'] = $game['id'];
        $view = 'team';
        include __DIR__ . "/view/layout.php";
    }
    
    /**
     * промежуточный этап, участник узнгает к какой команде определен и редиректится на ответы
     * @throws Exception
     */
    public function actionGetTeamState()
    {
        if (!isset($_SESSION['is_gamer'])) {
            throw new Exception('Вы не зарегистрированы как участник');
        }
        $gamersCount = $this->db->getGamersCountForGame($_SESSION['game']);
        $round = $this->db->getCurrentRound($_SESSION['game']);
        $_SESSION['round'] = null;
        if ($round) {
            $_SESSION['round'] = $round['id'];
        }
        $ready = ($round && $round['state'] === self::ROUND_PLAYED);
        echo json_encode([
            'gamersCount' => $gamersCount,
            'ready' => $ready
        ]);
    }
    
    /**
     * страница ответа
     */
    public function actionAnswer()
    {
        $round = $this->db->getCurrentRound($_SESSION['game']);
        if (!$round || $round['state'] === self::ROUND_FINISHED) {
            header('Location: /?view=team');
            die();
        }
        $view = 'answer';
        $answerHash = $this->getCurrentRoundHash();
        include __DIR__ . "/view/layout.php";
    }
    
    /**
     * получение статуса раунда игроками
     * - раунд запущен, ответа нет => roundState === self::ROUND_PLAYED
     * - раунд запущен, есть ответ => roundState === self::ROUND_HAS_ANSWER
     * - раунд завершен => roundState === self::ROUND_FINISHED
     * - запрос для другого раунда => roundState === self::ROUND_FINISHED
     * @throws Exception
     */
    public function actionGetRoundState()
    {
        if (!isset($_SESSION['is_gamer'])) {
            throw new Exception('Вы не зарегистрированы как участник');
        }
        if (!$_SESSION['round']) {
            throw new Exception('Раунд не начат');
        }
        $hash = $this->getCurrentRoundHash();
        $roundState = null;
        if ($_GET['hash'] !== $hash) {
            $roundState = self::ROUND_FINISHED;
        } else {
            $round = $this->db->getRound($_SESSION['round']);
            $roundState = intval($round['state']);
        }
        echo json_encode([
            'roundState' => $roundState,
            'currentAnswer' => $round['current_answer_id'],
            'hash' => $hash,
        ]);
    }
    
    /**
     * отправка ответа игроком
     * - если его ответ первый, answer_id === current_answer_id
     * - если его ответ не первый, answer_id !== current_answer_id
     * - если его ответ после завершения раунда, answer_id !== current_answer_id и round_state === ROUND_FINISHED (3)
     * - если его ответ для другого раунда (не совпали хэши), round_state === ROUND_FINISHED
     */
    public function actionSendAnswer()
    {
        if (!isset($_SESSION['is_gamer'])) {
            throw new Exception('Вы не зарегистрированы как участник');
        }
        if (!$_SESSION['round']) {
            throw new Exception('Раунд не начат');
        }
        $hash = $this->getCurrentRoundHash();
        if ($hash !== $_GET['hash']) {
            echo json_encode([
                'hash' => $hash,
                'round_state' => self::ROUND_FINISHED,
            ]);
        }
        $result = $this->db->insertAnswer($_SESSION['id'], $_SESSION['round']);
        echo json_encode([
            'hash' => $hash,
            'answerId' => $result['answer_id'],
            'roundState' => $result['round_state'],
            'currentAnswerId' => $result['current_answer_id'],
        ]);
    }
    
    public function actionExit()
    {
        header('Location: /');
    }
    
    /**
     * страница админки
     */
    public function actionAdmin()
    {
        if (!isset($_SESSION['is_admin'])) {
            header('Location: /?view=login');
            die();
        }
        $view = 'admin';
        $games = $this->db->getAllGames();
        $currentGame = $this->db->getCurrentGame();
        $rounds = [];
        $currentRound = null;
        if ($currentGame) {
            $rounds = $this->db->getAllRounds($currentGame['id']);
            $currentRound = $this->db->getCurrentRound($currentGame['id']);
        }
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
    
    /**
     * новая игра в даминке
     */
    public function actionNewGame()
    {
        if (!isset($_SESSION['is_admin'])) {
            throw new Exception('Вы не админ');
        }
        $gameId = $this->db->newGame($_GET['type'] ?? 1);
        echo json_encode([
            'result' => $gameId
        ]);
    }
    
    /**
     * создание раунда в админке
     */
    public function actionNewRound()
    {
        if (!isset($_SESSION['is_admin'])) {
            throw new Exception('Вы не админ');
        }
        $game = $this->db->getCurrentGame();
        $roundId = intval($this->db->newRound($game['id']));
        $roundsRows = $this->db->getAllRounds($game['id']);
        $rounds = [];
        foreach($roundsRows as $row) {
            $rounds[] = [
                'id' => intval($row['id'])
            ];
        }
        echo json_encode([
            'result' => $roundId,
            'rounds' => $rounds
        ]);
    }
    
    /**
     * запуск раунда в админке
     */
    public function actionStartRound()
    {
        if (!isset($_SESSION['is_admin'])) {
            throw new Exception('Вы не админ');
        }
        $game = $this->db->getCurrentGame();
        if (!$game) {
            throw new Exception('Не начата игра');
        }
        $round = $this->db->getCurrentRound($game['id']);
        if (!$round) {
            throw new Exception('Не начат раунд');
        }
        $this->db->startRound($round['id']);
        echo json_encode([
            'result' => true,
            'round' => $round
        ]);
    }
    
    /**
     * статус раунда в админке
     */
    public function actionGetAdminCheckAnswer()
    {
        if (!isset($_SESSION['is_admin'])) {
            throw new Exception('Вы не админ');
        }
        $roundId = $_GET['round_id'] ?? null;
        if (!$roundId) {
            throw new Exception('Не передан раунд');
        }
        $firstAnswer = $this->db->getCurrentAnswer($roundId);
        $answer = null;
        if ($firstAnswer) {
            $answer = [
                'id' => $firstAnswer['id'],
                'round_id' => $firstAnswer['round_id'],
                'gamer_id' => $firstAnswer['gamer_id'],
                'gamer_name' => $firstAnswer['gamer_name'],
                'team_id' => $firstAnswer['team_id'],
                'team_name' => $firstAnswer['team_name'],
            ];
        }
        echo json_encode([
            'result' => true,
            'answer' => $answer
        ]);
    }

    public function actionError()
    {
        $error = $this->error;
        $view = 'error';
        include __DIR__ . "/view/layout.php";
    }
    
    public function actionScreen()
    {
        $view = 'screen';
        include __DIR__ . "/view/layout.php";
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
    
    
    /**
     * @return string
     */
    private function getCurrentRoundHash()
    {
        return md5($_SESSION['round'] . $_SESSION['id']);
    }
}
