<?php

namespace Steampunk;


class PasswordValidateController
{
    private $redirect;

    public function __construct(Site $site,array $post)
    {
        $root = $site->getRoot();
        $this->redirect="$root/";

        if (isset($post['create'])){
            $validators = new Validators($site);
            $userid = $validators->getOnce($post['validator']);
            if($userid === null) {
                $this->redirect = "$root/";
                return;
            }
            $users = new Users($site);
            $editUser = $users->get($userid);
            if($editUser === null) {
                // User does not exist!
                $this->redirect = "$root/";
                return;
            }
        }
        $email = trim(strip_tags($post['email']));
        if($email !== $editUser->getEmail()) {
            // Email entered is invalid
            $newValidator = new Validators($site);
            $temp=$newValidator->newValidator($userid);
            $this->redirect = "$root/password-validate.php?e=1&v=$temp";
            return;
        }

        $password1 = trim(strip_tags($post['password']));
        $password2 = trim(strip_tags($post['password2']));
        if($password1 !== $password2) {
            // Passwords do not match
            $newValidator = new Validators($site);
            $temp=$newValidator->newValidator($userid);
            $this->redirect = "$root/password-validate.php?e=2&v=$temp";
            return;
        }

        if(strlen($password1) < 8) {
            // Password too short
            $newValidator = new Validators($site);
            $temp=$newValidator->newValidator($userid);
            $this->redirect = "$root/password-validate.php?e=3&v=$temp";
            return;
        }
        $users->setPassword($userid, $password1);
    }

    /**
     * @return string
     */
    public function getRedirect()
    {
        return $this->redirect;
    }
}