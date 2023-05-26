<?php

namespace Lobby;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\TransferPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

use Lobby\customui\network\ModalFormRequestPacket;

use Lobby\customui\windows\SimpleForm;
use Lobby\customui\elements\Button;


class Lobby extends PluginBase implements Listener{

    public $a = 0;

    public $category = [];

    private $HOST;
    private $USER;
    private $PASS;

    private $ip;
    private $port;

    private $time;

    public function onEnable(){
    	$this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->exec();

        PacketPool::registerPacket(new ModalFormRequestPacket());

    }

    public function exec(){
        $this->getLogger()->info(TextFormat::YELLOW."(Multi-serv) Working...");

        @mkdir($this->getDataFolder());
        
        $this->saveDefaultConfig();
        $this->getResource("config.yml");

        $this->HOST = $this->getConfig()->get("HOST");
        
        $this->USER = $this->getConfig()->get("USER");
            $this->PASS = $this->getConfig()->get("PASS");

        $this->timer((int)$this->getConfig()->get("timer.restart.speed"));

        if($this->getConfig()->get("mysqli.work.?") != false){
            
            $this->getLogger()->info(TextFormat::GREEN."Mysql is activated.");

            $TABLE = "Lobby"."(NAME VARCHAR(255), PLAYERS INT(100) DEFAULT 0, MAXPLAYERS INT(100) DEFAULT 100, IP VARCHAR(255), PORT INT(100) DEFAULT 0, SWITCH VARCHAR(30))";

            $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS);
                $mysqli->query("CREATE DATABASE IF NOT EXISTS "."Lobby");

            $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Lobby");
                $mysqli->query("CREATE TABLE IF NOT EXISTS ".$TABLE);

            $this->getScheduler()->scheduleRepeatingTask(new LobbyClock($this), 20*$this->getConfig()->get("lobby.update.speed")); //int
            $this->getScheduler()->scheduleRepeatingTask(new XstopClock($this), 20*1);

        }else{
            $this->getLogger()->info(TextFormat::RED."Mysql is disabled, check in config.yml for activate it, plugin disabled.");
            $this->getServer()->getPluginManager()->disablePlugin($this->getServer()->getPluginManager()->getPlugin("Lobby"));

        }

    }

    public function onJoin(PlayerJoinEvent $event){ 
        $player = $event->getPlayer();

        if($this->getConfig()->get("lobby.switch") == "on"){

            if($this->ip == "null" or $this->port == "null"){
                $player->kick($this->getConfig()->get("lobby.transfer.error"));

                return;

            }

            $this->onTransfer($player, $this->ip, (int)$this->port);

        }else{
            $player->sendMessage($this->getConfig()->get("lobby.join.success")." ".TextFormat::YELLOW.$this->getConfig()->get("lobby.name"));

        }

        $this->category[$player->getName()] = "null";

        #$player->getInventory()->setItem(8, Item::get($this->getConfig()->get("lobby.item.id"), $this->getConfig()->get("lobby.item.damage"), 1)); #debug
        #$player->getInventory()->setHeldItemIndex(0, 1, 2, 3, 4, 5, 6, 7, 8); #debug

    } 

    public function onQuit(PlayerQuitEvent $event){
        $player = $event->getPlayer();

        if(isset($this->category[$player->getName()])){
            unset($this->category[$player->getName()]);

        }

    }

    public function onDisable(){
        $this->switch($this->getConfig()->get("lobby.name"), "on");

    }

    public function onDrop(PlayerDropItemEvent $event){ $event->setCancelled(true); }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{

		switch($command->getName()){

            case "lobby": 
                if($sender instanceOf Player){

                    if(empty($args[0])){
                        $sender->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn());

                    }elseif($args[0] == "list"){
                        $this->list($sender);

                    }elseif($args[0] == "switch" && $sender->isOp()){

                        if(!empty($args[1])){

                            if($args[1] == "on" or $args[1] == "off"){
                                $this->switch($this->getConfig()->get("lobby.name"), $args[1]);

                                $this->getConfig()->set("lobby.switch", $args[1]);
                                $this->getConfig()->save();

                                $sender->sendMessage($this->getConfig()->get("lobby.switch.success")." ".TextFormat::YELLOW.$args[1]);

                            }else{
                                $sender->sendMessage($this->getConfig()->get("lobby.switch.error"));

                            }

                        }else{
                            $sender->sendMessage($this->getConfig()->get("lobby.switch.error"));

                        }

                    }elseif($args[0] == "switch" && !$sender->isOp()){
                        $sender->sendMessage($this->getConfig()->get("permission.error"));

                    }
                
                    if(!empty($args[0])){

                        if($args[0] != "list" && $args[0] != "switch" && $args[0] == $this->getConfig()->get("lobby.name")){
                            $sender->sendMessage($this->getConfig()->get("lobby.join.already.error"));

                        }elseif($args[0] != "list" && $args[0] != "switch"){
                            $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Lobby");

                            $name = $args[0];

                            if($mysqli->query("SELECT * FROM "."Lobby"." WHERE NAME = '$name'")->num_rows > 0){
                                $info = mysqli_fetch_row($mysqli->query("SELECT * FROM "."Lobby"." WHERE NAME = '$name'"));

                                if($info[5] == "off"){

                                    if($info[1] <= $info[2] + 1){
                                        if($sender instanceof Player) $this->onTransfer($sender, $info[3], $info[4]);

                                    }else{
                                        $sender->sendMessage($this->getConfig()->get("lobby.join.full.error"));

                                    }

                                }else{
                                    $sender->sendMessage($this->getConfig()->get("lobby.join.off.error"));

                                }
            
                            }else{
                                $sender->sendMessage($this->getConfig()->get("lobby.join.exist.error"));

                            }

                        }

                    }

                }

            break;

            case "xstop": 
                if($sender->isOp()){
                    $time = 25;

                    $sender->sendMessage($this->getConfig()->get("xstop.success.message"));
                    $this->timer($time);

                }else{
                    $sender->sendMessage($this->getConfig()->get("xstop.error.message"));
 
                }

            break;

        }

        return true;

    }

    public function timer(int $x){

        if($x <= 0){
            $this->time -= 1;

        }else{
            $this->time = $x;

        }

        #$this->getLogger()->info("restart-timer: ".$this->time); #debug

        if($this->time == 20){
            foreach($this->getServer()->getOnlinePlayers() as $all){
                $all->sendMessage($this->getConfig()->get("xstop.now.transfer.message"));

            }

            $this->switch($this->getConfig()->get("lobby.name"), "on");

        }elseif($this->time == 17){
            $this->Selector();

        }elseif($this->time <= 10){
            $this->getServer()->shutdown();

        }

    }

    public function onTransfer(Player $player, string $ip, int $port){
        $pk = new TransferPacket();

        $pk->address = $ip;
        $pk->port = $port;

        $player->dataPacket($pk);

    }

    public function onUpdate(){

        if($this->getConfig()->get("lobby.switch") == "off"){

            $name = $this->getConfig()->get("lobby.name");

            $players = count($this->getServer()->getOnlinePlayers());
                $maxplayers = $this->getServer()->getMaxPlayers();

            $ip = $this->getConfig()->get("lobby.ip");
                $port = $this->getConfig()->get("lobby.port");

            $i = $this->getConfig()->get("lobby.switch");

            $this->getServer()->getAsyncPool()->submitTask(new Query($this->HOST, $this->USER, $this->PASS, $name, $players, $maxplayers, $ip, $port, $i, $this->a));

            $this->a = 1;

        }else{
            $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Lobby");
            $info = mysqli_fetch_all($mysqli->query("SELECT * FROM "."Lobby"));

            $a = 1000;

            $this->ip = "null";
                $this->port = "null";

            for($i = 0; $i < count($info); $i += 1){

                $name = $info[$i][0];

                if($a > $info[$i][1] && $info[$i][5] == "off" && $name !== $this->getConfig()->get("lobby.name")){
                    $a = $info[$i][1];

                    $this->ip = $info[$i][3];
                        $this->port = $info[$i][4];

                }

            }

            #var_dump($this->ip); #debug
            #var_dump($this->port); #debug

        }

    }

    public function List(Player $player){
        $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Lobby");
        $info = mysqli_fetch_all($mysqli->query("SELECT * FROM "."Lobby"));

        $a = 0;
        
        $player->sendMessage(" \n".$this->getConfig()->get("lobby.list.success").":"."\n ");
        
        for($i = 0; $i < count($info); $i += 1){
            $a += 1;

            if($info[$i][5] == "off"){ 

                if($info[$i][0] == $this->getConfig()->get("lobby.name")){ 
                    $player->sendMessage(TextFormat::GRAY." ".$info[$i][0]." ".TextFormat::BOLD.TextFormat::GOLD."CONNECTED");

                }else{ 
                    $player->sendMessage(TextFormat::GRAY." ".$info[$i][0]." ".TextFormat::BOLD.TextFormat::DARK_GREEN."ONLINE");

                }

                $player->sendMessage(TextFormat::GRAY." Players:"." ".$info[$i][1]."/".$info[$i][2]);

            }else{ 
                $player->sendMessage(TextFormat::GRAY." ".$info[$i][0]." ".TextFormat::BOLD.TextFormat::RED."OFFLINE");
                $player->sendMessage(TextFormat::GRAY." Players:"." "."0"."/".$info[$i][2]);

            }

            $player->sendMessage(" ");

        }

    }

    public function onUiOpen(PlayerInteractEvent $event){
        $player = $event->getPlayer();
        $item = $event->getItem();

        if($item->getId() == $this->getConfig()->get("lobby.item.id") && $item->getDamage() == $this->getConfig()->get("lobby.item.damage")){
            $this->createUi($player, "lobby");

        }

    }

    public function onUiTransaction(DataPacketReceiveEvent $event){
        $packet = $event->getPacket();
        $player = $event->getPlayer();

        if($packet instanceof ModalFormResponsePacket && isset($this->category[$player->getName()]) && $this->category[$player->getName()] == "lobby"){
            $pk = $packet->formData;
            $id = trim($pk);

            $this->category[$player->getName()] = "null";

            if($id == "null"){
                return;

            }

            $this->Join($player, (int)$id);

        }

    }

    public function createUi(Player $player, $b){
        $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Lobby");
        $info = mysqli_fetch_all($mysqli->query("SELECT * FROM "."Lobby"));

        $this->category[$player->getName()] = $b;

        $a = 0;

        $modal = new SimpleForm("Lobby", TextFormat::BOLD."NOTE".TextFormat::RESET.":"." ".$this->getConfig()->get("lobby.note.info"));
        
        for($i = 0; $i < count($info); $i += 1){
            $a += 1;

            if($info[$i][5] == "off"){ 

                if($info[$i][0] == $this->getConfig()->get("lobby.name")){
                    $message = TextFormat::BOLD.TextFormat::GOLD."CONNECTED";

                    $button = new Button($info[$i][0]." ".$message);

                    $modal->addButton($button->addImage('url', 'https://minecraft-fr.gamepedia.com/media/minecraft-fr.gamepedia.com/f/fd/Verre_orange.png?'));

                }else{
                    $message = TextFormat::BOLD.TextFormat::DARK_GREEN."ONLINE";

                    $button = new Button($info[$i][0]." ".$message);

                    $modal->addButton($button->addImage('url', 'https://minecraft-fr.gamepedia.com/media/minecraft-fr.gamepedia.com/b/b8/Verre_vert_clair.png?'));

                }

            }else{
                $message = TextFormat::BOLD.TextFormat::RED."OFFLINE";

                $button = new Button($info[$i][0]." ".$message);

                $modal->addButton($button->addImage('url', 'https://minecraft-fr.gamepedia.com/media/minecraft-fr.gamepedia.com/b/b8/Verre_rouge.png?'));

            }

        }

        $pk = new ModalFormRequestPacket();

        $pk->formId = 1;
        $pk->formData = json_encode($modal);

        $player->dataPacket($pk);
        
    }

    public function Selector(){
        $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Lobby");
        $info = mysqli_fetch_all($mysqli->query("SELECT * FROM "."Lobby"));

        $a = 1000;

        $ip = "null";
            $port = "null";

        for($i = 0; $i < count($info); $i += 1){

            $name = $info[$i][0];

            if($a > $info[$i][1] && $info[$i][5] == "off" && $name !== $this->getConfig()->get("lobby.name")){
                $a = $info[$i][1];

                $ip = $info[$i][3];
                    $port = $info[$i][4];

            }

        }

        if($ip == "null" or $port == "null"){
            foreach($this->getServer()->getOnlinePlayers() as $all){
                $all->kick($this->getConfig()->get("lobby.transfer.error"));

            }

            return;

        }

        foreach($this->getServer()->getOnlinePlayers() as $all){
            $this->onTransfer($all, $ip, (int)$port);

        }
        
    }

    public function Switch(string $name, $i){
        $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Lobby");
        $mysqli->query("UPDATE "."Lobby"." SET SWITCH = '$i' WHERE NAME = '$name'");

    }

    public function Join(Player $player, int $id){
        $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Lobby");
        $info = mysqli_fetch_all($mysqli->query("SELECT * FROM "."Lobby"));

        $name = $info[$id][0];

        if($mysqli->query("SELECT * FROM "."Lobby"." WHERE NAME = '$name'")->num_rows > 0){

            if($info[$id][5] == "off"){

                if($name == $this->getConfig()->get("lobby.name")){
                    $player->sendMessage($this->getConfig()->get("lobby.join.already.error"));
                    
                }elseif($info[$id][1] < $info[$id][2]){
                    $this->onTransfer($player, $info[$id][3], $info[$id][4]);

                }else{
                    $player->sendMessage($this->getConfig()->get("lobby.join.full.error"));

                }

            }else{
                $player->sendMessage($this->getConfig()->get("lobby.join.off.error"));

            }
            
        }else{
            $player->sendMessage($this->getConfig()->get("lobby.join.exist.error"));

        }

    }


}
