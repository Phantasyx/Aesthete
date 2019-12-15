<?php

require __DIR__ . "/../../vendor/autoload.php";

/** @file
 * @brief Empty unit testing template
 * @cond 
 * @brief Unit tests for the class 
 */
class PlayerTest extends \PHPUnit_Framework_TestCase
{

    public function test_construct() {
        $player = new Steampunk\Player("TestPlayer",1,6);
        $this->assertInstanceOf('Steampunk\Player', $player);
        $this->assertEquals("TestPlayer",$player->ReturnName());
        $this->assertEquals(1,sizeof($player->ReturnSteam()));
        $this->assertEquals(3,sizeof($player->ReturnPipeline()));

        $source = $player->ReturnPipeline()[0];
        $this->assertEquals(true,$source instanceof \Steampunk\TileSource);
        $this->assertEquals(array(0,0),$source->ReturnCoordination());
        $this->assertEquals(array('N'=>false,'S'=>false,"W"=>false,'E'=>true),$source->GetDirection());

        $gauge = $player->ReturnPipeline()[1];
        $this->assertEquals(true,$gauge instanceof \Steampunk\TileGaugeBottom);
        $this->assertEquals(array(7,1),$gauge->ReturnCoordination());
        $this->assertEquals(array('N'=>false,'S'=>false,"W"=>true,'E'=>false),$gauge->GetDirection());

        $this->assertEquals(false,isset($player->ReturnPipeline()[3]));

        $steam = $player->ReturnSteam()[0];
        $this->assertEquals(true,$steam instanceof \Steampunk\TileSteam);
        $this->assertEquals(array(1,0),$steam->ReturnCoordination());
        $this->assertEquals(\Steampunk\TileSteam::W,$steam->GetDirection());

        $player = new Steampunk\Player("TestPlayer2",2,6);
        $this->assertInstanceOf('Steampunk\Player', $player);
        $this->assertEquals("TestPlayer2",$player->ReturnName());
        $this->assertEquals(1,sizeof($player->ReturnSteam()));
        $this->assertEquals(3,sizeof($player->ReturnPipeline()));

        $source = $player->ReturnPipeline()[0];
        $this->assertEquals(true,$source instanceof \Steampunk\TileSource);
        $this->assertEquals(array(0,5),$source->ReturnCoordination());
        $this->assertEquals(array('N'=>false,'S'=>false,"W"=>false,'E'=>true),$source->GetDirection());

        $gauge = $player->ReturnPipeline()[1];
        $this->assertEquals(true,$gauge instanceof \Steampunk\TileGaugeBottom);
        $this->assertEquals(array(7,4),$gauge->ReturnCoordination());
        $this->assertEquals(array('N'=>false,'S'=>false,"W"=>true,'E'=>false),$gauge->GetDirection());

        $this->assertEquals(false,isset($player->ReturnPipeline()[3]));

        $steam = $player->ReturnSteam()[0];
        $this->assertEquals(true,$steam instanceof \Steampunk\TileSteam);
        $this->assertEquals(array(1,5),$steam->ReturnCoordination());
        $this->assertEquals(\Steampunk\TileSteam::W,$steam->GetDirection());
    }

    public function test_addPipe(){
        $player = new \Steampunk\Player('Jon',1,6);
        $temp_pipe = new \Steampunk\TilePipe(1,0,\Steampunk\TilePipe::Cap,'Jon');
        $player->AddPipe($temp_pipe);
        $pipe = $player->ReturnPipeline()[3];
        $this->assertEquals(true,$pipe instanceof \Steampunk\TilePipe);
        $this->assertEquals(array(1,0),$pipe->ReturnCoordination());
        $this->assertEquals('Jon',$pipe->ReturnPlayer());
    }

    public function test_steam(){
        $player = new \Steampunk\Player('John',1,6);
        $steam1 = new \Steampunk\TileSteam(1,1,\Steampunk\TileSteam::N,'John');
        $steam2 = new \Steampunk\TileSteam(1,2,\Steampunk\TileSteam::S,'John');
        $player->addSteam($steam1);
        $player->addSteam($steam2);
        $this->assertEquals(true,isset($player->ReturnSteam()[1]));
        $this->assertEquals(true,isset($player->ReturnSteam()[2]));
        $player->deleteSteam(1);
        $this->assertEquals(2,count($player->ReturnSteam()));
        $this->assertEquals(true,isset($player->ReturnSteam()[1]));
        $this->assertEquals(false,isset($player->ReturnSteam()[2]));
        $this->assertEquals($steam2, $player->ReturnSteam()[1]);
    }

