<?php
$open=true;
require "lib/site.inc.php";
$view = new \Steampunk\NewGameView();
$view->setTitle("NewGame");?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php echo $view->head()?>
</head>

<?php
echo $view->present_Game();
?>
</html>