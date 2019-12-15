<?php

require "../../lib/site.inc.php";
//the PHP array that will encoded in the JSON format
$player1 = new \Steampunk\Player("a",1,6);
$player2 = new \Steampunk\Player('b',2,6);
$title = new \Steampunk\Game();
$title->setGridSize(6);
$title->setPlayer1($player1);
$title->setPlayer2($player2);
$title->set_current_turn($player1);
$grid = $title->getGrid();


//encoding the PHP array
// convert object => json
//ob_start();
//print_r($grid);
//$temp =  ob_get_clean();
$aaa = serialize($grid);
$bbb = unserialize($aaa);
$array = array();
echo count($bbb);
for ($a =0; $a<count($bbb);$a++){
    for ($b = 0; $b<count($bbb[$a]);$b++){
        if ($bbb[$a][$b] instanceof \Steampunk\Tile){
            print_r($bbb[$a][$b]->ReturnCoordination());
            echo $bbb[$a][$b]->ReturnPlayer();
            //print($bbb[$a][$b]);
            echo "<br>";
        }
    }
}
print_r(unserialize($aaa))  ;
$json = json_encode($bbb);
echo "<br>Start:<br>";
print_r(json_decode($json));


