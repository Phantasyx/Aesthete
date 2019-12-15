<?php


namespace Steampunk;


class GameView extends View
{

    private $game;
    private $site;
    private $user;

    public function __construct(Game $game, Site $site,User $user)
    {
        $this->game=$game;
        $this->site=$site;
        $this->user = $user;
    }

    public function refresh_line() {
        $games = new Games($this->site);
        if($games->your_turn($this->game->getGameId(),$this->user->getId())){
            $html="";
        } else {
            $root = $this->site->getRoot();
            $html=<<<HTML
<meta http-equiv="refresh" content="2;url=$root/game-post.php" >
HTML;
        }

        return $html;

    }


    public function present_Game(){
        $html= '<div class="row">' ;
        $html2='</div>';
        $html4='<div class="cell">';
        $htmlcell='<div class="cell">';
        $htmlcellsteam='<div class="cell_steam">';
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
                        if ($this->game->getWinner()===null){
                            $games = new Games($this->site);
                            if ($games->your_turn($this->game->getGameId(),$this->user->getId())){
                                $htmlgrid = <<<HTML
<input type="submit" name="leak" style=background:url($htmlurl) value=$j,$i>
HTML;
                            }else{

                                $htmlgrid = <<<HTML
<img src=$htmlurl width="50" height="50">
HTML;
                            }
                        }else{
                            $htmlgrid = <<<HTML
<img src=$htmlurl width="50" height="50">
HTML;
                        }
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


    public function presnt_5images()
    {

        $html1 = '<div class="stock">';

        $html2 = '<label class="stock_image">';

        $htmldiv = '</label>';


        $stock = $this->game->get_current_turn()->ReturnStock();
        print_r($html1);
        for ($j = 0; $j < 5; $j++) {
            print_r($html2);
            print_r($stock[$j]->getImageHTML());
            if ($this->game->getNumber()==$j){
                $htmlcb = <<<HTML
<input class="cb1" type="radio" name="cb" value=$j checked>
HTML;
            }else{
                $htmlcb = <<<HTML
<input class="cb1" type="radio" name="cb" value=$j>
HTML;
            }

            print_r($htmldiv);
            print_r($htmlcb);
        }
        print_r($htmldiv);
    }

    public function present_Buttons(){

        $htmldivbuttons='<div class=buttons>';
        $htmldivbutton='<div class=button>';
        $htmldivbuttonsend='</div>';
        $htmlcb1s='<input class="cb1s" type="submit" name="cbs" value="Rotate">';
        $htmlcb2s='<input class="cb2s" type="submit" name="cbs" value="Discard">';
        $htmlcb3s='<input class="cb3s" type="submit" name="cbs" value="Open Valve">';
        $htmlcb4s='<input class="cb4s" type="submit" name="cbs" value="Give Up">';
        print_r('<br>');
        print_r($htmldivbuttons);
        print_r($htmldivbutton);
        print_r($htmlcb1s);
        print_r($htmldivbuttonsend);
        print_r($htmldivbutton);
        print_r($htmlcb2s);
        print_r($htmldivbuttonsend);
        print_r($htmldivbutton);
        print_r($htmlcb3s);
        print_r($htmldivbuttonsend);
        print_r($htmldivbutton);
        print_r($htmlcb4s);
        print_r($htmldivbuttonsend);
        print_r($htmldivbuttonsend);
    }

    public function present_Player(){
        if ($this->game->getWinner()===null){
            $playerName = $this->game->get_current_turn()->ReturnName();
            $htmlName = <<< HTML
<H1>$playerName, it is your turn! </H1>
HTML;
        }elseif ($this->game->getWinner()->ReturnName()==$this->game->getPlayer1()->ReturnName()){
            $playerName = $this->game->getPlayer1()->ReturnName();
            $htmlName = <<< HTML
<H1>$playerName, you have won! </H1>
HTML;
        }else{
            $playerName = $this->game->getPlayer2()->ReturnName();
            $htmlName = <<< HTML
<H1>$playerName, you have won! </H1>
HTML;
        }
        print_r($htmlName);
    }
}