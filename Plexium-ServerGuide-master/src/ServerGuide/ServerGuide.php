<?php
namespace ServerGuide;

use pocketmine\Player;

use pocketmine\item\Item;

use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerDropItemEvent;

class ServerGuide extends PluginBase implements Listener{
	
	private static $instance = null; # For future
	
	# StartUp
	
	public function onLoad(){
		 self::$instance = $this;
		 $this->getLogger()->info("§7Checking data...");
	    $this->saveDefaultConfig();
	    if($this->getConfig()->get("version", 0) !== $this->getDescription()->getVersion()){
		   rename($this->getDataFolder()."config.yml", $this->getDataFolder()."config.old.yml");
		   $this->getLogger()->info("§cUpdating config...");
		   $this->saveResource("config.yml", true);
		   $this->getConfig()->reload();
		}
	}
	
	# Register/Schedule needs

	public function onEnable(){
		 $this->getLogger()->info("§bServerGuide has been enabled!".PHP_EOL.
		                                             "Author: StrafelessPvP (StrafelessPvP)".PHP_EOL.
		                                             "Github: Github.com/StrafelessPvP/ServerGuide");
	    $this->getServer()->getPluginManager()->registerEvents($this, $this);
	    $this->getServer()->getScheduler()->scheduleRepeatingTask(new CheckTask($this), 20);
	}
	
	/* @function getHelpItem
	 * @return Item
	 */
	
	public function getHelpItem(){
	    $steam = $this->getConfig()->get("item");
       $i = explode(":", $steam);
       $item = Item::get($i[0], $i[1], 1);
       $item->setCustomName(str_replace("\n", PHP_EOL, $i[2]));
   return $item;
	}
	
	/*
	 * @function getTranslatedLines
	 * @return array
	 */
	
	public function getTranslatedLines(Player $player){
	    $x = $player->x;
		 $y = $player->y;
	    $z = $player->z;
		 $level = $player->getLevel()->getName();
		 $name = $player->getName();
		 $status = count($this->getServer()->getOnlinePlayers())."/".$this->getServer()->getMaxPlayers();
		 $return = [];
		 foreach($this->getConfig()->get("help.list", []) as $line){
	      $return[] = str_replace(["{x}", "{y}", "{z}", "{level}", "{player}", "{online count}"], [$x, $y, $z, $level, $name, $status], $line);
	   }
	return $return;
	}
	
	/*
	 * @handle PlayerInteractEvent
	 */
	
	public function onInteract(PlayerInteractEvent $event){
	    $player = $event->getPlayer();
	    $item = $event->getItem();
	    $key = $this->getHelpItem();
	    if($item->getId() == $key->getId() && $item->getDamage() == $key->getDamage()){
		   $player->sendMessage($this->getConfig()->get("header")."§r");
		   foreach($this->getTranslatedLines($player) as $line){
		     $player->sendMessage($line."§r");
		   }
		}
	}
	
	/*
	 * @handle PlayerDropItemEvent
	 */
	
	public function onDrop(PlayerDropItemEvent $event){
	    $player = $event->getPlayer();
	    $item = $event->getItem();
	    $key = $this->getHelpItem();
	    if($item->getId() == $key->getId() && $item->getDamage() == $key->getDamage()){
		   $player->sendMessage("§cYou can't do that!");
		   $event->setCancelled();
		}
	}
}
