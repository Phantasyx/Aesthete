<?php

$open = true;
require "lib/site.inc.php";
$view = new \Steampunk\PasswordValidateView($site,$_GET)
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!--<meta charset="UTF-8">
    <title>Welcome</title>
    <link href="lib/css/Style.css" type="text/css" rel="stylesheet" />-->
    <?php echo $view->head()?>

</head>

<body>

<div class="welcome_main"><img src="images/title.png" alt="Title"><br>
    <img id="steamsplash" src="images/steamsplash.jpg" width="500" height="352" alt="Demo image">
    <?php echo $view->present()?>
</div>
</body>
</html>
