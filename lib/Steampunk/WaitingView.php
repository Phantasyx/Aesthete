<?php


namespace Steampunk;


class WaitingView extends View
{

    private $site;

    public function __construct(Site $site)
    {
        $this->site=$site;
    }
    public function refresh(){
        $root = $this->site->getRoot();
        $html = <<<HTML
<meta http-equiv="refresh" content="2;url=$root/post/waiting.php">
HTML;
    return $html;
    }


    public function present_Game(){
        $html= '<div class="row">' ;
        $html2='</div>';
        $html4='<div class="cell">';
        $htmlcell='<div class="cell">';
        //changed from $htmlcellsteam='<div class="cell_steam">'; to make steam non clickable!
        $htmlcellsteam='<div class="cell">';
        $htmldiv='</div>';


        $grid=$this->game->getGrid();
        $rows= count($grid);
        $cols=count($grid[0]);
        for($i=0;$i<$rows;$i++){
            print_r($html); //print row div
            for($j=0;$j<$cols;$j++) {
                if ($grid[$i][$j] instanceof Tile) {
                    if ($grid[$i][$j] instanceof TileSteam){
                        $htmlurl = $grid[$i][$j]->getImageHTML();

                        $htmlgrid = <<<HTML
<img src=$htmlurl width="50" height="50">
HTML;
                        //}
                        if ($grid[$i][$j]->ReturnPlayer()==$this->game->get_current_turn()->ReturnName() && $this->game->getWinner()===null){
                            print_r($htmlcellsteam);
                        }else{
                            print_r($htmlcell);
                        }
                        print_r($htmlgrid); //print the image
                    }else{
                        print_r($htmlcell); //print column div
                        if ($this->game->getWinner()===null){
                            print_r($grid[$i][$j]->getImageHTML());
                        }elseif($this->game->getWinner()->WinStatus($this->game->getSize())){
                            print_r($grid[$i][$j]->getImageHTML($this->game->getWinner()->ReturnName()));
                        } else{
                            print_r($grid[$i][$j]->getImageHTML());
                        }
                    }
                }
                else{
                    print_r($html4);//print empty cell
                }
                print_r($htmldiv);
            }
            print($html2); //close the row div

        }


    }

    public function present_Player(){

        //this gets the other players name
        if ($this->game->getWinner()===null){
            $playerName = $this->game->get_current_turn()->ReturnName();

            //get opposite player name if this is needed
            /*if ($playerName == $this->game->getPlayer1()->ReturnName()){
                $playerName = $this->game->getPlayer2()->ReturnName();
            }
            else{
                $playerName = $this->game->getPlayer1()->ReturnName();
            }*/

            $htmlName = <<< HTML
<H1>Waiting for $playerName to take their turn! </H1>
HTML;
        }

        elseif ($this->game->getWinner()->ReturnName()==$this->game->getPlayer1()->ReturnName()){
            $playerName = $this->game->getPlayer1()->ReturnName();
            $htmlName = <<< HTML
<H1>$playerName, you have won! </H1>
HTML;
        }

        else{
            $playerName = $this->game->getPlayer2()->ReturnName();
            $htmlName = <<< HTML
<H1>$playerName, you have won! </H1>
HTML;
        }
        print_r($htmlName);
    }
}