<?php
$open=true;
require 'lib/site.inc.php';
$view = new \Steampunk\IndexView($site, $_POST);
$view->setTitle('Welcome');
//require lib game_php
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!--<meta charset="UTF-8">
    <title>Welcome</title>
    <link href="lib/css/Style.css" type="text/css" rel="stylesheet" />-->
    <?php echo $view->head()?>

</head>
<?php
echo $view->present()
?>
</html>