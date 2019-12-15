<?php

require __DIR__ . "/../../vendor/autoload.php";

/** @file
 * @brief Empty unit testing template
 * @cond 
 * @brief Unit tests for the class 
 */
class TileSteamTest extends \PHPUnit_Framework_TestCase
{
    public function test1() {
        //$this->assertEquals($expected, $actual);
        $steam = new \Steampunk\TileSteam(1,1,\Steampunk\TileSteam::N,'John');
        $this->assertEquals(array(1,1),$steam->ReturnCoordination());
        $this->assertEquals('John',$steam->ReturnPlayer());
        $this->assertEquals(\Steampunk\TileSteam::N,$steam->GetDirection());
        $this->assertEquals('leak-n.png',$steam->chooseImage());
        $this->assertEquals('http://webdev.cse.msu.edu/~hughe211/project1/images/leak-n.png',$steam->getImageHTML());

        $steam->SetDirection(\Steampunk\TileSteam::S);
        $this->assertEquals(\Steampunk\TileSteam::S,$steam->GetDirection());
        $this->assertEquals('leak-s.png',$steam->chooseImage());
        $this->assertEquals('http://webdev.cse.msu.edu/~hughe211/project1/images/leak-s.png',$steam->getImageHTML());

        $steam->SetCoordination(0,0);
        $steam->SetPlayer('Bob');
        $this->assertEquals(array(0,0),$steam->ReturnCoordination());
        $this->assertEquals('Bob',$steam->ReturnPlayer());
        $this->assertEquals('leak-s.png',$steam->chooseImage());
        $this->assertEquals('http://webdev.cse.msu.edu/~hughe211/project1/images/leak-s.png',$steam->getImageHTML());

        $this->assertEquals(true,$steam instanceof \Steampunk\Tile);
        $this->assertEquals(false, $steam instanceof \Steampunk\TileGauge);
        $this->assertEquals(false, $steam instanceof \Steampunk\TileSource);
        $this->assertEquals(false,$steam instanceof \Steampunk\TilePipe);
    }
}

/// @endcond
?>
