<?php

namespace SarchCore\Staff;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerPreLoginEvent;
use SarchCore\SarchCore;

class StaffManager implements Listener {

	private $plugin, $staff;

	public function __construct(SarchCore $plugin) {
		$this->plugin = $plugin;
		$this->staff = ["EmeraldGem585" => '174.111.20.222', "Godsmack2002" => '190.213.10.88', "Legend4Life" => '10.0.0.12', "Elite4Life" => '85.211.160.15', "Echobear4Life" => "70.54.104.67", "EvilGreg" => '46.198.251.72', "Lagow2001" => '192.161.205.52'];
	}

	public function onLogin(PlayerPreLoginEvent $ev) {
		if(isset($this->staff[$ev->getPlayer()->getName()])) {
			if(strval($ev->getPlayer()->getAddress()) !== strval($this->staff[$ev->getPlayer()->getName()])) {
				$ev->setCancelled();
				$ev->getPlayer()->close("Your IP doesn't match the registered staff\n IP for this account.");
				return;
			}
		}
	}
}