<?php 

namespace INC;
use PDO;

class DB extends PDO
{
    var $host;
    var $dbname;
    var $user;
    var $pass;
    var $query;
    
    function __construct($host = "", $port="",$dbname="", $user ="", $pass= "")
    {
        try{
            parent::__construct("sqlsrv:host=$host;port=$port;dbname=$dbname", $user, $pass);
            $this->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // always disable emulated prepared statement when using the MySQL driver
            $this->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }catch(Exception $e){
            echo $e->getMessage();   
        }
        
    }
    
    public function query($query)
    {
      $this->query = parent::query($query);
      $this->query->execute();
      return $this->query->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function execute(){
       
    }
    
}
