<?php

namespace Box;

use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\utils\UUID;
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\utils\Config;
use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;
use pocketmine\network\mcpe\protocol\AddItemEntityPacket;
use pocketmine\network\mcpe\protocol\SetEntityMotionPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

use Box\customui\network\ModalFormRequestPacket;

use Box\customui\windows\SimpleForm;
use Box\customui\elements\Button;


class Box extends PluginBase implements Listener{

    public $particle = [], $pet = [], $armor = [];

    public $category = [];

    public $allowed = true;

    private $HOST;
    private $USER;
    private $PASS;

    private $id;
    private $x, $y, $z;

    public function onEnable(){
    	$this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->exec();

        PacketPool::registerPacket(new ModalFormRequestPacket());

        $this->registerParticles();
        $this->registerPets();
        $this->registerArmors();

    }

    public function exec(){
        $this->getLogger()->info(TextFormat::YELLOW."(Multi-serv) Working...");

        @mkdir($this->getDataFolder());
        
        $this->saveDefaultConfig();
        $this->getResource("config.yml");
        $this->saveResource("Block.yml");

        $this->HOST = $this->getConfig()->get("HOST");
        
        $this->USER = $this->getConfig()->get("USER");
            $this->PASS = $this->getConfig()->get("PASS");

        if($this->getConfig()->get("mysqli.work.?") != false){
            
            $this->getLogger()->info(TextFormat::GREEN."Mysql is activated.");

            $CBOX = "CBox"."(PLAYER VARCHAR(255), COUNT INT(100) DEFAULT 0)";

            $CKEY = "CKey"."(PLAYER VARCHAR(255), COUNT INT(100) DEFAULT 0)";

            $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS);
                $mysqli->query("CREATE DATABASE IF NOT EXISTS "."Crate");

            $mysqli = new \mysqli($this->HOST, $this->USER, $this->PASS, "Crate");

                $mysqli->query("CREATE TABLE IF NOT EXISTS ".$CBOX);
                    $mysqli->query("CREATE TABLE IF NOT EXISTS ".$CKEY);

            $this->BlockSetting(1, 1, 1, 1);

        }else{
            $this->getLogger()->info(TextFormat::RED."Mysql is disabled, check in config.yml for activate it, plugin disabled.");
            $this->getServer()->getPluginManager()->disablePlugin($this->getServer()->getPluginManager()->getPlugin("Box"));

        }

    }

    public function onJoin(PlayerJoinEvent $event){
        $player = $event->getPlayer();

        $this->category[$player->getName()] = "null";
        $this->BlockNaming($event->getPlayer());

    }

    public function onQuit(PlayerQuitEvent $event){
        $player = $event->getPlayer();

        if(isset($this->category[$player->getName()])){
            unset($this->category[$player->getName()]);

        }

    }

    public function onDrop(PlayerDropItemEvent $event){ $event->setCancelled(true); }

    public function BlockPlacing(BlockPlaceEvent $event){
        $block = $event->getBlock();
        $player = $event->getPlayer();

        if($block->getId() == $this->id && $player->isOp()){

            if($block->getSide(Vector3::SIDE_DOWN)->getId() == 41){
                $this->BlockSetting(2, $block->x, $block->y, $block->z);

                foreach($this->getServer()->getOnlinePlayers() as $all){
                    $this->BlockNaming($all);

                }

                $player->sendMessage($this->getConfig()->get("set.message.box"));

            }

        }

    }

    public function BlockSetting(int $i, int $bx, int $by, int $bz){
        $file = new Config($this->getDataFolder()."Block".".yml", Config::YAML);

        if($i == 1){
            $this->id = $file->get("block.id");

            $this->x = $file->get("block.x");
                $this->y = $file->get("block.y");
                    $this->z = $file->get("block.z");

        }elseif($i == 2){

            $file->set("block.x", $bx);
                $file->set("block.y", $by);
                    $file->set("block.z", $bz);

            $file->save();
            
            $this->BlockSetting(1, 1, 1, 1);

        }

    }

    public function BlockNaming(Player $player){
        $pk = new AddPlayerPacket();

        $pk->entityRuntimeId = 1000019; 

        $pk->uuid = UUID::fromRandom();
        $pk->username = $this->getConfig()->get("name.box.entity");

        $pk->position = new Vector3($this->x + 0.5, $this->y + 1.4, $this->z + 0.5);

        $pk->item = Item::get(0);

        $flags =

        (
            (1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG) |
            (1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG) |
            (1 << Entity::DATA_FLAG_IMMOBILE)

        );

        $pk->metadata = 

        [
            Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0],
            Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
            //Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $this->getConfig()->get("name.box.entity")],

        ];

        $player->dataPacket($pk);
    
    }

    public function Item(Item $item, int $a){
        for($i = 0; $i <= 10; $i += 1){ 

            $pk = new AddItemEntityPacket();

            $pk->entityRuntimeId = 1000000 + $i;

            $pk->item = $item->setDamage($i);

            $x = cos($i) * 1.5;
            $z = sin($i) * 1.5;

            $pk->position = new Vector3($this->x + $x + 0.5, $this->y + 1.2, $this->z + $z + 0.5);

            foreach($this->getServer()->getOnlinePlayers() as $all){
                $all->dataPacket($pk);

            }

            $pk = new SetEntityMotionPacket();

            $pk->entityRuntimeId = 1000000 + $i;

            $pk->motion = new Vector3(0, $i / 10, 0);

            foreach($this->getServer()->getOnlinePlayers() as $all){
                $all->dataPacket($pk);

            }

            if($a == 1){
                $pk = new RemoveEntityPacket();

                $pk->entityUniqueId = 1000000 + $i;

                foreach($this->getServer()->getOnlinePlayers() as $all){
                    $all->dataPacket($pk);

                }

            }

        }

    }

    public function Text(string $text, int $a){
        $pk = new AddPlayerPacket();

        $pk->entityRuntimeId = 1000020;

        $pk->uuid = UUID::fromRandom();
        $pk->username = $text;

        $pk->position = new Vector3($this->x + 0.5, $this->y + 1.1, $this->z + 0.5);

        $pk->item = Item::get(0);

        $flags =

        (
            (1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG) |
            (1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG) |
            (1 << Entity::DATA_FLAG_IMMOBILE)

        );

        $pk->metadata = 

        [
            Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0],
            Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
            //Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $text],

        ];

        if($a == 1){
            $pk = new RemoveEntityPacket();

            $pk->entityUniqueId = 1000020;

        }

        foreach($this->getServer()->getOnlinePlayers() as $all){
            $all->dataPacket($pk);

        }


    }

    public function Random(Player $player){
        $IntToType = [1 => "Particle", 2 => "Pet", 3 => "Armor", 4 => "Coins"];
        $TypeConvertTo = ["Particle" => "Coins-750", "Pet" => "Coins-750", "Armor" => "Coins-750", "Coins" => null];

        $random = mt_rand(1, 4);
        $string = null;
        $coins = 0;

        if($random == 1) $string = $this->particle[mt_rand(1, 14)];
        if($random == 2) $string = $this->pet[mt_rand(1, 26)];
        if($random == 3) $string = $this->armor[mt_rand(1, 10)];

        if($TypeConvertTo[$IntToType[$random]] != null){
            $coins = explode("-", $TypeConvertTo[$IntToType[$random]]);

        }

        if($string != null){
            $name = explode(":", $string);

        }else{
            $name[2] = $coins = mt_rand(1, 10) * 100;

        }

        $this->Text(TextFormat::BOLD.strtoupper($name[2]." ".$IntToType[$random]), 0);
        $this->Item(Item::get(35, 0, 1), 0);


        $this->getServer()->getDefaultLevel()->addParticle(new DestroyBlockParticle(new Vector3($this->x + 0.5, $this->y + 1.2, $this->z + 0.5), Block::get($this->id)));
        $this->getServer()->broadcastMessage($player->getName().$this->getConfig()->get("win.all.message.0.box").TextFormat::RESET.TextFormat::BOLD.strtoupper($name[2]." ".$IntToType[$random]).$this->getConfig()->get("win.all.message.1.box"));


        if($random >= 4){

            if($this->getServer()->getPluginManager()->getPlugin("Coins")){
                $this->getServer()->getPluginManager()->getPlugin("Coins")->exchangeCoins($player, $coins);

            }else{
                $player->sendMessage("Critical error #Box_Coins_".$coins." report on twitter.");

            }

        }else{

            if($this->getServer()->getPluginManager()->getPlugin("Cosmetics")){
                $this->getServer()->getPluginManager()->getPlugin("Cosmetics")->CallTransaction($IntToType[$random], (int)$name[1], $player->getName(), "false_force");

            }else{
                $player->sendMessage("Critical error #Box_Cosmetics_".$IntToType[$random]."_".(int)$name[1]." report on twitter.");

            }

        }

        $this->getScheduler()->scheduleDelayedTask(new FinishClock($this), 20*7);

    }
    
    public function onInventoryOpen(PlayerInteractEvent $event){
        $player = $event->getPlayer();
        $block = $event->getBlock();

        if($block->getId() == $this->id && $block->x == $this->x && $block->y == $this->y && $block->z == $this->z){
            $event->setCancelled(true);

            if($this->allowed == true){
                $this->getServer()->getAsyncPool()->submitTask(new Inspect($this->HOST, $this->USER, $this->PASS, $player->getName()));

            }else{
                $player->sendMessage($this->getConfig()->get("error.message.use"));

            }

        }

    }

    public function onUiTransaction(DataPacketReceiveEvent $event){
        $packet = $event->getPacket();
        $player = $event->getPlayer();
        $name = $player->getName();
 
        if($packet instanceof ModalFormResponsePacket && isset($this->category[$name]) && $this->category[$name] == "box"){
            $pk = $packet->formData;
            $id = trim($pk);

            $this->category[$name] = "null";

            if($id == "null"){
                return;

            }

            $pool = $this->getServer()->getAsyncPool();

            $coins = 0;

            if($this->getServer()->getPluginManager()->getPlugin("Coins")){
                $coins = $this->getServer()->getPluginManager()->getPlugin("Coins")->getCoins($player);

            }else{
                $player->sendMessage("Critical error #Box_Coins report on twitter.");

            }

            if((int)$id == 1){
                $pool->submitTask(new Query($this->HOST, $this->USER, $this->PASS, $name, $coins, "CKey"));

            }else{
                $pool->submitTask(new Query($this->HOST, $this->USER, $this->PASS, $name, $coins, "CBox"));

            }

        }

    }

    public function ResultQuery(string $result, string $name, string $category){
        $player = $this->getServer()->getPlayerExact($name);

        if($player->isOnline() && $result == "open_normal"){

            if($this->allowed == true){
                $this->allowed = false;

                $player->sendMessage($this->getConfig()->get("open.message.cbox"));

                $this->getScheduler()->scheduleDelayedTask(new BoxClock($this, $player), 20*2.5);

            }else{
                $player->sendMessage($this->getConfig()->get("error.message.use"));

            }

        }elseif($player->isOnline() && $result == "error_key"){
            $player->sendMessage($this->getConfig()->get("0.message.key"));

        }elseif($player->isOnline() && $result == "error_price"){

            if($category == "CKey"){
                $player->sendMessage($this->getConfig()->get("error.message.ckey"));

            }elseif($category == "CBox"){
                $player->sendMessage($this->getConfig()->get("error.message.cbox"));

            }
            
        }

        $PriceForCategory = ["CKey" => -750, "CBox" => -750];

        if($result == "buy_normal"){

            if($this->getServer()->getPluginManager()->getPlugin("Coins")){
                $this->getServer()->getPluginManager()->getPlugin("Coins")->exchangeCoins($player, $PriceForCategory[$category]);

            }else{
                if($player->isOnline()){
                    $player->sendMessage("Critical error #Box_Coins_".$PriceForCategory[$category]." report on twitter.");

                }

            }

            if($player->isOnline()){
                $player->sendMessage($this->getConfig()->get("buy.message.".strtolower($category)));

            }

        }

    }

    public function createUi(string $name, string $i, int $ckey, int $cbox){
        $player = $this->getServer()->getPlayerExact($name);

        if($player->isOnline()){
            $this->category[$name] = $i;

            $coins = 0;

            if($this->getServer()->getPluginManager()->getPlugin("Coins")){
                $coins = $this->getServer()->getPluginManager()->getPlugin("Coins")->getCoins($player);

            }else{
                $player->sendMessage("Critical error #Box_Coins report on twitter.");

            }

            $modal = new SimpleForm("Box - §lCOINS§r: ".$coins, TextFormat::BOLD."NOTE".TextFormat::RESET.":"." ".$this->getConfig()->get("box.note.info"));

            if($cbox > 0){
                $button = new Button($cbox." box(s) "."§l:§r".TextFormat::BOLD.TextFormat::DARK_AQUA." OPEN BOX");

            }else{
                $button = new Button( $cbox." box(s) "."§l:§r".TextFormat::BOLD.TextFormat::DARK_AQUA." BUY BOX");

            }

            $modal->addButton($button->addImage('url', 'https://minecraft-fr.gamepedia.com/media/minecraft-fr.gamepedia.com/5/5d/Cristaux_de_prismarine.png?'));

            $button = new Button($ckey." key(s) "."§l:§r".TextFormat::BOLD.TextFormat::DARK_AQUA." BUY KEY"); //$key[1]

            $modal->addButton($button);

            $pk = new ModalFormRequestPacket();

            $pk->formId = 1;
            $pk->formData = json_encode($modal);

            $player->dataPacket($pk);

        }

    }

    public function registerParticles(){
        $this->particle[1] = "0:2:Critical";
        $this->particle[2] = "0:7:Flame";
        $this->particle[3] = "0:17:Heart";
        $this->particle[4] = "0:20:Portal";
        $this->particle[5] = "0:36:NoteBlock";
        $this->particle[6] = "0:29:Ink";
        $this->particle[7] = "0:33:Happy";
        $this->particle[8] = "0:32:Angry";
        $this->particle[9] = "0:40:EndRod";
        $this->particle[10] = "0:34:Enchant";
        $this->particle[11] = "0:10:RedStone";
        $this->particle[12] = "0:11:RisingDust";
        $this->particle[13] = "0:25:FallingDust";
        $this->particle[14] = "0:9:SmokeFire";

    }

    public function registerPets(){
        $this->pet[1] = "0:45:Witch";
        $this->pet[2] = "0:15:Villager";
        $this->pet[3] = "0:39:SilverFish";
        $this->pet[4] = "0:47:Husk"; 
        $this->pet[5] = "0:55:Endermite"; 
        $this->pet[6] = "0:40:Spider";
        $this->pet[7] = "0:32:Zombie";    
        $this->pet[8] = "0:33:Creeper";
        $this->pet[9] = "0:30:Parrot";
        $this->pet[10] = "0:61:ArmorStand";
        $this->pet[11] = "0:54:Shulker";
        $this->pet[12] = "0:44:ZombieVillager";
        $this->pet[13] = "0:14:Wolf"; 
        $this->pet[14] = "0:17:Squid"; 
        $this->pet[15] = "0:18:Rabbit"; 
        $this->pet[16] = "0:22:Ocelot"; 
        $this->pet[17] = "0:29:Lama"; 
        $this->pet[18] = "0:28:PolarBear";
        $this->pet[19] = "0:42:MagmaCube"; 
        $this->pet[20] = "0:37:SlimeCube"; 
        $this->pet[21] = "0:57:Vindicator"; 
        $this->pet[22] = "0:105:Vex";
        $this->pet[23] = "0:104:Evoker";
        $this->pet[24] = "0:49:Guardian";
        $this->pet[25] = "0:48:Wither";
        $this->pet[26] = "0:46:Stray";

    }

    public function registerArmors(){
        $this->armor[1] = "0:11141120:Red"; 
        $this->armor[2] = "0:65793:Black"; 
        $this->armor[3] = "0:16777045:Yellow"; 
        $this->armor[4] = "0:5286480:Green"; 
        $this->armor[5] = "0:16777215:White"; 
        $this->armor[6] = "0:5592575:Blue";
        $this->armor[7] = "0:16755200:Gold"; 
        $this->armor[8] = "0:11184810:Gray"; 
        $this->armor[9] = "0:11141290:Purple"; 
        $this->armor[10] = "0:5592405:DarkGray";

    }
    

}
