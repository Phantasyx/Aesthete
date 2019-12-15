<?php

require __DIR__ . "/../../vendor/autoload.php";

/** @file
 * @brief Empty unit testing template
 * @cond 
 * @brief Unit tests for the class 
 */
class GameTest extends \PHPUnit_Framework_TestCase
{

	public function test_addPipe(){
	    $game = new \Steampunk\Game();
	    $player1 = new \Steampunk\Player('John',1,6);
	    $player2 = new \Steampunk\Player('Bob',2,6);
	    $game->setPlayer1($player1);
	    $game->setPlayer2($player2);
	    $game->setGridSize(6);
	    $game->set_current_turn($player1);
	    $pipe = new \Steampunk\TilePipe(1,0,\Steampunk\TilePipe::Straight,'John');
        $steam = $game->getPlayer1()->ReturnSteam()[0];
        $this->assertEquals(array(1,0),$steam->ReturnCoordination());
	    $game->addPipe($game->getPlayer1(),$pipe);
	    $steam = $game->getPlayer1()->ReturnSteam()[0];
	    //$check_pipe = $game->getPlayer1()->ReturnPipeline()[3];
	    $this->assertNotEquals(null, $game->getPlayer1()->ReturnPipeline()[3]);
	    $this->assertEquals(array(2,0),$steam->ReturnCoordination());
	    $game->update_turn();
        $pipe = new \Steampunk\TilePipe(1,5,\Steampunk\TilePipe::Straight,'Bob');
        $player = $game->get_current_turn();
        $this->assertEquals('Bob', $player->ReturnName());
        //echo $game->gameToJSON();
       // $game->addPipe()
    }


	public function test_Getters()
    {
        $game = new \Steampunk\Game();
        $player1 = new \Steampunk\Player('John',1,6);
        $player2 = new \Steampunk\Player('Bob',2,6);
        $game->setPlayer1($player1);
        $game->setPlayer2($player2);
        $game->setGridSize(6);
        $game->set_current_turn($player1);
        $this->assertEquals($player1, $game->GetPlayer1());
        $this->assertEquals($player2, $game->GetPlayer2());
        $this->assertEquals(6, $game->getSize());
        $this->assertEquals($player1, $game->get_current_turn());
        $this->assertEquals(null,$game->getWinner());

        $this->assertTrue($game->getGrid()[0][0] instanceof \Steampunk\TileSource);
        $this->assertTrue($game->getGrid()[1][7] instanceof \Steampunk\TileGaugeBottom);
        $this->assertTrue($game->getGrid()[0][7] instanceof \Steampunk\TileGaugeTop);
        $this->assertTrue($game->getGrid()[0][1] instanceof \Steampunk\TileSteam);

        $this->assertTrue($game->getGrid()[5][0] instanceof \Steampunk\TileSource);
        $this->assertTrue($game->getGrid()[4][7] instanceof \Steampunk\TileGaugeBottom);
        $this->assertTrue($game->getGrid()[3][7] instanceof \Steampunk\TileGaugeTop);
        $this->assertTrue($game->getGrid()[5][1] instanceof \Steampunk\TileSteam);
    }

    public function test_setNumber(){
        $game = new \Steampunk\Game();
        $player1 = new \Steampunk\Player('John',1,6);
        $player2 = new \Steampunk\Player('Bob',2,6);
        $game->setPlayer1($player1);
        $game->setPlayer2($player2);
        $game->setGridSize(6);
        $game->set_current_turn($player1);
        $game->setNumber(3);
        $this->assertEquals(3,$game->getNumber());
    }

    public function test_setLoser(){
        $game = new \Steampunk\Game();
        $player1 = new \Steampunk\Player('John',1,6);
        $player2 = new \Steampunk\Player('Bob',2,6);
        $game->setPlayer1($player1);
        $game->setPlayer2($player2);
        $game->setGridSize(6);
        $game->set_current_turn($player1);
        $game->set_loser();
        $this->assertEquals($game->getPlayer2(),$game->getWinner());
        $game->reset();
        $this->assertEquals(null,$game->getWinner());
        $this->assertEquals($game->getPlayer1(),$game->get_current_turn());

        $game = new \Steampunk\Game();
        $player1 = new \Steampunk\Player('John',1,6);
        $player2 = new \Steampunk\Player('Bob',2,6);
        $game->setPlayer1($player1);
        $game->setPlayer2($player2);
        $game->setGridSize(6);
        $game->set_current_turn($player1);
        $game->update_turn();
        $game->set_loser();
        $this->assertEquals($game->getPlayer1(),$game->getWinner());
        $game->reset();
        $this->assertEquals(null,$game->getWinner());
        $this->assertEquals($game->getPlayer1(),$game->get_current_turn());
    }

    public function test_setWinner(){
        $game = new \Steampunk\Game();
        $player1 = new \Steampunk\Player('John',1,6);
        $player2 = new \Steampunk\Player('Bob',2,6);
        $game->setPlayer1($player1);
        $game->setPlayer2($player2);
        $game->setGridSize(6);
        $game->set_current_turn($player1);
        $game->set_winner();
        $this->assertEquals($game->getPlayer1(),$game->getWinner());
        $game->reset();
        $this->assertEquals(null,$game->getWinner());
        $this->assertEquals($game->getPlayer1(),$game->get_current_turn());

        $game = new \Steampunk\Game();
        $player1 = new \Steampunk\Player('John',1,6);
        $player2 = new \Steampunk\Player('Bob',2,6);
        $game->setPlayer1($player1);
        $game->setPlayer2($player2);
        $game->setGridSize(6);
        $game->set_current_turn($player1);
        $game->update_turn();
        $game->set_winner();
        $this->assertEquals($game->getPlayer2(),$game->getWinner());
        $game->reset();
        $this->assertEquals(null,$game->getWinner());
        $this->assertEquals($game->getPlayer1(),$game->get_current_turn());
    }

    public function test_randompipe(){
        $game = new \Steampunk\Game();
        $player1 = new \Steampunk\Player('John',1,6);
        $player2 = new \Steampunk\Player('Bob',2,6);
        $game->setPlayer1($player1);
        $game->setPlayer2($player2);
        $game->setGridSize(6);
        $game->set_current_turn($player1);
        $pipe = $game->RandomPipe('John');
        $this->assertEquals('John',$pipe->ReturnPlayer());
        $this->assertTrue($pipe instanceof \Steampunk\TilePipe);
    }
}