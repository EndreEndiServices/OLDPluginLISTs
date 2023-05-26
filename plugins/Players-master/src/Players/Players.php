<?php

namespace Players;

use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\event\server\DataPacketReceiveEvent;


class Players extends PluginBase implements Listener{

    public $kick = 0;

    private $HOST;
    private $USER;
    private $PASS;

    public function onEnable(){
    	$this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->exec();
    }

    public function onDisable(){
        $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Players");

        foreach($this->getServer()->getOnlinePlayers() as $players){
            $name = $players->getName();
        
            if($mysqli->query("SELECT * FROM "."Players"." WHERE PLAYER = '$name'")->num_rows > 0){
                $mysqli->query("DELETE FROM "."Players"." WHERE PLAYER = '$name'");

            }

        }

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

            $TABLE = "Players"."(PLAYER VARCHAR(255))";

            $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS);
                $mysqli->query("CREATE DATABASE IF NOT EXISTS "."Players");

            $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Players");
                $mysqli->query("CREATE TABLE IF NOT EXISTS ".$TABLE);

        }else{
            $this->getLogger()->info(TextFormat::RED."Mysql is disabled, check in config.yml for activate it, plugin disabled.");
            $this->getServer()->getPluginManager()->disablePlugin($this->getServer()->getPluginManager()->getPlugin("Players"));

        }

    }

    public function PlayerJoin(PlayerJoinEvent $event){
        $player = $event->getPlayer();

        if($this->getConfig()->get("players.update") == "on"){
            $this->getServer()->getAsyncPool()->submitTask(new Query($this->HOST, $this->USER, $this->PASS, $player->getName(), true, []));

        }

    }

    public function PlayerQuit(PlayerQuitEvent $event){
        $name = $event->getPlayer()->getName();

        if($this->getConfig()->get("players.update") == "on"){

            if($this->kick == 0){
                $this->getServer()->getAsyncPool()->submitTask(new Query($this->HOST, $this->USER, $this->PASS, $name, false, []));

            }

        }

        $this->kick = 0;

    }

    public function PlayerKick(PlayerKickEvent $event){
        $name = $event->getPlayer()->getName();

        if($this->getConfig()->get("players.update") == "on"){

            if($this->kick == 0){
                $this->getServer()->getAsyncPool()->submitTask(new Query($this->HOST, $this->USER, $this->PASS, $name, false, []));

            }

        }

    }

    public function onPreJoin(DataPacketReceiveEvent $event){
        $pk = $event->getPacket();

        if($pk instanceof LoginPacket){
                $pk->protocol = ProtocolInfo::CURRENT_PROTOCOL;

        }

    }

    public function Query(QueryRegenerateEvent $event){
        $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Players");
        $info = mysqli_fetch_all($mysqli->query("SELECT * FROM "."Players"));

        $event->setPlayerCount(count($info) + $this->getConfig()->get("fake.player.count"));
        
    }

    public function Kick(String $name){
        $kicked = $this->getServer()->getPlayer($name);

        if($kicked){
            $this->kick = 1;
            
            $kicked->kick($this->getConfig()->get("kick.player.message")); 

        }

    }


}
