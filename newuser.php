<?php

$open=true;
require "lib/site.inc.php";
$view = new \Steampunk\View();
$view->setTitle("Create New User");
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
    <h1>Account Creation</h1>
    <div class="username">
        <form class="username" action="post/addNewUser.php" method="post">
            Username:<br>
            <input type="text" name="username" placeholder="Username"><br>
            Email:<br>
            <input type="text" name="email" placeholder="Email"><br>
            <br>
            <input type="submit" name="create" value="Create">
        </form>
    </div>
</div>
</body>
</html>
