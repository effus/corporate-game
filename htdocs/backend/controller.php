<?php

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/db.php";

class Controller {

    const ROUND_CREATED = 0;
    const ROUND_PLAYED = 1;
    const ROUND_HAS_ANSWER = 2;
    const ROUND_FINISHED = 3;

    const GAME_TYPE_WHOS_FIRST = 1;
    const GAME_TYPE_RANDOM = 2;
    
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
        if ($game) {

            $rounds = $this->db->getAllRounds($game['id']);
            $teamsCount = $this->db->getTeamsCountInGame($game['id']);
            if (count($rounds) > 0 || $teamsCount > 0) {
                throw new Exception('Игра уже началась, дождитесь следующей');
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if ($_GET['action'] === 'new') {

                    if ($_SESSION['id']) {
                        $this->db->unlinkGamerFromGames($_SESSION['id']);
                    }

                    $gamer = mb_substr(trim($_POST['gamer']), 0, 100);
                    $userId = $this->db->registerGamer($gamer, $game['id']);
                    $_SESSION['is_gamer'] = true;
                    $_SESSION['id'] = $userId;
                    $_SESSION['name'] = $gamer;
                    $_SESSION['team'] = null;
                    $_SESSION['game'] = $game['id'];
                    
                } else if ($_GET['action'] === 'connect') {
                    $this->db->connectGamer($game['id'], $_SESSION['id']);
                    $_SESSION['game'] = $game['id'];
                }

                if (intval($game['type']) === self::GAME_TYPE_WHOS_FIRST) {
                    header('Location: /?view=team');
                    return;   
                } else if (intval($game['type']) === self::GAME_TYPE_RANDOM) {
                    header('Location: /?view=random');
                    return;   
                } else {
                    throw new Exception('Ошибка в игре: неизвестный тип игры');
                }
                
            }
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
        $rounds = $this->db->getAllRounds($game['id']);
        $lastRound = null;
        $lastWinner = null;
        if (count($rounds) > 0) {
            $lastRound = reset($rounds);
            if ($lastRound['winner_id']) {
                $lastWinner = $this->db->getGamer($lastRound['winner_id']);

            } else if ( in_array(intval($lastRound['state']), [self::ROUND_PLAYED, self::ROUND_HAS_ANSWER]) ) {
                header('Location: /view=answer');
                die();
            }
        }
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
        if (!$game || ($game && $game['id'] !== $_SESSION['game'])) {
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

    public function actionRandom()
    {
        if (!isset($_SESSION['is_gamer'])) {
            throw new Exception('Вы не зарегистрированы как участник');
        }
        $game = $this->db->getCurrentGame();
        if (!$game) {
            header('Location: /');
            die();
        }
        $view = 'random';
        include __DIR__ . "/view/layout.php";
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
     * получение статуса раунда в рандомайзере
     * @return void
     */
    public function actionGetRoundRandomizerState() 
    {
        if (!isset($_SESSION['is_gamer'])) {
            throw new Exception('Вы не зарегистрированы как участник');
        }
        $game = $this->db->getCurrentGame();
        if (!$game) {
            die(json_encode([
                'gameFinished' => true,
                'roundState' => 3,
                'gamersCount' => 0,
                'gamer' => null
            ]));
        }
        $round = $this->db->getCurrentRound($game['id']);
        $gamer = null;
        if ($round) {
            $roundState = intval($round['state']);
            if ($roundState === self::ROUND_HAS_ANSWER) {
                $answer = $this->db->getCurrentAnswer($round['id']);
                $gamer = $answer['gamer_name'];
            }
        } else {
            $game = $this->db->getCurrentGame();
            if ($game['finished_at']) {
                $roundState = 3;
            } else {
                $roundState = 0;
            }
        }
        echo json_encode([
            'roundState' => $roundState,
            'gamersCount' => $this->db->getGamersCountForGame($_SESSION['game']),
            'gamer' => $gamer
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

    public function actionAdminEndCurrentGame()
    {
        if (!isset($_SESSION['is_admin'])) {
            throw new Exception('Вы не админ');
        }
        echo json_encode([
            'result' => $this->db->endCurrentGame()
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
        $teamNames = [];
        foreach($chunked as $chunk) {
            $teamName = $this->getRandomTeamName();
            $exists = in_array($teamName, $teamNames);
            if ($exist) {
                $teamName = $teamName . ' ' . round(100, 999);
            }
            $teamId = $this->db->newTeam($teamName);
            $teamNames[] = $teamName;
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
        echo json_encode([
            'result' => $this->db->applyCurrentAnswer($round['id'])
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

    /**
     * @return void
     */
    public function actionAdminRunRandom()
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
        $teamList = $this->db->getTeams($game['id']);
        // нужно ли исключать уже выбираемых ранее?
        if (count($teamList) === 0) {
            throw new Exception('Нет игроков');
        }
        $i = rand(0,count($teamList) - 1);
        $choosenOne = $teamList[$i];
        $this->db->insertAnswer($choosenOne['gamer_id'], $round['id']);
        echo json_encode([
            'result' => $this->db->insertAnswer($choosenOne['gamer_id'], $round['id'])
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
            $teamId = $row['id'] ? $row['id'] : -1;
            if (!$result[$teamId]) {
                $result[$teamId] = [
                    'name' => $row['name'] ? $row['name'] : 'без команды',
                    'scores' => $row['scores'] ? $row['scores'] : 0,
                    'members' => []
                ];
            }
            $result[$teamId]['members'][] = [
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
            self::ROUND_CREATED => 'Готовимся к следующему раунду',
            self::ROUND_PLAYED => 'Внимание, вопрос!',
            self::ROUND_HAS_ANSWER => 'Есть ответ!',
            self::ROUND_FINISHED => 'Раунд завершен'
        ];
        $game = $this->db->getCurrentGame();
        $round = null;
        $state = 'Ждем начала игры';
        $roundState = 0;
        $answer = null;
        $info = '';
        if ($game) {
            $round = $this->db->getCurrentRound($game['id']);
            $state = $states[self::ROUND_CREATED];
            if ($round) {
                $roundState = intval($round['state']);
                $state = $states[$roundState] ? $states[$roundState] : 'Пауза';
                if ($round['current_answer_id']) {
                    $answer = $this->db->getCurrentAnswer($round['id']);
                }
            }
            if (!$round) {
                $gamersCount = $this->db->getGamersCountForGame($game['id']);
                $info = 'Игроков учавствует: ' . $gamersCount;
            }
        } else {
            $games = $this->db->getAllGames();
            if (count($games) > 0) {
                $game = reset($games);
                $state = 'Игра завершена';
            }
        }

        echo json_encode([
            'game' => 'Игра #' . $game['id'],
            'state' => $state,
            'hasAnswer' => $roundState === self::ROUND_HAS_ANSWER ? true : false,
            'team' => $answer ? $answer['team_name'] : '',
            'gamer' => $answer ? $answer['gamer_name'] : '',
            'gamersCount' => $gamersCount,
            'info' => $info,
            'result' => $roundState === self::ROUND_FINISHED && !empty($round['winner_id'])
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
