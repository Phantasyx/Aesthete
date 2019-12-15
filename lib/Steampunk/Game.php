<?php


namespace Steampunk;


class Game
{
    private $grid=array(array()); // array for all the pipes on the map
    private $gridSize;
    private $seed;
    private $player1;
    private $player2;
    private $game_over;
    private $winner;
    private $current_turn;
    //private $OpenValve;
    private $number;
    private $game_id;

    /**
     * @return mixed
     */
    public function getGameId()
    {
        return $this->game_id;
    }

    /**
     * @param mixed $game_id
     */
    public function setGameId($game_id)
    {
        $this->game_id = $game_id;
    }



    public function __construct($seed = null)
    {
        $this->winner=null;
        $this->game_over=false;
        $this->current_turn = $this->player1;
        //$this->OpenValve=null;
        $this->number = 0;
        $this->seed=$seed;
    }

//    /*
// * Return the entire array to be written to the database for the game object on which this method is called
// */
//    public function gameToJSON () {
//        $built_array = array();
//        //call the pipes to json function on both of the players. this function returns an array of arrays,
//        //where the outside array elements are pipeline and stock, and the sub elements are JSON representations of the pipe objects
//        $built_array['p1'] = $this->player1->pipesToJSON();
//        $built_array['p2'] = $this->player2->pipesToJSON();
//        //the out array will only contain the info on pipeline and stock. all of the other info is either stored separately
//        //in the database, or it can be calculated based on the information that is present there
//        return json_encode($built_array);
//    }

    /*
     * Accepts an array of the following format:
     * array(key,gamename,p1,p2,size,turn,winner,json,time)
//     *
//     * A new game object should be instantiated, and then this method should be called on it, passing in the correct
//     * array. the object on which the method is called will be transformed into an object with all of the correct values
//     */
//    public function JSONToGame ($in_array) {
//
//        //create the 2 player objects that will be a part of this new game object
//        $this->player1 = (new Player($in_array['p1_name'],1,$in_array['size']));
//        $this->player2 = (new Player($in_array['p2_name'],2,$in_array['size']));
//
//        //set grid size
//        $this->gridSize=$in_array['size'];
//        //if the winner field in the database is set, that means that game is over
//        if($in_array['winner']==null) {
//            //winner was null, so game isn't over
//            $this->game_over = false;
//        } else {
//            $this->game_over = true;
//            //check and see who is the winner. set the member variable of this game object accordingly
//            if($in_array['p1']==$in_array['winner']){
//                $this->winner = $this->player1;
//            } else {
//                $this->winner = $this->player2;
//            }
//        }
//        //check whos turn it is and set the member variable of this game object accordingly
//        if($in_array['turn']==$in_array['p1']) {
//            $this->current_turn=$this->player1;
//        } else {
//            $this->current_turn=$this->player2;
//        }
//        //go through all of the elements of player 1's pipeline, calling the necessary functions to convert the JSON text into objects
//        foreach($in_array['p1']['pipeline'] as $pipetext){
//            $pipe = Tile::JSONToObject($pipetext);
//            //call the add pipeline function. this function must be called because the function is also responsible for adding things like steam
//            $this->addPipe($this->player1, $pipe);
//        }
//        //add all of p1's stock. pretty much the same as above
//        foreach($in_array['p1']['stock'] as $pipetext){
//            $pipe = Tile::JSONToObject($pipetext);
//            $this->player1->addStock($pipe);
//        }
//        //now do all of these things for player 2
//        foreach($in_array['p2']['pipeline'] as $pipetext){
//            $pipe = Tile::JSONToObject($pipetext);
//            $this->addPipe($this->player2, $pipe);
//        }
//        foreach($in_array['p2']['stock'] as $pipetext){
//            $pipe = Tile::JSONToObject($pipetext);
//            $this->player2->addStock($pipe);
//        }
//    }


