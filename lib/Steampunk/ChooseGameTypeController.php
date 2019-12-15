<?php


namespace Steampunk;


class ChooseGameTypeController
{

    private $redirect;

    public function __construct(Site $site, array $post)

    {
        $root=$site->getRoot();
        if(isset($post['join'])){
            $this->redirect="$root/games.php";
        }
        elseif(isset($post['make'])){
            $this->redirect="$root/NewGame.php";
        }
    }

    /**
     * @return mixed
     */
    public function getRedirect()
    {
        return $this->redirect;
    }
}