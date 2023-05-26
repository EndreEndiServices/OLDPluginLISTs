<?php

namespace SarchCore\Security;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\Player;
use pocketmine\utils\Config;
use SarchCore\SarchCore;

class SecurityManager implements Listener {

	private $plugin, $data;

	public function __construct(SarchCore $plugin) {
		$this->plugin = $plugin;
		$this->data = (new Config($this->plugin->getDataFolder() . "/pinfo.json", Config::JSON))->getAll();
	}

	public function __destruct() {
		@unlink($this->plugin->getDataFolder() . "/pinfo.json");
		(new Config($this->plugin->getDataFolder() . "/pinfo.json", Config::JSON, $this->data))->save();
	}

	public function getData(Player $player) {
		return $this->data[$player->getName()];
	}

	public function getAddresses(Player $player) {
		return $this->getData($player)["ips"];
	}

	public function onPreLogin(PlayerPreLoginEvent $ev) {
		$p = $ev->getPlayer();
		if(!isset($this->data[$p->getName()])) {
			$this->data[$p->getName()] = ["ips" => [$p->getAddress()], "name" => $p->getName()];
			return;
		}
		$this->data[$p->getName()]["ips"][] = $p->getAddress();
	}
}