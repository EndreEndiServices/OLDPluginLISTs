<?php
namespace IPECore;

use pocketmine\{Server, scheduler\PluginTask, plugin\PluginBase, event\Listener, utils\Config, utils\TextFormat as TF};
use IPECore\Task;
use pocketmine\Player;
use pocketmine\level\{Level,Position,ChunkManager};
use pocketmine\math\Vector3;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\block\Flowable;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\level\generator\object\Tree;
use pocketmine\utils\Random;
use pocketmine\item\Item;
use pocketmine\block\Block;
use pocketmine\event\TranslationContainer;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\EventPriority;
use pocketmine\event\Event;

class Main extends PluginBase implements Listener{

   public $config;
   public $listener;
   public $levels = [];
   public $removers = [];
   public $farmConfig, $speedConfig;
   public $farmData, $speedData;
   public $players = array();
   public $interval = 10;
   public $blockedcommands = array();
   private static $instance;

	public $crops = [ [ "item" => Item::SEEDS,"block" => Block::WHEAT_BLOCK ],[ "item" => Item::CARROT,"block" => Block::CARROT_BLOCK ],[ "item" => Item::POTATO,"block" => Block::POTATO_BLOCK ],[ "item" => Item::BEETROOT,"block" => Block::BEETROOT_BLOCK ],[ "item" => Item::SUGAR_CANE,"block" => Block::SUGARCANE_BLOCK ],[ "item" => Item::SUGARCANE_BLOCK,"block" => Block::SUGARCANE_BLOCK ],[ "item" => Item::PUMPKIN_SEEDS,"block" => Block::PUMPKIN_STEM ],[ "item" => Item::MELON_SEEDS,"block" => Block::MELON_STEM ],[ "item" => Item::DYE,"block" => 127 ],[ "item" => Item::CACTUS,"block" => Block::CACTUS ] ];
  public function onEnable(){
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    $this->getServer()->getScheduler()->scheduleRepeatingTask(new Task($this), 20);
    $this->saveDefaultConfig();
    $this->getServer()->getScheduler()->scheduleRepeatingTask(new Bar($this), 10);
    self::$instance=$this;
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
   	Server::getInstance()->getLogger()->info("IPECore by XFizzer Starting...");
   	Server::getInstance()->getCommandMap()->register("FloatingText", new Livecommands("lt"));
   	Entity::registerEntity(Text::class, true);
   	$this->loadConfig();
   	$this->setListener();
   	      $this->ores=array(14,15,16,73,56);
 $this->ingot=array(
 14 => 266,
 15 => 265,
 16 => 263,
 73 => 331,
 56 => 264);
       @mkdir($this->getDataFolder());
          $this->config = new Config ($this->getDataFolder() . "popup.yml" , Config::YAML, array(
               "mode" => "popup",
               "join" => "§a-player- joined the game!",
               "quit" => "§4-player- left the game!",
          ));
          $this->saveResource("popup.yml");
          
          $this->interval = $this->getConfig()->get("interval");
        $cmds = $this->getConfig()->get("blocked-commands");
        foreach($cmds as $cmd){
            $this->blockedcommands[$cmd]=1;
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new Scheduler($this, $this->interval), 20);
      }
           		$this->farmConfig = new Config ( $this->getDataFolder () . "farmlist.yml", Config::YAML );
		$this->farmData = $this->farmConfig->getAll ();
		
		$this->speedConfig = new Config ( $this->getDataFolder () . "speed.yml", Config::YAML, [ "growing-time" => 1200,"vip-growing-time" => 600 ] );
		$this->speedData = $this->speedConfig->getAll ();
		
