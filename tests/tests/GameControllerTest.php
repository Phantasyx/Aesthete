<?php

require __DIR__ . "/../../vendor/autoload.php";

/** @file
 * @brief Empty unit testing template
 * @cond 
 * @brief Unit tests for the class 
 */
class GameControllerTest extends \PHPUnit_Framework_TestCase
{
    public function test_construct() {
        $game = new \Steampunk\Game();
        $player1 = new \Steampunk\Player('John',1,6);
        $player2 = new \Steampunk\Player('Bob',2,6);
        $gameController = new \Steampunk\GameController($game);
        $gameController->setPlayer1($player1);
        $gameController->setPlayer2($player2);
        $gameController->setCurrentPlayer($player1);
        $gameController->setSize(6);
        $this->assertEquals($player1,$game->getPlayer1());
        $this->assertEquals($player2,$game->getPlayer2());
        $this->assertEquals($player1,$game->get_current_turn());
        $this->assertFalse($gameController->getWinner());
    }

    public function test_rotate(){
        $game = new \Steampunk\Game();
        $player1 = new \Steampunk\Player('John',1,6);
        $player2 = new \Steampunk\Player('Bob',2,6);
        $gameController = new \Steampunk\GameController($game);
        $gameController->setPlayer1($player1);
        $gameController->setPlayer2($player2);
        $gameController->setCurrentPlayer($player1);
        $gameController->setSize(6);

        $stockpipe = $game->getPlayer1()->ReturnStock()[0];
        $stockpipe->Rotate();
        $gameController->rotate(0);
        $this->assertEquals($stockpipe,$game->getPlayer1()->ReturnStock()[0]);

        $stockpipe = $game->getPlayer1()->ReturnStock()[1];
        $stockpipe->Rotate();
        $gameController->rotate(1);
        $this->assertEquals($stockpipe,$game->getPlayer1()->ReturnStock()[1]);

        $stockpipe = $game->getPlayer1()->ReturnStock()[2];
        $stockpipe->Rotate();
        $gameController->rotate(2);
        $this->assertEquals($stockpipe,$game->getPlayer1()->ReturnStock()[2]);

        $stockpipe = $game->getPlayer1()->ReturnStock()[3];
        $stockpipe->Rotate();
        $gameController->rotate(3);
        $this->assertEquals($stockpipe,$game->getPlayer1()->ReturnStock()[3]);

        $stockpipe = $game->getPlayer1()->ReturnStock()[4];
        $stockpipe->Rotate();
        $gameController->rotate(4);
        $this->assertEquals($stockpipe,$game->getPlayer1()->ReturnStock()[4]);

        $this->assertFalse($gameController->getWinner());

	}
    public function test_addPipe(){
	    $game = new \Steampunk\Game();
	    $controller = new \Steampunk\GameController($game);
	    $player1 = new \Steampunk\Player('John',1,6);
	    $controller->setPlayer1($player1);
	    $player2 = new \Steampunk\Player('Bob',2,6);
	    $controller->setPlayer2($player2);
	    $controller->setSize(6);
	    $controller->setCurrentPlayer($player1);
        for ($i = 0;$i<5;$i++){
            $stockpipe = $game->getPlayer1()->ReturnStock()[$i];
            if ($bool = $controller->addPipe(1,0,$i)){
                $pipe = $game->getPlayer1()->ReturnPipeline()[3];
                $this->assertEquals(new \Steampunk\TilePipe(1,0,$stockpipe->ReturnType(),'John'),$pipe);
                break;
            }
        }
        $this->assertFalse($controller->getWinner());
    }

    public function test_nextTurn(){
        $game = new \Steampunk\Game();
        $controller = new \Steampunk\GameController($game);
        $player1 = new \Steampunk\Player('John',1,6);
        $controller->setPlayer1($player1);
        $player2 = new \Steampunk\Player('Bob',2,6);
        $controller->setPlayer2($player2);
        $controller->setSize(6);
        $controller->setCurrentPlayer($player1);
        $controller->nextTurn();
        $this->assertEquals('Bob',$game->get_current_turn()->ReturnName());
        $this->assertEquals('Bob',$controller->getName());
        $this->assertFalse($controller->getWinner());
    }

