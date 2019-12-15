<?php

namespace Steampunk;

//represents a steam tile
class TileSteam extends Tile
{
    private $direction; // direction constain the variables of N(0) W(1) S(2) E(3)
    private $image;

    const N=0; //North
    const W=1; //West
    const S=2; //South
    const E=3; //East

    public function __construct($x,$y,$direction,$name)
    {
        parent::__construct($x,$y,$name);
        $this->direction=$direction;
    }

//    /*
//* Returns JSON text representation of the object
//*/
//    public function objectToJSON() {
//        $built_array = parent::JSONToObject();
//        $built_array['direction']=$this->direction;
//        $built_array['image']=$this->image;
//        $built_array['tileType'] = "TileSteam";
//        return json_encode($built_array);
//    }

//    /*
//* This function accepts an array that is returned by json_decode, not the actual JSON text!
//* The array should be decoded and then sent the this function because the array has to be
//* decoded in the first place to determine what type the tile is, so there is no purpose
//* in decoding in two places
//*/
//    public function JSONToObject($JSON_array)
//    {
//        $out_obj = new TilePipe($JSON_array['coordination']['x'],$JSON_array['coordination']['y'],$JSON_array['direction'],$JSON_array['player']);
//        $out_obj->chooseImage();
//        return $out_obj;
//    }

    public function SetDirection($direction){
        $this->direction = $direction;
    }
    public function GetDirection(){
        return $this->direction;
    }
    public function chooseImage(){
        switch ($this->direction){
            case self::E:
                $this->image='leak-e.png';
                break;
            case self::N:
                $this->image='leak-n.png';
                break;
            case self::S:
                $this->image='leak-s.png';
                break;
            case self::W:
                $this->image='leak-w.png';
                break;
        }
        return $this->image;
    }

//    public function getImageHTML($win=false){
//        return '<img src="http://webdev.cse.msu.edu/~hughe211/project1/images/"."$this->ChooseImage()" width="50" height="50">';
//    }

    public function getImageHTML($winner=null){
        $html=$this->ChooseImage();

        $html_out = "http://webdev.cse.msu.edu/~hughe211/project1/images/$html";

        return $html_out;


    }
}