		$this->getServer ()->getScheduler ()->scheduleRepeatingTask ( new FarmsTask ( $this ), 20 );
  }
  
       public function onDisable() {
		$this->farmConfig->setAll ( $this->farmData );
		$this->farmConfig->save ();
		$this->saveResource("ChatFormat.yml");
		
		$this->speedConfig->save ();
	}
  
    public function onCommand(CommandSender $sender, Command $cmd, $label, array $param ) {
		switch(strtolower($cmd->getName())){
			case "wild":
				if($sender->hasPermission("IncurrentCore.command.wild")) {
					if($sender instanceof Player) {
						$x = rand(1,350000);
            					$y = rand(1,256);
						$z = rand(1,350000);
						$sender->teleport($sender->getLevel()->getSafeSpawn(new Vector3($x, $y, $z)));
						$sender->sendTip(TF::AQUA . "[IPE] §7You've been teleported somewhere wild!");
						$sender->sendMessage(TF::AQUA . "[IPE] §7teleporting to: X-$x Z-$z");
					}
					else {
						$sender->sendMessage(TF::AQUA . "[IPE] &7Only in-game!");
					}
				}
				else {
					$sender->sendMessage(TF::AQUA . "[IPE] §7You have no permission to use this command!");
				}
				return true;
			break;
		}
	}
	
	        	public function getMax(){
		return $this->config->get("max-level");
	}
	
	       	public function onItemHeld(PlayerItemHeldEvent $ev){
		$p = $ev->getPlayer();
		$max = $this->getMax();
		$i = $p->getInventory()->getItemInHand();
		if($i instanceof Item){
			if($i->hasEnchantments()){
				foreach($i->getEnchantments() as $e){
					if($e->getLevel() >= $max){
						$levelofenchant = $e->getLevel();
						$p->getInventory()->removeItem($i);
						$p->sendMessage(TF::GREEN."[AbusiveEnchants]".TF::BLUE.$i->getName()."  has been removed from your inventory for being above or equal to the max enchantment level!");
					}
				}
			}
		}
	}
	
	          public function onBlockBreak(BlockBreakEvent $event) {
		$key = $event->getBlock ()->x . "." . $event->getBlock ()->y . "." . $event->getBlock ()->z;
		foreach ( $this->crops as $crop ) {
			if ( $event->getItem ()->getId () == $crop ["item"] and isset ( $this->farmData [$key] )) {
				unset ( $this->farmData [$key] );
			}
		}
	}
	
	    public function onBlock(PlayerInteractEvent $event) {
		if (! $event->getPlayer ()->hasPermission ( "Farms" ) and ! $event->getPlayer ()->hasPermission ( "Farms.VIP" )) return;
		$block = $event->getBlock ()->getSide ( 1 );
		
		// Cocoa bean
		if ($event->getItem ()->getId () == Item::DYE and $event->getItem ()->getDamage () == 3) {
			$tree = $event->getBlock ()->getSide ( $event->getFace () );
			// Jungle wood
			if ($tree->getId () == Block::WOOD and $tree->getDamage () == 3) {
				$event->getBlock ()->getLevel ()->setBlock ( $event->getBlock ()->getSide ( $event->getFace () ), new CocoaBeanBlock ( $event->getFace () ), true, true );
				return;
			}
		}
		
		// Farmland or sand
		if ($event->getBlock ()->getId () == Item::FARMLAND or $event->getBlock ()->getId () == Item::SAND) {
			foreach ( $this->crops as $crop ) {
				if ($event->getItem ()->getId () == $crop ["item"]) {
					$key = $block->x . "." . $block->y . "." . $block->z;
					
					$this->farmData [$key] ['id'] = $crop ["block"];
					$this->farmData [$key] ['damage'] = 0;
					$this->farmData [$key] ['level'] = $block->getLevel ()->getFolderName ();
					$this->farmData [$key] ['time'] = $this->makeTimestamp ( date ( "Y-m-d H:i:s" ) );
					$this->farmData [$key] ['growtime'] = $this->speedData [$event->getPlayer ()->hasPermission ( "Farms.VIP" ) ? "vip-growing-time" : "growing-time"];
					break;
				}
			}
		}
	}
	
	    public function checkVoid(PlayerMoveEvent $event){
    $player = $event->getPlayer();
    $x = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getFloorX();
    $y = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getFloorY();
    $z = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getFloorZ();
    $level = $this->getServer()->getDefaultLevel();
        if($event->getTo()->getFloorY() < 0){
            switch(mt_rand(1, 2) == 1){
              case 1:
              $player->teleport(new Position($x, $y, $z, $level));
              $player->setHealth($player->getHealth(20));
              $player->sendTip("§d§l»§r§aYou were saved from the §eVOID §d§l«");
              break;
              case 2:
              break;
             }
         }
      }
      
         public function tick(){
		foreach(array_keys($this->farmData) as $key){
            if(!isset($this->farmData[$key]['id'])){
                unset($this->farmData[$key]);
                continue;
            }
			if(! isset($this->farmData[$key]['time'])){
				unset($this->farmData[$key]);
				break;
			}
			$progress = $this->makeTimestamp(date("Y-m-d H:i:s")) - $this->farmData[$key]['time'];
			if($progress < $this->farmData[$key]['growtime']){
                continue;
            }

            $level = isset($this->farmData[$key]['level']) ? $this->getServer()->getLevelByName($this->farmData[$key]['level']) : $this->getServer()->getDefaultLevel();
            if(!$level instanceof Level)
            	continue;
            
            $coordinates = explode(".", $key);
			$position = new Vector3((int)$coordinates[0], (int)$coordinates[1], (int)$coordinates[2]);

            if($this->updateCrops($key, $level, $position)){
                unset($this->farmData[$key]);
                break;
            }
            $this->farmData[$key]['time'] = $this->speedData["growing-time"];
		}
	}
	
	   public function onChat(PlayerChatEvent $event){
		$pl = $event->getPlayer();
		$name = $pl->getName();
		$msg = $event->getMessage();
     		$event->setFormat("§6Member §f:: §7$name §f> §7$msg");
	}
	
	       public function makeTimestamp($date) {
		$yy = substr ( $date, 0, 4 );
		$mm = substr ( $date, 5, 2 );
		$dd = substr ( $date, 8, 2 );
		$hh = substr ( $date, 11, 2 );
		$ii = substr ( $date, 14, 2 );
		$ss = substr ( $date, 17, 2 );
		return mktime ( $hh, $ii, $ss, $mm, $dd, $yy );
	}

    /**
     * @param $key
     * @param Level $level
     * @param Vector3 $position
     * @return bool
     */
	
  
	public static function getInstance(){
   		return self::$instance;
   	}
   	
   	  	public function setListener() {
		if(!$this->listener instanceof EventListener) {
			$this->listener = new EventListener($this);
		}
		return;
	}
	
	     	public function getListener() {
		return $this->listener;
	}
	
	 public function onBreak(BlockBreakEvent $ev){
$p = $ev->getPlayer();
$block = $ev->getBlock();
$item = $ev->getItem()->getId();
$ev->setInstaBreak(true);
foreach($this->ores as $ore){
if($block->getId() === $ore && !$ev->isCancelled()){
$ev->setDrops(array());
$p->getInventory()->addItem(Item::get($this->ingot[$ore]));
$x = $p->getX();
                $y = $p->getY();
                $z = $p->getZ();
}
}
}

    public function onPlayerLogin(PlayerLoginEvent $event) {
			$player = $event->getPlayer();
			$x = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getX();
			$y = $this->getServer()->getDefaultLevel()->getSafeSpawn()-> getY();
			$z = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getZ();
			$level = $this->getServer()->getDefaultLevel();
			$player->setLevel($level);
			$player->teleport(new Vector3($x, $y, $z, $level));
		}
		
		   public function onJoin(PlayerJoinEvent $jevent){
          if($this->config->get("mode") == "popup"){
               $joining = $jevent->getPlayer();
               $pname = $joining->getName();
               $jevent->setJoinMessage("");
               $joinpop = str_replace("-player-", $pname, $this->config->get("join"));
               $this->getServer()->broadcastPopup($joinpop);
          }elseif($this->config->get("mode") == "tip"){
               $joining = $jevent->getPlayer();
               $pname = $joining->getName();
               $jevent->setJoinMessage("");
               $jointip = str_replace("-player-", $pname, $this->config->get("join"));
               $this->getServer()->broadcastTip($jointip);
          }
	}
     public function onQuit(PlayerQuitEvent $qevent){
          if($this->config->get("mode") == "popup"){
               $quitting = $qevent->getPlayer();
               $pname = $quitting->getName();
               $qevent->setQuitMessage("");
               $quitpop = str_replace("-player-", $pname, $this->config->get("quit"));
               $this->getServer()->broadcastPopup($quitpop);
	     }elseif($this->config->get("mode") == "tip"){
               $quitting = $qevent->getPlayer();
               $pname = $quitting->getName();
               $qevent->setQuitMessage("");
               $quittip = str_replace("-player-", $pname, $this->config->get("quit"));
               $this->getServer()->broadcastTip($quittip);
	     }
	      if(isset($this->players[$qevent->getPlayer()->getName()])){
            $player = $qevent->getPlayer();
            if((time() - $this->players[$player->getName()]) < $this->interval){
                $player->kill();
            }
        }
	}
   	
   	public function loadConfig(){
   		$main=Main::getInstance();
   		@mkdir($this->getDataFolder());
   		$this->config=new Config($this->getDataFolder() . "texts.yml", Config::YAML);
    		if(!$this->config->get("LiveTexts")){
   			$opt=[
   			"File"=>"welcome.txt"
   			/*"ShowToPlayers" => []  COMING SOON */];
   			$this->config->set("LiveTexts", array());
   			$cfg=$this->config->get("LiveTexts");
   			$cfg["Welcome"]=$opt;
   			$this->config->set("LiveTexts", $cfg);
   	    touch($main->getDataFolder()."welcome.txt");
   	    $dosya=fopen($main->getDataFolder()."welcome.txt", "a");
   	    fwrite($dosya, "Welcome to the LiveTexts");
   	    fclose($dosya);
   			$this->config->save();
   		}
   	}
   	
   	public function onDamage(EntityDamageEvent $event){
   		$entity=$event->getEntity();
   		$main=Main::getInstance();
   		if($event instanceof EntityDamageByEntityEvent){
   			$damager=$event->getDamager();
   			if($damager instanceof Player){
   		 if(isset($main->removers[$damager->getName()])){
   		  	 $entity->close();
   		  	 $damager->sendMessage("§6[LiveTexts]§c LiveText removed.");
   		  	 unset($main->removers[$damager->getName()]);
   		  }
   		 }
   		}
   		$entity = $event->getEntity();
		$cause = $event->getCause();
		if($entity instanceof Player && $entity->hasPermission("nofalldamage")){
			if($cause == EntityDamageEvent::CAUSE_FALL){
				$event->setCancelled(true);
			}
		}
	   if($event instanceof EntityDamageByEntityEvent){
            if($event->getDamager() instanceof Player and $event->getEntity() instanceof Player){
                foreach(array($event->getDamager(),$event->getEntity()) as $players){
                    $this->setTime($players);
                }
            }
        }
   	}
   	
   	    private function setTime(Player $player){
        $msg = "§7[§cCombatLogger§7]§c Logging out now will cause you to die.\n§cPlease wait ".$this->interval." seconds.§r";
        if(isset($this->players[$player->getName()])){
            if((time() - $this->players[$player->getName()]) > $this->interval){
                $player->sendMessage($msg);
            }
        }else{
            $player->sendMessage($msg);
        }
        $this->players[$player->getName()] = time();
    }
        
        public function PlayerDeathEvent(PlayerDeathEvent $event){
        if(isset($this->players[$event->getEntity()->getName()])){
            unset($this->players[$event->getEntity()->getName()]);
            /*$cause = $event->getEntity()->getLastDamageCause();
            if($cause instanceof EntityDamageByEntityEvent){
                $e = $cause->getDamager();
                if($e instanceof Player){
                    $message = "death.attack.player";
                    $params[] = $e->getName();
                    $event->setDeathMessage(new TranslationContainer($message, $params));
                }
            }*/
        }
    }
            public function PlayerCommandPreprocessEvent(PlayerCommandPreprocessEvent $event){
        if(isset($this->players[$event->getPlayer()->getName()])){
            $cmd = strtolower(explode(' ', $event->getMessage())[0]);
            if(isset($this->blockedcommands[$cmd])){
                $event->getPlayer()->sendMessage("§7[§cCombatLogger§7]§c You cannot use this command during combat.§r");
                $event->setCancelled();
            }
        }
    }
    
            public function updateCrops($key, Level $level, Vector3 $position){
        switch($this->farmData[$key]['id']){
            case Block::WHEAT_BLOCK:
            case Block::CARROT_BLOCK:
            case Block::POTATO_BLOCK:
            case Block::BEETROOT_BLOCK:
                return $this->updateNormalCrops($key, $level, $position);

            case Block::SUGARCANE_BLOCK:
            case Block::CACTUS:
                return $this->updateVerticalGrowingCrops($key, $level, $position);

            case Block::PUMPKIN_STEM :
            case Block::MELON_STEM :
                return $this->updateHorizontalGrowingCrops($key, $level, $position);

            default:
                return true;
        }
    }

    /**
     * @param $key
     * @param Level $level
     * @param Vector3 $position
     * @return bool
     */
	public function updateNormalCrops($key, Level $level, Vector3 $position){
		if(++$this->farmData[$key]["damage"] >= 8){ //FULL GROWN!
			return true;
		}

		$level->setBlock($position, Block::get((int)$this->farmData[$key]["id"], (int)$this->farmData[$key]["damage"]));
        return false;
	}

    /**
     * @param $key
     * @param Level $level
     * @param Vector3 $position
     * @return bool
     */
	public function updateVerticalGrowingCrops($key, Level $level, Vector3 $position){
		if(++$this->farmData[$key]["damage"] >= 4){ //FULL GROWN!
			return true;
		}
		
		$cropPosition = $position->setComponents((int)$position->x, (int)$position->y+$this->farmData[$key]["damage"], (int)$position->z);
		if($level->getBlockIdAt((int)$cropPosition->x, (int)$cropPosition->y, (int)$cropPosition->z) !== Item::AIR){ //SOMETHING EXISTS
			return true;
		}
		$level->setBlock($cropPosition, Block::get((int)$this->farmData[$key]["id"], 0));
        return false;
	}

    /**
     * @param $key
     * @param Level $level
     * @param Vector3 $position
     * @return bool
     */
	public function updateHorizontalGrowingCrops($key, Level $level, Vector3 $position){
		$cropBlock = null;

		switch($this->farmData[$key]["id"]){
			case Block::PUMPKIN_STEM:
				$cropBlock = Block::get(Block::PUMPKIN);
				break;

			case Block::MELON_STEM:
				$cropBlock = Block::get(Block::MELON_BLOCK);
				break;

            default:
                return true;
		}
		
		if(++$this->farmData[$key]["damage"] >= 8){ // FULL GROWN!
			for($xOffset = - 1; $xOffset <= 1; $xOffset ++){
                for($zOffset = - 1; $zOffset <= 1; $zOffset ++){
                    if($xOffset === 0 and $zOffset === 0){ //STEM
                        continue;
                    }

                    $cropPosition = $position->setComponents((int)$position->x+$xOffset, (int)$position->y, (int)$position->z+$zOffset);
                    if($level->getBlockIdAt((int)$cropPosition->x, (int)$cropPosition->y, (int)$cropPosition->z) !== Item::AIR){ //SOMETHING EXISTS
                        $level->setBlock($cropPosition, $cropBlock);
                        return true;
                    }
                }
            }
            return true;
		}

		$level->setBlock($position, Block::get((int)$this->farmData[$key]["id"], (int)$this->farmData[$key]["damage"]));
        return false;
	}
}

