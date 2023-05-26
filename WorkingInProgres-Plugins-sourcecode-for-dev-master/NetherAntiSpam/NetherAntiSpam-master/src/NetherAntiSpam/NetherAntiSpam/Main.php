<?php

namespace NetherAntiSpam\NetherAntiSpam;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

	class Main extends PluginBase implements Listener{

	  private $last=[];
                
		public function onEnable()
		{
			$this->getServer()->getPluginManager()->registerEvents($this,$this);
			$this->getLogger()->info("NetherAntiSpam has been enabled.");
			$this->cfg = $this->getConfig();
		}

		public function onDisable()
		{
			$this->getLogger()->info("NetherAntiSpam has been disabled.");
		}		
		
		public function onChat(PlayerChatEvent $event)
		{
                  $message = $this->cfg->get("kick-message");
		  $player = $event->getPlayer();
		  $playerId = $player->getId(); // an identifier just for that player
		   if(isset($this->last[$playerId]) and microtime(true) - $this->last[$playerId] < 0.5){ // if this is not the first message the player chatted, and the last message was sent less than 0.5 second ago
		     $event->setCancelled();
		     $player->close("", $message);
                     return;
                   }
                  $this->last[$playerId] = microtime(true); // save the current time as the time the player last chatted
                }
         }

?>  
