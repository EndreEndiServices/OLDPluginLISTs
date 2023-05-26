<?php

namespace Games;

use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\utils\UUID;
use pocketmine\math\Vector3;
use pocketmine\utils\Config;
use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\TransferPacket;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;

use Games\customui\network\ModalFormRequestPacket;

use Games\customui\windows\SimpleForm;
use Games\customui\elements\Button;


class Games extends PluginBase implements Listener{

    public $games = [];
    public $category = [];

    public $HOST;
    public $USER;
    public $PASS;

    public function onEnable(){
    	$this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->exec();

        $this->games["null"] = "null:null:0"; 

        PacketPool::registerPacket(new ModalFormRequestPacket());

    }

    public function exec(){
        $this->getLogger()->info(TextFormat::YELLOW."(Multi-serv) Working...");

        @mkdir($this->getDataFolder());
        @mkdir($this->getDataFolder()."Entity");
        
        $this->saveDefaultConfig();
        $this->getResource("config.yml");

        $this->HOST = $this->getConfig()->get("HOST");
        
        $this->USER = $this->getConfig()->get("USER");
            $this->PASS = $this->getConfig()->get("PASS");

        if($this->getConfig()->get("mysqli.work.?") != false){
    
            $this->getLogger()->info(TextFormat::GREEN."Mysql is activated.");

            $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS);
                $mysqli->query("CREATE DATABASE IF NOT EXISTS "."Games");

            $this->GenerateAll();

            $this->getScheduler()->scheduleRepeatingTask(new GamesClock($this), 20*$this->getConfig()->get("timer.server.speed")); //int

        }else{
            $this->getLogger()->info(TextFormat::RED."Mysql is disabled, check in config.yml for activate it, plugin disabled.");
            $this->getServer()->getPluginManager()->disablePlugin($this->getServer()->getPluginManager()->getPlugin("Games"));

        }

    }

    public function onTransfer(Player $player, string $ip, int $port){
        $pk = new TransferPacket();

        $pk->address = $ip;
        $pk->port = $port;

        $player->dataPacket($pk);

    }

    public function GenerateAll(){
        $count = $this->getConfig()->get("entity.max");

        for($i = 1; $i <= $count; $i += 1){ 
            $file = new Config($this->getDataFolder()."Entity"."/".$this->getConfig()->get("entity.eid.".$i).".yml", Config::YAML);

            $this->GenerateMysql($file->get("entity.game"));
            
        }

    }

    public function Join(PlayerJoinEvent $event){
        $player = $event->getPlayer();

        $this->LoadEntity($player);
        $this->LoadText($player);
        $this->LoadTitle($player);

    }

    public function LoadEntity(Player $player){
        $count = $this->getConfig()->get("entity.max");

        for($i = 1; $i <= $count; $i += 1){ 
            $this->GenerateEntity($this->getConfig()->get("entity.eid.".$i), $player);
            
        }

    }

    public function LoadText($player){
        $count = $this->getConfig()->get("entity.max");

        for($i = 1; $i <= $count; $i += 1){ 
            $this->GenerateText($this->getConfig()->get("entity.eid.".$i), $player);
            
        }

    }

    public function LoadTitle($player){
        $count = $this->getConfig()->get("entity.max");

        for($i = 1; $i <= $count; $i += 1){ 
            $this->GenerateTitle($this->getConfig()->get("entity.eid.".$i), $player);
            
        }

    }

    public function GenerateEntity(int $eid, Player $player){
        $file = new Config($this->getDataFolder()."Entity"."/".$eid.".yml", Config::YAML);

        $pk = new AddEntityPacket();
        
        $pk->type = $file->get("entity.type");
            $pk->entityRuntimeId = $eid;

        $pk->position = new Vector3($file->get("entity.x") + 0.5, $file->get("entity.y") + 0.01, $file->get("entity.z") + 0.5);

        $pk->yaw = $file->get("entity.yaw");
  
        $flags =

        (
            (1 << Entity::DATA_FLAG_IMMOBILE)

        );

        $pk->metadata = 

        [ 
            Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags]

        ];
        
        $player->dataPacket($pk);

    }

    public function GenerateText(int $eid, $player){
        $file = new Config($this->getDataFolder()."Entity"."/".$eid.".yml", Config::YAML);

        $pk = new AddPlayerPacket();

        $pk->entityRuntimeId = $eid + 1;

        $pk->uuid = UUID::fromRandom();

        $pk->position = new Vector3($x = $file->get("entity.x") + 0.5, $y = $file->get("entity.y") + 1.8, $z = $file->get("entity.z") + 0.5);

        $pk->item = Item::get(0);

        $string = $this->games[$file->get("entity.game")];

        $explode = explode(":", $string);
            $count = (int)$explode[2];

        $pk->username = TextFormat::YELLOW.$file->get("entity.name").TextFormat::GRAY." - ".TextFormat::AQUA.$count.TextFormat::GREEN." Player(s)";

        $flags = ((1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG) | (1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG) | (1 << Entity::DATA_FLAG_IMMOBILE));

        $pk->metadata = 

        [
            Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0], 
            Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags], 
            //Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $text]

        ];

        if($player instanceof Player){
            $player->dataPacket($pk);

        }else{
            foreach($this->getServer()->getOnlinePlayers() as $all){
                $all->dataPacket($pk);

                if($all->distance(new Vector3($x, $y - 1.8, $z)) <= 1.7){
                    $all->knockBack($all, 0, - $all->getDirectionVector()->x, - $all->getDirectionVector()->z, 0.5);
                    
                }

            }

        }

    }

    public function GenerateTitle(int $eid, $player){
        $file = new Config($this->getDataFolder()."Entity"."/".$eid.".yml", Config::YAML);

        $pk = new AddPlayerPacket();

        $pk->entityRuntimeId = $eid + 2;

        $pk->uuid = UUID::fromRandom();
        $pk->username = TextFormat::BOLD.TextFormat::WHITE."CLICK TO JOIN THE GAME";

        $pk->position = new Vector3($file->get("entity.x") + 0.5, $file->get("entity.y") + 2.7, $file->get("entity.z") + 0.5);

        $pk->item = Item::get(0);

        $flags = ((1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG) | (1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG) | (1 << Entity::DATA_FLAG_IMMOBILE));

        $pk->metadata = 

        [
            Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0], 
            Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags], 
            //Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $text]

        ];

        if($player instanceof Player){
            $player->dataPacket($pk);

        }

    }

    public function InspectMysql(String $key, String $value){
        $this->games[$key] = $value;

    }

    /*API*/

    public function GenerateMysql(String $name){
        $table = $name."(SWITCH VARCHAR(255), STATUS VARCHAR(255), PLAYERS INT(100) DEFAULT 0, MAXPLAYERS INT(100) DEFAULT 1, SERVER VARCHAR(255))";

        $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Games");
        $mysqli->query("CREATE TABLE IF NOT EXISTS ".$table);
        
    }

    /*API*/

    public function ActionMysql(DataPacketReceiveEvent $event){
        $pk = $event->getPacket();
        $player = $event->getPlayer();

        if($pk instanceof InventoryTransactionPacket && $pk->transactionType == InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY){

            if(file_exists($this->getDataFolder()."Entity"."/".$pk->trData->entityRuntimeId.".yml")){
                $file = new Config($this->getDataFolder()."Entity"."/".$pk->trData->entityRuntimeId.".yml", Config::YAML);

                $string = $this->games[$file->get("entity.game")];

                if($file->get("entity.ui") == true){
                    $this->createUi($player, $file->get("entity.game"));

                    return true;

                }

                $explode = explode(":", $string);

                if($explode[0] == "null" or $explode[1] == "null"){
                    $player->sendMessage($this->getConfig()->get("null.server"));

                    return false;

                }

                $explode = explode(":", $string);
                    $ip = $explode[0];
                        $port = (int)$explode[1];

                $this->onTransfer($player, $ip, $port);

            }

        }

        if($pk instanceof ModalFormResponsePacket && isset($this->category[$player->getName()]) && $this->category[$player->getName()] != "null"){
            $pk = $pk->formData;
            $id = trim($pk);

            if($id == "null"){
                return false;

            }

            $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Games");
            $info = mysqli_fetch_all($mysqli->query("SELECT * FROM ".$this->category[$player->getName()]));

            if($info[(int)$id][0] == "off"){
                $player->sendMessage($this->getConfig()->get("null.server"));

                return false;

            }elseif($info[(int)$id][2] >= $info[(int)$id][3]){
                $player->sendMessage($this->getConfig()->get("full.server"));

                return false;

            }

            $explode = explode(":", $info[(int)$id][4]);
            $ip = $explode[0];
            $port = (int)$explode[1];

            $this->category[$player->getName()] = "null";

            $this->onTransfer($player, $ip, $port);

        }

        return true;

    }

    public function createUi(Player $player, $game){
        $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Games");
        $info = mysqli_fetch_all($mysqli->query("SELECT * FROM ".$game));

        $this->category[$player->getName()] = $game;

        if(count($info) == 0){
            $player->sendMessage($this->getConfig()->get("null.server"));
            $this->category[$player->getName()] = "null";

            return;

        }

        $modal = new SimpleForm("Selection", TextFormat::BOLD."NOTE".TextFormat::RESET.":"." ".$this->getConfig()->get("games.note.info"));

        for($i = 0; $i < count($info); $i += 1){

            if($info[$i][0] == "off"){
                $message = TextFormat::BOLD.TextFormat::RED."OFFLINE".TextFormat::RESET.TextFormat::WHITE." "."(".$info[$i][2]."/".$info[$i][3].")";
                
            }else{
                $message = TextFormat::BOLD.TextFormat::DARK_AQUA."ONLINE".TextFormat::RESET.TextFormat::WHITE." "."(".$info[$i][2]."/".$info[$i][3].")";

            }

            $modal->addButton(new Button($info[$i][1]." ".$message));

        }

        $pk = new ModalFormRequestPacket();

        $pk->formId = 1;
        $pk->formData = json_encode($modal);

        $player->dataPacket($pk);
        
    }


}
