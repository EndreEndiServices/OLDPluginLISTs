<?php

namespace SarchCore\Message;

use pocketmine\utils\TextFormat as C;
use SarchCore\SarchCore;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\event\Listener;

Class JoinMSG implements Listener {
	
	public function onJoin(PlayerJoinEvent $event) {
		$player = $event->getPlayer();
		$player->sendMessage(C::DARK_AQUA . "§6=======================§3SarchonFactions§6========================");
		$player->sendMessage(C::DARK_AQUA . "                                 §3Welcome, §b" . $player->getName() . "§3!");
		$player->sendMessage(C::DARK_AQUA . "        §2Shop:     §dShop.TheSarch.net §f| §2Twitter: §d@SourServers");
		$player->sendMessage(C::DARK_AQUA . "        §2Forums: §dTheSarch.Enjin.com §f| §2Vote: §dhttp://bit.ly/2x2QEZj");
		$player->sendMessage(C::DARK_AQUA . "        §2Prison:     §dPSN.TheSarch.net §f| §2HUB: §dCOMING SOON §2(TBA)");
		$player->sendMessage(C::DARK_AQUA . "§6=======================§3SarchonFactions§6========================");
	}
}