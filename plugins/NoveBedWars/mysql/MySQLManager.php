<?php

namespace BedWars\mysql;

use BedWars\BedWars;
use pocketmine\utils\TextFormat as TF;

class MySQLManager{

    /** @var BedWars $bw */
    public $bw;
    /** @var  \mysqli $database */
    public $database;
    
    public function __construct(BedWars $bw){
        $this->bw = $bw;
    }
    
    public function createMySQLConnection(){
        $database = new \mysqli("93.91.250.135", "180532_mysql_db", "kaktus01", "180532_mysql_db");
        $this->setDatabase($database);
        if($database->connect_error)
        {
            $this->bw->getLogger()->critical(TF::RED."Couldn't connect to database: ". $database->connect_error);
        }
        else
        {
            $this->bw->getLogger()->info(TF::GREEN."Successfully connected to ".$this->bw->prefix.TF::GREEN."MySQL Server!");
            $this->bw->getServer()->getScheduler()->scheduleRepeatingTask(new MySQLPingTask($this), 20);
        }
    }
    
    public function registerPlayer($player){
        $name = trim(strtolower($player));
        $data =
        [
            "name" => $name,
            "kills" => 0,
            "deaths" => 0,
            "wins" => 0,
            "losses" => 0,
            "beds" => 0
        ];

        $this->getDatabase()->query
        (
            "INSERT INTO bedwars (
            name, kills, deaths, wins, losses, beds)
            VALUES
            ('".$this->getDatabase()->escape_string($name)."', '".$data["kills"]."', '".$data["deaths"]."', '".$data["wins"]."', '".$data["losses"]."', '".$data["beds"]."')"
        );
        $this->bw->getLogger()->Info(TF::GREEN."Registered player ". $player);
        return $data;
    }
    
    public function getPlayer($player){
        $result = $this->getDatabase()->query
        (
            "SELECT * FROM bedwars WHERE name = '" . $this->getDatabase()->escape_string(trim(strtolower($player)))."'"
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
    
    public function addKill($player, $kills = 1){
        $this->getDatabase()->query
        (
            "UPDATE bedwars SET kills = kills+'".$kills."' WHERE name = '".$this->getDatabase()->escape_string(trim(strtolower($player)))."'"
        );
    }
    
    public function addDeath($player, $deaths = 1){
        $this->getDatabase()->query
        (
            "UPDATE bedwars SET deaths = deaths+'".$deaths."' WHERE name = '".$this->getDatabase()->escape_string(trim(strtolower($player)))."'"
        );
    }
    
    public function addWin($player, $kills = 1){
        $this->getDatabase()->query
        (
            "UPDATE bedwars SET wins = wins+'".$kills."' WHERE name = '".$this->getDatabase()->escape_string(trim(strtolower($player)))."'"
        );
    }
    
    public function addLoss($player, $kills = 1){
        $this->getDatabase()->query
        (
            "UPDATE bedwars SET losses = losses+'".$kills."' WHERE name = '".$this->getDatabase()->escape_string(trim(strtolower($player)))."'"
        );
    }
    
    public function addBed($player, $kills = 1){
        $this->getDatabase()->query
        (
            "UPDATE bedwars SET beds = beds+'".$kills."' WHERE name = '".$this->getDatabase()->escape_string(trim(strtolower($player)))."'"
        );
    }
    
    public function getKills($p){
        $data = $this->getPlayer($p);
        return $data['kills'];
    }
    
    public function getDeaths($p){
        $data = $this->getPlayer($p);
        return $data['deaths'];
    }
    
    public function getWins($p){
        $data = $this->getPlayer($p);
        return $data['wins'];
    }
    
    public function getLosses($p){
        $data = $this->getPlayer($p);
        return $data['losses'];
    }
    
    public function getBeds($p){
        $data = $this->getPlayer($p);
        return $data['beds'];
    }
}