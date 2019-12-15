<?php

namespace Steampunk;


class NewUserController
{
    private $redirect;

    public function __construct(Site $site,array $post)
    {
        $root = $site->getRoot();
        $this->redirect="$root/";
        if (isset($post['create'])){
            if (isset($post['username']) && isset($post['email'])){
                $users = new Users($site);
                $mailer = new Email();
                $user = New User(array('id'=>0,'name'=>strip_tags($post['username']),'email'=>strip_tags($post['email'])));
                $users->add($user,$mailer);
            }else{
                $this->redirect="$root/newuser.php?e=1";
            }
        }
    }

    /**
     * @return string
     */
    public function getRedirect()
    {
        return $this->redirect;
    }


}
