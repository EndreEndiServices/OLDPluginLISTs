<?php

namespace AntiServer;

use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener{

	public function onEnable(): void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	private $antiserver = ["happype", "hive", "happybedrock", "ownage", "hyperland", "hyperlands", "hypixel" ,"lbsg" ,"lifeboat" ,"my server" ,"join this server" ,"cubecraft" ,"inpvp" ,"trash server" ,"s e v e r sucks" ,"mineplex" ,"bad server" ,"fallentech" ,"nethergames" ,"primegames" ,"ecpe" ,"brokenlens" ,"pixelbe" ,"withernation"];
	
	public function onChat(PlayerChatEvent $event): void{
		$msg = $event->getMessage();
		$player = $event->getPlayer();
		foreach($this->antiserver as $antiserver){
			if(strpos($msg, $antiserver) !== false){
				$player->sendMessage(TextFormat::RED . "§c§lPLEASE DON'T USE OTHER SERVERS NAMES!");
				$event->setCancelled();
				return;
			}
		}
	}
}