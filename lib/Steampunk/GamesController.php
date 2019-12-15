<?php

namespace Steampunk;


class GamesController
{
    private $redirect;

    public function __construct(Site $site,array $post,array &$session)

    {
        $root = $site->getRoot();

        $this->redirect = "$root/post/gameSetup.php";

        $user = $session[User::SESSION_NAME];
        $id = $post['id'];

        $games = new Games($site);
        $games->joinGame($id,$user);
        $user->setGameName($post['game_name']);
    }


    /**
     * @return mixed
     */
    public function getRedirect()
    {
        return $this->redirect;
    }
}
