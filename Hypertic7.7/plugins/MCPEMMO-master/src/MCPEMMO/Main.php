<?php

namespace MCPEMMO;

use pocketmine\level\sound\GhastShootSound;
use pocketmine\level\sound\BlazeShootSound;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\scheduler\CallbackTask;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\level\particle\DustParticle;

class Main extends PluginBase implements Listener
{

 public $players = array();
public function onEnable() {
		  @mkdir($this->getDataFolder());
@mkdir($this->getDataFolder()."Players/");
		//$this->config = $this->getConfig();
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		$this->getServer()->getLogger()->info("[MCPEMMO]Enabled");
		 		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this,"popup"]),15);
		 		 		//$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this,"streaks"]),10);
		}
		public function GetDataFolderMCPE(){
			return $this->getDataFolder();
		}
		public function onJoin(PlayerJoinEvent $ev){
		$ign=$ev->getPlayer()->getName();
		$p=$ev->getPlayer();
		$player=$p;
		 $this->PlayerFile = new Config($this->getDataFolder()."Players/".$ign."_Stats.yml", Config::YAML);
		 
		 if($this->PlayerFile->get("Deaths") === 0 && $this->PlayerFile->get("Kills") === 0){
         //nothin  
         }else{
                 if($p->getLevel()->getName() == "pvp2"){

		}
		$this->addPlayer($p);
		}
  }

