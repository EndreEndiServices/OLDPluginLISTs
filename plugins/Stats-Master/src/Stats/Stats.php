<?php

namespace Stats;

use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\utils\UUID;
use pocketmine\math\Vector3;
use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;

use Stats\customui\network\ModalFormRequestPacket;

use Stats\customui\elements\Button;
use Stats\customui\windows\SimpleForm;


class Stats extends PluginBase implements Listener{

    private $HOST;
    private $USER;
    private $PASS;

    private $TABLE;

    private $list;

    private $stats;

    public function onEnable(){
    	$this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->exec();

        PacketPool::registerPacket(new ModalFormRequestPacket());

        $this->list = "";

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

            $this->TABLE = "Players"."(PLAYER VARCHAR(255))";

            $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS);
                $mysqli->query("CREATE DATABASE IF NOT EXISTS "."Stats");

            $this->getScheduler()->scheduleRepeatingTask(new StatsClock($this), 20);

            #$this->GenerateMysql("test"); #debug

        }else{
            $this->getLogger()->info(TextFormat::RED."Mysql is disabled, check in config.yml for activate it, plugin disabled.");
            $this->getServer()->getPluginManager()->disablePlugin($this->getServer()->getPluginManager()->getPlugin("Stats"));

        }

    }

    public function onJoin(PlayerJoinEvent $event){
        $player = $event->getPlayer();

        if($this->getConfig()->get("stats.utmost") >= 1){
            $this->sendStats($this->getConfig()->get("stats.table.name.1"), $player, "POINTS", 79448094830);
            $this->sendStats($this->getConfig()->get("stats.table.name.1"), $player, "KILLS", 89448094830);
            $this->sendStats($this->getConfig()->get("stats.table.name.1"), $player, "VICTORIES", 99448094830);

        }

    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{

        switch($command->getName()){

            case "stats":
                if(empty($args[0])){

                    if($this->list == ""){

                        for($i = 1; $i <= $this->getConfig()->get("stats.utmost"); $i += 1){

                            if($i == 1){
                                $this->list = $this->list." ".$this->getConfig()->get("stats.table.name.".$i);

                            }else{
                                $this->list = $this->list.", ".$this->getConfig()->get("stats.table.name.".$i);

                            }

                        }

                    }

                    $sender->sendMessage($this->getConfig()->get("command.return.use"));
                        $sender->sendMessage($this->getConfig()->get("command.return.list").$this->list);
                        
                }elseif($sender instanceof Player){
                    $info = $this->getStats($args[0], $sender);

                    if($info == "null"){
                        $sender->sendMessage($this->getConfig()->get("command.return.error"));

                        return false;

                    }

                    $modal = new SimpleForm(TextFormat::BOLD.TextFormat::DARK_AQUA."STATISTIC:"." ".strtoupper($args[0]), TextFormat::BOLD."NOTE".TextFormat::RESET.":"." ".$this->getConfig()->get("stats.note.info"));

                    $modal->addButton(new Button(TextFormat::BOLD.TextFormat::DARK_AQUA."POINTS:".TextFormat::RESET.TextFormat::WHITE." ".$info[1]));

                    $modal->addButton(new Button(TextFormat::BOLD.TextFormat::DARK_AQUA."KILLS:".TextFormat::RESET.TextFormat::WHITE." ".$info[2]));
                    $modal->addButton(new Button(TextFormat::BOLD.TextFormat::DARK_AQUA."DEATHS:".TextFormat::RESET.TextFormat::WHITE." ".$info[3]));

                    $modal->addButton(new Button(TextFormat::BOLD.TextFormat::DARK_AQUA."PARTIES:".TextFormat::RESET.TextFormat::WHITE." ".$info[4]));
                    $modal->addButton(new Button(TextFormat::BOLD.TextFormat::DARK_AQUA."TIMER:".TextFormat::RESET.TextFormat::WHITE." ".$info[5]));

                    $modal->addButton(new Button(TextFormat::BOLD.TextFormat::DARK_AQUA."VICTORIES:".TextFormat::RESET.TextFormat::WHITE." ".$info[6]));
                    $modal->addButton(new Button(TextFormat::BOLD.TextFormat::DARK_AQUA."DEFEATS:".TextFormat::RESET.TextFormat::WHITE." ".$info[7]));

                    $pk = new ModalFormRequestPacket();

                    $pk->formId = 1;
                    $pk->formData = json_encode($modal);

                    $sender->dataPacket($pk);

                }

                return true;

            break;  
        
        }

        return true;

    }   

    public function getStats(String $table, Player $broadcast){
        $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Stats");
        $info = $mysqli->query("SHOW TABLES");

        $tables = $info->fetch_all();

        for($i = 0; $i < count($tables); $i += 1){

            if($tables[$i][0] == $table){

                $name = $broadcast->getName();

                if($mysqli->query("SELECT * FROM ".$table." WHERE PLAYERS = '$name'")->num_rows > 0){
                    $info = mysqli_fetch_row($mysqli->query("SELECT * FROM ".$table." WHERE PLAYERS = '$name'"));

                    return $info;

                }

            }

        }

        return "null";

    }

    public function sendStats(String $table, $broadcast, String $order, int $eid){
        $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Stats");

        $while = 9;

        $stats = @$mysqli->query("SELECT * FROM ".$table." ORDER BY ".$order." DESC LIMIT 0, 10");

        $this->stats = [];

        while($return = @mysqli_fetch_array($stats)){

            $player = $return['PLAYERS'];
            $point = $return[$order];

            $this->stats[$while] = $player."#".$point;

            $while -= 1;

        }

        //$stats->free();

        $id = 10;

        for($i = 0; $i <= 11; $i += 1){

            if(empty($this->stats[$i])){
                $name = "not found";
                $point = "0";

            }else{
                $explode = explode("#", $this->stats[$i]);
                    $name = $explode[0];
                    $point = $explode[1];

            }

            $pk = new AddPlayerPacket();

            $pk->entityRuntimeId = $eid + $i;
                $pk->uuid = UUID::fromRandom();

            $pk->position = new Vector3(
                                            (int)$this->getConfig()->get("stats.".strtolower($order).".pos.x") + 0.5, 
                                            $this->getConfig()->get("stats.".strtolower($order).".pos.y") - (0.4 * $id), 
                                            (int)$this->getConfig()->get("stats.".strtolower($order).".pos.z") + 0.5

                                        );

            $pk->item = Item::get(0);

            $flags = (
                (1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG) |
                (1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG) |
                (1 << Entity::DATA_FLAG_IMMOBILE)

            );

            if($id >= 4){

                $pk->metadata = 

                [
                    Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0],
                    Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
                    Entity::DATA_BOUNDING_BOX_WIDTH => [Entity::DATA_TYPE_FLOAT, 0],
                    Entity::DATA_BOUNDING_BOX_HEIGHT => [Entity::DATA_TYPE_FLOAT, 0]

                ];

                $pk->username = TextFormat::GREEN.$id."th. ".TextFormat::GRAY.$name.TextFormat::GREEN." with ".TextFormat::YELLOW.$point;
  
            }elseif($id == 3){

                $pk->metadata = 

                [
                    Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0],
                    Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
                    Entity::DATA_BOUNDING_BOX_WIDTH => [Entity::DATA_TYPE_FLOAT, 0],
                    Entity::DATA_BOUNDING_BOX_HEIGHT => [Entity::DATA_TYPE_FLOAT, 0]

                ];

                $pk->username = TextFormat::GOLD.$id."rd. ".TextFormat::GRAY.$name.TextFormat::GREEN." with ".TextFormat::YELLOW.$point;

            }elseif($id == 2){

                $pk->metadata = 

                [
                    Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0],
                    Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
                    Entity::DATA_BOUNDING_BOX_WIDTH => [Entity::DATA_TYPE_FLOAT, 0],
                    Entity::DATA_BOUNDING_BOX_HEIGHT => [Entity::DATA_TYPE_FLOAT, 0]

                ];

                $pk->username = TextFormat::GOLD.$id."nd. ".TextFormat::GRAY.$name.TextFormat::GREEN." with ".TextFormat::YELLOW.$point;

            }elseif($id == 1){

                $pk->metadata = 

                [
                    Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0],
                    Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
                    Entity::DATA_BOUNDING_BOX_WIDTH => [Entity::DATA_TYPE_FLOAT, 0],
                    Entity::DATA_BOUNDING_BOX_HEIGHT => [Entity::DATA_TYPE_FLOAT, 0]

                ];

                $pk->username = TextFormat::GOLD.$id."st. ".TextFormat::GRAY.$name.TextFormat::GREEN." with ".TextFormat::YELLOW.$point;

            }elseif($id == 0){

                $pk->metadata = 

                [
                    Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0],
                    Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
                    Entity::DATA_BOUNDING_BOX_WIDTH => [Entity::DATA_TYPE_FLOAT, 0],
                    Entity::DATA_BOUNDING_BOX_HEIGHT => [Entity::DATA_TYPE_FLOAT, 0],

                ];

                $pk->username = $this->getConfig()->get("stats.type")." ".strtoupper($order);

            }elseif($id == -1){

                $pk->metadata = 

                [
                    Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0],
                    Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
                    Entity::DATA_BOUNDING_BOX_WIDTH => [Entity::DATA_TYPE_FLOAT, 0],
                    Entity::DATA_BOUNDING_BOX_HEIGHT => [Entity::DATA_TYPE_FLOAT, 0],

                ];

                $pk->username = TextFormat::DARK_AQUA.TextFormat::BOLD.strtoupper($table);

            }

            if($broadcast instanceof Player){
                $broadcast->dataPacket($pk);
                
            }else{
                foreach($this->getServer()->getOnlinePlayers() as $players){
                    $players->dataPacket($pk);

                }

            }

            $id -= 1;

        }

    }

    /*API*/

    public function GenerateMysql(String $name){
        $table = $name."(
                            PLAYERS VARCHAR(255),
                            POINTS INT(100) DEFAULT 0,
                            KILLS INT(100) DEFAULT 0,  
                            DEATHS INT(100) DEFAULT 0, 
                            PARTIES INT(100) DEFAULT 0, 
                            TIMER INT(100) DEFAULT 0, 
                            VICTORIES INT(100) DEFAULT 0, 
                            DEFEATS INT(100) DEFAULT 0

                        )";

        $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Stats");
        $mysqli->query("CREATE TABLE IF NOT EXISTS ".$table);
        
    }

    /*API*/


}
