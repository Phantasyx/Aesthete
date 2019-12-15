<?php


namespace Steampunk;


class Validators extends Table
{
    public function __construct(Site $site)
    {
        parent::__construct($site, 'validator');
    }

    /**
     * Create a new validator and add it to the table.
     * @param $userid User this validator is for.
     * @return The new validator.
     */
    public function newValidator($userid) {
        $validator = $this->createValidator();

        // Write to the table
        $sql=<<<SQL
INSERT INTO $this->tableName(`userid`, `validator`, `date`) 
VALUES ('$userid','$validator',now())
SQL;
        $pdo = $this->pdo();
        $statement = $pdo->prepare($sql);
        $statement->execute();

        return $validator;
    }
    /**
     * @brief Generate a random validator string of characters
     * @param $len Length to generate, default is 32
     * @returns Validator string
     */
    private function createValidator($len = 32) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $l = strlen($chars) - 1;
        $str = '';
        for ($i = 0; $i < $len; ++$i) {
            $str .= $chars[rand(0, $l)];
        }
        return $str;
    }

    /**
     * Determine if a validator is valid. If it is,
     * get the user ID for that validator. Then destroy any
     * validator records for that user ID. Return the
     * user ID.
     * @param $validator Validator to look up
     * @return User ID or null if not found.
     */
    public function getOnce($validator) {

        $select=<<<SQL
SELECT userid
FROM $this->tableName
WHERE validator=?
ORDER BY date
SQL;
        $delete=<<<SQL
DELETE FROM $this->tableName
WHERE userid=?
SQL;
        $pdo=$this->pdo();
        $select_statement = $pdo->prepare($select);
        $delete_statement = $pdo->prepare($delete);
        try{
            $select_statement->execute(array($validator));
            if($select_statement->rowCount()===0){
                return null;
            }else{
                $id = $select_statement->fetch(\PDO::FETCH_ASSOC)['userid'];
                $delete_statement->execute(array($id));
                return $id;
            }
        }catch (\PDOException $exception){
            return null;
        }

    }
}