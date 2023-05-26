<?php

namespace Players;

use pocketmine\Server;
use pocketmine\scheduler\AsyncTask;


class Query extends AsyncTask{

    private $HOST;
    private $USER;
    private $PASS;

    private $name;

    private $i;

    private $players;

    private $kick;

    public function __construct(String $host, String $user, String $pass, String $name, $i, array $players){
        $this->HOST = $host;
        $this->USER = $user; 
        $this->PASS = $pass;

        $this->name = $name;

        $this->i = $i;

        $this->players = $players;
        
    }

    public function onRun(){
        $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Players");

        $this->kick = 0;

        if($this->i == false){

            if($mysqli->query("SELECT * FROM "."Players"." WHERE PLAYER = '$this->name'")->num_rows > 0){
                $mysqli->query("DELETE FROM "."Players"." WHERE PLAYER = '$this->name'");
            
            }

        }elseif($this->i == true){

            if($mysqli->query("SELECT * FROM "."Players"." WHERE PLAYER = '$this->name'")->num_rows > 0){
                $this->kick = 1;

            }else{
                $mysqli->query("INSERT INTO "."Players"."(PLAYER) VALUES ('$this->name')");

            }
            
        }else{
            return false;

        }

    }

    public function onCompletion(Server $server){

        if($this->kick == 1){
            $server->getPluginManager()->getPlugin("Players")->Kick($this->name);

        }

    }


}
