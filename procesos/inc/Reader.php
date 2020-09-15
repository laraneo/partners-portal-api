<?php 
namespace INC;
class Reader {
         
    public static $content;
    
    
    function __construct($file = ""){
        try{
            $root = dirname(__DIR__);
            self::$content = file_get_contents($root.DIRECTORY_SEPARATOR.$file);
        }catch(Exception $e){
           print_r($e); 
        }
        
    }
    
    static function open($file = ""){
       try{
            $root = dirname(__DIR__);
            self::$content = file_get_contents($root.DIRECTORY_SEPARATOR.$file);
        }catch(Exception $e){
           print_r($e); 
        }
    }
    
    static function getConnectionArray(){
        $x = explode("\n", self::$content);
        $arr = array();
        foreach($x as $value){
            $y = explode("=", $value);
            $arr[$y[0]] = trim($y[1]);
        }
        return $arr;    
    }
    
}

