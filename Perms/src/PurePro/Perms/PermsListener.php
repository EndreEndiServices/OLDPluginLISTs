<?php

/**
 *     
 *    ███  ███  █      ███  █   █ ████   ███   ███   ███
 *        █     █     █   █ ██  █ █   █ █   █ █     █      ███  █  █
 *     █   ███  █     █████ █ █ █ █   █ █████ █ ██  ███    █ █  █  █
 *     █      █ █   █ █   █ █  ██ █   █ █   █ █   █ █      ██   █  █
 *    ███  ███  ████  █   █ █   █ ████  █   █  ███   ███ █ █ █  ███
 *     
**/

namespace Richen\Perms;

use pocketmine\event\Listener;

use pocketmine\event\player\{PlayerChatEvent,PlayerJoinEvent,PlayerQuitEvent,PlayerCommandPreprocessEvent};

class PermsListener implements Listener
{	
    public function __construct(PermsMain $plugin){
		$this->plugin = $plugin;
	}

    public function onPlayerJoin(PlayerJoinEvent $event){
		$event->setJoinMessage(null);
		
		$this->plugin->registerPlayer($event->getPlayer());
		
		$nameTag = $this->plugin->getNameTag($event->getPlayer());
		
		$event->getPlayer()->setDisplayName($nameTag);
		
		$event->getPlayer()->setNameTag($nameTag . "\n§e* §fТУТА САЙТ ИЛИ СМС! §e*");
	}
	
    public function onPlayerQuit(PlayerQuitEvent $event){
		$event->setQuitMessage(null);
		
		$this->plugin->unregisterPlayer($event->getPlayer());
	}
	
	public function onPlayerCommandPreprocess(PlayerCommandPreprocessEvent $event){
		if(isset($this->plugin->flood[$event->getPlayer()->getName()]) && !$event->getPlayer()->hasPermission("perms.chat") && !$event->getPlayer()->isOp()){
			$event->getPlayer()->sendMessage("§6[§ePerms§6] §6Нельзя так быстро использовать чат.");
			return $event->setCancelled();
		}
		$this->plugin->flood[$event->getPlayer()->getName()] = true;
	}
	
    public function onPlayerChat(PlayerChatEvent $event){
		$message = $event->getMessage();
		
		$name = $event->getPlayer()->getName();
		
		if(!$event->getPlayer()->hasPermission("perms.chat") && !$event->getPlayer()->isOp()){
			if(mb_strlen($message) < 2){
				$event->getPlayer()->sendMessage("§6[§ePerms§6] §6Нельзя отправлять в чат менее двух символов.");
				return $event->setCancelled();
			}
		}
		
		foreach(explode(" ", $message) as $word){
			if(($p = $this->plugin->getServer()->getPlayer($word)) != null){
				$message = str_replace($word, "§6" . $this->plugin->getNick($p->getName()) . "§r", $message);
			}
		}
		
		$message = str_replace(array(":)", ":(", "<3"), array("§a☺§f", "§c☹§f", "§c❤§f"), $message);
		
		$event->setFormat($this->plugin->formatMessage($event->getPlayer(), $message));
	}
}