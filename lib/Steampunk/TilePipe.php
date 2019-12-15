<?php


namespace Steampunk;

//represents a pipe piece in the game
class TilePipe extends Tile
{
    //private $rotationTimes = 0; //it is use for contain how many times the pipe have rotated and helpful for choose the image in present
    private $direction; // array of bools NSEW
    private $type;
    private $image;
    private $rotation = 0;

    const Cap=0;
    const Ninety=1;
    const Straight=2;
    const Tee=3;


    public function __construct($x, $y, $type, $name)
    {
        parent::__construct($x,$y,$name);
        $this->type=$type;
        $this->SetDirection($type);

    }

    /*
 * Returns JSON text representation of the object
 */
    public function objectToJSON() {
        $built_array = parent::objectToJSON();
        //$built_array['direction']=$this->direction;
        $built_array['rotation']=$this->rotation;
        $built_array['type']=$this->type;
        //$built_array['image']=$this->image;
        $built_array['tileType']="TilePipe";
        return json_encode($built_array);
    }

//    /*
// * This function accepts an array that is returned by json_decode, not the actual JSON text!
// * The array should be decoded and then sent the this function because the array has to be
// * decoded in the first place to determine what type the tile is, so there is no purpose
// * in decoding in two places
// */
//    public function JSONToObject($JSON_array)
//    {
//        $out_obj = new TilePipe($JSON_array['coordination']['x'],$JSON_array['coordination']['y'],$JSON_array['type'],$JSON_array['player']);
//        $out_obj->SetDirection($JSON_array['direction']);
//        $out_obj->chooseImage();
//        return $out_obj;
//    }


    public function Rotate(){
        $temp = $this->direction['N'];
        $this->direction['N'] = $this->direction['W'];
        $this->direction['W'] = $this->direction['S'];
        $this->direction['S'] = $this->direction['E'];
        $this->direction['E'] = $temp;
        $this->rotation = ($this->rotation+1) % 4;
        $this->chooseImage();
    }

    public function SetDirection($type){
        switch ($type){
            case self::Cap:
                $this->direction = ["N"=>false,"S"=>true,"E"=>false,"W"=>false];
                $this->image = 'cap-s.png';
                break;
            case self::Ninety:
                $this->direction = ["N"=>false,"S"=>true,"E"=>false,"W"=>true];
                $this->image = 'ninety-sw.png';
                break;
            case self::Straight:
                $this->direction = ["N"=>false,"S"=>false,"E"=>true,"W"=>true];
                $this->image = 'straight-h.png';
                break;
            case self::Tee:
                $this->direction = ["N"=>false,"S"=>true,"E"=>true,"W"=>true];
                $this->image = 'tee-esw.png';
                break;
        }
    }

    public function ReturnType()
    {
        return $this->type;
    }

    public function GetDirection(){
        return $this->direction;
    }

    public function chooseImage(){
        switch ($this->type){
            case self::Cap:
                $imagearray = array('cap-s.png','cap-w.png','cap-n.png','cap-e.png');
                $key = array_search($this->image,$imagearray)+1;
                $this->image = $imagearray[$key%4];
                break;
            case self::Ninety:
                $imagearray = array('ninety-sw.png','ninety-wn.png','ninety-ne.png','ninety-es.png');
                $key = array_search($this->image,$imagearray)+1;
                $this->image = $imagearray[$key%4];
                break;
            case self::Straight:
                $imagearray = array('straight-h.png','straight-v.png');
                $key = array_search($this->image,$imagearray)+1;
                $this->image = $imagearray[$key%2];
                break;
            case self::Tee:
                $imagearray = array('tee-esw.png','tee-swn.png','tee-wne.png','tee-nes.png');
                $key = array_search($this->image,$imagearray)+1;
                $this->image = $imagearray[$key%4];
                break;
        }
    }

    public function getImage(){
        return $this->image;
    }

    public function getImageHTML($winner=null){
        $html=$this->getImage();
        $html_out = <<<HTML
<img src="http://webdev.cse.msu.edu/~hughe211/project1/images/$html" width="50" height="50">
HTML;

        return $html_out;


    }
}
