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
            $this->redirect = "$root/chooseGameType.php";
                $users = new Users($site);
                $user = $users->tempUser();
                $login = $users->login($user->getName(),'password');
            $session[User::SESSION_NAME] = $login;
            //Why would we return back to index.php we should just display an error if can't login a guestlogin if we are playing as guest????? scratch this code below
           /* if($login === null) {
                // Login failed
                $this->redirect = "$root/index.php?e=1";
            } else {
                $this->redirect = "$root/chooseGameType.php";
            }*/

        }else{
            $root = $site->getRoot();
           // $this->redirect = "$root/index.php?";
            $this->redirect = "$root/chooseGameType.php";
        }
    }
}