class CocoaBeanBlock extends Flowable {
	public function __construct($face = 0) {
		parent::__construct ( 127, $this->getBeanFace ( $face ), "Cocoa Bean" );
		$this->treeFace = $this->getTreeFace ();
	}
	public function canBeActivated() {
		return true;
	}
	public function getDrops(Item $item) {
		$drops = [ ];
		
		if ($this->meta == $this->meta % 4 + 8) {
			$drops [] = [ 351,3,mt_rand ( 1, 4 ) ];
		} else {
			$drops [] = [ 351,3,1 ];
		}
		return $drops;
	}
	public function getTree() {
		return $this->getSide ( $this->treeFace );
	}
	public function getTreeFace() {
		switch ($this->meta % 4) {
			case 0 :
				return 2;
			case 1 :
				return 5;
			case 2 :
				return 3;
			case 3 :
				return 4;
			default :
				return rand ( 2, 5 );
		}
	}
	public function getBeanFace($face = 0) {
		switch ($face) {
			case 2 :
				return 0;
			case 3 :
				return 2;
			case 4 :
				return 3;
			case 5 :
				return 1;
			default :
			// TODO: $face === 0 등의 다른 경우에 대한 처리를 추가해야 함
		}
	}

   	
   	public function replacedText(string $text){
   		$tps=$this->getServer()->getTicksPerSecond();
   		$onlines=count($this->getServer()->getOnlinePlayers());
   		$maxplayers=$this->getServer()->getMaxPlayers();
   		$worldsc=count($this->getServer()->getLevels());
   		$server=$this->getServer();
   		$variables=[
   		"{line}"=>"\n",
   		"{tps}"=>$tps,
   		"{maxplayers}"=>$maxplayers,
   		"{onlines}"=>$onlines,
   		"{worldscount}"=>$worldsc,
   		"{ip}"=>$server->getIp(),
   		"{port}"=>$server->getPort(),
   		"{motd}"=>$server->getMotd(),
   		"{network}"=>$server->getNetwork()->getName()];
   		foreach($variables as $var=>$ms){
   			$text=str_ireplace($var, $ms, $text);
   		}
   		return $text;
   	}
   	
