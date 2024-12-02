<?php 
class Database {
    private $host = "postgres";
    private $dbname = "postgres";
    private $dbusername = "admin";
    private $dbpassword = "password";
    private $conn;

    public function connection() {
        $conn = pg_connect("host=$this->host dbname=$this->dbname user=$this->dbusername password=$this->dbpassword");

        if($conn != true) {
            print "Błąd połączenia z bazą";
            die();
        }
    }

    public function getDB() {

        $this -> connection();

        if($this -> conn instanceof PgSql\Connection) {
            return $this-> conn;
        }
    }
}
?>