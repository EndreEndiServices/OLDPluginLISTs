<?php

namespace Games;

use pocketmine\Server;
use pocketmine\scheduler\AsyncTask;

class Query extends AsyncTask{

    public $games = [];

    public $save = [];
    
    public function __construct(String $host, String $user, String $pass){
        $this->HOST = $host;
        $this->USER = $user; 
        $this->PASS = $pass;
        
    }

    public function onRun(){
        $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Games");
        $info = $mysqli->query("SHOW TABLES");

        $tables = $info->fetch_all();

        $id = -1;

        for($i = 0; $i < count($tables); $i += 1){

            $this->name = $tables[$i][0];

            $info = @mysqli_fetch_all($mysqli->query("SELECT * FROM ".$this->name));

            $count = 0;
            $status = false;

            $players = -1;

            for($a = 0; $a < count($info); $a += 1){
                $count += $info[$a][2];

                if($info[$a][0] == "on" && $info[$a][1] == "wait"){

                    if($info[$a][2] < $info[$a][3]){

                        if($players < $info[$a][2]){
                            $players = $info[$a][2];

                            $server = $info[$a][4];

                        }

                        $status = true;

                    }

                }

            }

            $id += 1;

            if($status == false){
                $this->save[$id] = $this->name;
                $this->games[$this->name] = "null:null:".$count;

            }else{
                $this->save[$id] = $this->name;
                $this->games[$this->name] = $server.":".$count;

            }

            #var_dump($this->games[$this->name]); #debug
            #var_dump($this->name); #debug

        }

    }

    public function onCompletion(Server $server){
        for($i = 0; $i < count($this->games); $i += 1){
            $key = $this->save[$i];
            $value = $this->games[$key];

            $server->getPluginManager()->getPlugin("Games")->InspectMysql($key, $value);

        }

        $server->getPluginManager()->getPlugin("Games")->LoadText("null");

    }


}