    /*
     * only use when the new game is asked
     * set everything to the install value
     */
    public function reset(){
        $this->winner=null;
        $this->game_over=false;
        $this->current_turn = $this->player1;
        //$this->OpenValve=null;
        $this->number = 0;
    }
    /*
     * setNumber is use for the 5 radio choose
     * it will store which radio the player is currently choosed
     * and keep this number until player choose other radio
     * so that player can continuously to rotate the same image
     */
    public function setNumber($index){
        $this->number=$index;
    }
    public function getNumber(){
        return $this->number;
    }

    /*
     * get the winner of the game
     * it will return a Player class
     */
    public function getWinner(){
        return $this->winner;
    }
    /*
     * get the current turn for which player can to make the motion
     */
    public function get_current_turn () {
        return $this->current_turn;
    }
    /*
     * initially set the current player to be player 1
     */
    public function set_current_turn ($player) {
        $this->current_turn = $player;
    }
    /*
     * set_loser is used in open valve when the pipes are not all closed and connected
     * and also used for one of the player click the give up button.
     * it will set the winner to be other player in the game
     */
    public function set_loser () {
        if ($this->current_turn == $this->player1) {
            $this->winner = $this->player2;
        } else {
            $this->winner = $this->player1;
        }
    }
    public function set_winner(){
        if ($this->current_turn->ReturnName() == $this->player1->ReturnName()) {
            $this->winner = $this->player1;
        } else {
            $this->winner = $this->player2;
        }
    }
    /*
     * change the player after each time a player make the motion
     */
    public function update_turn() {
        if($this->current_turn == $this->getPlayer1()) {
            $this->current_turn = $this->getPlayer2();
        }
        else {
            $this->current_turn = $this->getPlayer1();
        }
    }
    /*
     * initially set the player 1
     */
    public function setPlayer1(Player $player){
        $this->player1=$player;
    }
    public function getPlayer1(){
        return $this->player1;
    }
    /*
     * initially set the player 1
     */
    public function setPlayer2(Player $player){
        $this->player2=$player;
    }
    public function getPlayer2(){
        return $this->player2;
    }
    /*
     * initially set the game board size
     * and set the position of source, gauge, first leak for the
     * two players
     */
    public function setGridSize($size){
        $this->gridSize=$size;
        for ($i = 0; $i < $size + 2; $i++) {                     //Set all the grid cells to be false
            for ($j = 0; $j < $size; $j++) {                   //which is mean there empty
                $this->grid[$i][$j] = false;
            }
        }
        //Set the install place for the first player to be true
        $this->grid[0][$size/2-3]=true;                //Set the install source in grid to be true
        $this->grid[$size+1][$size/2-2]=true;          //Set the install gauge bottom in grid to be true
        $this->grid[$size+1][$size/2-3]=true;           //set the install gauge top in grid to be true
        $this->grid[1][$size/2-3]=true;                 //set the install steam in the grid to be true

        //Set the install place for the second player to be true
        $this->grid[0][$size/2+2]=true;                //Set the install source in grid to be true
        $this->grid[$size+1][$size/2+1]=true;          //Set the install gauge bottom in grid to be true
        $this->grid[$size+1][$size/2]=true;             //set the install gauge top in grid to be true
        $this->grid[1][$size/2+2]=true;                 //Set the install steam in the grid to be true
    }
    public function getSize(){
        return $this->gridSize;
    }

//
//    public function setSeed($seed = null){
//        if (!is_null($seed)){
//            $this->seed = $seed;
//        }
//    }

    //checks if either player has won and updates member variables accordingly
    public function UpdateStatus() {
        if($this->player1->WinStatus($this->gridSize)) {
            $this->game_over = true;
        }
        else if($this->player2->WinStatus($this->gridSize)) {
            $this->game_over = true;
        }
    }

    //Get the seed to generate the random pipes
    //@return a random pipe with Tile class
    public function RandomPipe($name){
      // if ($this->seed === null){
      //      $seed = time();
      //  }else{
      //     $seed = $this->seed;
      // }
     // $seed=null;
        $random = mt_rand(0,3);
        return $pipe = new TilePipe(0,0,$random,$name);
    }

