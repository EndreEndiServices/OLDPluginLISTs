<?php

namespace BeatsCore;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;
use pocketmine\event\{Listener, EventPriority, Event, player\PlayerCommandPreprocessEvent, player\PlayerQuitEvent, player\PlayerDeathEvent, entity\EntityDamageByEntityEvent, entity\EntityDamageEvent};
use pocketmine\lang\TranslationContainer;
use pocketmine\entity\Entity;
use pocketmine\utils\{Config, TextFormat as C};

#Anti
use BeatsCore\Anti\{AntiAdvertising, AntiSwearing};

#Command
use BeatsCore\Command\{NickCommand, FlyCommand};

#Message
use BeatsCore\Message\Broadcast;

#Events
use BeatsCore\Events\{DamageEvent, JoinEvent, QuitEvent};

#Tasks
use BeatsCore\Tasks\CombatLoggerTask;

#Manager
use BeatsCore\{item\ItemManager, entity\EntityManager};

class Core extends PluginBase implements Listener{

    public $players = array();
    public $interval = 10;
    public $blockedcommands = array();

    private $task;

    const PERM_RANK =  "§7> §cYou must purchase a rank to use this command. §6You may purchase one at: §bBeatsNetworkPE.buycraft.net";
    const PERM_STAFF = "§7> §cOnly staff members may use this command!";
    const USE_IN_GAME = "§7> §cPlease use this command in-game!";

    public function onEnable(){
     $this->getServer()->getPluginManager()->registerEvents(($this->antiadvertising = new AntiAdvertising($this)), $this);
     $this->getServer()->getPluginManager()->registerEvents(($this->antiswearing = new AntiSwearing($this)), $this);
     $this->getServer()->getPluginManager()->registerEvents($this, $this);

     $this->getServer()->getPluginManager()->registerEvents(($this->damageevent = new DamageEvent($this)), $this);
     $this->getServer()->getPluginManager()->registerEvents(($this->joinevent = new JoinEvent($this)), $this);
     $this->getServer()->getPluginManager()->registerEvents(($this->quitevent = new QuitEvent($this)), $this);

     $this->getServer()->getCommandMap()->register("nick", new NickCommand($this));
     $this->getServer()->getCommandMap()->register("fly", new FlyCommand($this));

     $this->getLogger()->info("Plugin Enabled");

     $this->getServer()->getScheduler()->scheduleRepeatingTask(new Broadcast($this), 2400);

   }	

   public function Plugins(){
    #Other
    ItemManager::init();
    EntityManager::init();

    #CombatLogger
    $this->saveResource("CombatLogger/config.yml");
    $this->combatlcfg = new Config($this->getDataFolder() . "CombatLogger/config.yml", Config::YAML);

    $this->interval = $this->combatlcfg->get("interval");
    $cmds = $this->combatlcfg->get("blocked-commands");
    foreach($cmds as $cmd){
        $this->blockedcommands[$cmd]=1;
    }

    $this->getServer()->getScheduler()->scheduleRepeatingTask(new CombatLoggerTask($this, $this->interval), 20);
   }

    #-----------------------------COMBAT LOGGER--------------------------#

    public function EntityDamageEvent(EntityDamageEvent $event){

        if($event instanceof EntityDamageByEntityEvent){

            if($event->getDamager() instanceof Player and $event->getEntity() instanceof Player){

                foreach(array($event->getDamager(),$event->getEntity()) as $players){

                    $this->setTime($players);

                }

            }

        }

    }

    private function setTime(Player $player){

        $msg = "§l§8(§4!§8)§r §cLogging out now will cause you to die!\n§l§8(§4!§8)§r §cPlease wait ".$this->interval." seconds...§r";

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

        }

    }


    public function PlayerQuitEvent(PlayerQuitEvent $event){

        if(isset($this->players[$event->getPlayer()->getName()])){

            $player = $event->getPlayer();

            if((time() - $this->players[$player->getName()]) < $this->interval){

                $player->kill();

            }

        }

    }


    public function PlayerCommandPreprocessEvent(PlayerCommandPreprocessEvent $event){

        if(isset($this->players[$event->getPlayer()->getName()])){

            $cmd = strtolower(explode(' ', $event->getMessage())[0]);

            if(isset($this->blockedcommands[$cmd])){

                $event->getPlayer()->sendMessage("§l§8(§4!§8)§r §cYou cannot use this command during combat!§r");

                $event->setCancelled();

            }

        }

    }
}