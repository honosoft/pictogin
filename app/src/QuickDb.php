<?php

namespace pictogin;

use mysqli;
use Ramsey\Uuid\Uuid;

/**
 * Class QuickDb
 * @package pictogin
 * Simple SQL class to encapsulate the operation. Not really injection safe for now.
 *
 * @Note Will include my other project one day.
 */
class QuickDb {
    private $connection;

    public function __construct() {
        $config = require_once(getcwd() . '/../configs/mysql.php');
        $this->connection = new mysqli($config['host'], $config['username'], $config['password'], $config['dbname'], $config['port']);

        // TODO: not a really nice way to terminate.
        if ($this->connection->connect_error) {
            die('Connect Error (' . $this->connection->connect_errno . ') ' . $this->connection->connect_error);
        }
    }

    function __destruct() {
        $this->connection->close();
    }

    /**
     * @param string $email
     * @param array $password List of image ids.
     * @return bool
     * @throws \Exception
     */
    public function addUser(string $email, array $password) {
        $email = strtolower($email);
        $id = Uuid::uuid1()->toString();
        $password = json_encode($password); // TODO: Probably use a crypt with a key? How can it be secure if the key is in this code?

        $stm = $this->connection->prepare("INSERT INTO user (id, email, password) VALUES (?, ?, ?);");
        $stm->bind_param('sss', $id, $email, $password);
        $result = $stm->execute();
        $stm->close();

        return $result;
    }

    /**
     * @param $email
     * @return mixed User if exists, null otherwise.
     */
    public function getUser($email) {
        $email = strtolower($email);

        $stm = $this->connection->prepare("SELECT * FROM user WHERE email=?");
        $stm->bind_param('s',$email);
        if ($stm->execute()) {
            $result = $stm->get_result();
            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                $row['password'] = json_decode($row['password']); // override the password as array.
                return $row;
            }
        }
        $stm->close();
        return NULL;
    }

    public function incrementPasswordRetries($email) {
        $email = strtolower($email);
        $stm = $this->connection->prepare("UPDATE user SET password_retries = password_retries + 1 WHERE email=?;");
        $stm->bind_param('s',$email);
        $ret = $stm->execute();
        $stm->close();
        return $ret;
    }

    public function resetPasswordRetries($email) {
        $email = strtolower($email);
        $stm = $this->connection->prepare("UPDATE user SET password_retries = 0 WHERE email=?;");
        $stm->bind_param('s',$email);
        $ret = $stm->execute();
        $stm->close();
        return $ret;
    }
}