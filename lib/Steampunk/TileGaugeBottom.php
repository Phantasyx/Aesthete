<?php


namespace Steampunk;

//represents a gauge tile
class TileGaugeBottom extends Tile
{
    public function __construct( $x , $y, $player )
    {
        parent::__construct( $x , $y, $player );
    }

//    /*
//     * Returns JSON text representation of the object
//     */
//    public function objectToJSON() {
//        $built_array = parent::objectToJSON();
//        $built_array['type'] = "TileGaugeBottom";
//        return json_encode($built_array);
//    }

//    /*
//     * This function accepts an array that is returned by json_decode, not the actual JSON text!
//     * The array should be decoded and then sent the this function because the array has to be
//     * decoded in the first place to determine what type the tile is, so there is no purpose
//     * in decoding in two places
//     */
//    public function JSONToObject($JSON_array)
//    {
//        return new TileGaugeBottom($JSON_array['coordination']['x'],$JSON_array['coordination']['y'],$JSON_array['player']);
//    }

    //this function is needed so that the player doesn't assume a pipe connected to the gauge is not connected
    public function GetDirection(){
        return array('N'=>false,'S'=>false,'W'=>true,'E'=>false);
    }

    //function for choosing which image will be generated
    public function ChooseImage($win) {
        if ($win){
            return $this->image = 'gauge-190.png';
        }else{
            return $this->image = 'gauge-0.png';
        }
    }

    public function getImageHTML($winner=null){
        if ($winner==$this->ReturnPlayer()){
            $html=$this->ChooseImage(true);
        }else{
            $html=$this->ChooseImage(false);
        }
        $html_out = <<<HTML
<img src="http://webdev.cse.msu.edu/~hughe211/project1/images/$html" width="50" height="50">
HTML;

        return $html_out;


    }
}