<?php

namespace Steampunk;


class Users extends Table
{

    /**
     * Constructor
     * @param $site The Site object
     */
    public function __construct(Site $site) {
        parent::__construct($site, "user");
    }
    /**
     * Test for a valid login.
     * @param $email User email
     * @param $password Password credential
     * @returns User object if successful, null otherwise.
     */
    public function login($name, $password) {
        $sql =<<<SQL
SELECT * from $this->tableName
where name=? OR email=?
SQL;

        $pdo = $this->pdo();
        $statement = $pdo->prepare($sql);

        $statement->execute(array($name,$name));
        if($statement->rowCount() === 0) {
            return null;
        }

        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        // Get the encrypted password and salt from the record
        $hash = $row['password'];
        $salt = $row['salt'];

        // Ensure it is correct
        if($hash !== hash("sha256", $password . $salt)) {
            return null;
        }
        return new User($row);
    }
    /*
     * Add the temp user for guest to play the game
     * @return $user with password "password" with username 'guest'.id
     */
    public function tempUser(){
        $select_sql = <<<SQL
select max(id) from $this->tableName
SQL;
        $pdo=$this->pdo();
        $select_statement=$pdo->prepare($select_sql);
        $select_statement->execute();
        $row = $select_statement->fetch(\PDO::FETCH_ASSOC);
        $id = $row['max(id)']+1;
        $salt = self::randomSalt();
        $name = "guest".strval($id);
        $newpass = hash("sha256", "password" . $salt);
        $insert_sql=<<<SQL
INSERT INTO $this->tableName(name,password,email,salt,guest)
VALUES (?,?,?,?,?)
SQL;
        $insert_statement=$pdo->prepare($insert_sql);
        $insert_statement->execute(array($name,$newpass,'',$salt,'g'));

        return new User(array('id'=>$id,'email'=>'','name'=>$name));
    }

    /**
     * Get a user based on the id
     * @param $id ID of the user
     * @returns User object if successful, null otherwise.
     */
    public function get($id) {
        $sql = <<<SQL
SELECT * FROM $this->tableName
WHERE id=?
SQL;

        $pdo = $this->pdo();
        $statement = $pdo->prepare($sql);

        $statement->execute(array($id));
        if ($statement->rowCount()===0){
            return null;
        }

        return new User($statement->fetch(\PDO::FETCH_ASSOC));
    }

    /**
     * Determine if a user exists in the system.
     * @param $email An email address.
     * @returns true if $email is an existing email address
     */
    public function exists($email) {
        $sql=<<<SQL
SELECT email
FROM $this->tableName
WHERE email=?
SQL;
        $pdo = $this->pdo();
        $statement=$pdo->prepare($sql);
        try{
            $statement->execute(array($email));
            if ($statement->rowCount()===0){
                return false;
            }
            return true;
        }catch (\PDOException $exception){
            return false;
        }
    }
    /**
     * Determine if a user exists in the system.
     * @param $email An email address.
     * @returns true if $email is an existing email address
     */
    public function existsUserName($name) {
        $sql=<<<SQL
SELECT name
FROM $this->tableName
WHERE name=?
SQL;
        $pdo = $this->pdo();
        $statement=$pdo->prepare($sql);
        try{
            $statement->execute(array($name));
            if ($statement->rowCount()===0){
                return false;
            }
            return true;
        }catch (\PDOException $exception){
            return false;
        }
    }
    /**
     * Create a new user.
     * @param User $user The new user data
     * @param Email $mailer An Email object to use
     * @return null on success or error message if failure
     */
    public function add(User $user, Email $mailer) {
        // Ensure we have no duplicate email address
        if($this->exists($user->getEmail())) {
            return "Email address already exists.";
        }
        if($this->existsUserName($user->getName())){
            return "Username already exists.";
        }
        // Add a record to the user table
        $sql = <<<SQL
INSERT INTO $this->tableName(email, name,guest)
values(?, ?, ?)
SQL;

        $statement = $this->pdo()->prepare($sql);
        $statement->execute(array($user->getEmail(),$user->getName(),'u'));
        $id = $this->pdo()->lastInsertId();
        // Create a validator and add to the validator table
        $validators = new Validators($this->site);
        $validator = $validators->newValidator($id);

        // Send email with the validator in it
        $link = "http://webdev.cse.msu.edu"  . $this->site->getRoot() .
            '/password-validate.php?v=' . $validator;

        $from = $this->site->getEmail();
        $name = $user->getName();

        $subject = "Confirm your email";
        $message = <<<MSG
<html>
<p>Greetings, $name,</p>

<p>Welcome to Felis. In order to complete your registration,
please verify your email address by visiting the following link:</p>

<p><a href="$link">$link</a></p>
</html>
MSG;
        $headers = "MIME-Version: 1.0\r\nContent-type: text/html; charset=iso=8859-1\r\nFrom: $from\r\n";
        $mailer->mail($user->getEmail(), $subject, $message, $headers);
    }

    /**
     * Set the password for a user
     * @param $userid The ID for the user
     * @param $password New password to set
     */
    public function setPassword($userid, $password) {
        $salt = self::randomSalt();
        $newpass = hash("sha256", $password . $salt);
        $sql=<<<SQL
UPDATE $this->tableName
SET `password`='$newpass',`salt`='$salt'
WHERE id=?
SQL;
        $pdo=$this->pdo();
        $statement = $pdo->prepare($sql);
        $statement->execute(array($userid));
    }
    /**
     * Generate a random salt string of characters for password salting
     * @param $len Length to generate, default is 16
     * @returns Salt string
     */
    public static function randomSalt($len = 16) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789`~!@#$%^&*()-=_+';
        $l = strlen($chars) - 1;
        $str = '';
        for ($i = 0; $i < $len; ++$i) {
            $str .= $chars[rand(0, $l)];
        }
        return $str;
    }
    public function lostPassword($email,Email $mailer){
        $sql=<<<SQL
SELECT id,name
FROM $this->tableName
WHERE email=?
SQL;
        $pdo=$this->pdo();
        $statement = $pdo->prepare($sql);
        $statement->execute(array($email));
        $row=$statement->fetch(\PDO::FETCH_ASSOC);
        $id = $row['id'];
        $name = $row['name'];

        $validators = new Validators($this->site);
        $validator = $validators->newValidator($id);

        // Send email with the validator in it
        $link = "http://webdev.cse.msu.edu"  . $this->site->getRoot() .
            '/password-validate.php?v=' . $validator;

        $from = $this->site->getEmail();

        $subject = "New Password";
        $message = <<<MSG
<html>
<p>Greetings, $name,</p>

<p>Welcome to Felis. In order to change your password, please visit the following link:</p>

<p><a href="$link">$link</a></p>
</html>
MSG;
        $headers = "MIME-Version: 1.0\r\nContent-type: text/html; charset=iso=8859-1\r\nFrom: $from\r\n";
        $mailer->mail($email, $subject, $message, $headers);

    }
}