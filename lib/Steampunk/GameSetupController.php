<?php

namespace Steampunk;


class GameSetupController
{
    private $redirect;

    public function __construct(Site $site,array &$session)

    {
        $user = $session[User::SESSION_NAME];
        $root = $site->getRoot();

        $this->redirect = "$root/Game.php";

        $games = new Games($site);

        $data_array = $games->get();
        $current_game = array();
        foreach ($data_array as $game){
            //$this->redirect = "$root/Lose.php";
            if ($game['gamename'] === $user->getGameName()){
                $game_name = $user->getGameName();
                $game_controller = new GameController($session[GAME_SESSION],$site);
                $player1 = new Player($game['p1_name'],1,$game['size']);
                $game_controller->setPlayer1($player1);
                $player2 = new Player($game['p2_name'],2,$game['size']);
                $game_controller->setPlayer2($player2);
                $game_controller->setCurrentPlayer($player1);
                $game_controller->setSize($game['size']);
                $game_controller->setGameId($game['id']);
            }
        }
    }


    /**
     * @return mixed
     */
    public function getRedirect()
    {
        return $this->redirect;
    }
}