    public function test_CheckConnection(){
        $player = new \Steampunk\Player('John',1,6);
        $straight = new \Steampunk\TilePipe(1,0,\Steampunk\TilePipe::Straight,'John');
        $player->AddPipe($straight);
        $player->deleteSteam(0);
        $cap = new \Steampunk\TilePipe(2,0,\Steampunk\TilePipe::Cap,'John');
        $cap->Rotate();
        $player->AddPipe($cap);
        $this->assertEquals(true,$player->CheckConnection($cap,6));
        $this->assertEquals(true,$player->CheckConnection($straight,6));
    }

    public function test_WinStatus(){
        $player = new \Steampunk\Player('John',1,6);
        $player->AddPipe(new \Steampunk\TilePipe(1,0,\Steampunk\TilePipe::Straight,'John'),6);
        $this->assertEquals(false,$player->WinStatus(6));
        $player->AddPipe(new \Steampunk\TilePipe(2,0,\Steampunk\TilePipe::Straight,'John'),6);
        $this->assertEquals(false,$player->WinStatus(6));
        $player->AddPipe(new \Steampunk\TilePipe(3,0,\Steampunk\TilePipe::Straight,'John'),6);
        $this->assertEquals(false,$player->WinStatus(6));
        $player->AddPipe(new \Steampunk\TilePipe(4,0,\Steampunk\TilePipe::Straight,'John'),6);
        $this->assertEquals(false,$player->WinStatus(6));
        $player->AddPipe(new \Steampunk\TilePipe(5,0,\Steampunk\TilePipe::Straight,'John'),6);
        $this->assertEquals(false,$player->WinStatus(6));
        $ninety1 = new \Steampunk\TilePipe(6,0,\Steampunk\TilePipe::Ninety,'John');
        $player->AddPipe($ninety1);
        $this->assertEquals(false,$player->WinStatus(6));
        $ninety2 = new \Steampunk\TilePipe(6,1,\Steampunk\TilePipe::Ninety,'John');
        $ninety2->Rotate();
        $ninety2->Rotate();
        $player->AddPipe($ninety2);
        $this->assertEquals(true,$player->WinStatus(6));
    }

    public function test_stocks(){
        $player = new \Steampunk\Player('John',1,6);
        $pipe1 = new \Steampunk\TilePipe(1,0,\Steampunk\TilePipe::Straight,'John');
        $player->AddStock($pipe1);
        $pipe2 = new \Steampunk\TilePipe(1,0,\Steampunk\TilePipe::Straight,'John');
        $player->AddStock($pipe2);
        $pipe3 = new \Steampunk\TilePipe(1,0,\Steampunk\TilePipe::Straight,'John');
        $player->AddStock($pipe3);
        $pipe4 = new \Steampunk\TilePipe(1,0,\Steampunk\TilePipe::Straight,'John');
        $player->AddStock($pipe4);
        $pipe5 = new \Steampunk\TilePipe(1,0,\Steampunk\TilePipe::Straight,'John');
        $player->AddStock($pipe5);
        $array = array($pipe1,$pipe2,$pipe3,$pipe4,$pipe5);
        $this->assertEquals($array,$player->ReturnStock());
        $pipeTemp = new \Steampunk\TilePipe(1,0,\Steampunk\TilePipe::Ninety,'John');
        $player->ReplaceStock($pipeTemp,3);
        $array = array($pipe1,$pipe2,$pipe3,$pipeTemp,$pipe5);
        $this->assertEquals($array,$player->ReturnStock());
    }

    public function test_connect(){
        $player = new \Steampunk\Player('John',1,6);
        $pipe = new \Steampunk\TilePipe(1,0,\Steampunk\TilePipe::Straight,'John');
        $this->assertTrue($player->Connected($pipe,$player->ReturnSteam()[0]));
    }
}

/// @endcond
?>
