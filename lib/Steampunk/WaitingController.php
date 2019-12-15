<?php


namespace Steampunk;


class WaitingController
{

    private $redirect;
    private $user;
    private $site;

    public function __construct(Site $site, User $user)
    {
        $root = $site->getRoot();
        $this->user = $user;
        $this->site = $site;
        //need quarry to see if the player in session is the current player
        //if player2 is null stay in waiting
        //is p2 not null go to game page.
    }

    public function redirection(){
        $root = $this->site->getRoot();
        $games = new Games($this->site);
        if($games->p2_exist($this->user->getName())){
            $this->redirect="$root/post/gameSetup.php";
        } else {
            $root = $this->site->getRoot();
            $this->redirect="$root/waiting.php";
        }
    }
    /**
     * @return mixed
     */
    public function getRedirect()
    {
        $this->redirection();
        return $this->redirect;
    }
}