   	public function replaceForPlayer(Player $p, string $text){
   		$specialvars = [
   		"{name}"=>$p->getName(),
   		"{nametag}"=>$p->getNameTag(),
   		"{hunger}"=>$p->getFood(),
   		"{health}"=>$p->getHealth(),
   		"{maxhealth}"=>$p->getMaxHealth(),
   		"{nbt}"=>$p->namedtag,
   		"{level}"=>$p->getLevel()->getFolderName()];
   		foreach($specialvars as $var=>$ms){
   			$text = str_ireplace($var, $ms, $text);
   		}
   		return $text;
   	}
   	
   	public function createLiveText($x, $y, $z, $skin, $skinId, $inv, $yaw, $pitch, $chunk, $tag, $name, $file=""){
   	 $nbt = new CompoundTag;
   	 $nbt->Pos = new ListTag("Pos", [
   	 new DoubleTag("", $x),
   	 new DoubleTag("", $y),
   	 new DoubleTag("", $z)
   	 ]);
    $nbt->Rotation = new ListTag("Rotation", [
    new FloatTag("", $yaw),
    new FloatTag("", $pitch)
    ]);
    $nbt->Inventory = new ListTag("Inventory", $inv);
    $nbt->Skin = new CompoundTag("Skin", ["Data" => new StringTag("Data", $skin), "Name" => new StringTag("Name", $skinId)]);
    $nbt->Health = new ShortTag("Health", 20);
    $nbt->Invulnerable = new ByteTag("Invulnerable", 1);
    $nbt->LiveTextName= new StringTag("LiveTextName", $name);
    $nbt->CustomName=new StringTag("CustomName", $tag);
    $nbt->infos=new ListTag("infos", ["file"=>$file, "datafolder"=>$this->getDataFolder()."$name"]);
   		$entity=Entity::createEntity("Text", $chunk, $nbt, $tag);
   		$entity->spawnToAll();
   	}
}


