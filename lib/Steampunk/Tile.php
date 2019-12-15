<?php

namespace Steampunk;

//a generic tile class that will never be directly instantiated
abstract class Tile
{
    private $coordination=array(); //coordination is a 2 int array of (x,y)
    private $player; // player variable

    const GAUGE_BOTTOM="GaugeBottom";
    const GAUGE_TOP="GaugeTop";
    const TILE_PIPE="TilePipe";
    const TILE_SOURCE="TileSource";
    const TILE_STEAM="TileSteam";

    public function __construct($x,$y,$name)
    {
        $this->SetCoordination($x,$y);
        $this->SetPlayer($name);
    }

    //set the coordination for the tiles
    public function SetCoordination($x,$y){
        $this->coordination[0] = $x;
        $this->coordination[1] = $y;
    }
    public function ReturnCoordination(){
        return $this->coordination;
    }
    //set the item belong to which player
    public function SetPlayer($name){
        $this->player = $name;
    }
    public function ReturnPlayer(){
        return $this->player;
    }

    abstract public function getImageHTML($winer=null);

    /*
     * Returns the an array which contains the data from member
     * variables present in the base class. This array will be
     * extended and converted to JSON text in the child classes
     */
    public function objectToJSON(){
        $built_array = array();
        $built_array["coordination"] = $this->coordination;
        $built_array["player"]=$this->player;
        return $built_array;
    }

    /*
     * This static function can be called to convert a blob of text into objects. the reason that this function is here with all
     * of these downcalls is that it is impossible to tell what type of tile the tile should be when it is just in text form.
     * this way, the json text can be decoded, and based on the type of the tile that is discovered, the correct constructor can
     * be called and the new object can be returned
     */
    public static function JSONToObject($JSON_text) {
        $decoded_array = json_decode($JSON_text, true);
        $out_obj = new TilePipe($decoded_array['coordination']['0'],$decoded_array['coordination']['1'],$decoded_array['type'],$decoded_array['player']);
        for($i=0;$i<$decoded_array['rotation'];$i++){$out_obj->Rotate();}
        return $out_obj;
    }
}