    /**
     * @param Tile $pipe, $player
     * check if the player have a steam with same coordination
     * player will add Pipe to player->pipeline
     * and delete that steam in his steamline
     * and run game addsteam once to add the new steams to player steamline
     */
    public function addPipe(Player $player,TilePipe $pipe){
        $coordination = $pipe->ReturnCoordination();
        $steam = $player->ReturnSteam();
        for ($i=0;$i<count($steam);$i++){
            if ($steam[$i]->ReturnCoordination()==$coordination){
                var_dump($steam[$i]);
                $player->AddPipe($pipe);
                $player->deleteSteam($i);
                $this->addSteam($player,$pipe);
            }
        }
    }



    /**
     * @param Tile $pipe
     * add the steam to the current pipe
     * first check which direction $pipe is open, and in this direction the next cell is not over the grid
     * and then if there is nothing which mean grid variable in this coordination is false,
     * we will put a new steam in the cell
     */
    public function addSteam(Player $player,TilePipe $pipe){
        $direction = $pipe->GetDirection();
        $coordination = $pipe->ReturnCoordination();
        if ($direction['N']){
            $x = $coordination[0];
            $y = $coordination[1]-1;
            if ($x>=0 && $y>=0 && $x<$this->gridSize+2 && $y<$this->gridSize){
                $cell = $this->grid[$x][$y];
                if (!$cell){
                    $this->grid[$x][$y] = true;
                    $player->addSteam(new TileSteam($x,$y,TileSteam::S,$player->ReturnName()));
                }
            }
        }
        if ($direction['S']){
            $x = $coordination[0];
            $y = $coordination[1]+1;
            if ($x>=0 && $y>=0 && $x<$this->gridSize+2 && $y<$this->gridSize){
                $cell = $this->grid[$x][$y];
                if (!$cell){
                    $this->grid[$x][$y] = true;
                    $player->addSteam(new TileSteam($x,$y,TileSteam::N,$player->ReturnName()));
                }
            }
        }
        if ($direction['E']){
            $x = $coordination[0]+1;
            $y = $coordination[1];
            if ($x>=0 && $y>=0 && $x<$this->gridSize+2 && $y<$this->gridSize){
                $cell = $this->grid[$x][$y];
                if (!$cell){
                    $this->grid[$x][$y] = true;
                    $player->addSteam(new TileSteam($x,$y,TileSteam::W,$player->ReturnName()));
                }
            }
        }
        if ($direction['W']){
            $x = $coordination[0]-1;
            $y = $coordination[1];
            if ($x>=0 && $y>=0 && $x<$this->gridSize+2 && $y<$this->gridSize){
                $cell = $this->grid[$x][$y];
                if (!$cell){
                    $this->grid[$x][$y] = true;
                    $player->addSteam(new TileSteam($x,$y,TileSteam::E,$player->ReturnName()));
                }
            }
        }
    }

    /*
     * getGrid collect the source, gauge, steam, pipe from two player and pass them into
     * a grid to use for display the image in GameView class
     */
    public function getGrid(){
        $grid = array(array());
        for ($i = 0; $i < $this->gridSize + 2; $i++) {                     //Set all the grid cells to be false
            for ($j = 0; $j < $this->gridSize; $j++) {                   //which is mean there empty
                $grid[$j][$i] = null;
            }
        }
        $pipearray1 = $this->player1->ReturnPipeline();
        $pipearray2 = $this->player2->ReturnPipeline();

        $steamline1 = $this->player1->ReturnSteam();
        $steamline2 = $this->player2->ReturnSteam();

        foreach ($steamline1 as $item){
            $grid[$item->ReturnCoordination()[1]][$item->ReturnCoordination()[0]] = $item;
        }
        foreach ($steamline2 as $item){
            $grid[$item->ReturnCoordination()[1]][$item->ReturnCoordination()[0]] = $item;
        }
        foreach ($pipearray1 as $item){
            $grid[$item->ReturnCoordination()[1]][$item->ReturnCoordination()[0]] = $item;
        }
        foreach ($pipearray2 as $item){
            $grid[$item->ReturnCoordination()[1]][$item->ReturnCoordination()[0]] = $item;
        }
        return $grid;
    }

}