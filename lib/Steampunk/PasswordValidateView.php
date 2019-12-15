<?php


namespace Steampunk;


class PasswordValidateView extends View
{
    private $validator;
    private $e;
    public function __construct(Site $site,array $get)
    {
        if (isset($get['v'])){
            $this->validator = $get['v'];
        }
        if (isset($get['e'])){
            $this->e=$get['e'];
        }
        $this->setTitle("Create Password");
    }

    public function present(){
        $html = <<<HTML
<form class ="login" method="post" action="post/password-validator.php"><br>
HTML;
        switch ($this->e){
            case 1:
                $html.='<p>Invalid Email Address</p>';
                break;
            case 2:
                $html.='<p>Two password not match</p>';
                break;
            case 3:
                $html.='<p>Password is too short</p>';
                break;
            default:
                $html.='';
                break;
        }
        if ($this->validator!==null) {
            $html .= <<<HTML
        <input type="hidden" name="validator" value="$this->validator">
HTML;
        }
        $html.=<<<HTML
<form class ="login" method="post" action="post/password-validator.php"><br>
        Email:
        <br>
        <input type="text" name="email" placeholder="Email Address">
        <br>
        Password:
        <br>
        <input type="PASSWORD" name="password" placeholder="Password">
        <br>
        Password(again):
        <br>
        <input type="PASSWORD" name="password2" placeholder="Password">
        <br>
        <input type="submit" name="create" value="Create">
    </form>
HTML;
        return $html;
    }
}