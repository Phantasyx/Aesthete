<?php

require __DIR__ . "/../../vendor/autoload.php";

/** @file
 * @brief Empty unit testing template
 * @cond 
 * @brief Unit tests for the class 
 */
class TileGaugeTopTest extends \PHPUnit_Framework_TestCase
{
    public function test1() {
        //$this->assertEquals($expected, $actual);
        $gauge = new \Steampunk\TileGaugeTop(1,1,'John');
        $this->assertEquals(array(1,1),$gauge->ReturnCoordination());
        $this->assertEquals('John',$gauge->ReturnPlayer());
        $this->assertEquals('gauge-top-190.png',$gauge->ChooseImage(true));
        $this->assertEquals('gauge-top-0.png',$gauge->ChooseImage(false));
        $this->assertContains('gauge-top-190.png',$gauge->getImageHTML('John'));
        $this->assertContains('gauge-top-0.png',$gauge->getImageHTML());

        $gauge->SetCoordination(0,0);
        $this->assertEquals(array(0,0),$gauge->ReturnCoordination());

        $gauge->SetPlayer('Bob');
        $this->assertEquals('Bob',$gauge->ReturnPlayer());
        $this->assertContains('gauge-top-0.png',$gauge->getImageHTML('John'));
        $this->assertContains('gauge-top-0.png',$gauge->getImageHTML());

        $this->assertEquals(true,$gauge instanceof \Steampunk\Tile);
        $this->assertEquals(false,$gauge instanceof \Steampunk\TilePipe);
        $this->assertEquals(false,$gauge instanceof \Steampunk\TileSource);
        $this->assertEquals(false,$gauge instanceof \Steampunk\TileSteam);
    }
}

/// @endcond
?>
