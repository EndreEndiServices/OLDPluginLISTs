<?php

namespace safecreative;

use safecreative\MySqlPingTask;

class MySqlManager{
    
    public $plugin;
    public $database;
    
    public function __construct(Main $plugin){
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
            $this->plugin->getServer()->getScheduler()->scheduleRepeatingTask(new MySqlPingTask($this), 20);
        }
    }
    
    public function setDatabase(\mysqli $database){
        $this->database = $database;
    }
    
    public function getDatabase(){
        return $this->database;
    }
    
    public function addBlock($x, $y, $z, $level){
        $this->getDatabase()->query
        (
            "INSERT INTO safecreative (
            x, y, z, level)
            VALUES
            ('".$x."', '".$y."', '".$z."', '".$level."')"
        );
        //$this->plugin->getServer()->getLogger()->info('block added');
    }
    
    public function removeBlock($x, $y, $z, $level){
        $this->getDatabase()->query
        (
            "DELETE FROM safecreative WHERE (x = '".$x."', y = '".$y."', z = '".$z."', level = '".$level."')"
        );
        //$this->plugin->getServer()->getLogger()->info('block removed');
    }
    
    public function isBlock($x, $y, $z, $level){
        $result = $this->getDatabase()->query
        (
            "SELECT * FROM safecreative WHERE (x = '" .$x."' AND y = '" .$y."' AND z = '" .$z."' AND level = '" .$level."')"
        );
        if($result instanceof \mysqli_result){
            $data = $result->fetch_assoc();
            $result->free();
            return $data;
        }
        return null;
    }
}