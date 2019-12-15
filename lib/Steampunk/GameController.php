<?php



namespace Steampunk;

class GameController
{
private $game;
private $winner;
private $player1;
private $player2;
private $reset = false;
private $cheat = false;
private $site;

    public function __construct(Game $game,Site $site)
    {
        $this->site = $site;
        if (isset($array['clear'])) {
            $this->reset = true;
        }
        elseif (!isset($array['value'])) {

            $this->reset = false;

        }
        $this->game=$game;
        $this->winner = null;
        if (isset($request['c'])) {
            $this->cheat=true;
        }
           // $this->game->setSeed($seed);
      //  $this->seed = $seed;                            //tran the seed variable to the random pipe function
    }
    /*
     * use the game controller to pass the install the player1 and player 2 in to the game class
     */
    public function setGameId($id){
        $this->game->setGameId($id);
    }

    public function getGameId(){
        return $this->game->getGameId();
    }


    public function setPlayer1(Player $player){
        $this->player1 = $player;
        $this->game->setPlayer1($this->player1);

        $this->game->RandomPipe($player->ReturnName());

        for($i=0;$i<5;$i++) {
            $this->game->getPlayer1()->AddStock($this->game->RandomPipe($player->ReturnName()));
        }
    }
    public function setPlayer2(Player $player){
        $this->player2 = $player;
        $this->game->setPlayer2($this->player2);
        for($i=0;$i<5;$i++) {
            $this->game->getPlayer2()->AddStock($this->game->RandomPipe($player->ReturnName()));
        }
    }
    /*
     * set the current player
     * initially we set the current player to be player 1
     */
    public function setCurrentPlayer($player) {
        $this->game->set_current_turn($player);
    }



    public function isCheat(){
        return $this->cheat;
    }

    /*
     * change the current player to be the next player
     */

    public function nextTurn(){
        $games = new Games($this->site);
        $games->nextTurn($this->game->getGameId());
        $this->game->update_turn();
    }
    /*
     * install the play board size of the game class
     */
    public function setSize($size){
        $this->game->setGridSize($size);
    }
    /*
     * rotate the pipes in the player stock array
     */
    public function rotate($index){
        if($this->game->get_current_turn()==$this->game->getPlayer1()){
            $this->game->getPlayer1()->ReturnStock()[$index]->Rotate();
        }else{
            $this->game->getPlayer2()->ReturnStock()[$index]->Rotate();
        }
    }
    /*
     * replace of the random pipes with $index in the stock array to another random pipe
     */
    public function discard($index){
        $player = $this->game->get_current_turn();
        if ($player==$this->game->getPlayer1()){
            $this->game->getPlayer1()->ReplaceStock($this->game->RandomPipe($player->ReturnName()),$index);
        }else{
            $this->game->getPlayer2()->ReplaceStock($this->game->RandomPipe($player->ReturnName()),$index);
        }
    }
    /*
     * return a name string of current player's name
     */
    public function getName(){
        return $this->game->get_current_turn()->ReturnName();
    }
    /*
     * add the identity pipe in the stock array with $index into the player pipe array
     * after put in this pipe generate a random pipe to replace it in the stock array
     */
    public function addPipe($x,$y,$index){
        $steam = $this->game->getGrid()[$y][$x];
        $player = $this->game->get_current_turn();
        if($this->game->get_current_turn()==$this->game->getPlayer1() ){                                                //check if the it is the player 1 want to add the pipe or player 2
            $pipe = $this->game->getPlayer1()->ReturnStock()[$index];
            $newpipe = new TilePipe($x,$y,$pipe->ReturnType(),$this->game->getPlayer1()->ReturnName());                 //generate a new pipe with the same coordination of steam where you want to add the pipe
            while ($pipe->GetDirection()!=$newpipe->GetDirection()){
                $newpipe->Rotate();                                                                                     //rotate the new pipe until it have same direction as the pipe in stock array
            }
            if ($this->game->getPlayer1()->Connected($newpipe,$steam)){
                //we only allow to put pipe that are connect to the previous one


                $this->game->addPipe($this->game->getPlayer1(),$newpipe);
                $games = new Games($this->site);
                $games->addPipe($this->game->getGameId(),$newpipe);
                $this->game->getPlayer1()->ReplaceStock($this->game->RandomPipe($player->ReturnName()),$index);
                $this->game->set_current_turn($this->game->getPlayer2());

                $this->game->UpdateStatus();

               //update the status

                return true;
            }else{
                return false;
            }
        }else{
            $pipe = $this->game->getPlayer2()->ReturnStock()[$index];
            $newpipe = new TilePipe($x,$y,$pipe->ReturnType(),$this->game->getPlayer2()->ReturnName());
            while ($pipe->GetDirection()!=$newpipe->GetDirection()){
                $newpipe->Rotate();
            }
            if ($this->game->getPlayer2()->Connected($newpipe,$steam)){
                $this->game->addPipe($this->game->getPlayer2(),$newpipe);
                $games = new Games($this->site);
                $games->addPipe($this->game->getGameId(),$newpipe);
                $this->game->getPlayer2()->ReplaceStock($this->game->RandomPipe($player->ReturnName()),$index);
                $this->game->UpdateStatus();
                $this->game->set_current_turn($this->game->getPlayer1());
              //update the status

                return true;
            }else{
                return false;
            }
        }
        return false;
    }

    public function IsReset(){
        return $this->reset;

    }
    /*
     * return the bool variables
     * if the winner is the current player return true
     * otherwise return false
     */
    public function getWinner(){
        $this->game->UpdateStatus();
        if ($this->game->getWinner()!=$this->game->get_current_turn()){
            return false;
        }else{
            return true;
        }
    }
    /*
     * open the valve
     * if the player finished pipe lines, set the winner to be the player and change the source and gauge image
     * otherwise, another player win but ont change the image.
     */
    public function openValve(){
        if ($this->game->get_current_turn()->WinStatus($this->game->getSize())){
            $this->game->set_winner();
        }else{
            $this->game->update_turn();
            $this->game->set_winner();
        }
    }
    public function reset(){
        $this->game->reset();
    }

    public function set_loser() {
        if (is_null($this->game->getWinner())){
            $this->game->set_loser();
        }

    }

    public function setNumber($index){
        $this->game->setNumber($index);
    }
}