    public function test_openValve(){
        $game = new \Steampunk\Game();
        $controller = new \Steampunk\GameController($game);
        $player1 = new \Steampunk\Player('John',1,6);
        $controller->setPlayer1($player1);
        $player2 = new \Steampunk\Player('Bob',2,6);
        $controller->setPlayer2($player2);
        $controller->setSize(6);
        $controller->setCurrentPlayer($player1);
        $this->assertEquals($game->getPlayer1(),$game->get_current_turn());
        $this->assertFalse($controller->getWinner());
        $controller->openValve();
        $this->assertTrue($controller->getWinner());
        $this->assertEquals($game->getPlayer2(),$game->getWinner());

        $game = new \Steampunk\Game();
        $controller = new \Steampunk\GameController($game);
        $player1 = new \Steampunk\Player('John',1,6);
        $controller->setPlayer1($player1);
        $player2 = new \Steampunk\Player('Bob',2,6);
        $controller->setPlayer2($player2);
        $controller->setSize(6);
        $controller->setCurrentPlayer($player1);
        $this->assertEquals($game->getPlayer1(),$game->get_current_turn());
        $controller->nextTurn();
        $this->assertFalse($controller->getWinner());
        $controller->openValve();
        $this->assertTrue($controller->getWinner());
        $this->assertEquals($game->getPlayer1(),$game->getWinner());
    }

    public function test_setLoser(){
        $game = new \Steampunk\Game();
        $controller = new \Steampunk\GameController($game);
        $player1 = new \Steampunk\Player('John',1,6);
        $controller->setPlayer1($player1);
        $player2 = new \Steampunk\Player('Bob',2,6);
        $controller->setPlayer2($player2);
        $controller->setSize(6);
        $controller->setCurrentPlayer($player1);
        $this->assertEquals($game->getPlayer1(),$game->get_current_turn());
        $this->assertFalse($controller->getWinner());
        $controller->set_loser();
        $this->assertFalse($controller->getWinner());
        $this->assertEquals($game->getPlayer2(),$game->getWinner());

        $game = new \Steampunk\Game();
        $controller = new \Steampunk\GameController($game);
        $player1 = new \Steampunk\Player('John',1,6);
        $controller->setPlayer1($player1);
        $player2 = new \Steampunk\Player('Bob',2,6);
        $controller->setPlayer2($player2);
        $controller->setSize(6);
        $controller->setCurrentPlayer($player1);
        $controller->nextTurn();
        $this->assertEquals($game->getPlayer2(),$game->get_current_turn());
        $this->assertFalse($controller->getWinner());
        $controller->set_loser();
        $this->assertFalse($controller->getWinner());
        $this->assertEquals($game->getPlayer1(),$game->getWinner());
    }

    public function test_setNumber(){
        $game = new \Steampunk\Game();
        $controller = new \Steampunk\GameController($game);
        $player1 = new \Steampunk\Player('John',1,6);
        $controller->setPlayer1($player1);
        $player2 = new \Steampunk\Player('Bob',2,6);
        $controller->setPlayer2($player2);
        $controller->setSize(6);
        $controller->setCurrentPlayer($player1);
        $controller->setNumber(3);
        $this->assertEquals(3,$game->getNumber());
    }

    public function test_reset(){
        $game = new \Steampunk\Game();
        $controller = new \Steampunk\GameController($game);
        $player1 = new \Steampunk\Player('John',1,6);
        $controller->setPlayer1($player1);
        $player2 = new \Steampunk\Player('Bob',2,6);
        $controller->setPlayer2($player2);
        $controller->setSize(6);
        $controller->setCurrentPlayer($player1);
        $this->assertEquals($game->getPlayer1(),$game->get_current_turn());
        $this->assertFalse($controller->getWinner());
        $controller->openValve();
        $this->assertTrue($controller->getWinner());
        $this->assertEquals($game->getPlayer2(),$game->getWinner());
        $controller->reset();
        $this->assertEquals($game->getPlayer1(),$game->get_current_turn());
        $this->assertFalse($controller->getWinner());
        $this->assertEquals(null,$game->getWinner());

    }
}

/// @endcond
?>