class Task extends PluginTask{

  public function __construct($plugin){
    $this->plugin = $plugin;
    parent::__construct($plugin);
  }
  
  public function onRun($tick){
    $pl = $this->plugin->getServer()->getOnlinePlayers();
    $cfg = $this->plugin->getConfig();
    foreach($pl as $p){
    if(!$p->getInventory()->getItemInHand()->hasEnchantments()){
    $p->sendPopup(TF::GRAY."You are playing on ".TF::BOLD.$cfg->get("server-name").TF::RESET." ".$cfg->get("server-type")."\n".TF::DARK_GRAY."[".TF::LIGHT_PURPLE.count($this->plugin->getServer()->getOnlinePlayers()).TF::DARK_GRAY."/".TF::LIGHT_PURPLE.$this->plugin->getServer()->getMaxPlayers().TF::DARK_GRAY."] | ".TF::YELLOW."$".$this->plugin->getServer()->getPluginManager()->getPlugin("EconomyAPI")->myMoney($p).TF::DARK_GRAY." | ".TF::BOLD.TF::AQUA."SHOP: ".TF::RESET.TF::GREEN.$cfg->get("server-shop"));
      }
    }
  }
}
class Livecommands extends Command{
   	
   	private $name;
   	
   	public function __construct($name){
   		parent::__construct(
   		$name,
   		"LiveTexts plugin main Command",
   		"/lt <add|cancel|remove>");
   		$this->setPermission("livetext.command.use");
   	}
   	
