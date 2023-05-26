<?php
namespace PrisonCore;

use pocketmine\Server;
use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\command\Command;

use pocketmine\item\Item;
use pocketmine\block\Block;

use pocketmine\level\Level;
use pocketmine\level\Position;

use pocketmine\math\Vector3;

use pocketmine\event\Listener as MainCore;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerInteractEvent;

use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as C;
use onebone\economyapi\EconomyAPI;
use PrisonCore\BasicLib;

class Core extends PluginBase implements MainCore{
	  
	public $prefix;
	//public $flying = [];
	
	public function onEnable(){
		 $this->saveDefaultConfig();
		 //$this->updateChangelog();
	    $this->prefix = $this->getConfig()->get("prefix");
	    $this->ranks = new Config($this->getDataFolder()."ranks.yml", Config::YAML);
	    $this->factory = new Config($this->getDataFolder()."factories.yml", Config::YAML);
	    //$this->badwords = new Config($this->getDataFolder()."badwords.yml", Config::YAML);
	    //$this->saveResource("badwords.yml");
	    $this->cfg = $this->getConfig()->getAll();
	    $this->getLogger()->info($this->prefix." §aloaded prisons successfully!");
	    $this->registerTasks();
	    $this->registerEvents();
	    $this->registerAbilities();
	    $this->registerCommands();
	}
		
	   /* @param update changeLog */
	 
	   public function updateChangelog(){
		     if(file_exists($this->getDataFolder()."changelog.txt")){
		       unlink($this->getDataFolder()."changelog.txt");
		     }
	        if(!file_exists($this->getDataFolder()."changelog.txt")){
		       $this->saveResource("changelog.txt", false);
		     }
		  }
		
		/* 
		  *@ API function
		  */
		
		public function getInstance(){
		   return self::$instance;
			}
			
		/* 
		  * @ Bad word checker in dev
		  */
		
		 public function containsBadWord($msg, array $words){
		    foreach($words as $word){
		      if(in_array($msg, $word)){
			     return true;
			    }else{
			      return false;
				 }
			  }
			}
		 public function getBadWords(){
		      //$this->badwords = new Config($this->getDataFolder()."badwords.yml", Config::YAML);
		      //$words = $this->badwords->getAll();
		    //return $words["words"];
			}
	 
	  /* @param Abilities register */
	
	   public function registerAbilities(){
	       $this->getServer()->getPluginManager()->registerEvents(new Abilities\FastMiner($this), $this);
	       $this->getServer()->getPluginManager()->registerEvents(new Abilities\SelfDestruct($this), $this);
	       $this->getServer()->getPluginManager()->registerEvents(new Abilities\SelfDefense($this), $this);
	       $this->getServer()->getPluginManager()->registerEvents(new Abilities\NightWalker($this), $this);
	       //$this->getServer()->getPluginManager()->registerEvents(new Abilities\Mending($this), $this);
		}
			
	 /* 
	  * @param factories stuff
	  */
	
	 public function registerTasks(){
	      //$this->getServer()->getScheduler()->scheduleRepeatingTask(new Tasks\FactoryTask($this), 20);
		}
    public function createFactory($player){
         $factory = strtolower($player->getName());
         $this->factory->set("$factory", array("level" => 1));
         $this->factory->save();
        $this->factory->reload();
	  }
	public function factoryExists($player){
		  $this->factory->reload();
	     $factory = strtolower($player->getName());
	     $temp = $this->factory->getAll();
	     if(isset($temp["$factory"])){
		     return true;
		  }else{
		    return false;
			}
		}
	public function getFactoryLevel($player){
		  $this->factory->reload();
	     $factory = strtolower($player->getName());
	     $output = $this->factory->getAll();
	    return $output["$factory"]["level"];
		}
	public function setFactoryLevel($player, $level){
	     $factory = strtolower($player->getName());
         $this->factory->set("$factory", array("level" => $this->getFactoryLevel($player) + $level));
         $this->factory->save();
        $this->factory->reload();
		}
	/*
	 * @registerEvents function
	 */
	 public function giveFactoryIncome(Player $player){
		if($this->factoryExists($player)){
	      $economy = EconomyAPI::getInstance();
	      $level = $this->getFactoryLevel($player);
	        $temp = $this->cfg["deposit.item"];
	        $itemdata = explode(":", $temp);
	      $item = Item::get($itemdata[0], $itemdata[1], $level);
	      $min = $this->cfg["deposit.min.money"];
	      $max = $this->cfg["deposit.max.money"];
	      $player->getInventory()->addItem($item);
	        $money = rand($min, $max * $level);
	      $economy->addMoney($player, $money);
	      $player->sendMessage("§a§l[FactoryManager]§8 ›› §r§7Successfully recieved a diposit!");
		   $player->sendMessage("§a§l[FactoryManager]§8 ›› §r§7Your factory has made ".$money."$ this session!");
		   switch(rand(1, 20)){
		      case 20:
		         BasicLib::customGive($player, Item::get(339,100,1), "§aFactory extra income\n§7Value:§e 100$");
			}
		}
	}
	public function registerEvents(){
	   $this->getServer()->getPluginManager()->registerEvents($this, $this);
	   $this->getServer()->getPluginManager()->registerEvents(new Listeners\FactoryListener($this), $this);
	   if($this->cfg["built-in-chat"] == "true" && $this->getServer()->getPluginManager()->getPlugin("PureChat") == null){
		  $ranks = $this->ranks;
		  $this->getServer()->getPluginManager()->registerEvents(new Formats\chat($this, $ranks), $this);
		}else{
		  $this->getLogger()->info("§cBuilt in chat is disabled. It maybe disabled from config or PureChat is installed");
			}
	   // register other event class if need
      }

