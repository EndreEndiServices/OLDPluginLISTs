<?php

namespace Richen\Clans;

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

class ClanListener implements Listener {
	
	public $plugin;
	
	public function __construct(ClanMain $pg) {
		$this->plugin = $pg;
	}
	
	public function clanPVP(EntityDamageEvent $clanDamage)
	{
		if($clanDamage instanceof EntityDamageByEntityEvent)
		{
			if(!($clanDamage->getEntity() instanceof Player) or !($clanDamage->getDamager() instanceof Player)) { return true; }
			
			if(($this->plugin->isInClan($clanDamage->getEntity()->getPlayer()->getName()) == false)
			or($this->plugin->isInClan($clanDamage->getDamager()->getPlayer()->getName()) == false)) {
				return true;
			}
			if(($clanDamage->getEntity() instanceof Player) and ($clanDamage->getDamager() instanceof Player))
			{
				$player1 = $clanDamage->getEntity()->getPlayer()->getName();
				$player2 = $clanDamage->getDamager()->getPlayer()->getName();
				if($this->plugin->sameClan($player1, $player2) == true)
				{
					$clanDamage->setCancelled(true);
				}
			}
		}
	}
}
