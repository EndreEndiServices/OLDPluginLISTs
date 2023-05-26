<?php

namespace UltraPlugins;

use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;

class SiteTag extends PluginBase {

	public function onEnable() {
		$this->saveDefaultConfig();
		$this->getServer()->getPluginManager()->registerEvents(new SiteEvents ($this), $this);
		$this->getLogger()->info('§6Плагин §cSite§3Tag §6активирован!');
	}

	public function onDisable() {
		$this->getLogger()->info('§6Плагин §cSite§3Tag §6дезактивирован!');
	}

}

?>