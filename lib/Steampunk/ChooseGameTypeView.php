<?php

namespace Steampunk;


class ChooseGameTypeView extends View
{
    public function __construct()
    {
        $this->setTitle("Choose Game Type");
    }

    public function present(){
        $html = <<<HTML
        
<body>

<div class="welcome_main"><img src="images/title.png" alt="Title"><br>
    <img id="steamsplash" src="images/steamsplash.jpg" width="500" height="352" alt="Demo image">
    <div class="ChooseGameType">
        <form action="post/chooseGameType.php" method="post">
            <input type="submit" name="join" value="Join Game">
            <input type="submit" name="make" value="Make Game">
        </form>
    </div>
</div>
</body>

HTML;
        return$html;
    }
}