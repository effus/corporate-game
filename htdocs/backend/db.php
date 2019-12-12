<?php

class Db {
    private $connection;

    public function __construct($db)
    {
        $this->connection = new PDO('mysql:host=' . $db['host'].';dbname=' . $db['database'], $db['user'], $db['password']);
    }
    
    /**
     * @return PDOStatement
     * @throws Exception
     */
    public function getAllGames()
    {
        try {
            return $this->connection->query('SELECT * FROM games ORDER BY id DESC', PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Ошибка при получении списка игр');
        }
    }
    
    /**
     * @return mixed
     * @throws Exception
     */
    public function getCurrentGame()
    {
        try {
            $statement = $this->connection->query('SELECT * FROM games WHERE finished_at IS NULL LIMIT 1', PDO::FETCH_ASSOC);
            foreach ($statement as $row) {
                return $row;
            }
        } catch (Exception $e) {
            throw new Exception('Ошибка при получении текущей игры');
        }
    }
    
    /**
     * @param int $type
     * @return string
     * @throws Exception
     */
    public function newGame($type = 1)
    {
        try {
            $this->connection->prepare('UPDATE rounds SET finished_at = NOW(), state = 10 WHERE finished_at IS NULL')->execute();
            $this->connection->prepare('UPDATE games SET finished_at = NOW() WHERE finished_at IS NULL')->execute();
            $stmt = $this->connection->prepare('INSERT INTO games (`type`) VALUES (' . intval($type) . ')');
            if ($stmt->execute() === false) {
                throw new Exception(json_encode($stmt->errorInfo()));
            }
            return $this->connection->lastInsertId();
        } catch (Exception $e) {
            throw new Exception('Ошибка при создании игры');
        }
    }
    
    /**
     * @param $name
     * @return string
     * @throws Exception
     */
    public function registerGamer($name, $gameId)
    {
        try {
            $stmt = $this->connection->prepare('INSERT INTO gamers (name, game_id) VALUES (:name, :game_id)');
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':game_id', $gameId);
            if ($stmt->execute() === false) {
                throw new Exception(json_encode($stmt->errorInfo()));
            }
            return $this->connection->lastInsertId();
        } catch (Exception $e) {
            throw new Exception('Ошибка при регистрации пользователя');
        }
    }
    
    /**
     * @param $gameId
     * @return mixed
     * @throws Exception
     */
    public function getGamersCountForGame($gameId)
    {
        try {
            $statement = $this->connection->query('SELECT count(*) c FROM gamers WHERE game_id = ' . intval($gameId), PDO::FETCH_ASSOC);
            foreach ($statement as $row) {
                return intval($row['c']);
            }
        } catch (Exception $e) {
            throw new Exception('Ошибка при получении списка игроков');
        }
    }

