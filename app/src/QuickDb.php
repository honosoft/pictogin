<?php

namespace pictogin;

use mysqli;
use Ramsey\Uuid\Uuid;

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
     * @param array $images
     * @return bool
     * @throws \Exception
     */
    public function addUser(string $email, array $images) {
        $email = strtolower($email);
        $id = Uuid::uuid1()->toString();
        $password = json_encode($images); // TODO: Probably use a crypt with a key? How can it be secure if the key is in this code?

        $result = $this->connection->query("INSERT INTO user (id, email, password) VALUES (\"$id\", \"$email\", \"$password\");");

        if ($result && $result->num_rows > 0) {
            // TODO: send a mail for validation!
            return TRUE;
        }
        return FALSE;
    }

    /**
     * @param $email
     * @return mixed User if exists, null otherwise.
     */
    public function getUser($email) {
        $email = strtolower($email);

        $result = $this->connection->query("SELECT * FROM user WHERE email=\"$email\"");

        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return NULL;
    }
}