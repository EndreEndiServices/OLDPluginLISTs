<?php

namespace ChatGuard;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;

class ChatGuard extends PluginBase implements Listener {

	private $time = [];
	private $log = [];
	
	const PREFIX = "";
    
    public function onEnable(){
		$this->saveDefaultConfig();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getLogger()->notice("ASR_ успешно загружен!");
    }
	
    public function onDisable(){
		$this->getLogger()->notice("AR_and_and_AS успешно выключен!");
    }


	public function onChat(PlayerChatEvent $event){
        $player = strtolower($event->getPlayer()->getName());
		$config = $this->getConfig()->getAll();
		if ($config["spam-allow"] != true && !$event->getPlayer()->hasPermission("sp.on")){
            $getTime = microtime(true);
			if (isset($this->time[$player])){
				$playerTick = $this->time[$player];
				$seconds = $config["spam"]["time"];
				if ($getTime - $playerTick < $seconds){
					$event->getPlayer()->sendMessage(str_replace("{seconds}", $seconds - (int) ($getTime - $playerTick), ChatGuard::PREFIX . $config["spam"]["warn-message"]));
					$event->setCancelled(true);
					return true;
				}
			}
            $this->time[$player] = $getTime;
		}
		if ($config["repeats-allow"] != true && !$event->getPlayer()->hasPermission("rep.on")){
			if (!isset($this->log[$player])){
				$this->log[$player] = [];
			}
			$log =& $this->log[$player];
			$message = $event->getMessage();
			$repeats = 0;
			array_unshift($log, $message);
			foreach($log as $msg){
				if ($msg !== $message) break;
					$repeats++;
				if ($repeats > $config["repeats"]["max-repeats"]){
					$event->setCancelled();
					$event->getPlayer()->sendMessage(ChatGuard::PREFIX . $config["repeats"]["warn-message"]);
					break;
				}
			}
			if (count($log) > $config["repeats"]["max-repeats"] + 1) unset($log[count($log) - 1]);
		}
		return true;
	}
	
	public function onPlayerQuit(PlayerQuitEvent $event){
		$player = strtolower($event->getPlayer()->getName());
		if (isset($this->time[$player])) unset($this->time[$player]);
		if (isset($this->log[$player])) unset($this->log[$player]);
	}
}
