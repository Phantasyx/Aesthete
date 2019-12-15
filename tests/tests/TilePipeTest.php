<?php

require __DIR__ . "/../../vendor/autoload.php";

/** @file
 * @brief Empty unit testing template
 * @cond
 * @brief Unit tests for the class
 */

class TilePipeTest extends \PHPUnit_Framework_TestCase
{
    public function test1() {
        //$this->assertEquals($expected, $actual);
        $pipe = new \Steampunk\TilePipe(0,0,\Steampunk\TilePipe::Cap,"John");
        $this->assertEquals(array(0,0),$pipe->ReturnCoordination());
        $this->assertEquals(\Steampunk\TilePipe::Cap,$pipe->ReturnType());
        $this->assertEquals("John",$pipe->ReturnPlayer());
        $this->assertEquals(array("N"=>false,"S"=>true,"E"=>false,"W"=>false),$pipe->GetDirection());
        $this->assertEquals('cap-s.png',$pipe->getImage());
        $this->assertContains('cap-s.png',$pipe->getImageHTML());

        $pipe->Rotate();
        $this->assertEquals(array("N"=>false,"S"=>false,"E"=>false,"W"=>true),$pipe->GetDirection());
        $this->assertEquals('cap-w.png',$pipe->getImage());
        $this->assertContains('cap-w.png',$pipe->getImageHTML());

        $pipe->SetPlayer("Bob");
        $this->assertEquals("Bob",$pipe->ReturnPlayer());

        $pipe->SetCoordination(1,1);
        $this->assertEquals(array(1,1),$pipe->ReturnCoordination());

        $pipe = new \Steampunk\TilePipe(0,0,\Steampunk\TilePipe::Ninety,"John");
        $this->assertEquals(\Steampunk\TilePipe::Ninety,$pipe->ReturnType());
        $this->assertEquals(array('N'=>false,"S"=>true,"W"=>true,'E'=>false),$pipe->GetDirection());
        $this->assertEquals('ninety-sw.png',$pipe->getImage());
        $this->assertContains('ninety-sw.png',$pipe->getImageHTML());

        $pipe->Rotate();
        $this->assertEquals(array('N'=>true,'W'=>true,'S'=>false,'E'=>false),$pipe->GetDirection());
        $this->assertEquals('ninety-wn.png',$pipe->getImage());
        $this->assertContains('ninety-wn.png',$pipe->getImageHTML());

        $pipe = new \Steampunk\TilePipe(0,0,\Steampunk\TilePipe::Straight,"John");
        $this->assertEquals(\Steampunk\TilePipe::Straight,$pipe->ReturnType());
        $this->assertEquals(array('N'=>false,"S"=>false,"W"=>true,'E'=>true),$pipe->GetDirection());
        $this->assertEquals('straight-h.png',$pipe->getImage());
        $this->assertContains('straight-h.png',$pipe->getImageHTML());

        $pipe->Rotate();
        $this->assertEquals(array('N'=>true,'W'=>false,'S'=>true,'E'=>false),$pipe->GetDirection());
        $this->assertEquals('straight-v.png',$pipe->getImage());
        $this->assertContains('straight-v.png',$pipe->getImageHTML());

        $pipe->Rotate();
        $this->assertEquals(array('N'=>false,"S"=>false,"W"=>true,'E'=>true),$pipe->GetDirection());
        $this->assertEquals('straight-h.png',$pipe->getImage());
        $this->assertContains('straight-h.png',$pipe->getImageHTML());

        $pipe = new \Steampunk\TilePipe(0,0,\Steampunk\TilePipe::Tee,"John");
        $this->assertEquals(\Steampunk\TilePipe::Tee,$pipe->ReturnType());
        $this->assertEquals(array("N"=>false,"S"=>true,"E"=>true,"W"=>true),$pipe->GetDirection());
        $this->assertEquals('tee-esw.png',$pipe->getImage());
        $this->assertContains('tee-esw.png',$pipe->getImageHTML());

        $pipe->Rotate();
        $this->assertEquals(array("N"=>true,"S"=>true,"E"=>false,"W"=>true),$pipe->GetDirection());
        $this->assertEquals('tee-swn.png',$pipe->getImage());
        $this->assertContains('tee-swn.png',$pipe->getImageHTML());

        $this->assertEquals(true,$pipe instanceof \Steampunk\Tile);
        $this->assertEquals(false,$pipe instanceof \Steampunk\TileSteam);
        $this->assertEquals(false,$pipe instanceof \Steampunk\TileGauge);
        $this->assertEquals(false,$pipe instanceof \Steampunk\TileSource);

    }
}

/// @endcond
?>