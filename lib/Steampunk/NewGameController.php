<?php

namespace Steampunk;


class NewGameController
{
    private $redirect;

    public function __construct(Site $site,array &$session,array $post)
    {
        $root=$site->getRoot();
        if(isset($post['gameboard_size']) && ($post['gameboard_size']==6 || $post['gameboard_size']==10 || $post['gameboard_size']==20)){
            $this->redirect="$root/waiting.php";
            $user = $session[User::SESSION_NAME];
            $size = $post['gameboard_size'];
            $games = new Games($site);
            $games->create_game($user,$size,$post['room']);
            $user->setGameName($post['room']);
        }
    }

    public function getRedirect()
    {
        return $this->redirect;
    }
}