<?php
require 'lib/site.inc.php';
$view = new Steampunk\GameView($Game,$site,$_SESSION[\Steampunk\User::SESSION_NAME]);
?>
<!DOCTYPE html>
<html lang="en">
<link href="lib/css/main.css" type="text/css" rel="stylesheet" />
<head>
    <?php
        echo $view->refresh_line();
    ?>
    <meta charset="UTF-8">
    <title>Game</title>


</head>

<body>
<div class="game_board"><img src="images/title.png">
<article>
    <form method="post" action="game-post.php">

        <div class="game">
        <?php
            $view->present_Game();

        ?>
        </div>
        <?php
        $view->present_Player();
        ?>

        <div class="stock">
        <?php
        $games = new \Steampunk\Games($site);
        if ($games->your_turn($Game->getGameId(),$_SESSION[\Steampunk\User::SESSION_NAME]->getId())){

            //if json is not null
                //add to stock
                //call the add pipe function
                //delete old json info

            $view->presnt_5images();
            $view->present_Buttons();
        }else{
            echo <<<HTML
<p>Waiting</p>
HTML;

        }

        ?>
        </div>
    </form>
</article>
</body>
</html>