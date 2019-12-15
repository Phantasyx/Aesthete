<?php
require 'lib/site.inc.php';
$view = new Steampunk\WaitingView($site);
?>
<!DOCTYPE html>
<html lang="en">
<link href="lib/css/main.css" type="text/css" rel="stylesheet" />
<head>
    <?php echo $view->refresh()?>
<!--    <meta http-equiv="refresh" content="5;url=http://www.yoursite.com">
        needs to be changed to this once I figure out how to link the pages-->
    <meta charset="UTF-8">
    <title>Waiting for Game</title>


</head>

<body>
<div class="welcome_main"><img src="images/title.png">
<article>
    <form method="post" action="game-post.php">
        <div class="game">
            <p>Waiting for another player to join.</p>
        </div>
    </form>
</article>
</body>
</html>