<?php
$open = true;
require 'lib/site.inc.php';

$games = new \Steampunk\Games($site);

echo $games->getP1(27)."<br>";
echo $games->getP2(27)."<br>";
echo $games->getTurn(27)."<br>";
$games->nextTurn(27);
echo $games->getP1(27)."<br>";
echo $games->getP2(27)."<br>";
echo $games->getTurn(27)."<br>";


$test_pipe = new Steampunk\TilePipe(1,2,\Steampunk\TilePipe::Ninety,"BillyBob");
$test_pipe->Rotate();$test_pipe->Rotate();

echo "Testing all attributes of original object:";echo "<br>";
echo $test_pipe->ReturnPlayer();echo "<br>";
echo $test_pipe->GetDirection()['N'];echo "<br>";
echo $test_pipe->GetDirection()['S'];echo "<br>";
echo $test_pipe->GetDirection()['E'];echo "<br>";
echo $test_pipe->GetDirection()['W'];echo "<br>";
echo $test_pipe->getImage();echo "<br>";
echo $test_pipe->getImageHTML();echo "<br>";
echo $test_pipe->ReturnCoordination()['0'];echo "<br>";
echo $test_pipe->ReturnCoordination()['1'];echo "<br>";
echo $test_pipe->ReturnType();echo "<br>";

$json_text = $test_pipe->objectToJSON();
echo $json_text;

$new_pipe = Steampunk\Tile::JSONToObject($json_text);
echo "<br>";echo "<br>";echo "<br>";echo "<br>";
echo "Testing all attributes of new object:";echo "<br>";
echo $new_pipe->ReturnPlayer();echo "<br>";
echo $new_pipe->GetDirection()['N'];echo "<br>";
echo $new_pipe->GetDirection()['S'];echo "<br>";
echo $new_pipe->GetDirection()['E'];echo "<br>";
echo $new_pipe->GetDirection()['W'];echo "<br>";
echo $new_pipe->getImage();echo "<br>";
echo $new_pipe->getImageHTML();echo "<br>";
echo $new_pipe->ReturnCoordination()['0'];echo "<br>";
echo $new_pipe->ReturnCoordination()['1'];echo "<br>";
echo $new_pipe->ReturnType();echo "<br>";

$json_text2 = $new_pipe->objectToJSON();
echo $json_text2;