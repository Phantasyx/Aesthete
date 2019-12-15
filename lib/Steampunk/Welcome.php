<?php

namespace Steampunk;


class Welcome
{
    private $p1;
    private $p2;
    private $size;
    public function __construct()
    {
        $this->p1="";
        $this->p2="";
        $this->size=null;
    }


    public function IsReady(){
        if($this->p1!=null && $this->p2!=null && $this->size!=null){
            return true;
        }
        else{
            return false;
        }
    }
    public function Get_Player1(){
        return $this->p1;
    }
    public function Get_Player2(){
        return $this->p2;
    }
    public function Get_Size(){
        return $this->size;
    }
    public function Set_Player1($pp1){
         $this->p1=$pp1;
    }
    public function Set_Player2($pp2){
        $this->p2=$pp2;
    }
    public function Set_Size($s){
        $this->size=$s;
    }
}