    /*
     * @ register commands function
    */

    public function registerCommands(){
         $this->getServer()->getCommandMap()->register("setformat", new Commands\setformat($this));
         $this->getServer()->getCommandMap()->register("withdraw", new Commands\withdraw($this));
         $this->getServer()->getCommandMap()->register("fly", new Commands\fly($this));
         $this->getServer()->getCommandMap()->register("factory", new Commands\factory($this));
	 }
   /* 
    * @Check function
    */
    public function canBreak($player, $block){
         if($player->isOp()){
	       return true;
  	     }
	     if($player->hasPermission("break.".$block->getId().".".$block->getDamage())){
		    return true;
	     	}
	    elseif(in_array($block->getId().":".$block->getDamage(), $this->cfg["breakable.blocks"])){
		    return true;
		   }else{
		     return false;
			}
		 }
	  /* 
    * @Check function
    */
    public function canPlace($player, $block){
         if($player->isOp()){
	        return true;
	      }
	      if($player->hasPermission("place.".$block->getId().".".$block->getDamage())){
		    return true;
		  }
	     elseif(in_array($block->getId().":".$block->getDamage(), $this->cfg["placeable.blocks"])){
		    return true;
		   }else{
		     return false;
			}
		 }
	  /*
	   * @ setFormat
	   */
	  public function setformat($player, String $format){
		 $target = strtolower($player->getName());
	    $this->ranks->set($target, array("format" => $format));
	    $this->ranks->save();
		}
	  public function onBreak(BlockBreakEvent $event){
		  if($event->getPlayer()->getLevel() == $this->getServer()->getLevelByName($this->cfg["world"])){
	       $player = $event->getPlayer();
	       $block = $event->getBlock();
		    if(!$this->canBreak($player, $block)){
			  $event->setCancelled();
			  $player->sendPopup($this->prefix." ".$this->cfg["cannot.break.block"]);
			}
		}
		}
		public function onPlace(BlockPlaceEvent $event){
		   if($event->getPlayer()->getLevel() == $this->getServer()->getLevelByName($this->cfg["world"])){
	         $player = $event->getPlayer();
	         $block = $event->getBlock();
		      if(!$this->canPlace($player, $block)){
		    	  $event->setCancelled();
			     $player->sendPopup($this->prefix." ".$this->cfg["cannot.place.block"]);
			}
		 }
		}
		public function onInteract(PlayerInteractEvent $event){
		   $player = $event->getPlayer();
		   $item = $event->getItem();
		   $id = $item->getId();
		   $meta = $item->getDamage();
		   if($id == 339 && $meta >= 1){
			  $event->setCancelled();
			  $grant = $meta;
			  EconomyAPI::getInstance()->addMoney($player, $meta);
			  $player->sendMessage($this->prefix." §7You have claimed the cheque!");
			  $player->sendMessage($this->prefix." §a+ ".$meta."$");
			  $player->getInventory()->removeItem(Item::get(339, $meta, 1));
			  }
			}
		public function onFight(EntityDamageEvent $event){
			$entity = $event->getEntity();
			if($entity instanceof Player && $entity->isSurvival() && $entity->isFlying() && !$event->isCancelled()){
	             //unset($this->flying[strtolower($entity->getName())]);
	             BasicLib::disableFlight($entity);
	           }
			if($event->getEntity()->getLevel() == $this->getServer()->getLevelByName($this->cfg["world"])){
			  if($event instanceof EntityDamageByEntityEvent){
              $vector = new Vector3($entity->getLevel()->getSpawnLocation()->getX(),$entity->getPosition()->getY(),$entity->getLevel()->getSpawnLocation()->getZ());
              $radius = $this->cfg["radius"];
              $cause = $entity->getLastDamageCause();
              $damager = $event->getDamager();
              //if($cause instanceof EntityDamageByEntityEvent){
	           //$damager = $cause->getDamager();
              if($entity->getPosition()->distance($vector) <= $radius && $this->cfg["enable-pvp"] == "false"){
                $event->setCancelled();
                if($damager instanceof Player){
                  $damager->sendPopup($this->prefix." ".$this->cfg["cannot.pvp"]);
                 }
              }
            }
          }
        }
         public function onDamage(EntityDamageEvent $event){
			$entity = $event->getEntity();
			if($event->getEntity()->getLevel() == $this->getServer()->getLevelByName($this->cfg["world"])){
              $vector = new Vector3($entity->getLevel()->getSpawnLocation()->getX(),$entity->getPosition()->getY(),$entity->getLevel()->getSpawnLocation()->getZ());
              $radius = $this->cfg["radius"];
              if($entity->getPosition()->distance($vector) <= $radius ){
                $event->setCancelled();
         }
      }
   }
}