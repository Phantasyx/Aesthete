<?php

require __DIR__ . "/../../vendor/autoload.php";

/** @file
 * @brief Empty unit testing template
 * @cond 
 * @brief Unit tests for the class 
 */
class GameViewTest extends \PHPUnit_Framework_TestCase
{
	public function test_construct() {
		//$this->assertEquals($expected, $actual);
        $game = new \Steampunk\Game();
        $player1 = new \Steampunk\Player('John',1,6);
        $player2 = new \Steampunk\Player('Bob',2,6);
        $game->setPlayer1($player1);
        $game->setPlayer2($player2);
        $game->setGridSize(6);
        $game->set_current_turn($player1);

        $view = new \Steampunk\GameView($game);

        $view->present_Game();
        $view->present_Buttons();
        $view->present_Player();

        $this->assertInstanceOf("\Steampunk\GameView",$view);
    }
}

/// @endcond
?>
