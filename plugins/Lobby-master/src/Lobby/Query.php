<?php

namespace Lobby;

use pocketmine\Server;
use pocketmine\scheduler\AsyncTask;


class Query extends AsyncTask{

    private $HOST;
    private $USER;
    private $PASS;

    private $name;

    private $players;
    private $maxplayers;

    private $ip;
    private $port;

    private $i;

    private $a;

    public function __construct(String $host, String $user, String $pass, String $name, int $players, int $maxplayers, String $ip, int $port, String $i, int $a){
        $this->HOST = $host;
        $this->USER = $user; 
        $this->PASS = $pass;

        $this->name = $name;

        $this->players = (int)$players;
        $this->maxplayers = (int)$maxplayers;

        $this->ip = $ip;
        $this->port = $port;

        $this->i = $i;

        $this->a = $a;
        
    }

    public function onRun(){
        $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Lobby");

        if($this->a == 0){
            $mysqli->query("DELETE FROM "."Lobby WHERE NAME = '$this->name'");
            $mysqli->query("INSERT INTO "."Lobby"."(NAME, PLAYERS, MAXPLAYERS, IP, PORT, SWITCH) VALUES ('$this->name', '$this->players', '$this->maxplayers', '$this->ip', '$this->port', '$this->i')");

        }else{
            $mysqli->query("UPDATE "."Lobby"." SET PLAYERS = '$this->players', MAXPLAYERS = '$this->maxplayers', IP = '$this->ip', PORT = '$this->port', SWITCH = '$this->i' WHERE NAME = '$this->name'");

        }

    }

    public function onCompletion(Server $server){

    }


}