    public function getAllGamersOfGame($gameId)
    {
        try {
            return $this->connection->query('SELECT * FROM gamers WHERE game_id = ' . intval($gameId), PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            throw new Exception('Ошибка при получении списка игроков');
        }
    }

    /**
     * @param [type] $gamerId
     * @return void
     */
    public function getGamer($gamerId)
    {
        try {
            $rows = $this->connection->query('
                SELECT g.*, t.name as team_name, t.scores as team_score, t.name_changed_game
                FROM gamers g 
                LEFT JOIN teams t ON t.id = g.team_id
                WHERE g.id = ' . intval($gamerId), PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                return $row;
            }
            
        } catch (Exception $e) {
            throw new Exception('Ошибка при получении списка игроков');
        }
    }

    /**
     * создание команды
     * @param [type] $name
     * @return void
     */
    public function newTeam($name)
    {
        try {
            $stmt = $this->connection->prepare('INSERT INTO teams (`name`, `scores`) VALUES (:name, 0)');
            $stmt->bindParam(':name', $name);
            if ($stmt->execute() === false) {
                throw new Exception(json_encode($stmt->errorInfo()));
            }
            return $this->connection->lastInsertId();
        } catch (Exception $e) {
            throw new Exception('Ошибка при создании команды: ' . $e->getMessage());
        }
    }

    /**
     * @param [type] $gamerId
     * @param [type] $teamId
     * @return void
     */
    public function setTeamForGamer($teamId, $gamerId)
    {
        try {
            $stmt = $this->connection->prepare('UPDATE gamers SET team_id = :teamId WHERE id = :id');
            $stmt->bindParam(':teamId', $teamId);
            $stmt->bindParam(':id', $gamerId);
            if ($stmt->execute() === false) {
                throw new Exception(json_encode($stmt->errorInfo()));
            }
        } catch (Exception $e) {
            throw new Exception('Ошибка при подключении игрока к команде');
        }
    }

    /**
     * @param [type] $gameId
     * @return void
     */
    public function getTeamsCountInGame($gameId)
    {
        try {
            $rows = $this->connection->query('SELECT count(distinct team_id) as c FROM gamers WHERE game_id = ' . intval($gameId), PDO::FETCH_ASSOC);
            foreach($rows as $row) {
                return intval($row['c']);
            }
            
        } catch (Exception $e) {
            throw new Exception('Ошибка при получении списка игроков');
        }
    }

    /**
     * @param [type] $teamId
     * @return void
     */
    public function getTeamMembers($teamId)
    {
        try {
            return $this->connection->query('SELECT * FROM gamers WHERE team_id = ' . intval($teamId), PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            throw new Exception('Ошибка при получении списка игроков');
        }
    }

    /**
     * @param [type] $teamId
     * @param [type] $name
     * @return void
     */
    public function changeTeamName($teamId, $name, $gameId) {
        try {
            $stmt = $this->connection->prepare('UPDATE teams SET name = :name, name_changed_game = :gameId WHERE id = :id');
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':gameId', $gameId);
            $stmt->bindParam(':id', $teamId);
            if ($stmt->execute() === false) {
                throw new Exception(json_encode($stmt->errorInfo()));
            }
        } catch (Exception $e) {
            throw new Exception('Ошибка при создании игры');
        }
    }
    
    /**
     * @param $gameId
     * @return array
     * @throws Exception
     */
    public function getCurrentRound($gameId)
    {
        try {
            $statement = $this->connection->query('SELECT * FROM rounds WHERE finished_at IS NULL AND game_id = ' . intval($gameId), PDO::FETCH_ASSOC);
            foreach ($statement as $row) {
                $row['state'] = intval($row['state']);
                return $row;
            }
        } catch (Exception $e) {
            throw new Exception('Ошибка при получении данных текущего раунда');
        }
    }
    
    /**
     * @param $gameId
     * @return PDOStatement
     * @throws Exception
     */
    public function getAllRounds($gameId)
    {
        try {
            return $this->connection->query('SELECT * FROM rounds WHERE game_id = ' . intval($gameId) . ' ORDER BY id DESC', PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Ошибка при получении списка раундов');
        }
    }
    
    /**
     * @param $gameId
     * @return string
     * @throws Exception
     */
    public function newRound($gameId)
    {
        try {
            $this->connection->prepare('UPDATE rounds SET finished_at = NOW(), state = 10 WHERE finished_at IS NULL')->execute();
            $stmt = $this->connection->prepare('INSERT INTO rounds (`game_id`, `state`) VALUES (' . intval($gameId) . ', 0)');
            if ($stmt->execute() === false) {
                throw new Exception(json_encode($stmt->errorInfo()));
            }
            return $this->connection->lastInsertId();
        } catch (Exception $e) {
            throw new Exception('Ошибка при создании игры');
        }
    }
    
    /**
     * @param $roundId
     * @return bool
     * @throws Exception
     */
    public function startRound($roundId)
    {
        try {
            $stmt = $this->connection->prepare('UPDATE rounds SET state = 1 WHERE id = :id');
            $stmt->bindParam(':id', $roundId);
            if ($stmt->execute() === false) {
                throw new Exception(json_encode($stmt->errorInfo()));
            }
            return true;
        } catch (Exception $e) {
            throw new Exception('Ошибка при запуске раунда');
        }
    }
    
    /**
     * @param $roundId
     * @return mixed
     * @throws Exception
     */
    public function getRound($roundId)
    {
        try {
            $rows = $this->connection->query('SELECT * FROM rounds WHERE id = ' . intval($roundId), PDO::FETCH_ASSOC);
            foreach($rows as $row) {
                return $row;
            }
        } catch (Exception $e) {
            throw new Exception('Ошибка при получении раунда');
        }
    }
    
    /**
     * @param $gamerId
     * @param $roundId
     * @return array
     * @throws Exception
     */
    public function insertAnswer($gamerId, $roundId)
    {
        $this->connection->beginTransaction();
        try {
            $stmt = $this->connection->prepare('INSERT INTO answers(gamer_id, round_id, dt) VALUES(:gamer_id, :round_id, NOW())');
            $stmt->bindParam(':gamer_id', $gamerId);
            $stmt->bindParam(':round_id', $roundId);
            if ($stmt->execute() === false) {
                throw new Exception(json_encode($stmt->errorInfo()));
            }
            $answerId = intval($this->connection->lastInsertId());
           
            $stmt = $this->connection->prepare('UPDATE rounds SET current_answer_id = :answer, state = 2 WHERE current_answer_id IS NULL AND id = :round_id');
            $stmt->bindParam(':answer', $answerId);
            if ($stmt->execute() === false) {
                throw new Exception(json_encode($stmt->errorInfo()));
            }
            $this->connection->commit();
            
            $currentRound = $this->getRound($roundId);
            
            return [
                'answer_id' => $answerId,
                'round_state' => $currentRound['state'],
                'current_answer_id' => $currentRound['current_answer_id'],
            ];
            
        } catch (Exception $e) {
            $this->connection->rollBack();
            throw new Exception('Ошибка при запуске раунда');
        }
    }
    
    /**
     * @param $roundId
     * @return PDOStatement
     * @throws Exception
     */
    public function getCurrentAnswer($roundId)
    {
        try {
            $rows = $this->connection->query('
                SELECT a.id, r.id as round_id, g.id as gamer_id, g.name as gamer_name, t.id as team_id, t.name as team_name
                FROM answers a
                INNER JOIN rounds r ON r.current_answer_id = a.id
                INNER JOIN gamers g ON g.id = a.gamer_id
                INNER JOIN teams t ON t.id = g.team_id
                WHERE r.id = ' . intval($roundId), PDO::FETCH_ASSOC);
            foreach($rows as $row) {
                return $row;
            }
        } catch (Exception $e) {
            throw new Exception('Ошибка при получении списка раундов');
        }
    }
}
