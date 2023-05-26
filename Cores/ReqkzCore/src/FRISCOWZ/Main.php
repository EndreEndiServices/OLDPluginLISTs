<?php
/**
 * This is property of FRISCOWZ.
 *
 * Copyright (C) 2016 FRISCOWZ
 *
 * This is private software, you cannot redistribute it and/or modify any way
 * unless otherwise given permission to do so. If you have not been given explicit
 * permission to view or modify this software you should take the appropriate actions
 * to remove this software from your device immediately.
 *
 * @author FRISCOWZ
 * @twitter @FRISCOWZMCPE
 *
 */

namespace FRISCOWZ;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\entity\Effect;
use pocketmine\block\IronOre;
use pocketmine\block\GoldOre;
use pocketmine\block\Sand;
use pocketmine\block\Gravel;
use pocketmine\item\Item;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\math\Vector3;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\level\Position;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerItemConsumeEvent as pic;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\command\Command;
use pocketmine\level\particle\CriticalParticle;
use pocketmine\level\Level;
use pocketmine\scheduler\CallbackTask;
use pocketmine\scheduler\PluginTask;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;


class Main extends PluginBase implements Listener {
public $pvp = 0;
    public $prefix = TextFormat::DARK_GRAY . "-=]" . TextFormat::RED . "§r§cUHC§fReqKz" . TextFormat::DARK_GRAY . "[=-§r " . TextFormat::GRAY;
    public $globalmute = false;
    public $spam = [];
    public $kills = array();
    public static $nametag = [];
   public function onEnable() {
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    $this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "Coord")), 10);
    $this->pvp = 0;
	@mkdir($this->getDataFolder());
		$this->saveDefaultConfig();
		$this->scenarios = new Config($this->getDataFolder()."/scenarios.yml", Config::YAML, array(
			"cutclean" => true,
			"no-fall" => false,
			"blood-diamond" => false,
			"double-ores" => false,
       "cats-eyes" => true,
			"death-pole" => true,
			"TimeBomb" => false,
			"FireRes" => false,
			"golden-head" => false,
			"AppleRate" => true,
			"win10" => false,
			"LightingStrike" => false,
			"KillHeal" => false,
			"KnockBack" => 3.5
			 ));
   }
	public function Coord(){
		$aop = 0;
		$spec = 0;
 		$allplayers = $this->getServer()->getOnlinePlayers();
	    foreach($allplayers as $p){if($p->isSurvival()){$aop=$aop+1;}else{$spec=$spec+1;                                                                      ;}}
	    foreach($this->getServer()->getOnlinePlayers() as $player){
	    	if(!isset($this->kills[$player->getName()])){
	    		$this->kills[$player->getName()] = 0;
	    	} 
	        $x = $player->getFloorX();
	        $y = $player->getFloorY();
          $z = $player->getFloorZ();
          $kill = $this->kills[$player->getName()];
 	        $player->sendTip("                                                                       " . $this->prefix . PHP_EOL . TextFormat::DARK_GRAY. "                                                                       X" . TextFormat::DARK_GRAY . ": " . TextFormat::RED . "$x " . TextFormat::DARK_GRAY . "Y" . TextFormat::DARK_GRAY . ": " . TextFormat::RED . "$y " . TextFormat::DARK_GRAY . "Z" . TextFormat::DARK_GRAY . ": " . TextFormat::RED . "$z" . PHP_EOL . TextFormat::DARK_GRAY . "                                                                       Players Alive :" . TextFormat::RED . " $aop " . PHP_EOL . TextFormat::DARK_GRAY . "                                                                       Kills : " . TextFormat::RED . $kill .PHP_EOL . " " . PHP_EOL . " " . PHP_EOL . " " . PHP_EOL . " " . PHP_EOL . " " . PHP_EOL . " " . PHP_EOL);
        }
    } 
  public function Helmet(){
	  if(mt_rand(1, 2) == 1){
	  $protection = Enchantment::getEnchantment(0);
      $protection->setLevel(1);
	  $helmet = Item::get(Item::DIAMOND_HELMET, 0, 1);
      $helmet->addEnchantment($protection);
	  return $helmet;
	  } else {
	  $protection = Enchantment::getEnchantment(0);
      $protection->setLevel(2);
	  $helmet = Item::get(Item::IRON_HELMET, 0, 1);
      $helmet->addEnchantment($protection);
	  return $helmet;
	  }
  }
  public function Chestplate(){
	  if(mt_rand(1, 2) == 1){
	  $protection = Enchantment::getEnchantment(0);
      $protection->setLevel(1);
	  $helmet = Item::get(Item::DIAMOND_CHESTPLATE, 0, 1);
      $helmet->addEnchantment($protection);
	  return $helmet;
	  } else {
	  $protection = Enchantment::getEnchantment(0);
      $protection->setLevel(mt_rand(1, 2));
	  $helmet = Item::get(Item::IRON_CHESTPLATE, 0, 1);
      $helmet->addEnchantment($protection);
	  return $helmet;
	  }
  }
  public function Leggins(){
	  if(mt_rand(1, 2) == 1){
	  $protection = Enchantment::getEnchantment(0);
      $protection->setLevel(1);
	  $helmet = Item::get(Item::DIAMOND_LEGGINGS, 0, 1);
      $helmet->addEnchantment($protection);
	  return $helmet;
	  } else {
	  $protection = Enchantment::getEnchantment(0);
      $protection->setLevel(mt_rand(1, 2));
	  $helmet = Item::get(Item::IRON_LEGGINGS, 0, 1);
      $helmet->addEnchantment($protection);
	  return $helmet;
	  }
  }
  public function Sword(){

	  $sharpness = Enchantment::getEnchantment(9);
      $sharpness->setLevel(1);
	  $sword = Item::get(Item::DIAMOND_SWORD, 0, 1);
      $sword->addEnchantment($sharpness);
	  return $sword;
  }
  public function Boots(){
	  if(mt_rand(1, 2) == 1){
	  $protection = Enchantment::getEnchantment(0);
      $protection->setLevel(1);
	  $helmet = Item::get(Item::DIAMOND_BOOTS, 0, 1);
      $helmet->addEnchantment($protection);
	  return $helmet;
	  } else {
	  $protection = Enchantment::getEnchantment(0);
    $protection->setLevel(2);
	  $helmet = Item::get(Item::IRON_BOOTS, 0, 1);
      $helmet->addEnchantment($protection);
	  return $helmet;
	  }
  }
  public function givekit(Player $p){
	    $p->getInventory()->clearAll(); 
        $p->getInventory()->addItem($this->Sword());
        $p->getInventory()->addItem(Item::get(364, 0, 64));
        $p->getInventory()->addItem(Item::get(ITEM::BOW, 0, 1));
		$p->getInventory()->addItem(Item::get(ITEM::GOLDEN_APPLE, 0, 9));
		$p->getInventory()->addItem(Item::get(325, 8, 1));
		$p->getInventory()->addItem(Item::get(325, 10, 1));
		$p->getInventory()->addItem(Item::get(4, 0, 64));
		$p->getInventory()->addItem(Item::get(384, 0, 32));
		$p->getInventory()->addItem(Item::get(145, 0, 1));
		$p->getInventory()->addItem(Item::get(279, 0, 1));
		$p->getInventory()->addItem(Item::get(ITEM::ARROW, 0, 40));
        $p->getInventory()->setHelmet($this->Helmet());
        $p->getInventory()->setChestplate($this->Chestplate());
        $p->getInventory()->setLeggings($this->Leggins());
        $p->getInventory()->setBoots($this->Boots());
        $p->getInventory()->sendArmorContents($p);
  }
  public function getDrop($d){
	  switch($d){
		  case 15:
		  if($this->scenarios->get("cutclean") === true) return 1;
		  if($this->scenarios->get("double-ores") === true) return 2;
		  break;
		  case 14:
		  if($this->scenarios->get("cutclean") === true) return 1;
		  if($this->scenarios->get("double-ores") === true) return 2;
		  break;
		  case 56:
		  if($this->scenarios->get("cutclean") === true) return 1;
		  if($this->scenarios->get("double-ores") === true) return 2;
		  break;
	  }
	  
  }
  public function onBreak(BlockBreakEvent $event) {
	  if($this->scenarios->get("cutclean") === false) return false;
    if($event->getBlock()->getId() == 15) {
      $drops = array(Item::get(265, 0, $this->getDrop(15)));
      $event->setDrops($drops);
    }
    if($event->getBlock()->getId() == 17) {
      $drops = array(Item::get(5, 0, mt_rand(2, 5)));
      $event->setDrops($drops);
    }
    if($event->getBlock()->getId() == 14) {
      $drops = array(Item::get(266, 0, $this->getDrop(14)));
      $event->setDrops($drops);
    }
	if($this->scenarios->get("AppleRate") === true){
        if($event->getBlock()->getId() == 18) {
            $drops = array(Item::get(260, 0, mt_rand(0, 1)));
            $event->setDrops($drops);
        }
	}
    if($event->getBlock()->getId() == 56) {
      $drops = array(Item::get(264, 0, $this->getDrop(56)));
      $event->setDrops($drops);
    }
  }
  /*public function AntiWin10(PlayerPreLoginEvent $event){
	if($event->getPlayer()->getDeviceOS() == 7) {
	    if($this->scenarios->get("win10") === false) $event->getPlayer->close(" ", $this->prefix . "Win10 isn't allowed");
    } else $this->getServer()->broadcastMessage($event->getPlayer()->getName() . "isn't Win10 !");
  }*/
  