public function onHit(EntityDamageEvent $ev){


$p = $ev->getEntity();

if($ev instanceof EntityDamageByEntityEvent){
$damager = $ev->getDamager();
if($damager instanceof Player){

$this->DamagerFile = new Config($this->getDataFolder()."Players/".$damager->getName()."_Stats.yml", Config::YAML);

$this->PlayerFile = new Config($this->getDataFolder()."Players/".$p->getName()."_Stats.yml", Config::YAML);
}
}
}
		//entiyregenhealthevent
		 public function onPlayerLogin(PlayerPreLoginEvent $event){
        $ign = $event->getPlayer()->getName();
        $player = $event->getPlayer();
        $file = ($this->getDataFolder()."Players/".$ign."_Stats.yml");  
      if(!file_exists($file)){
                $this->PlayerFile = new Config($this->getDataFolder()."Players/".$ign."_Stats.yml", Config::YAML);
                $this->PlayerFile->set("Deaths", 0);
                $this->PlayerFile->set("Kills", 0);
                $this->PlayerFile->set("Mining_Level", 0);
                
                $this->PlayerFile->set("Blocks_Broken", 0);
                $this->PlayerFile->set("Power_Level", 0);
                $this->PlayerFile->set("Sword_Level", 0);
                $this->PlayerFile->save();
            }
            $this->PlayerFile = new Config($this->getDataFolder()."Players/".$ign."_Stats.yml", Config::YAML);
            
        } 
        
        public function onDeath(PlayerDeathEvent $ev){
        $p=$ev->getEntity();
        $player=$ev->getEntity();
        $ign=$player->getName();
        $this->PlayerFile = new Config($this->getDataFolder()."Players/".$ign."_Stats.yml", Config::YAML);
        $i=$this->PlayerFile->get("Deaths");
        $ii=$this->PlayerFile->get("Power_Level");
       $n = $i+1;
       $p=$ii-3;
       $this->PlayerFile->set("Deaths", $n);
       $this->PlayerFile->set("Power_Level", $p);
       $level = $player->getLevel();
                $level->addSound(new BlazeShootSound($player->getLocation()));
       $this->PlayerFile->save();
       
       $cause = $ev->getEntity()->getLastDamageCause();

	 			//$killer = $cause->getDamager();
	 			 if($ev->getEntity()->getLastDamageCause() instanceof EntityDamageByEntityEvent){
                   $killer = $ev->getEntity()->getLastDamageCause()->getDamager();
                $this->PlayerFile = new Config($this->getDataFolder()."Players/".$killer->getName()."_Stats.yml", Config::YAML);
                if(!$killer->hasPermission("vip")){
                $iii=$this->PlayerFile->get("Kills");
                $nn = $iii+1;
                $this->PlayerFile->set("Kills", $nn);
                $iiii=$this->PlayerFile->get("Power_Level");
                $nnn = $iiii+3;
                $this->PlayerFile->set("Power_Level", $nnn);
                $level = $killer->getLevel();
                $level->addSound(new GhastShootSound($killer->getLocation()));        
                //$killer->setHealth($killer->getHealth() + 2);
                $this->PlayerFile->save();
                $this->updatePlayer($killer);
      $this->removePlayer($player);
       
      $this->addPlayer($player);
                }else{
     $iii=$this->PlayerFile->get("Kills");
                $nn = $iii+1;
                $this->PlayerFile->set("Kills", $nn);
                $iiii=$this->PlayerFile->get("Power_Level");
                $nnn = $iiii+6;
                $this->PlayerFile->set("Power_Level", $nnn);
                $killer->sendTip("§5+6 Power Level");
                $level = $killer->getLevel();
                $level->addSound(new GhastShootSound($killer->getLocation()));
                
                //$killer->setHealth($killer->getHealth() + 2);
                $this->PlayerFile->save();
      $this->updatePlayer($killer);
      $this->removePlayer($player);
       
      $this->addPlayer($player);
      }
      		if($this->players[$killer->getName()]["kills"] === 5){
		Server::getInstance()->broadcastMessage($killer->getName()." §8> §5Is On A 5 Killstreak!");
		 		if($this->PlayerFile->exists("KSREWARD")){
		 
		}else{
		 		Server::getInstance()->broadcastMessage($killer->getName()." §5Earned The Achivement §aFirst Killstreak!");
		 		$this->PlayerFile->set("KSREWARD");
		 		$this->PlayerFile->save();
		}
		}
		 		if($this->players[$killer->getName()]["kills"] === 10){
		Server::getInstance()->broadcastMessage($killer->getName()." §8> §5Is On A 10 Killstreak!");
		}
		 		if($this->players[$killer->getName()]["kills"] === 15){
		Server::getInstance()->broadcastMessage($killer->getName()." §8> §5Is On A 15 Killstreak!");
		}
		 		if($this->players[$killer->getName()]["kills"] === 20){
		Server::getInstance()->broadcastMessage($killer->getName()." §8> §5Is On A 20 Killstreak!\n§8> §5".$killer->getName()." is beyond §eGODLIKE!");
		}
		 		if($this->players[$killer->getName()]["kills"] === 25){
		Server::getInstance()->broadcastMessage($killer->getName()." §8> §5Is On A 25 Killstreak!");
		}
		 		if($this->players[$killer->getName()]["kills"] === 30){
		Server::getInstance()->broadcastMessage($killer->getName()." §8> §5Is On A Mass Killstreak!!!");

       }
        
       }
       }
       public function onBreak(BlockBreakEvent $ev){
       $player=$ev->getPlayer();
       $ign=$player->getName();
        $this->PlayerFile = new Config($this->getDataFolder()."Players/".$ign."_Stats.yml", Config::YAML);
        $i=$this->PlayerFile->get("Blocks_Broken");
       $n = $i+1;
       $this->PlayerFile->set("Blocks_Broken", $n);
       $this->PlayerFile->save();
       }
       public function popup(){
       foreach($this->getServer()->getOnlinePlayers() as $p){
       $ign=$p->getName();
       $this->PlayerFile = new Config($this->getDataFolder()."Players/".$ign."_Stats.yml", Config::YAML);
       if($p->getLevel()->getName() == "nt"){
         if($this->PlayerFile->get("Kills") > 0 && $this->PlayerFile->get("Deaths") > 0){

         
            
           // $p->sendPopup("§8> §5K§8: §c".$this->PlayerFile->get("Kills")."§8- §5D§8: §c".$this->PlayerFile->get("Deaths")."\n§8> §5KDR§8: §c".round($this->PlayerFile->get("Kills")/$this->PlayerFile->get("Deaths"), 2)."§8- §5KillStreaks§8: §c".$this->players[$p->getName()]["kills"]);
              $p->sendTip("§5KillStreaks§8: §c".$this->players[$p->getName()]["kills"]);
            
       }
         
       }
              if($p->getLevel()->getName() == "pvp2"){
        $p->sendPopup("§8> §5K§8: §c".$this->PlayerFile->get("Kills")."§8- §5D§8: §c".$this->PlayerFile->get("Deaths")."\n§8> §5KDR§8: §c".round($this->PlayerFile->get("Kills")/$this->PlayerFile->get("Deaths"), 2)."§8- §5KillStreaks§8: §c".$this->players[$p->getName()]["kills"]);
		}
		}
  }
		
    
    public function updatePlayer(Player $player) {
        
            $this->players[$player->getName()] = array(
              "kills" => $this->players[$player->getName()]["kills"] + 1  
            );
        
    }

    public function addPlayer(Player $player) {
        $this->players[$player->getName()] = array(
            "kills" => 0
        );
    }
    
    public function isPlayerSet(Player $player) {
        return in_array($player->getName(), $this->players);
    }
    
    public function removePlayer(Player $player) {
        unset($this->players[$player->getName()]);
    }

    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
        $name = $sender->getName();
        $ign = $sender->getName();

        $this->PlayerFile = new Config($this->getDataFolder()."Players/".$ign."_Stats.yml", Config::YAML);

        //if($this->PlayerFile->get("Kills") > 0 && $this->PlayerFile->get("Deaths") > 0){
        
        if(strtolower($cmd->getName()) === 'stats'){

          if($sender->hasPermission("mcpemmo.stats")) {

            if($this->PlayerFile->get("Kills") > 0 && $this->PlayerFile->get("Deaths") > 0){


           $sender->sendMessage("§8> §l§7YOUR STATS M8 §8<"."\n§8> §5Kills§8: §c".$this->PlayerFile->get("Kills")."\n§8> §5Deaths§8: §c".$this->PlayerFile->get("Deaths")."\n§8> §5KDR§8: §c".round($this->PlayerFile->get("Kills")/$this->PlayerFile->get("Deaths"), 2));
        } else {
          $sender->sendMessage("§8> §l§7YOUR STATS M8 §8<"."\n§8> §5Kills§8: §c".$this->PlayerFile->get("Kills")."\n§8> §5Deaths§8: §c".$this->PlayerFile->get("Deaths")."\n§8> §5KDR§8: §c".round($this->PlayerFile->get("Kills")/$this->PlayerFile->get("Deaths"), 2));
            }
          }
        }
      }

		}
		
		