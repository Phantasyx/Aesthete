<?php

namespace Steampunk;


class IndexView extends View
{
    private $redirect;

    /**
     * @return mixed
     */
    public function getRedirect()
    {
        return $this->redirect;
    }

 public function __construct(Site $site, array $post){
     $this->setTitle("Choose Game Type");

}

public function present(){
    $html = <<<HTML

<body>

    <div class="welcome_main"><img src="images/title.png" alt="Title"><br>
        <img id="steamsplash" src="images/steamsplash.jpg" width="500" height="352" alt="Demo image">
        <form class ="login" method="post" action="post/index.php"><br>
            Username:
            <br>
            <input type="text" name="username" placeholder="Username">
            <br>
            Password:
            <br>
            <input type="PASSWORD" name="password" placeholder="Password">
            <br>
            <input type="submit" name="login" id="login" value="Log In">
            <input type="submit" name="guest" id="guest" value="Guest">
        </form>
        <form class="newUser" method="post" action="newuser.php">
            <input type="submit" name="newaccount" value="Create New Account">
        </form>
        <h2>Wait! Not sure about the game? Click the button below!</h2>
        <form action="Instructions.php">
            <input type="submit" value="Check Instruction" name="instruction">
        </form>
    </div>
</body>
HTML;
    return $html;

}
}