<?php

namespace TheBridges;

use TheBridges\MySQLPingTask;
use TheBridges\TheBridges;
use pocketmine\utils\TextFormat;

class MySQLManager{
    
    public $plugin;
    public $database;
    
    public function __construct(TheBridges $plugin){
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
            $this->plugin->getLogger()->info(TextFormat::DARK_GREEN."Navazano pripojeni k ".$this->plugin->getPrefix().TextFormat::RESET.TextFormat::DARK_AQUA."MySQL ".TextFormat::DARK_GREEN."Serveru!");
            $this->plugin->getServer()->getScheduler()->scheduleRepeatingTask(new MySQLPingTask($this), 20);
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
            "kits" => 011
        ];

        $this->getDatabase()->query
        (
            "INSERT INTO bridges (
            name, kills, deaths, wins, losses, kits)
            VALUES
            ('".$this->getDatabase()->escape_string($name)."', '".$data["kills"]."', '".$data["deaths"]."', '".$data["wins"]."', '".$data["losses"]."', '".$data["kits"]."')"
        );
        $this->plugin->getLogger()->Info(TextFormat::GREEN."Zaregistrovan novy hrac ". $player);
        return $data;
    }
    
    public function getPlayer($player){
        $result = $this->getDatabase()->query
        (
            "SELECT * FROM bridges WHERE name = '" . $this->getDatabase()->escape_string(trim(strtolower($player)))."'"
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
            "UPDATE bridges SET kills = kills+'".$kills."' WHERE name = '".$this->getDatabase()->escape_string(trim(strtolower($player)))."'"
        );
    }
    
    public function addDeath($player, $deaths = 1){
        $this->getDatabase()->query
        (
            "UPDATE bridges SET deaths = deaths+'".$deaths."' WHERE name = '".$this->getDatabase()->escape_string(trim(strtolower($player)))."'"
        );
    }
    
    public function addWin($player, $kills = 1){
        $this->getDatabase()->query
        (
            "UPDATE bridges SET wins = wins+'".$kills."' WHERE name = '".$this->getDatabase()->escape_string(trim(strtolower($player)))."'"
        );
    }
    
    public function addLoss($player, $kills = 1){
        $this->getDatabase()->query
        (
            "UPDATE bridges SET losses = losses+'".$kills."' WHERE name = '".$this->getDatabase()->escape_string(trim(strtolower($player)))."'"
        );
    }
    
    public function changeKit($player, $apple, $archer, $miner){
        $this->getDatabase()->query
        (
            "UPDATE bridges SET kits = '".$apple.$archer.$miner."' WHERE name = '".$this->getDatabase()->escape_string(trim(strtolower($player)))."'"
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
    
    public function getKits($p){
        $data = $this->getPlayer($p);
        return $data['kits'];
    }
}