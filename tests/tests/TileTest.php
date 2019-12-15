<?php

require __DIR__ . "/../../vendor/autoload.php";

/** @file
 * @brief Empty unit testing template
 * @cond 
 * @brief Unit tests for the class 
 */
class TileTemp extends \Steampunk\Tile
{
    public function __construct($x, $y, $name)
    {
        parent::__construct($x, $y, $name);
    }
    public function getImageHTML($winer = null)
    {
        // TODO: Implement getImageHTML() method.
    }
}

class TileTest extends \PHPUnit_Framework_TestCase
{
    public function test_construct(){
        $tile = new TileTemp(1,1,'John');
        $this->assertEquals(array(1,1),$tile->ReturnCoordination());
        $this->assertEquals('John',$tile->ReturnPlayer());
    }
    public function test_setPlayer(){
        $tile = new TileTemp(1,1,'John');
        $this->assertEquals(array(1,1),$tile->ReturnCoordination());
        $this->assertEquals('John',$tile->ReturnPlayer());
        $tile->SetPlayer('Bob');
        $this->assertEquals(array(1,1),$tile->ReturnCoordination());
        $this->assertEquals('Bob',$tile->ReturnPlayer());
    }
    public function test_setCoordination(){
        $tile = new TileTemp(1,1,'John');
        $this->assertEquals(array(1,1),$tile->ReturnCoordination());
        $this->assertEquals('John',$tile->ReturnPlayer());
        $tile->SetCoordination(0,0);
        $this->assertEquals(array(0,0),$tile->ReturnCoordination());
        $this->assertEquals('John',$tile->ReturnPlayer());
    }

}

/// @endcond
?>
