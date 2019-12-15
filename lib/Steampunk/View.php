<?php


namespace Steampunk;


class View
{
    private $title;

    public function setTitle($title){
        $this->title = $title;
    }

    public function head(){
        $html=<<<HTML
<meta charset="UTF-8">
    <title>$this->title</title>
    <link href="lib/css/main.css" type="text/css" rel="stylesheet" />
HTML;
        return $html;
    }

}