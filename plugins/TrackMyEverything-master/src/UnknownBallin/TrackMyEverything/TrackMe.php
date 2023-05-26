<?php

namespace UnknownBallin\TrackMyEverything;

use pocketmine\Player;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\textFormat as C;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class TrackMe extends PluginBase implements Listener{
	
      public function onEnable():void{
             @mkdir($this->getDataFolder());
             $this->saveDefaultConfig();
             $this->stats=new Config($this->getDataFolder()."stats.yml",Config::YAML, array());
		     $this->getServer()->getPluginManager()->registerEvents($this, $this);
		     $this->getLogger()->info("TrackMyEverything by UnknownBallin | Enabled, happy playing!");
	  }

      public function onDisable():void{
      	   $this->getLogger()->info("TrackMyEverything has been disabled.");
      }
      
      public function addPlayer($player){ #adding a player to stats.yml
      	    $this->stats->setNested(strtolower($player->getName()).".kills", "0");
              $this->stats->setNested(strtolower($player->getName()).".deaths", "0");
              $this->stats->setNested(strtolower($player->getName()).".joins", "0");
              $this->stats->setNested(strtolower($player->getName()).".leaves", "0");
              $this->stats->setNested(strtolower($player->getName()).".respawns", "0");
              $this->stats->setNested(strtolower($player->getName()).".timeskicked", "0");
              $this->stats->setNested(strtolower($player->getName()).".blockbreaks", "0");
              $this->stats->setNested(strtolower($player->getName()).".blocksplaced", "0");
              $this->stats->setNested(strtolower($player->getName()).".messagessent", "0");
              $this->stats->setNested(strtolower($player->getName()).".commandsrun", "0");
              $this->stats->setNested(strtolower($player->getName()).".itemsconsumed", "0");
              $this->stats->setNested(strtolower($player->getName()).".itemsdropped", "0");
              $this->stats->setNested(strtolower($player->getName()).".timesjumped", "0");
              $this->stats->save();
      }
      public function newPlayerJoin(PlayerJoinEvent $event){ #when a player that doesnt exist in stats.yml joins, the event addPlayer is ran
      	   $player=$event->getPlayer();
      	   if(!$this->stats->exists(strtolower($player->getName()))){
      	   	  $this->addPlayer($player);
             }
      }
      public function getKills($player){
             $this->stats->getAll()[strtolower($player->getName())]["kills"];
             $this->stats->save();
      }
      public function getDeaths($player){
             $this->stats->getAll()[strtolower($player->getName())]["deaths"];
             $this->stats->save();
      }
      public function getJoins($player){
             $this->stats->getAll()[strtolower($player->getName())]["joins"];
             $this->stats->save();
      }
      public function getLeaves($player){
             $this->stats->getAll()[strtolower($player->getName())]["leaves"];
             $this->stats->save();
      }
      public function getRespawns($player){
             $this->stats->getAll()[strtolower($player->getName())]["respawns"];
             $this->stats->save();
      }
      public function getTimesKicked($player){
             $this->stats->getAll()[strtolower($player->getName())]["timeskicked"];
             $this->stats->save();
      }
      public function getBbreaks($player){
      	   $this->stats->getAll()[strtolower($player->getName())]["blockbreaks"];
             $this->stats->save();
      }
      public function getBplaced($player){
      	   $this->stats->getAll()[strtolower($player->getName())]["blocksplaced"];
             $this->stats->save();
      }
      public function getMessagesSent($player){
      	   $this->stats->getAll()[strtolower($player->getName())]["messagessent"];
             $this->stats->save();
      }
      public function getCommandsRun($player){
      	   $this->stats->getAll()[strtolower($player->getName())]["commandsrun"];
             $this->stats->save();
      }
      public function getItemsConsumed($player){
      	   $this->stats->getAll()[strtolower($player->getName())]["itemsconsumed"];
             $this->stats->save();
      }
      public function getItemsDropped($player){
      	   $this->stats->getAll()[strtolower($player->getName())]["itemsdropped"];
             $this->stats->save();
      }
      public function getTimesJumped($player){
      	   $this->stats->getAll()[strtolower($player->getName())]["timesjumped"];
             $this->stats->save();
      }
      public function addKill($player){
      	   $this->stats->setNested(strtolower($player->getName()).".kills", $this->stats->getAll()[strtolower($player->getName())]["kills"] + 1);
             $this->stats->save();
      }
      public function addDeath($player){
      	   $this->stats->setNested(strtolower($player->getName()).".deaths", $this->stats->getAll()[strtolower($player->getName())]["deaths"] + 1);
             $this->stats->save();
      }
      public function addJoins($player){
      	   $this->stats->setNested(strtolower($player->getName()).".joins", $this->stats->getAll()[strtolower($player->getName())]["joins"] + 1);
             $this->stats->save();
      }
      public function addLeaves($player){
      	   $this->stats->setNested(strtolower($player->getName()).".leaves", $this->stats->getAll()[strtolower($player->getName())]["leaves"] + 1);
             $this->stats->save();
      }
      public function addRespawns($player){
      	   $this->stats->setNested(strtolower($player->getName()).".respawns", $this->stats->getAll()[strtolower($player->getName())]["respawns"] + 1);
             $this->stats->save();
      }
      public function addTimesKicked($player){
      	   $this->stats->setNested(strtolower($player->getName()).".timeskicked", $this->stats->getAll()[strtolower($player->getName())]["timeskicked"] + 1);
             $this->stats->save();
      }
      public function addBbreaks($player){
      	   $this->stats->setNested(strtolower($player->getName()).".blockbreaks", $this->stats->getAll()[strtolower($player->getName())]["blockbreaks"] + 1);
             $this->stats->save();
      }
      public function addBplaced($player){
      	   $this->stats->setNested(strtolower($player->getName()).".blocksplaced", $this->stats->getAll()[strtolower($player->getName())]["blocksplaced"] + 1);
             $this->stats->save();
      }
      public function addMessagesSent($player){
      	   $this->stats->setNested(strtolower($player->getName()).".messagessent", $this->stats->getAll()[strtolower($player->getName())]["messagessent"] + 1);
             $this->stats->save();
      }
      public function addCommandsRun($player){
      	   $this->stats->setNested(strtolower($player->getName()).".commandsrun", $this->stats->getAll()[strtolower($player->getName())]["commandsrun"] + 1);
             $this->stats->save();
      }
      public function addItemsConsumed($player){
      	   $this->stats->setNested(strtolower($player->getName()).".itemsconsumed", $this->stats->getAll()[strtolower($player->getName())]["itemsconsumed"] + 1);
             $this->stats->save();
      }
      public function addItemsDropped($player){
      	   $this->stats->setNested(strtolower($player->getName()).".itemsdropped", $this->stats->getAll()[strtolower($player->getName())]["itemsdropped"] + 1);
             $this->stats->save();
      }
      public function addTimesJumped($player){
      	   $this->stats->setNested(strtolower($player->getName()).".timesjumped", $this->stats->getAll()[strtolower($player->getName())]["timesjumped"] + 1);
             $this->stats->save();
      }
      #AVAILABLE STATISTICAL EVENTS
      public function onKill(PlayerDeathEvent $event){ #on a kill, killer gets a kill, killed gets a death
      	   $player=$event->getPlayer();
             $cause=$player->getLastDamageCause();
         	$this->addDeath($event->getPlayer());
             if($cause instanceof EntityDamageByEntityEvent){
             	  $killer=$cause->getDamager()->getPlayer();
                   $this->addKill($killer);
                   return true;
              }
      }
      public function onPlyJoin(PlayerJoinEvent $event){
      	   $player=$event->getPlayer();
             $this->addJoins($player);
      }
      public function onPlyLeave(PlayerQuitEvent $event){
      	   $player=$event->getPlayer();
             $this->addLeaves($player);
      }
      public function onPlyRespawn(PlayerRespawnEvent $event){
      	   $player=$event->getPlayer();
             $this->addRespawns($player);
      }
      public function onPlyKick(PlayerKickEvent $event){
      	   $player=$event->getPlayer();
             $this->addTimesKicked($player);
      }
      public function onPlyBlockBreak(BlockBreakEvent $event){
      	   $player=$event->getPlayer();
             $this->addBbreaks($player);
      }
      public function onPlyBlockPlace(BlockPlaceEvent $event){
      	   $player=$event->getPlayer();
             $this->addBplaced($player);
      }
      public function onPlyMessageSend(PlayerChatEvent $event){
      	   $player=$event->getPlayer();
             $this->addMessagesSent($player);
      }
      public function onPlyCommandsRun(PlayerCommandPreprocessEvent $event){
      	   $player=$event->getPlayer();
      	   $message=$event->getMessage();
             if($message[0] == "/"){
             	  $this->addCommandsRun($player);
             }
      }
      public function onPlyConsume(PlayerItemConsumeEvent $event){
      	   $player=$event->getPlayer();
             $this->addItemsConsumed($player);
      }
      public function onPlyItemDrop(PlayerDropItemEvent $event){
      	   $player=$event->getPlayer();
             $this->addItemsDropped($player);
      }
      public function onPlyJump(PlayerJumpEvent $event){
      	   $player=$event->getPlayer();
             $this->addTimesJumped($player);
      }
      
      public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
            $ply=$sender->getName();
            $config=$this->getDataFolder("config.yml");
      	  switch($cmd->getName()){
      	        case "mystats":
                  if(!$sender instanceof Player){
                  $sender->sendMessage($this->getConfig()->get("CommandViaConsole"));
                  return false;
                  }
                  if($sender instanceof Player){
            	  $k=$this->stats->getAll()[strtolower($sender->getName())]["kills"];
     	         $d=$this->stats->getAll()[strtolower($sender->getName())]["deaths"];
          	    $tj=$this->stats->getAll()[strtolower($sender->getName())]["joins"];
           	   $tl=$this->stats->getAll()[strtolower($sender->getName())]["leaves"];
        	      $tr=$this->stats->getAll()[strtolower($sender->getName())]["respawns"];
        	      $tk=$this->stats->getAll()[strtolower($sender->getName())]["timeskicked"];
   	           $bb=$this->stats->getAll()[strtolower($sender->getName())]["blockbreaks"];
       	       $bp=$this->stats->getAll()[strtolower($sender->getName())]["blocksplaced"];
    	          $ms=$this->stats->getAll()[strtolower($sender->getName())]["messagessent"];
        	      $cr=$this->stats->getAll()[strtolower($sender->getName())]["commandsrun"];
      	        $ic=$this->stats->getAll()[strtolower($sender->getName())]["itemsconsumed"];
   	           $id=$this->stats->getAll()[strtolower($sender->getName())]["itemsdropped"];
       	       $ttj=$this->stats->getAll()[strtolower($sender->getName())]["timesjumped"];
                  	  $sender->sendMessage("§l§8--- §eSTATISTICS§8 ---");
                        $sender->sendMessage("§8Player: §f".$ply);
                        $sender->sendMessage("§8Kills: §f".$k." §7| §8Deaths: §f".$d);
                        $sender->sendMessage("§8Joins: §f".$tj." §7| §8Leaves: §f".$tl);
                        $sender->sendMessage("§8Blocks placed: §f".$bp);
                        $sender->sendMessage("§8Blocks broken: §f".$bb);
                        $sender->sendMessage("§8Messages sent: §f".$ms);
                        $sender->sendMessage("§8Commands run: §f".$cr);
                        $sender->sendMessage("§8Items consumed: §f".$ic);
                        $sender->sendMessage("§8Items dropped: §f".$id);
                        $sender->sendMessage("§8Total respawns: §f".$tr);
                        $sender->sendMessage("§8Total times kicked: §f".$tk);
                        $sender->sendMessage("§8Total times jumped: §f".$ttj);
                        $sender->sendMessage("§e====================");
                  	  }
                  	  break;
            }
            return true;
     }
}