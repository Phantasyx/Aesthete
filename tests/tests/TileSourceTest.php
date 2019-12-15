<?php

require __DIR__ . "/../../vendor/autoload.php";

/** @file
 * @brief Empty unit testing template
 * @cond 
 * @brief Unit tests for the class 
 */
class TileSourceTest extends \PHPUnit_Framework_TestCase
{
    public function test1() {
        //$this->assertEquals($expected, $actual);
        $source = new \Steampunk\TileSource(0,0,'John');
        $this->assertEquals(array(0,0),$source->ReturnCoordination());
        $this->assertEquals('John',$source->ReturnPlayer());
        $this->assertEquals(array('N'=>false,'S'=>false,'W'=>false,'E'=>true),$source->GetDirection());
        $this->assertEquals('valve-closed.png',$source->ChooseImage(false));
        $this->assertEquals('valve-open.png',$source->ChooseImage(true));
        $this->assertContains('valve-closed.png',$source->getImageHTML());
        $this->assertContains('valve-open.png',$source->getImageHTML('John'));



        $source->SetCoordination(1,1);
        $source->SetPlayer("Bob");
        $this->assertEquals(array(1,1),$source->ReturnCoordination());
        $this->assertEquals('Bob',$source->ReturnPlayer());
        $this->assertEquals('valve-closed.png',$source->ChooseImage(false));
        $this->assertEquals('valve-open.png',$source->ChooseImage(true));
        $this->assertContains('valve-closed.png',$source->getImageHTML());
        $this->assertContains('valve-closed.png',$source->getImageHTML('John'));

        $this->assertEquals(true,$source instanceof \Steampunk\Tile);
        $this->assertEquals(false,$source instanceof \Steampunk\TileGauge);
        $this->assertEquals(false,$source instanceof \Steampunk\TilePipe);
        $this->assertEquals(false,$source instanceof \Steampunk\TileSteam);
    }
}

/// @endcond
?>
