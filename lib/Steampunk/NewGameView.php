<?php


namespace Steampunk;


class NewGameView extends View
{

    public function present_Game(){
        $html = <<<HTML
        
<body>

<div class="welcome_main">

    <img src="images/title.png" alt="Title"><br>
    <img id="steamsplash" src="images/steamsplash.jpg" width="500" height="352" alt="Demo image">
    <div class="players">
        <br>
        <p> Name, create a new game and wait for your opponent!</p>
        <br>
    </div>
    <form class ="Players_and_buttons" method="post" action="post/NewGame.php">
<label for="room">Your room name: </label><input type="text" name="room" placeholder="Create your own room">
<br>
<br>
Please choose grid size!
<br>
        
        <input type="radio" name="gameboard_size" value="6" checked>  6x6<br>
        <input type="radio" name="gameboard_size" value="10"> 10x10<br>
        <input type="radio" name="gameboard_size" value="20"> 20x20<br>
        <br>
        <input class="gamelinker" type="submit"  value="Submit" ></form>
        <br>
</div>
</body>

HTML;
        return $html;
    }


}