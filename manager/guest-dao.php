<?php
class GuestDAO {
    private $_db;

    function __construct() {
        $type = $_ENV["DB_TYPE"];
        $host = $_ENV["DB_HOST"];
        $port = $_ENV["DB_PORT"];
        $dbname = $_ENV["DB_NAME"];
        $dbuser = $_ENV["DB_USER"];
        $dbpass = $_ENV["DB_PASSWORD"];
        $this->_db = new PDO(
            "${type}:host=${host}${($port)?";port=${port}":""};dbname=${dbname};",
            $dbuser,
            $dbpass,
            array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
        );
    }
    function list() {
        $users = [];
        if($result = $this->_db->query(
            "select username, attribute, op, value from radcheck order by username",
            PDO::FETCH_OBJ
        )) {
            foreach($result as $row) {
                $users[$row->username][$row->attribute] = $row->value;
            }
        }
        return $users;
    }
    function get($username) {
        $attributes = Array();
        if($result = $this->_db->query(
            "select attribute, op, value from radcheck where username = ${$PDO::quote($username)}",
            PDO::FETCH_OBJ
        )) {
            foreach($result as $row) {
                $attributes[$row->attribute] = $row->value;
            }            
        }
        return $attributes;
    }
    function create($username, $attributes) {
        if(!$username) {
            throw new Exception("Invalid username", 1);
        }
        if($result = $this->_db->query(
            "select attribute, op, value from radcheck where username = {$this->_db->quote($username)}",
            PDO::FETCH_OBJ
        )) {
            if($result->rowCount() > 0) {
                throw new Exception("Username already exists", 2);
            }
        }
        // format expiration
        if(trim($attributes['Expiration'])) {
            $expiration = new DateTime($attributes['Expiration']);
            $attributes['Expiration'] = $expiration->format("F d Y H:i:s");
        } else {
            unset($attributes['Expiration']);
        }
        $values = [];
        // add to values array
        foreach ($attributes as $name => $value) {
            $quoted_value = $this->_db->quote(trim($value));
            if($name && $quoted_value) {
                $values[] = "('$username','$name',':=',$quoted_value)";
            }
        }
        $this->_db->exec(
            "insert into radcheck(username, attribute, op, value) values ".join($values, ", ")
        );
    }
    function delete($username) {
        $quoted_username = $this->_db->quote($username);
        $this->_db->exec("delete from radcheck where username = $quoted_username");
        return true;
    }
}
?>
