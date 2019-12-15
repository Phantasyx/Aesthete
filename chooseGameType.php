<?php
/**
 * Created by PhpStorm.
 * User: jingqiyang
 * Date: 4/6/17
 * Time: 7:44 PM
 */
require "lib/site.inc.php";
$view = new \Steampunk\ChooseGameTypeView()


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!--<meta charset="UTF-8">
    <title>Welcome</title>
    <link href="lib/css/Style.css" type="text/css" rel="stylesheet" />-->
    <?php echo $view->head()?>

</head>

<?php echo $view->present()
        ?>
</html>
