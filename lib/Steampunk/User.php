<?php

namespace Steampunk;


class User
{
    private $id;		///< The internal ID for the user
    private $email;		///< Email address
    private $name; 		///< Name as last, first
    private $game_name;

    /**
     * @return mixed
     */
    public function getGameName()
    {
        return $this->game_name;
    }

    /**
     * @param mixed $game_name
     */
    public function setGameName($game_name)
    {
        $this->game_name = $game_name;
    }

    const SESSION_NAME = 'user';

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }


    /**
     * Constructor
     * @param $row Row from the user table in the database
     */
    public function __construct($row) {
        $this->id = $row['id'];
        $this->email = $row['email'];
        $this->name = $row['name'];
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }
}