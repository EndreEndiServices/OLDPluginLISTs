<?php
namespace himbeer\popup;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\utils\config;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDeathEvent;
class Main extends PluginBase implements Listener{
     
     public function onEnable(){
          $this->getServer()->getPluginManager()->registerEvents($this,$this);
          $this->getLogger()->info("JoinPopup-NoMessage enabled!");
          @mkdir($this->getDataFolder());
          $this->config = new Config ($this->getDataFolder() . "config.yml" , Config::YAML, array(
               "mode" => "popup",
               "join" => "§a-player- joined the game!",
               "quit" => "§4-player- left the game!",
          ));
          $this->saveResource("config.yml");
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
	}
}
