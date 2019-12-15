<?php


namespace Steampunk;


class Games extends Table
{
    private $users;
    public function __construct(Site $site)
    {
        parent::__construct($site, "game");
        $this->users = new Users($site);
    }
    public function json_nullify($gameId) {
        $sql = <<<SQL
UPDATE  $this->tableName 
SET  `json` = NULL
WHERE id =?
SQL;
        $pdo = $this->pdo();
        $statement = $pdo->prepare($sql);
        $statement->execute(array($gameId));
    }

    public function json_empty($gameId) {
        $sql = <<<SQL
select json from $this->tableName
where id = ?
SQL;
        $pdo = $this->pdo();
        $statement = $pdo->prepare($sql);
        $statement->execute(array($gameId));
        if( $statement->fetch(\PDO::FETCH_ASSOC)['json']==null){
            return true;
        }
        return false;
    }

    public function getTurn($gameId) {
        $sql = <<<SQL
select turn from $this->tableName
where id = ?
SQL;
        $pdo = $this->pdo();
        $statement = $pdo->prepare($sql);
        $statement->execute(array($gameId));

        return $statement->fetch(\PDO::FETCH_ASSOC)['turn'];

    }

    public function getJSON($gameId) {
        $sql = <<<SQL
select json from $this->tableName
where id = ?
SQL;
        $pdo = $this->pdo();
        $statement = $pdo->prepare($sql);
        $statement->execute(array($gameId));

        return $statement->fetch(\PDO::FETCH_ASSOC)['json'];

    }

    public function getP1($gameId) {
        $sql = <<<SQL
select p1 from $this->tableName
where id = ?
SQL;
        $pdo = $this->pdo();
        $statement = $pdo->prepare($sql);
        $statement->execute(array($gameId));

        return $statement->fetch(\PDO::FETCH_ASSOC)['p1'];

    }

    public function getP2($gameId) {
        $sql = <<<SQL
select p2 from $this->tableName
where id = ?
SQL;
        $pdo = $this->pdo();
        $statement = $pdo->prepare($sql);
        $statement->execute(array($gameId));

        return $statement->fetch(\PDO::FETCH_ASSOC)['p2'];

    }

    public function nextTurn($gameId){
        $p1=$this->getP1($gameId);
        $turn=$this->getTurn($gameId);
        $p2=$this->getP2($gameId);

        $sql=<<<SQL
UPDATE  $this->tableName 
SET  `turn` =?
WHERE id =?
SQL;
        $pdo = $this->pdo();
        $statement = $pdo->prepare($sql);
        $statement->execute();

        if($p1===$turn){

            $statement->execute(array($p2,$gameId));

        }else{
            $statement->execute(array($p1,$gameId));
        }
    }

    public function addPipe($game_id, Tile $pipe){
        $json = $pipe->objectToJSON();
        $sql = <<<SQL
UPDATE $this->tableName
SET `json`=?
WHERE id=?
SQL;
        $pdo = $this->pdo();
        $statement = $pdo->prepare($sql);
        $statement->execute(array($json,$game_id));
    }

    public function getPipe($game_id){
        $sql = <<<SQL
SELECT json
FROM $this->tableName
WHERE id=?
SQL;
        $pdo = $this->pdo();
        $statement = $pdo->prepare($sql);
        try{
            $statement->execute(array($game_id));
            if ($statement->rowCount()===0){
                return null;
            }
            $json = $statement->fetch(\PDO::FETCH_ASSOC);
            $pipe = Tile::JSONToObject($json['json']);
            return $pipe;

        }catch (\PDOException $exception){
            return null;
        }
    }

    public function create_game($session,$size,$gamename){
        $gamesTable = $this->getTableName();
        $sql=<<<SQL
INSERT INTO $gamesTable
( `gamename`, `p1`,  `size`, `turn`, `time`) 
VALUES (?,?,?,?,now())
SQL;
        $pdo = $this->pdo();
        $statement = $pdo->prepare($sql);
        $statement->execute(array($gamename,$session->getId(),$size,$session->getId()));
        return;
    }

    public function your_turn($game_id,$user_id) {
        $usersTable = $this->users->getTableName();
        $gamesTable = $this->getTableName();
        $sql=<<<SQL
SELECT $gamesTable.turn
from $gamesTable
WHERE id = ? and turn= ?
SQL;
        $pdo = $this->pdo();
        $statement = $pdo->prepare($sql);
        $statement->execute(array($game_id,$user_id));
        if($statement->rowCount()===0){
            return false;
        }
        return true;
    }

    public function p2_exist($player_name){
        $usersTable = $this->users->getTableName();
        $gamesTable = $this->getTableName();
        $sql=<<<SQL
SELECT $gamesTable.p2
from $gamesTable
INNER JOIN $usersTable as p2_user
ON p2_user.id = $gamesTable.p1
WHERE p2_user.name = ?
SQL;
        $pdo = $this->pdo();
        $statement = $pdo->prepare($sql);
        $statement->execute(array($player_name));
        if($statement->fetch(\PDO::FETCH_ASSOC)['p2']===null){
            return false;
        }
        return true;
    }

