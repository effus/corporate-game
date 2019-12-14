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
            throw new Exception('Ни одной игры пока не начали, зайдите попозже');
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
        $game = $this->db->getCurrentGame();
        if ($game['id'] !== $_SESSION['game']) {
            die(json_encode([
                'gamersCount' => 0,
                'ready' => false,
                'team' => null
            ]));    
        }
        $gamersCount = $this->db->getGamersCountForGame($_SESSION['game']);
        $round = $this->db->getCurrentRound($_SESSION['game']);
        $gamer = $this->db->getGamer($_SESSION['id']);
        $_SESSION['round'] = null;
        if ($round) {
            $_SESSION['round'] = $round['id'];
        }
        $members = [];
        $allowChangeName = false;
        if ($gamer['team_id']) {
            $_SESSION['team'] = [
                'id' => $gamer['team_id'],
                'name' => $gamer['team_name']
            ];
            $memberRows = $this->db->getTeamMembers($gamer['team_id']);
            foreach($memberRows as $row) {
                $members[] = $row['name'];
            }
            $allowChangeName = intval($gamer['name_changed_game']) !== intval($_SESSION['game']) || intval($gamer['name_changed_game']) === 0;
        }
        $ready = ($round && $round['state'] === self::ROUND_PLAYED);
        echo json_encode([
            'gamersCount' => $gamersCount,
            'ready' => $ready,
            'team' => $gamer['team_name'],
            'members' => $members,
            'allowChangeName' => $allowChangeName
        ]);
    }

    public function actionSetTeamName()
    {
        if (!isset($_SESSION['is_gamer'])) {
            throw new Exception('Вы не зарегистрированы как участник');
        }
        $gamer = $this->db->getGamer($_SESSION['id']);
        if (!$gamer['team_id']) {
            throw new Exception('Команда пока не назначена');
        }
        $teamName = mb_substr(trim($_GET['name']),0,50);
        if (!$teamName) {
            throw new Exception('Неподходящее имя');
        }
        $this->db->changeTeamName($gamer['team_id'], $teamName, $gamer['game_id']);
        echo json_encode([
            'result' => true
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
        $answer = $this->db->getCurrentAnswer($round['id']);
        $myAnswer = $this->db->getGamerAnswer($round['id'], $_SESSION['id']);
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
        $roundAnswer = null;
        if ($_GET['hash'] !== $hash) {
            $roundState = self::ROUND_FINISHED;
        } else {
            $round = $this->db->getRound($_SESSION['round']);
            $roundState = intval($round['state']);
            $roundAnswer = intval($round['current_answer_id']);
        }
        echo json_encode([
            'roundState' => $roundState,
            'currentAnswer' => $roundAnswer,
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

    /*
     * подключившиеся к игре юзеры
     */
    public function actionGetGamersList()
    {
        if (!isset($_SESSION['is_admin'])) {
            throw new Exception('Вы не админ');
        }
        $currentGame = $this->db->getCurrentGame();
        $result = [];
        $teamsCount = $this->db->getTeamsCountInGame($currentGame['id']);
        if ($currentGame) {
            $gamers = $this->db->getAllGamersOfGame($currentGame['id']);
            foreach($gamers as $gamer) {
                $result[] = $gamer['name'];
            }
        }
        echo json_encode([
            'gamers' => $result,
            'teamsCount' => $teamsCount
        ]);
    }

    public function actionCreateCommands()
    {
        if (!isset($_SESSION['is_admin'])) {
            throw new Exception('Вы не админ');
        }
        $commandCount = $_GET['count'] ?? 2;
        if ($commandCount < 2) {
            throw new Exception('Такое число команд невозможно');
        }
        $currentGame = $this->db->getCurrentGame();
        $gamersRows = $this->db->getAllGamersOfGame($currentGame['id']);
        $gamerIds = [];
        foreach($gamersRows as $row) {
            $gamerIds[] = $row['id'];
        }
        shuffle($gamerIds);
        $chunked = array_chunk($gamerIds, ceil(count($gamerIds) / $commandCount));
        foreach($chunked as $chunk) {
            $teamId = $this->db->newTeam($this->getRandomTeamName());
            foreach($chunk as $gamerId) {
                $this->db->setTeamForGamer($teamId, $gamerId);
            }
        }
        echo json_encode([
            'result' => true
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
        $round = $this->db->getRound($roundId);
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
            'roundState' => intval($round['state']),
            'answer' => $answer
        ]);
    }

    /**
     * ответ корректен
     * @return void
     */
    public function actionAdminApplyCurrentAnswer() 
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
        $this->db->applyCurrentAnswer($round['id']);
        echo json_encode([
            'result' => true
        ]);
    }

    /**
     * ответ не корректен
     * @return void
     */
    public function actionAdminDenyCurrentAnswer()
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
        echo json_encode([
            'result' => $this->db->denyCurrentAnswer($round['id'])
        ]);
    }
    
    /**
     * нет ответа
     * @return void
     */
    public function actionAdminNoAnswerInRound()
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
        echo json_encode([
            'result' => $this->db->setNoAnswerInRound($round['id'])
        ]);
    }
    
    /*
    * список команд в игре
    */ 
    public function actionGetTeamList() 
    {
        if (!isset($_SESSION['is_admin'])) {
            throw new Exception('Вы не админ');
        }
        $game = $this->db->getCurrentGame();
        if (!$game) {
            throw new Exception('Не начата игра');
        }
        $teamList = $this->db->getTeams($game['id']);
        $result = [];
        $team = [];
        foreach($teamList as $row) {
            if (!$result[$row['id']]) {
                $result[$row['id']] = [
                    'name' => $row['name'],
                    'scores' => $row['scores'],
                    'members' => []
                ];
            }
            $result[$row['id']]['members'][] = [
                'id' => $row['gamer_id'],
                'name' => $row['gamer_name'],
                'scores' => $row['gamer_scores']
            ];
        }
        echo json_encode([
            'result' => $result
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

    private function getRandomTeamName() 
    {
        $firstWords = ['Серые', 'Зеленые', 'Красные', 'Черные', 'Оранжевые', 'Синие', 'Розовые', 'Белые', 'Желтые', 'Полосатые', 'Пятнистые'];
        $lastWords = ['Зайцы', 'Волки', 'Медведи', 'Ежи', 'Косули', 'Бобры', 'Суслики', 'Еноты', 'Белки', 'Чайки', 'Кошки', 'Совы', 'Утки', 'Моржи'];
        return $firstWords[rand(0, count($firstWords) - 1)] . ' ' . $lastWords[rand(0, count($lastWords)-1)];
    }
}
