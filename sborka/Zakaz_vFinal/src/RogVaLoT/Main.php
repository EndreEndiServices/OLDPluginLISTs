<?php

namespace RogVaLoT;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\utils\Config;

class Main extends PluginBase implements CommandExecutor, Listener {
public $kill = array();

    public function onEnable() {
        $this->enabled = true;
        $this->getLogger()->info("Плагин включен");
                 $this->getServer()->getPluginManager()->registerEvents($this, $this);
                   if (!file_exists($this->getDataFolder() . "config.yml")) { 
 @mkdir($this->getDataFolder()); 
 file_put_contents($this->getDataFolder() . "config.yml", $this->getResource("config.yml")); 
 } 
   }
    public function onDisable() {
        $this->enabled = false;
        $this->getLogger()->info("Плагин выключен");
        }
        public function onCommand(CommandSender $sender, Command $command, $label, array $args){ 
 switch($command->getName()){ 
 case "zakaz";
  $this->Economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
 if(isset($args[0]) && isset($args[1])){
 if(!$this->getServer()->getPlayer("$args[0]") == null){
   if($args[1] > 0){
   if($this->Economy->myMoney($sender) >= $args[1]){
     $p = strtolower($this->getServer()->getPlayer("$args[0]"));
    		
    		    		foreach($this->getServer()->getOnlinePlayers() as $player) {
    		    	     $p = $this->getServer()->getPlayer($args[0]);	
$username = strtolower($player->getPlayer()->getName());
  	$brek = $p->getID();
    		$pos = strtolower($args[0]);
    		if(! (in_array($brek, $this->kill)))
    		{
  $this->Economy->setMoney($sender, $this->Economy->myMoney($sender) -$args[1]);
  $names = $sender->getName();
  $playr = strtolower($args[0]);
    			$this->kill[$pos] = $brek;
    	    @mkdir($this->getDataFolder(). "/players");
    file_put_contents($this->getDataFolder(). 'players/'. $playr .'.yml', $names);
        file_put_contents($this->getDataFolder(). 'players/'. $playr .'money.yml', $args[1]);
    $sender->sendMessage($this->getConfig()->get("zakazok"));
    			}else{
    			$sender->sendMessage($this->getConfig()->get("errorset"));
    }
    $namep = strtolower($args[0]);
    if($username != "$namep") {
    $p = $player->getPlayer();
    $main = $this->getConfig()->get("main");
$p->sendMessage(str_replace("[name]", $args[0], str_replace("[money]", $args[1], $main)));
    }
   }
  }else{
    $sender->sendMessage($this->getConfig()->get("errormoney"));
  }
  }else{
   $sender->sendMessage($this->getConfig()->get("errornumber"));
  }
  }else{
  $sender->sendMessage(str_replace("[player]", $args[0], $this->getConfig()->get("notonline")));
 }
 }else{
  $sender->sendMessage($this->getConfig()->get("usage"));
  }
break;
  } 
 }
 public function death(PlayerDeathEvent $e){
   $this->Economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
 $player = $e->getEntity();
 if ($e->getEntity()->getLastDamageCause() instanceof EntityDamageByEntityEvent){
 $killer = $e->getEntity()->getLastDamageCause()->getDamager();
 if($killer instanceof Player){
 $name = strtolower($player->getName());
 		$log = $player->getID();
		if(in_array($log, $this->kill))
		{
		$money = file_get_contents($this->getDataFolder(). "players/" .$name .".yml");
	$strochka = file_get_contents($this->getDataFolder(). "players/" .$name ."money.yml");
 $killer->sendMessage($this->getConfig()->get("killer"));
 $this->Economy->addMoney($killer, $strochka);
 	$fr = array_search($log, $this->kill);
    			unset($this->kill[$fr]);
 if(file_exists($this->getDataFolder(). "players/" .$name."money.yml") && file_exists($this->getDataFolder(). "players/" .$name.".yml")){
 unlink($this->getDataFolder(). "players/" .$name."money.yml");
  unlink($this->getDataFolder(). "players/" .$name.".yml");
     }
    }
   }
  }
 }
 public function xz(PlayerJoinEvent $e){
    $name = strtolower($e->getPlayer()->getName());
   if (file_exists($this->getDataFolder() . 'players/' .$name. '.yml') && file_exists($this->getDataFolder() . 'players/' .$name. 'money.yml')) { 
 		$brek = $e->getPlayer()->getID();
 		$money = file_get_contents($this->getDataFolder(). 'players/' .$name .'.yml');
	$strochka = file_get_contents($this->getDataFolder(). 'players/' .$name .'money.yml');
		$player = $e->getPlayer();
		$pos = strtolower($player->getName());
			$this->kill[$pos] = $brek;
			foreach($this->getServer()->getOnlinePlayers() as $playe) {
			$username = strtolower($playe->getPlayer()->getName());
			    $namep = $player->getName();
    if($username != "$name") {
    $p = $playe->getPlayer();
				$p->sendMessage(str_replace("[name]", $namep, $this->getConfig()->get("playerback")));
    }
   }
  }
 }
}