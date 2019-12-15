<?php

namespace Steampunk;


class WelcomeController
{
    private $welcome;
    private $reset;

    public function __construct(Welcome $welcome, $array)
    {
        $this->welcome=$welcome;
        if(isset($_POST['firstplayer'])){
            $this->welcome->Set_Player1($_POST['firstplayer']);
        }
        if(isset($_POST['secondplayer'])){
            $this->welcome->Set_Player2($_POST['secondplayer']);
        }
        if(isset($_POST['gameboard_size'])){
            $this->welcome->Set_Size($_POST['gameboard_size']);
        }
    }
    public function IsReset(){
        return $this->reset;

    }

}