   	public function execute(CommandSender $s, $label, array $args){
    if(!$s->hasPermission("livetext.command.use")){
      return true;
    }
    $help="§bTexts §76Help Page\n
    §7- /lt addtext <text(unlimited args)> :§e Add livetext without file. You can use {line} for new line\n
    §7- /lt add <TextName> :§e Add livetext with file\n
    §7- /lt remove :§e Remove a LiveText when Tap a entity\n
    §7- /lt updateall :§e Update All old LiveTexts to New LiveTexts\n
    §7- /lt cancel :§e Cancel remove event";
   		if(!empty($args[0])){
   			$main=Main::getInstance();
   			$core=Main::getInstance();
   			switch($args[0]){
   				case "addtext":
   				    array_shift($args);
   				    $text="";
   				    foreach($args as $t){
   				    	$text.=$t." ";
   				    }
   				    $replaced=$main->replacedText($text);
   				    $main->createLiveText($s->x, $s->y - 1, $s->z, $s->getSkinData(), $s->getSkinId(), $s->getInventory(), $s->yaw, $s->pitch, $s->chunk, $replaced, $args[0]);
   				    $s->sendMessage("§b[IPE] §7LiveText created(not file)");
   				    break;
   				case "add":
   				    if(!empty($args[1])){
   				    	  $file=$args[1];
   				    	  if($main->config->getNested("LiveTexts.$file")){
   				    	  	  $ad=$main->config->getNested("LiveTexts.$file")["File"];
   				    	  	  $yazi = file_get_contents($main->getDataFolder()."$ad");
   				    	  	  $x=$s->x;
   				    	  	  $y=$s->y;
   				    	  	  $z=$s->z;
   				    	  	  $skin=$s->getSkinData();
   				    	  	  $skinId=$s->getSkinId();
   				    	  	  $yaw=$s->yaw;
   				    	  	  $pitch=$s->pitch;
   				    	  	  $inv=$s->getInventory();
   				    	  	  $main->createLiveText($x, $y, $z, $skin, $skinId, $inv, $yaw, $pitch, $s->chunk, $yazi, $args[1], $dosya);
   				    	  	  $s->sendMessage("§b[IPE] §7Text created.");
   				    	  }else{
   				    	  	 $s->sendMessage("§b[IPE] §7Text not found on texts.yml");
   				    	  }
   				    }else{
   				    	 $s->sendMessage("§eUsage: /lt add <textname>");
   				    }
   				    break;
   				case "updateall":
   				    $levels=$main->getServer()->getLevels();
   				    foreach($levels as $level){
   				    	$entities=$level->getEntities();
   				    	foreach($entities as $entity){
   				    		if(isset($entity->namedtag->LiveTextName)){
   				    			if(!isset($entity->namedtag->infos) or (!$entity instanceof Human)){
   				    				$ad=$entity->namedtag->LiveTextName;
   				    				$yazi = file_get_contents($main->getDataFolder()."$ad");
   				    				$main->createLiveText($entity->x, $entity->y + 1, $entity->z, $entity->getSkinData(), $entity->getSkinId(), $entity->getInventory(), $entity->yaw, $entity->pitch, $entity->chunk, $entity->namedtag->CustomName, "$ad", $dosya);
   				    				$entity->close();
   				    			}
   				    		}
   				    	}
   				    }
   				    $s->sendMessage("§b[IncurrentPE] §7All old LiveTexts has been updated!");
   				    break;
   		  case "cancel":
   		      if(isset($main->removers[$s->getName()])){
   		      	 unset($main->removers[$s->getName()]);
   		      }
   		      $s->sendMessage("§b[IncurrentPE] §7Event cancelled.");
   		      break;
   		  case "remove":
   		      $main->removers[$s->getName()]=true;
   		      $s->sendMessage("§b[IncurrentPE] §7Please Touch a LiveText now.");
   		      break;
     	}
   }else{
   	 $s->sendMessage($help);
   }
  }
}
  
      class CocoaBean extends Item {
	public function __construct($meta = 0, $count = 1) {
		$this->block = Block::get ( 127 );
		parent::__construct ( 351, 3, $count, "Cocoa Bean" );
	}
	public function onActivate(Level $level, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz) {
		return true;
	}
}



