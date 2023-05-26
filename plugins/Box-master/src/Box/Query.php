<?php

namespace Box;

use pocketmine\Server;
use pocketmine\scheduler\AsyncTask;


class Query extends AsyncTask{

    private $PriceForCategory = ["CKey" => -750, "CBox" => -750];

    private $HOST;
    private $USER;
    private $PASS;

    private $name;

    private $coins;

    private $category;

    private $result;

    public function __construct(string $host, string $user, string $pass, string $name, int $coins, string $category){
        $this->HOST = $host;
        $this->USER = $user;
        $this->PASS = $pass;

        $this->name = $name;

        $this->coins = $coins;

        $this->category = $category;

        $this->result = null;

    }

    public function onRun(){
        $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Crate");

        if($this->category == "CBox"){

            if($mysqli->query("SELECT * FROM "."CBox"." WHERE PLAYER = '$this->name'")->num_rows > 0){
                $box = mysqli_fetch_row($mysqli->query("SELECT * FROM "."CBox"." WHERE PLAYER = '$this->name'"));

                if($mysqli->query("SELECT * FROM "."CKey"." WHERE PLAYER = '$this->name'")->num_rows > 0){
                    $key = mysqli_fetch_row($mysqli->query("SELECT * FROM "."CKey"." WHERE PLAYER = '$this->name'"));

                    if($box[1] > 1){
                        $mysqli->query("UPDATE "."CBox"." SET COUNT = COUNT - '1' WHERE PLAYER = '$this->name'");

                    }else{
                        $mysqli->query("DELETE FROM "."CBox"." WHERE PLAYER = '$this->name'");

                    }

                    if($key[1] > 1){
                        $mysqli->query("UPDATE "."CKey"." SET COUNT = COUNT - '1' WHERE PLAYER = '$this->name'");

                    }else{
                        $mysqli->query("DELETE FROM "."CKey"." WHERE PLAYER = '$this->name'");

                    }

                    return $this->result = "open_normal";

                }else{
                    return $this->result = "error_key";

                }

            }

        }

        if($this->coins >= abs($this->PriceForCategory[$this->category])){

            if($mysqli->query("SELECT * FROM ".$this->category." WHERE PLAYER = '$this->name'")->num_rows > 0) {
                $mysqli->query("UPDATE " . $this->category . " SET COUNT = COUNT + '1' WHERE PLAYER = '$this->name'");

            }else{
                $mysqli->query("INSERT INTO ".$this->category."(PLAYER, COUNT) VALUES ('$this->name', '1')");

            }

            return $this->result = "buy_normal";

        }else{
            return $this->result = "error_price";

        }

    }

    public function onCompletion(Server $server){
        $server->getPluginManager()->getPlugin("Box")->ResultQuery($this->result, $this->name, $this->category);

    }


}