public function onJoin(PlayerJoinEvent $event){
$player = $event->getPlayer();
$name = $player->getName();
$event->setJoinMessage(null);
$this->getServer()->broadcastPopup($this->prefix . "".$event->getPlayer()->getName()."§6 joined.");
}
public function onQuit(PlayerQuitEvent $event){
$player = $event->getPlayer();
$name = $player->getName();
$this->getServer()->broadcastPopup($this->prefix . "".$event->getPlayer()->getName()."§6 Leave.");
$event->setQuitMessage(null);
}
public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {

	$cmd = strtolower($cmd->getName());
$players = $sender->getName();
	switch($cmd){
case 'uhc':
if ($sender->isOp()){
switch($args[0]){

case "reset":
foreach($this->getServer()->getOnlinePlayers() as $p){
$p->setMaxHealth(20);
$p->setHealth(20);
$p->setFood(20);
$p->setGamemode(0);
$p->getInventory()->clearAll();
$p->removeAllEffects();
}
$this->getServer()->broadcastMessage($this->prefix . " §fReset player succesfuly!");
return true;
break;

case "help":
$sender->sendMessage("§l§8[§3+§8]§7=======§l§8[§r§c UHC§fReqKz §8§l]§7=======§8[§3+§8]");
$sender->sendMessage("§8| §c/uhc reset :§f Reset the UHC Player");
$sender->sendMessage("§8| §c/uhc meetup :§f Give player Build UHC kit");
$sender->sendMessage("§8| §c/uhc start :§f Start the Game");
$sender->sendMessage("§8| §c/uhc tpall :§f Tpall player to you for the DeathMatch!");
$sender->sendMessage("§8| §c/uhc food :§f give 64 steak for all player!");
$sender->sendMessage("§8| §c/uhc pvp <on/off> :§f enable / disable pvp !");
$sender->sendMessage("§8| §c/uhc wlall :§f Add all players to whitelst!");
$sender->sendMessage("§8| §c/uhc globalmute :§f Mute All player!");

return true;
break;
case "pvp":
if($args[1] == "on"){
 $this->pvp = 1;
 $this->getServer()->broadcastMessage($this->prefix."§fPVP is now active!");  
 }
 If($args[1] == "off"){
$this->pvp = 0;
 $this->getServer()->broadcastMessage($this->prefix."§fPVP is now off!");
}
return true;
break;

case "scenario":
//soon
return true;
break;
 case "meetup":
foreach($this->getServer()->getOnlinePlayers() as $p){
	$this->givekit($p);
}	            
$this->getServer()->broadcastMessage($this->prefix . "§fMeetup started!");
return true;
break;


case "start":
foreach($this->getServer()->getOnlinePlayers() as $p){
$p->getInventory()->clearAll();

$p->getInventory()->addItem(Item::get(364, 0, 64));
$p->getInventory()->addItem(Item::get(340, 0, 10));
if($this->scenarios->get("cats-eyes") === true){
    $nv = Effect::getEffect(16);
    $nv->setAmplifier(1);
    $nv->setDuration(9999999);
    $p->addEffect($nv);
}
	    $this->getServer()->addWhitelist($p->getName());
}	            
$this->getServer()->setConfigBool("white-list", true);

$this->getServer()->broadcastMessage($this->prefix . "§fUHC started !");
return true;
break;
case "wlall":
foreach($this->getServer()->getOnlinePlayers() as $p){
	$this->getServer()->addWhitelist($p->getName());
	}
	$sender->sendMessage($this->prefix."§fAll players have been added to the whitelist!");
	return true;
	break;
case "food":
foreach($this->getServer()->getOnlinePlayers() as $p){
$p->getInventory()->addItem(Item::get(364, 0, 64));

}
$this->getServer()->broadcastMessage($this->prefix."§f+64 steak!");
return true;
break;
///GlobalMute///

            case "globalmute":
                if ($sender->hasPermission("UHC.host")) {
                    if ($this->globalmute === false) {
                        $this->getServer()->broadcastMessage($this->prefix . TextFormat::GRAY . "GlobalMute has been enabled!");
                        $this->globalmute = true;
                        return true;
                    } else {
                        $this->getServer()->broadcastMessage($this->prefix . TextFormat::GRAY . "GlobalMute has been disabled!");
                        $this->globalmute = false;
                        return true;
                    }
                }


case "tpall":
foreach($this->getServer()->getOnlinePlayers() as $p){
$p->teleport($sender);
}
$this->getServer()->broadcastPopup($this->prefix."§fTpall");
return true;
break;

}
}else{
	$sender->sendMessage($this->prefix."§cYou are not allowed to do this.");
}
return true;
break;

}
}
public function onDamage(EntityDamageEvent $event){
		   //PROJECTILE
    if($event->getCause() === EntityDamageEvent::CAUSE_ENTITY_ATTACK || $event->getCause() === EntityDamageEvent::CAUSE_PROJECTILE){
     if($this->pvp == 0) $event->setCancelled();
	}
	if($event->getCause() === EntityDamageEvent::CAUSE_FALL){ 
	 if ($this->scenarios->get("no-fall") === true) $event->setCancelled();
	}
	if($event->getCause() === EntityDamageEvent::CAUSE_LAVA || $event->getCause() === EntityDamageEvent::CAUSE_FIRE || $event->getCause() === EntityDamageEvent::CAUSE_FIRE_TICK){ 
	 if ($this->scenarios->get("FireRes") === true) $event->setCancelled();
	}
 }
   public function onPlayerDeath(PlayerDeathEvent $event)
    {
        $player = $event->getPlayer();
        $name = $player->getName();
      
        if ($player instanceof Player) {
            $cause = $player->getLastDamageCause();
            if ($cause instanceof EntityDamageByEntityEvent) {
                $killer = $cause->getDamager();
				if($this->scenarios->get("KillHeal") === true) {
                $killer->setHealth($killer->getHealth() + 10);
                $killer->sendMessage($this->prefix."KillHeal §b+10 §3<3");
				}
                $event->setDeathMessage($this->prefix . TextFormat::WHITE . $player->getName() . " §cwas killed by§f " . $killer->getName() . ".");
                if(isset($this->kills[$killer->getName()])){
                ++$this->kills[$killer->getName()];
                } else {
                	$this->kills[$killer->getName()] = 1;
                }
            } else {
                $cause = $player->getLastDamageCause()->getCause();
                if($cause === EntityDamageEvent::CAUSE_SUFFOCATION)
                {
                    $event->setDeathMessage($this->prefix . TextFormat::WHITE . $player->getName() . " §csuffocated.");
                } elseif ($cause === EntityDamageEvent::CAUSE_DROWNING)
                {
                    $event->setDeathMessage($this->prefix . TextFormat::WHITE . $player->getName() . " §cdrowned.");
                } elseif ($cause === EntityDamageEvent::CAUSE_FALL)
                {
                    $event->setDeathMessage($this->prefix . TextFormat::WHITE . $player->getName() . " §cfell to hard.");
                } elseif ($cause === EntityDamageEvent::CAUSE_FIRE)
                {
                    $event->setDeathMessage($this->prefix . TextFormat::WHITE . $player->getName() . " §cburned.");
                } elseif ($cause === EntityDamageEvent::CAUSE_FIRE_TICK)
                {
                    $event->setDeathMessage($this->prefix . TextFormat::WHITE . $player->getName() . " §cburned.");
                } elseif ($cause === EntityDamageEvent::CAUSE_LAVA)
                {
                    $event->setDeathMessage($this->prefix . TextFormat::WHITE . $player->getName() . " §ctried to swim in lava.");
                } elseif ($cause === EntityDamageEvent::CAUSE_BLOCK_EXPLOSION)
                {
                    $event->setDeathMessage($this->prefix . TextFormat::WHITE . $player->getName() . " §cexplode.");
                } else {
                    $event->setDeathMessage($this->prefix . TextFormat::WHITE . $player->getName() . " §cdied.");
                }
            }
        }
		$player->setGamemode(2);
		$player->sendMessage($this->prefix . "§cYou died! §fYou can spec but you cannot chat");
        $this->getServer()->removeWhitelist(strtolower($name));
        $this->getServer()->removeWhitelist($name);
    }
