<?php
namespace ffa;

use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\command\Command;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\command\CommandSender;
use ffa\StateFFA;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\utils\TextFormat as color;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\event\entity\EntityLevelChangeEvent; 

class Main extends PluginBase implements Listener{
// public vars 
    public $players;
    public $s;
    public $state;
    public $timer;
    public $def;
    public $config;
// constants
    const MAX = 30; // default: 6
    const MAXPOINTS = 30; // default: 30
    const MINPLAYERS = 2; // default: 6
    const REWARD = 1000; // default:500

    public function onEnable() {
        @mkdir($this->getDataFolder());
        $this->config = new Config($this->getDataFolder()."config.yml", Config::YAML, array());
        $this->players = array();
        $this->def = array("x" => 0, "1y" => 0, "z" => 0, "level" => "world");
        $this->timer = new TimerFFA($this);
        $this->getLogger()->info(color::GREEN."timer starting....");
        $this->getServer()->getScheduler()->scheduleDelayedRepeatingTask($this->timer, 100, 17);
        StateFFA::SetState(StateFFA::WAITING);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        
        $this->s = array();
                
    } 
    public function onDisable() {

    }
    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        switch ($command){
            case 'ffa':
                if(count($args) == 1){
                    if($args[0] == "join"){
                        if($sender instanceof Player){
                        $this->TryJoin($sender);
                        return;
                    }}
                if($args[0] == "leave"){
                    if($sender instanceof Player){
                        $this->TryLeave($sender);
                        return;
                    }
                }
                if($args[0] == "setpos" && $sender->isOp() == TRUE){
                    $num = count($this->config->getAll()) + 1;
                    $pos = $this->def;
                    $pos["x"] = $sender->getX();
                    $pos["1y"] = $sender->getY();
                    $pos["z"] = $sender->getZ();
                    $pos["level"] = $sender->getLevel()->getName();
                    $this->config->set($num, $pos);
                    $this->config->save();
                    $sender->sendMessage(color::GREEN.$num." has been set!");
                    return;
                    }
                    
                }
                $sender->sendMessage(color::RED."error in your command!");
                break;
        }
    }
    public function IsEnough(){
        if(count($this->players) == Main::MINPLAYERS && StateFFA::GetState() == StateFFA::WAITING){
            StateFFA::SetState(StateFFA::LOADING);
            $this->getLogger()->info(color::GREEN."game starting!");
        }
    }
    public function TryJoin(Player $player){
        if(count($this->players) < Main::MAX){
         if(StateFFA::IsState(StateFFA::WAITING)){
             $this->players[$player->getName()] = $player;
             $player->sendMessage(color::GREEN."§8> §5You joined a game!");
             $this->IsEnough();
         } 
         if(StateFFA::IsState(StateFFA::INGAME)){
             $this->players[$player->getName()] = $player;
             $player->sendMessage(color::GREEN."§8> §5You joined a game!");
             $this->timer->points[$player->getName()] = 0;
             $this->s[$player->getName()] = TRUE;
             $this->timer->GivePlayerItems($player);
             $this->timer->TpPlayerToLoc($player);
         }
        }  else {
            $player->sendMessage(color::RED."§8> §cGame full!");
            return;
        }
    }
    public function TryLeave(Player $player){
        foreach ($this->players as $p){
            if($p instanceof Player){
                if($p->getName() == $player->getName()){
            $p = $this->players;
            unset($p[$player->getName()]);
            $this->players = $p;
            $player->sendMessage(color::GREEN."§8> §5You left the FFA");
            $player->setSpawn($this->getServer()->getDefaultLevel()->getSafeSpawn());
            $player->teleport($this->getServer()->getDefaultLevel()->getSpawn());
            $player->getInventory()->clearAll();
            $this->s[$player->getName()] = FALSE;
                   $this->IsLess();
               
                }
            }
        }
    }
    public function EDEe(EntityDamageEvent $event){
            if($event instanceof EntityDamageByEntityEvent){
                $vic = $event->getEntity();
              
                    $damager = $event->getDamager();
                    
                    if($damager instanceof Player){
                    //just cuz
                        if($this->s[$damager->getName()] == TRUE){
                            if($vic->isAlive() == FALSE){
                            $v = $this->timer->points[$damager->getName()];
                            $v++;
                            $this->timer->points[$damager->getName()] = $v;
                            $damager->sendMessage(color::BLUE."+1 point!");
                            return;
                        }}
//                        foreach ($this->timer->points as $key => $val){
//                            if($key == $damager->getName() && StateFFA::GetState() == StateFFA::INGAME){
//                                $va = $val + 1;
//                                $t = $this->timer->points;
//                                $t[$key] = $va;
//                                $this->timer->points = $t;
//                                $damager->sendMessage(color::BLUE."+1 point!");
//                                return;
//                            }
//                        }
                    
                }
            }
     
    }

    public function PlayerQE(PlayerQuitEvent $event){
        $player = $event->getPlayer();
        foreach ($this->players as $p){
            if($p instanceof Player){
                if($p->getName() == $player->getName()){
            $p = $this->players;
            unset($p[$player->getName()]);
            $this->players = $p;
            $player->sendMessage(color::GREEN."§8> §5You left the FFA");
                   $this->IsLess();
                }
            }
        }
    }
    public function IsLess(){
        if(count($this->players) <= 1 && StateFFA::GetState() == StateFFA::INGAME){
            StateFFA::SetState(StateFFA::RESTARTING);
            $this->getLogger()->info(color::RED."§8> §5Game force ended because of lack of people!");
            return;
        }
    }
    public function playerjoinevent(PlayerJoinEvent $event){
        $this->s[$event->getPlayer()->getName()] = FALSE;   
    }
    public function PlayerDE(PlayerDeathEvent $event){
if(($entity = $event->getEntity()) instanceof Player){
$cause = $entity->getLastDamageCause();
if($cause instanceof EntityDamageByEntityEvent && ($damager = $cause->getDamager()) instanceof Player){
$damager = $damager;
if($damager instanceof Player){
    if($this->s[$damager->getName()] == TRUE){
                            $v = $this->timer->points[$damager->getName()];
                            $v++;
                            $this->timer->points[$damager->getName()] = $v;
                            $damager->sendPopup(color::BLUE."§5-- +1 point! --");
                            return;
}}
}
    }
}
public function PlayerRespawnE(PlayerRespawnEvent $event){
    if($this->s[$event->getPlayer()->getName()] == TRUE){
        $this->timer->TpPlayerToLoc($event->getPlayer());
        $this->timer->GivePlayerItems($event->getPlayer());
        $p = $event->getPlayer();
                           $group = $this->getServer()->getPluginManager()->getPlugin("PurePerms")->getUserDataMgr()->getGroup($p);

        $groupname = $group->getName();
  if($groupname == "member"){
    $p->setMaxHealth(48);
    $p->setHealth(48);
  }
  if($groupname == "gamer"){
    $p->setHealth(44);
    $p->setMaxHealth(44);
  }
    if($groupname == "guest"){
    $p->setHealth(20);
    $p->setMaxHealth(20);
  }
    if($groupname == "user"){
    $p->setHealth(40);
    $p->setMaxHealth(40);
  }
    if($groupname == "leadadmin"){
    $p->setHealth(48);
    $p->setMaxHealth(48);
  }
    if($groupname == "helper"){
    $p->setHealth(44);
    $p->setMaxHealth(44);
  }
    if($groupname == "owner"){
    $p->setHealth(52);
    $p->setMaxHealth(52);
  }
    if($groupname == "moderator"){
    $p->setHealth(44);
    $p->setMaxHealth(44);
  }
    if($groupname == "helper"){
    $p->setHealth(44);
    $p->setMaxHealth(44);
  }
        if($groupname == "youtube"){
    $p->setHealth(44);
    $p->setMaxHealth(44);
  }
      if($groupname == "yt1"){
    $p->setHealth(48);
    $p->setMaxHealth(48);
  }
    }
}

public function onChange(EntityLevelChangeEvent $event){
    $player = $event->getEntity();
    if($player->getLevel()->getName() == "nt"){
      $this->TryLeave($player);
  }
}
}