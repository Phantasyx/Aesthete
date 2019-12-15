<?php


namespace Steampunk;


class Email
{
    public function mail($to, $subject, $message, $headers) {
        mail($to, $subject, $message, $headers);
    }
}