<?php

namespace Clans;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\utils\TextFormat;
use pocketmine\scheduler\PluginTask;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\utils\Config;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use Clans\utils\Session;
class ClanListener implements Listener {
	
	public $plugin;
	
	public function __construct(Main $pg) {
		$this->plugin = $pg;
	}
	
	public function onJoin(PlayerLoginEvent $event)
	{
		$this->plugin->addSession($event->getPlayer());
	}
	
	public function onQuit(PlayerQuitEvent $event)
	{
		$this->plugin->removeSession($event->getPlayer());
	}
	
	public function clanChat(PlayerChatEvent $PCE) {
		$clan = $this->plugin->getSession($PCE->getPlayer())->getClan();
		if($clan == null) {
			$PCE->setFormat($PCE->getPlayer()->getName() . ": " . $PCE->getMessage());
		} else {
			$PCE->setFormat("[" . $clan->getName() . "] " . $PCE->getPlayer()->getName() . ": " . $PCE->getMessage());
		}
		return true;
		
		$player = strtolower($PCE->getPlayer()->getName());
	}
	
	public function ClanPVP(EntityDamageEvent $factionDamage) {
		if($clanDamage instanceof EntityDamageByEntityEvent) {
			if(!($clanDamage->getEntity() instanceof Player) or !($factionDamage->getDamager() instanceof Player)) {
				return true;
			}
			if((!$this->plugin->getSession($factionDamage->getEntity()->getPlayer())->inClan()) or (!$this->plugin->getSession($clanDamage->getDamager()->getPlayer()))) {
				return true;
			}
			if(($factionDamage->getEntity() instanceof Player) and ($factionDamage->getDamager() instanceof Player)) {
				$player1 = $clanDamage->getEntity()->getPlayer()->getName();
				$player2 = $clanDamage->getDamager()->getPlayer()->getName();
				if($this->plugin->sameClan($player1, $player2) == true) {
					$clanDamage->setCancelled(true);
				}
			}
		}
	}
}