////Mute////
    public function onChat(PlayerChatEvent $event)
    {
        $player = $event->getPlayer();
		if ($player->getGamemode() === 2) {
			$player->sendMessage($this->prefix."§cYou can't chat after die!");
			$event->setCancelled(true);
			return;
		}
        if ($this->globalmute === true) {
            if (!$event->getPlayer()->hasPermission("UHC.host")) {
                $event->setCancelled();
                $player->sendMessage($this->prefix . "§cYou can't chat in Global Mute!");
            }
        } else {
            if(!$player->hasPermission("UHC.host") || !$player->isOp())
            {
                if(!isset($this->spam[$player->getName()]))
                {
                    $lastTime = 0;
                } else {
                    $lastTime = $this->spam[$player->getName()];
                }
                if(time() - $lastTime > 3)
                {
                    $this->spam[$player->getName()] = time();
                } else {
                    $event->setCancelled(true);
                    $player->sendMessage($this->prefix . TextFormat::RED . "§fDon't spam please! Wait 5 seconds until you can send a message again!");
                }
            }
        }
    }
    protected static function randy($p,$r,$o) {
      return $p+(mt_rand()/mt_getrandmax())*$r-$o;
    }
    protected static function randVector(Vector3 $center) {
      return new Vector3(self::randy($center->getX(),0.5,-0.25),
                 self::randy($center->getY(),2,0),
                 self::randy($center->getZ(),0.5,-0.25));
    }   

    public function onHit(EntityDamageEvent $ev){

      if($ev instanceof EntityDamageByEntityEvent && $ev->getEntity() instanceof Player && $ev->getDamager() instanceof Player){

        $p = $ev->getEntity();
        $damager = $ev->getDamager();
		
        $p->getLevel()->addParticle(new CriticalParticle(self::randVector($p),(mt_rand()/mt_getrandmax())*2));
        $p->getLevel()->addParticle(new CriticalParticle(self::randVector($p),(mt_rand()/mt_getrandmax())*2));
        $p->getLevel()->addParticle(new CriticalParticle(self::randVector($p),(mt_rand()/mt_getrandmax())*2));
        $p->getLevel()->addParticle(new CriticalParticle(self::randVector($p),(mt_rand()/mt_getrandmax())*2));

        //$ev->setKnockBack(3.777);

         if($damager->speed->y < 0){
            $p->getLevel()->addParticle(new CriticalParticle(self::randVector($p),(mt_rand()/mt_getrandmax())*2));
            $p->getLevel()->addParticle(new CriticalParticle(self::randVector($p),(mt_rand()/mt_getrandmax())*2));
            $p->getLevel()->addParticle(new CriticalParticle(self::randVector($p),(mt_rand()/mt_getrandmax())*2));
            $p->getLevel()->addParticle(new CriticalParticle(self::randVector($p),(mt_rand()/mt_getrandmax())*2));
            $p->getLevel()->addParticle(new CriticalParticle(self::randVector($p),(mt_rand()/mt_getrandmax())*2));
            $p->getLevel()->addParticle(new CriticalParticle(self::randVector($p),(mt_rand()/mt_getrandmax())*2));

          }
      }
}
		
    public static function alignStringCenter(string $string, string $string2) : string {
		$length = strlen($string);
		$half = $length / 4;
		$string = $string.PHP_EOL.str_repeat(' ', $half).$string2;
		return $string;
	}
}