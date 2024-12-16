<?php 
class Database {
    private $host = "postgres";
    private $dbname = "postgres";
    private $dbusername = "admin";
    private $dbpassword = "password";
    private $conn;

    public function connection() {
        $this -> conn = pg_connect("host=$this->host dbname=$this->dbname user=$this->dbusername password=$this->dbpassword");

        if($this -> conn != true) {
            print "Błąd połączenia z bazą";
            die();
        }

    }

    public function getDB() {

        if(!$this -> conn) {
            $this -> connection();
        }

        return $this-> conn;
    }
}
?>