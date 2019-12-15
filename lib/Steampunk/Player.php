<?php


namespace Steampunk;

//disregard this class
class Player
{
    private $pipeline=array();//a array to store all the pipes and start with Tile Gauge
    private $stock=array();//the pipes that a player has in the stock
    private $steamline = array();//a array to store all the steam
    private $name; //the name for the player

    public function __construct($name,$playerNumber,$size)
    {
        $this->name = $name;
        $this->valve = false;
        if($playerNumber == 1){
            $this->pipeline[] = new TileSource(0,$size/2-3,$name);
            $this->pipeline[] = new TileGaugeBottom($size+1,$size/2-2,$name);
            $this->pipeline[] = new TileGaugeTop($size+1,$size/2-3,$name);
            $this->steamline[]= new TileSteam(1,$size/2-3,TileSteam::W,$name);
        }elseif ($playerNumber == 2){
            $this->pipeline[] = new TileSource(0,$size/2+2,$name);
            $this->pipeline[] = new TileGaugeBottom($size+1,$size/2+1,$name);
            $this->pipeline[] = new TileGaugeTop($size+1,$size/2,$name);
            $this->steamline[] = new TileSteam(1,$size/2+2,TileSteam::W,$name);
        }
    }

///*
//* Returns JSON text representing the pipeline and stock of the player
//*/
//    public function pipesToJSON() {
//        $built_array = array();
//        $pipeline_array = array();
//        $stock_array = array();
//        //build the pipeline array
//        //calls object to json on all of the tile objects in the pipeline. this function returns the json representation of the objects
//        foreach ($this->pipeline as $pipe) {
//            array_push($pipeline_array,$pipe->objectToJSON());
//        }
//        //build the stock array
//        foreach ($this->stock as $pipe) {
//            array_push($stock_array,$pipe->objectToJSON());
//        }
//        //the out array will just have these two arrays included
//        $built_array['pipeline'] = $pipeline_array;
//        $built_array['stock'] = $stock_array;
//
//        return json_encode($built_array);
//    }

    //returns true if param pipe has all open directions connected to another pipe, else false
    public function CheckConnection(TilePipe $pipe, $grid_size){
        //get pipe info for easy access
        $pipe_coord = $pipe->ReturnCoordination();
        $pipe_dir = $pipe->GetDirection();

        //for following if statements, check if there is a pipe connected to each of $pipe's opening
        //if pipe direction North is open
        if($pipe_dir['N']) {
            //coordinate information of pipe north of current pipe
            $neighbor_x = $pipe_coord[0];
            $neighbor_y = $pipe_coord[1] - 1;

            //if true, pipe must be in the top row and a connection is impossible
            if ($neighbor_y < 0) {
                return false;
            }

            $neighbor_coord = array($neighbor_x, $neighbor_y);

            //will be set to true if the connection exists
            //this is necessary because we don't want to return true right away because we still need to check the other directions
            //but we still need a way to see if going through all of the pipes yielded a correct pipe
            $found = false;

            //go through all of the pipes in the line
            for ($i = 0; $i < count($this->pipeline); $i++) {
                //if current pipe in array is in the correct location && has south direction open
                if($this->pipeline[$i]->ReturnCoordination() == $neighbor_coord && $this->pipeline[$i]->GetDirection()['S']) {
                    $found = true;
                }
            }
            //if going through all pipes didn't yield one with a correct connection
            if(!$found) {
                return false;
            }
        }
        //if direction South is open
        if($pipe_dir['S']) {
            $neighbor_x = $pipe_coord[0];
            $neighbor_y = $pipe_coord[1] + 1;
            //if current pipe is in bottom row, no connection is possible, so return false
            if ($neighbor_y > $grid_size - 1) {
                return false;
            }
            $neighbor_coord = array($neighbor_x, $neighbor_y);
            $found = false;
            for ($i = 0; $i < count($this->pipeline); $i++) {
                //if current pipe in array is in the correct location && has north direction open
                if($this->pipeline[$i]->ReturnCoordination() == $neighbor_coord && $this->pipeline[$i]->GetDirection()['N']) {
                    $found = true;
                }
            }
            if(!$found) {
                return false;
            }
        }
        //if direction East is open
        if($pipe_dir['E']) {
            $neighbor_x = $pipe_coord[0] + 1;
            $neighbor_y = $pipe_coord[1];
            //if current pipe is in far right row, no connection is possible, so return false
            if ($neighbor_x > $grid_size + 1) {
                return false;
            }
            $neighbor_coord = array($neighbor_x, $neighbor_y);
            $found = false;
            for ($i = 0; $i < count($this->pipeline); $i++) {
                //if current pipe in array is in the correct location && has west direction open
                if($this->pipeline[$i]->ReturnCoordination() == $neighbor_coord && $this->pipeline[$i]->GetDirection()['W']) {
                    $found = true;
                }
            }
            if(!$found) {
                return false;
            }
        }
        //if direction West is open
        if($pipe_dir['W']) {
            $neighbor_x = $pipe_coord[0] - 1;
            $neighbor_y = $pipe_coord[1];
            //if current pipe is in far left row, no connection is possible, so return false
            if ($neighbor_x < 0) {
                return false;
            }
            $neighbor_coord = array($neighbor_x, $neighbor_y);
            $found = false;
            for ($i = 0; $i < count($this->pipeline); $i++) {
                //if current pipe in array is in the correct location && has east direction open
                if($this->pipeline[$i]->ReturnCoordination() == $neighbor_coord && $this->pipeline[$i]->GetDirection()['E']) {
                    $found = true;
                }
            }
            if(!$found) {
                return false;
            }
        }
        //if we got through all the tests without returning false, we can safely return true
        return true;
    }

