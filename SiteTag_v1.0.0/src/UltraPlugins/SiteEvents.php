<?php

namespace UltraPlugins;

use UltraPlugins\SiteTag;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\Player;

class SiteEvents extends PluginBase implements Listener {
	public function __construct(SiteTag $plugin) {
		$this->plugin = $plugin;
	}

	public function onSiteJoin(PlayerJoinEvent $e) {
		$p = $e->getPlayer();
		$p->setNameTag($p->getNameTag()."\n".$this->plugin->getConfig()->get("add"));
	}

 }

?>