<?php

require __DIR__ . "/../../vendor/autoload.php";

/** @file
 * @brief Empty unit testing template
 * @cond 
 * @brief Unit tests for the class 
 */
class WelcomeTest extends \PHPUnit_Framework_TestCase
{
	public function test_construct() {
		$welcome = new \Steampunk\Welcome();
        $this->assertInstanceOf("\Steampunk\Welcome",$welcome);
        $this->assertEquals($welcome->IsReady(),false);
        $welcome->Set_Player1("Bill");
        $welcome->Set_Player2("Bob");
        $welcome->Set_Size(16);

        $this->assertEquals($welcome->IsReady(),true);
        $this->assertEquals($welcome->Get_Player1(),"Bill");
        $this->assertEquals($welcome->Get_Player2(),"Bob");
        $this->assertEquals($welcome->Get_Size(),16);
	}
}

/// @endcond
?>