    //function returns true if the player has successfully completed the pipe to the gauge without openings in the line
    public function WinStatus($grid_size) {
        //coordinates of the players gauge
        $gauge_coord = $this->pipeline[1]->ReturnCoordination();

        //coordinates of where the final pipe in a completed line would have to be
        $last_pipe_x = $gauge_coord[0] - 1;
        $last_pipe_y = $gauge_coord[1];
        $last_pipe_coord = array($last_pipe_x, $last_pipe_y);

        //will be set to true if a pipe is found which is connected to the gauge
        $gauge_connected = false;

        //go through all of the players pipes
        //we start at $i == 2 so that the source and the gauge are not considered
        for ($i = 3; $i < count($this->pipeline); $i++) {
            if(! $this->CheckConnection($this->pipeline[$i],$grid_size)) {
                //return false if a pipe is found that has an invalid connection
                return false;
            }
            //if current pipe is in the correct location and has direction East open
            if($this->pipeline[$i]->ReturnCoordination() == $last_pipe_coord && $this->pipeline[$i]->GetDirection()['E']){
                $gauge_connected = true;
            }
        }
        if($gauge_connected == false){
            //pipeline is closed but gauge is not connected, user is bad and should feel bad
            return false;
        }
        else {
            return true;
        }
    }
    /*
     * ask the para of TilePipe
     * the new pipe will add to the pipeline start with index of pipeline[3]
     */
    public function AddPipe(TilePipe $pipe){
        $this->pipeline[] = $pipe;
    }
    /*
     * add the 5 random pipe where player can choose to the stock array
     * it total have 5 pipes in the stock array
     */
    public function AddStock(TilePipe $pipe){
        $this->stock[] = $pipe;
    }
    /*
     * after player click the discard or used one of the stock pipes
     * we will generate a new random pipe to replace the old one in the array
     * $index is the position of old pipe are stored in stock array
     * $pipe is the new random pipe
     */
    public function ReplaceStock(TilePipe $pipe,$index){
        $temp = $this->stock[$index];
        $this->stock[$index] = $pipe;
        return $temp;
    }
    /*
     * return all the pipes that player actually put in to the grid
     */
    public function ReturnPipeline(){
        return $this->pipeline;
    }
    /*
     * return all the random pipes that user can choose
     * each player have different 5 pipes
     */
    public function ReturnStock(){
        return $this->stock;
    }
    /*
     * add the new steams that generate from the pipe added in to the steam array
     */
    public function addSteam(TileSteam $steam){
        $this->steamline[] = $steam;
    }
    /*
     * delete the steam that have the same coordination as we added the pipe in
     */
    public function deleteSteam($number){
        array_splice($this->steamline,$number,1);
    }
    /*
     * return all the steam player have
     */
    public function ReturnSteam(){
        return $this->steamline;
    }
    /*
     * return the name of the player
     */
    public function ReturnName(){
        return $this->name;
    }
    /*
     * check the new added pipe is connected to the previous pipe or not
     * if the new add pipe has at least one direction has been connect,
     * return true, otherwise return false
     */
    public function Connected(TilePipe $pipe, TileSteam $steam){
        $direction = $steam->GetDirection();
        if ($pipe->ReturnCoordination()==$steam->ReturnCoordination() && $pipe->ReturnPlayer()==$steam->ReturnPlayer()){
            switch ($direction){
                case TileSteam::E:
                    return $pipe->GetDirection()['E'];
                    break;
                case TileSteam::N:
                    return $pipe->GetDirection()['N'];
                    break;
                case TileSteam::S:
                    return $pipe->GetDirection()['S'];
                case TileSteam::W:
                    return $pipe->GetDirection()['W'];
                    break;
            }
        }
        return false;
    }
}