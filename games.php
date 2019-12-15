<?php

require 'lib/site.inc.php';
$view = new Steampunk\GamesView($site);
?>
<!DOCTYPE html>
<html lang="en">
<link href="lib/css/main.css" type="text/css" rel="stylesheet" />
<head>
    <?php
    echo $view->head();
    ?>

</head>

<?php
echo $view->present();
?>
</html>