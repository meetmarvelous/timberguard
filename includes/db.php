<?php
require_once 'config.php';

class Database {
    private $conn;
    
    public function __construct() {
        $this->connect();
    }
    
    private function connect() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        
        if ($this->conn->connect_error) {
            die("Database connection failed: " . $this->conn->connect_error);
        }
        
        // Set character set to UTF-8
        $this->conn->set_charset("utf8");
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function query($sql) {
        return $this->conn->query($sql);
    }
    
    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }
    
    public function real_escape_string($string) {
        return $this->conn->real_escape_string($string);
    }
    
    public function insert_id() {
        return $this->conn->insert_id;
    }
    
    public function close() {
        $this->conn->close();
    }
}

// Initialize database connection
$db = new Database();
$conn = $db->getConnection();
?>