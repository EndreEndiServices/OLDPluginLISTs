<?php

namespace MTCore;

use pocketmine\Player;
use MTCore\MySQLPingTask;
use pocketmine\utils\TextFormat;
use MTCore\MTCore;

class MySQLManager{
    
    public $plugin;
    public $database;
    
    public function __construct(MTCore $plugin){
        $this->plugin = $plugin;
    }
    
    public function createMySQLConnection(){
        $database = new \mysqli("93.91.250.135", "180532_mysql_db", "kaktus01", "180532_mysql_db");
        $this->setDatabase($database);
        if($database->connect_error)
        {
            $this->plugin->getLogger()->critical("Nepodarilo se navazat pripojeni s databazi". $database->connect_error);
        }
        else
        {
            $this->plugin->getLogger()->info("§2Navazano pripojeni k §3MySQL §2Serveru!");
            $this->plugin->getServer()->getScheduler()->scheduleRepeatingTask(new MySQLPingTask($this), 20);
        }
    }
    
    public function registerPlayer($player){
        $name = trim(strtolower($player));
        $data =
        [
            "name" => $name,
            "rank" => "hrac",
            "doba" => 0,
            "tokens" => 0
        ];

        $this->getDatabase()->query
        (
            "INSERT INTO freezecraft (
            `name`, `rank`, `doba`)
            VALUES
            ('".$this->getDatabase()->escape_string($name)."', '".$data["rank"]."', '".$data["doba"]."')"
        );
        $this->plugin->getLogger()->Info(TextFormat::GREEN."Zaregistrovan novy hrac ". $player);
        return $data;
    }
    
    public function getPlayer($player){
        $result = $this->getDatabase()->query
        (
            "SELECT * FROM freezecraft WHERE name = '" . $this->getDatabase()->escape_string(trim(strtolower($player)))."'"
        );
        if($result instanceof \mysqli_result){
            $data = $result->fetch_assoc();
            $result->free();
            if(isset($data["name"]) and $data["name"] === trim(strtolower($player))){
                unset($data["name"]);
                return $data;
            }
        }
        return null;
    }
    
    public function setDatabase(\mysqli $database){
        $this->database = $database;
    }
    
    public function getDatabase(){
        return $this->database;
    }
    
    public function isPlayerRegistered($player){
        if($this->getPlayer($player) !== null){
            return true;
        }
        return false;
    }
    
    public function setRank($player, $rank){
        $this->getDatabase()->query
        (
            "UPDATE freezecraft SET rank = '".$rank."' WHERE name = '".$this->getDatabase()->escape_string(trim(strtolower($player)))."'"
        );
    }
    
    public function setTime($player, $time){
        $this->getDatabase()->query
        (
            "UPDATE freezecraft SET doba = '".$time."' WHERE name = '".$this->getDatabase()->escape_string(trim(strtolower($player)))."'"
        );
    }
    
    public function getRank($p){
        $data = $this->getPlayer($p);
        return $data['rank'];
    }
    
    public function getTime($p){
        $data = $this->getPlayer($p);
        return $data['doba'];
    }
    
    public function addTokens($p, $tokens){
        $this->getDatabase()->query
        (
            "UPDATE freezecraft SET tokens = tokens+'".$tokens."' WHERE name = '".$this->getDatabase()->escape_string(trim(strtolower($p)))."'"
        );
    }
    
    public function getTokens($p){
        $data = $this->getPlayer($p);
        return $data['tokens'];
    }
    
    public function setPassword($p, $heslo){
        $this->getDatabase()->query
        (
            "UPDATE freezecraft SET heslo = '".$heslo."' WHERE name = '".$this->getDatabase()->escape_string(trim(strtolower($p)))."'"
        );
    }
    
    public function setIP($p, $heslo){
        $this->getDatabase()->query
        (
            "UPDATE freezecraft SET ip = '".$heslo."' WHERE name = '".$this->getDatabase()->escape_string(trim(strtolower($p)))."'"
        );
    }
    
    public function setUUID($p, $heslo){
        $this->getDatabase()->query
        (
            "UPDATE freezecraft SET id = '".$heslo."' WHERE name = '".$this->getDatabase()->escape_string(trim(strtolower($p)))."'"
        );
    }
    
    public function getPassword($p){
        $data = $this->getPlayer($p);
        return $data['heslo'];
    }
    
    public function getIP($p){
        $data = $this->getPlayer($p);
        return $data['ip'];
    }
    
    public function getUUID($p){
        $data = $this->getPlayer($p);
        return $data['id'];
    }
}