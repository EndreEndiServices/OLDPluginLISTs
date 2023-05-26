<?php

namespace Box;

use pocketmine\Server;
use pocketmine\scheduler\AsyncTask;


class Inspect extends AsyncTask{

    private $category = ["CKey", "CBox"];

    private $CKey;
    private $CBox;

    private $HOST;
    private $USER;
    private $PASS;

    private $name;

    public function __construct(string $host, string $user, string $pass, string $name){
        $this->HOST = $host;
        $this->USER = $user;
        $this->PASS = $pass;

        $this->name = $name;

    }

    public function onRun(){
        $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Crate");

        for($i = 0; $i < count($this->category); $i += 1){

            if($mysqli->query("SELECT * FROM ".$this->category[$i]." WHERE PLAYER = '$this->name'")->num_rows > 0){
                $result = mysqli_fetch_row($mysqli->query("SELECT * FROM ".$this->category[$i]." WHERE PLAYER = '$this->name'"));

                if($i == 0) $this->CKey = (int)$result[1];
                if($i == 1) $this->CBox = (int)$result[1];

            }else{
                if($i == 0) $this->CKey = 0;
                if($i == 1) $this->CBox = 0;

            }

        }

    }

    public function onCompletion(Server $server){
        $server->getPluginManager()->getPlugin("Box")->createUi($this->name, "box", $this->CKey, $this->CBox);

    }


}
