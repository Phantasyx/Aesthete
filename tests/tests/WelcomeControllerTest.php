<?php


/** @file
 * @brief Empty unit testing template
 * @cond 
 * @brief Unit tests for the class 
 */
class EmptyTest extends \PHPUnit_Framework_TestCase
{
	public function test1() {

		$game = new Steampunk\Welcome();
		$Welcome = new Steampunk\WelcomeController($game,array());

		$this->assertInstanceOf('Steampunk\WelcomeController',$Welcome);
	}

    public function test_reset(){
        $game = new Steampunk\Welcome();
        $Welcome = new Steampunk\WelcomeController($game,array('size' => '16'));
        $this->assertEquals($Welcome->IsReset(),null);
    }
}

/// @endcond
?>
