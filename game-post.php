<?php
require __DIR__ . '/lib/site.inc.php';

$controller = new \Steampunk\GameController($Game,$site);
//$controller->setPlayer1($Game->getPlayer1());
//$controller->setPlayer2($Game->getPlayer2());
//Steampunk\GameController($game,$_POST);
$games = new \Steampunk\Games($site);
if($games->your_turn($Game->getGameId(),$_SESSION[\Steampunk\User::SESSION_NAME]->getId())){
    $pipe = $games->getPipe($Game->getGameId());
    if ($pipe instanceof \Steampunk\Tile){
        var_dump($pipe);
        if ($Game->getPlayer1()->ReturnName()==$_SESSION[\Steampunk\User::SESSION_NAME]->getName()){
            $Game->addPipe($Game->getPlayer2(),$pipe);
            $Game->set_current_turn($Game->getPlayer1());
        }else{
            $Game->addPipe($Game->getPlayer1(),$pipe);
            $Game->set_current_turn($Game->getPlayer2());
        }

    }
}

if($controller->IsReset()) {
    unset($_SESSION[GAME_SESSION]);
}
if (isset($_POST['cbs']) && isset($_POST['cb'])){
    switch ($_POST['cbs']){
        case "Rotate":
            $controller->rotate($_POST['cb']);                                                                          //rotate the pipe in stock
            $controller->setNumber($_POST['cb']);                                                                       //keep the same radio clicked each time
            break;
        case "Discard":
            $controller->discard($_POST['cb']);                                                                         //replace the selected pipe
            $controller->setNumber($_POST['cb']);                                                                       //keep the same radio clicked each time
            $controller->nextTurn();                                                                                    //jump to the next player
            break;
        case "Open Valve":
            $controller->openValve();                                                                                   //open valve for current player
            break;
        case "Give Up":
            $controller->set_loser();                                                                                   //make other player to be winner
            break;
    }

}
if (isset($_POST['leak']) && isset($_POST['cb'])){
    $array = explode(',',$_POST['leak'],2);
    if($controller->addPipe($array[0],$array[1],$_POST['cb'])){
        $controller->setNumber($_POST['cb']);
        $controller->nextTurn();
    }
}
//phpinfo();
header("location: Game.php");
exit;

