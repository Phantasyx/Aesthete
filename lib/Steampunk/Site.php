<?php

namespace Steampunk;


class Site
{
    private $email = '';        ///< Site owner email address
    private $dbHost = null;     ///< Database host name
    private $dbUser = null;     ///< Database user name
    private $dbPassword = null; ///< Database password
    private $tablePrefix = '';  ///< Database table prefix
    private $root = '';
    private $publicOrigin = ''; ///< Scheme and host for absolute links, no trailing slash
    private $pdo = null; ///< The PDO object

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @param string $root
     */
    public function setRoot($root)
    {
        $this->root = $root;
    }

    /**
     * @return string
     */
    public function getPublicOrigin()
    {
        return $this->publicOrigin;
    }

    /**
     * @param string $publicOrigin Scheme and host, such as https://play.example.com
     */
    public function setPublicOrigin($publicOrigin)
    {
        $this->publicOrigin = rtrim((string)$publicOrigin, '/');
    }

    /**
     * Build a site URL. Uses the public origin when one is configured.
     * @param string $path Path beginning with /
     * @return string
     */
    public function url($path)
    {
        $root = (string)$this->root;
        if ($root !== '' && $root[0] !== '/') {
            $root = '/' . $root;
        }
        $root = rtrim($root, '/');
        if ($path === '' || $path[0] !== '/') {
            $path = '/' . $path;
        }
        return $this->publicOrigin . $root . $path;
    }

    /**
     * Configure the database
     * @param $host
     * @param $user
     * @param $password
     * @param $prefix
     */
    public function dbConfigure($host, $user, $password, $prefix) {
        $this->dbHost = $host;
        $this->dbUser = $user;
        $this->dbPassword = $password;
        $this->tablePrefix = $prefix;
    }

    /**
     * @return string
     */
    public function getTablePrefix()
    {
        return $this->tablePrefix;
    }
    /**
     * Database connection function
     * @returns PDO object that connects to the database
     */
    function pdo() {
        // This ensures we only create the PDO object once
        if($this->pdo !== null) {
            return $this->pdo;
        }

        if ($this->dbHost === null || $this->dbHost === '') {
            die('Database is not configured');
        }

        try {
            $this->pdo = new \PDO($this->dbHost, $this->dbUser, $this->dbPassword);
        } catch(\PDOException $e) {
            die('Unable to select database');
        }

        return $this->pdo;
    }
}