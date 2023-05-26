<?php

namespace Rank;

use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerChatEvent;


class Rank extends PluginBase implements Listener{

    public $chat = [];

    private $HOST;
    private $USER;
    private $PASS;

    public function onEnable(){
    	$this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->exec();
    }

    public function exec(){
        $this->getLogger()->info(TextFormat::YELLOW."(Multi-serv) Working...");

        @mkdir($this->getDataFolder());
        
        $this->saveDefaultConfig();
        $this->getResource("config.yml");

        $this->HOST = $this->getConfig()->get("HOST");

        if($this->getConfig()->get("mysqli.work.?") != false){
            
            $this->getLogger()->info(TextFormat::GREEN."Mysql is activated.");

            $this->USER = $this->getConfig()->get("USER");
            $this->PASS = $this->getConfig()->get("PASS");

            $TABLE = "Rank"."(PLAYER VARCHAR(255), RANK INT(100) DEFAULT 0)";

            $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS);
                $mysqli->query("CREATE DATABASE IF NOT EXISTS "."Rank");

            $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Rank");
                $mysqli->query("CREATE TABLE IF NOT EXISTS ".$TABLE);

        }else{
            $this->getLogger()->info(TextFormat::RED."Mysql is disabled, check in config.yml for activate it, plugin disabled.");
            $this->getServer()->getPluginManager()->disablePlugin($this->getServer()->getPluginManager()->getPlugin("Rank"));

        }

    }

    public function Join(PlayerJoinEvent $event){ 
        $player = $event->getPlayer();

        if($this->formRank($player) == false){
            $this->addRank($player);

        }

    }

    public function Chat(PlayerChatEvent $event){
        $message = TextFormat::clean($event->getMessage());
        $name = $event->getPlayer()->getName();

        if(!isset($this->chat[$name])){
            $this->chat[$name] = 0;
        }

        if($this->chat[$name] >= time() - 2){
            $this->chat[$name] = time() + 1;

            $event->getPlayer()->sendMessage($this->getConfig()->get("spam.warning"));
        
        }else{
            $this->chat[$name] = time();

            foreach($this->getServer()->getOnlinePlayers() as $player){
                $player->sendMessage($event->getPlayer()->getNameTag().TextFormat::WHITE." : ".strtolower($message));

            }
        
        }
        
        $event->setCancelled(true);

    }

    public function onQuit(PlayerQuitEvent $event){
        $name = $event->getPlayer()->getName();

        unset($this->chat[$name]);

    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{

		switch($command->getName()){

            case "rank":  
                if($sender->isOp()){

                    if(count($args) >= 2 && is_numeric($args[1]) && $sender->isOp()){

                        if($args[1] >= 1 && $args[1] <= 7){
                            $this->setRank($args[0], $args[1]);

                            $sender->sendMessage($this->getConfig()->get("setrank.success"));

                        }elseif($args[1] == 0){

                            if($this->delRank($args[0]) == true){
                                $sender->sendMessage($this->getConfig()->get("delrank.success"));

                            }else{
                                $sender->sendMessage($this->getConfig()->get("delrank.error"));

                            }

                        }else{
                            $sender->sendMessage($this->getConfig()->get("command.error"));

                        }

                    }else{
                        $sender->sendMessage($this->getConfig()->get("command.error"));
                        
                    }

                }else{
                    $sender->sendMessage($this->getConfig()->get("permission.error"));

                }

            break;  

        }

        return true;

    }

    public function formRank(Player $player){
        $name = $player->getName();

        if($this->getRank($player) == 0){
            $explode = explode("_", $name);

            if(strtoupper($explode[0]) == strtoupper($this->getConfig()->get("rank.form"))){
                $this->setRank($name, 1);

                return true;

            }

        }

        return false;

    }

    public function setRank($name, int $rank){
        $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Rank");

        if($mysqli->query("SELECT * FROM "."Rank"." WHERE PLAYER = '$name'")->num_rows > 0){
            $mysqli->query("UPDATE "."Rank"." SET RANK = '$rank' WHERE PLAYER = '$name'");

        }else{ 
            $mysqli->query("INSERT INTO "."Rank"."(PLAYER, RANK) VALUES ('$name', '$rank')");

        }

        $i = $this->getServer()->getPlayer($name);
        
        if($i){ 
            $this->addRank($i);

        }

        return true;
        
    }

    public function delRank($name){
        $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Rank");

        if($mysqli->query("SELECT * FROM "."Rank"." WHERE PLAYER = '$name'")->num_rows > 0){
            $mysqli->query("DELETE FROM "."Rank"." WHERE PLAYER = '$name'");

            $i = $this->getServer()->getPlayer($name);
        
            if($i){ 
                $this->addRank($i);

            }

            return true;
            
        }else{
            return false;

        }

    }

    public function getRank(Player $player){
        $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Rank");

        $name = $player->getName();

        if($mysqli->query("SELECT * FROM "."Rank"." WHERE PLAYER = '$name'")->num_rows > 0){
            $info = mysqli_fetch_row($mysqli->query("SELECT PLAYER, RANK FROM "."Rank"." WHERE PLAYER = '$name'"));

            return $info[1];

        }else{
            return 0;

        }

    }

    public function addRank(Player $player){
        $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Rank");

        $name = $player->getName();

        if($mysqli->query("SELECT * FROM "."Rank"." WHERE PLAYER = '$name'")->num_rows > 0){
            $info = mysqli_fetch_row($mysqli->query("SELECT PLAYER, RANK FROM "."Rank"." WHERE PLAYER = '$name'"));

            $player->setNameTag($this->getConfig()->get("rank.".$info[1]).$name);

        }else{
            $player->setNameTag("§7".$name);

        }

    }


}
  