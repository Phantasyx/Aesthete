<?php


namespace Steampunk;


class IndexController{
    private $redirect;

    /**
     * @return mixed
     */
    public function getRedirect()
    {
        return $this->redirect;
    }

    public function __construct(Site $site, array &$session, array $post)
    {
        $root = $site->getRoot();
        if (isset($post["login"])){
            // Create a Users object to access the table
            $users = new Users($site);

            $name = strip_tags($post['username']);
            $password = strip_tags($post['password']);
            $user = $users->login($name, $password);
            $session[User::SESSION_NAME] = $user;

            if($user === null) {
                // Login failed
                $this->redirect = "$root/index.php?e=1";
            } else {
                $this->redirect = "$root/chooseGameType.php";
            }
        }
        elseif(isset($post["guest"])){
            $users = new Users($site);
            $session[User::SESSION_NAME] = $users->tempUser();
            $this->redirect = "$root/chooseGameType.php";
        }else{
            $root = $site->getRoot();
           // $this->redirect = "$root/index.php?";
            $this->redirect = "$root/chooseGameType.php";
        }
    }
}