<?php

/*

*/

namespace Chat;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerChatEvent;

class Main extends PluginBase implements Listener {

public $spam = array(); 
	
	public function onEnable() {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

public function checkAds($msg){ 
$banWords = [".ru", ".com", ".cc", ".pro", ".su",".RU","185"]; 
$msg = implode("", explode(" ", $msg)); 
$msg = strtolower($msg);
$s = false;  
foreach($banWords as $word){ 
if($s == false){ 
$state = count(explode($word, $msg)); 
if($state != 1){ 
$s = true; 
} 
} 
} 
return $s; 
} 

public function checkMat($msg){ 
$banWords = ["idiot", "pula", "mcx", "pizda", "mati", "ma-ti", "naiba", "fututi", "morti", "ddns.net", "playmc.net", "dick","such"]; 
$msg = implode("", explode(" ", $msg)); 
$msg = strtolower($msg);
$s = false;  
foreach($banWords as $word){ 
if($s == false){ 
$state = count(explode($word, $msg)); 
if($state != 1){ 
$s = true; 
} 
} 
} 
return $s; 
} 

 public function onChat(PlayerChatEvent $e){ 
$p = $e->getPlayer(); 
$n = $p->getName(); 
$count = count(explode(" ", $e->getMessage())); 
$count2 = count(explode("_", $e->getMessage())); 
if($count < 6 || $count2 < 6){ 
if($this->checkAds($e->getMessage()) == false){
if($this->checkMat($e->getMessage()) == false){ 
$this->spam[strtolower($n)] = 1; 
}else{ 
$p->sendMessage("§7(§cМат§7) §aМат здесь не приветствуется.\n§7(§cМат§7) §eНе §fматеритесь§e пожалуйста!"); 
$e->setCancelled(); 
} 
}else{
$p->sendMessage("§7(§cРеклама§7) §aПиар здесь не §fприветствуется.\n§7(§cРеклама§7) §eНе §fпиарьте §eпожалуйста!"); 
$e->setCancelled(); 
} 
}else{ 
$p->sendMessage("§6-------------\nMesajul conține multe cuvinte.\n§cNu utilizați mai mult de 5 cuvinte.\n§6-------------"); 
$e->setCancelled(); 
} 
} 

}