    /*
     * get the every roll of the game table
     * @return a array of all element
     *      id          => game id
     *      gamename    => game name
     *      p1          => player 1 id
     *      p1_name     => player 1 name
     *      p2          => player 2 id
     *      p2_name     => player 2 name
     *      size        => game size
     *      turn        => current player id
     *      turn_name   => current player name
     *      winner      => winner id
     *      winner_name => winner name
     *      json        => json files
     */
    public function get(){
        $usersTable = $this->users->getTableName();
        $sql=<<<SQL
SELECT $this->tableName.id,gamename,player1.name as p1_name,player2.name as p2_name,size,turn,winner,json,time
FROM $this->tableName
INNER JOIN $usersTable as player1
ON player1.id = p1
left join $usersTable as player2
on player2.id = p2
INNER JOIN $usersTable as t
ON t.id = turn
left join $usersTable as win
on win.id = winner
SQL;
        $pdo = $this->pdo();
        $statement = $pdo->prepare($sql);
        $statement->execute();
        if($statement->rowCount()===0){
            return null;
        }
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }
    /*
     * update the p2 column in the table to the second player2
     */
    public function joinGame($id,$user){
        $user_id = $user->getId();
        $sql = <<<SQL
UPDATE $this->tableName
SET `p2` = ?, `time` = now()
WHERE id=?
SQL;
        $pdo = $this->pdo();
        $statement = $pdo->prepare($sql);
        $statement->execute(array($user_id,$id));

    }

//    public function getGame($player1,$player2) {
//    $users = new Users($this->site);
//    $usersTable = $users->getTableName();
//
//    $sql = <<<SQL
//SELECT *
//from $this->tableName c,
//     $usersTable  user
//where c.id = user.id
//SQL;
//
//    $pdo = $this->pdo();
//    $statement = $pdo->prepare($sql);
//
//    $statement->execute(array(" needs to be filled with game info"));
//    if($statement->rowCount() === 0) {
//        return null;
//    }
//
//    return new Game($statement->fetch(\PDO::FETCH_ASSOC));
//}
//
//    public function insertGame($p1, $p2) {
//
//        $sql = <<<SQL
//insert into $this->tableName(key, gamename, p1, p2, size, turn, winner, json, time)
//values(?, "", ?, ?, ? ,?, ? "", "")
//SQL;
//
//        $pdo = $this->pdo();
//        $statement = $pdo->prepare($sql);
//
//        try {
//            if($statement->execute(array($key, $gamename, $p1, $p2, $size, $turn, $winner, $json, $time)) === false) {
//                return null;
//            }
//        } catch(\PDOException $e) {
//            return null;
//        }
//
//        return $pdo->lastInsertId();
//    }
//
//    public function getGames()
//    {
//        $users = new Users($this->site);
//        $the_users_Table = $users->getTableName();
//        $gametable = $this->tableName;
//        $sql = <<<SQL
//SELECT $gametable.key, $gametable.gamename, $gametable.p1, $gametable.p2, $gametable.size,  $gametable.turn,$gametable.winner ,$gametable.json,$gametable.time
//from $gametable
//inner join $the_users_Table as newtable
//on $gametable.gamename = newtable.id
//inner join $the_users_Table as secondtable
//on $gametable.key=secondtable.id
//SQL;
//        $pdo = $this->pdo();
//        $statement = $pdo->prepare($sql);
//        $statement->execute();
//
//        if($statement->rowCount()=== 0) {
//            return null;
//        }
//        $statements= $statement->fetchALL(\PDO::FETCH_ASSOC);
//        $games=array();
//        foreach ($statements as $game)
//        {
//            array_push($cases, new Game($game));
//        }
//        return $games;
//
//    }
//
//    public function update(Game $game)
//    {
//        $p1=$game->getPlayer1();
//        $p2=$game->getPlayer2();
//
//        $mygame = $this->get($p1,$p2);
//        if ($mygame) {
//
////need getters in game obj
//            $number=$game->getNumber();
////$name=$game->getName();
//
//            $winner=$game->getWinner();
//            $size=$game->getSize();
////$game->getJson();
//
//            /*$number = $case->getNumber();        ///< Email address
//            $summary = $case->getSummary();        ///< Name as last, first
//            $agent = $case->getAgent();
//            // $agentName = $case->getAgentName();///< Phone number
//            $status = $case->getStatus();    ///< User address
//            $mycaseid = $mycase->getId();*/
//
//            $sql = <<<SQL
//UPDATE $this->tableName
//SET number=?, grid=?, name=?, winner=?, size=?
//where gamename='?';
//SQL;
//            $pdo = $this->pdo();
//            $statement = $pdo->prepare($sql);
//
//            //  $statement->execute(array($number, $grid, $name, $winner, $size));
//
//            if ($statement->rowCount() === 0) {
//                return null;
//            }
//            return false;
//
//        }
//    }
//
//    public function delete(Game $game)
//    {
//        $mygame = $this->get($game->getPlayer1(),$game->getPlayer2());
//        if ($mygame !== NULL) {
//            $id = $game->getNumber();
//            $sql = <<<SQL
//DELETE FROM $this->tableName
//where id=?
//SQL;
//
//            $pdo = $this->pdo();
//            $statement = $pdo->prepare($sql);
//            $ret = $statement->execute(array($id));
//        }
//        else{
//            return null;
//        }
//    }
}