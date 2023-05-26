<?php

namespace survivalgames;

use pocketmine\command\Command;
use pocketmine\command\PluginIdentifiableCommand;

abstract class BaseCommand extends Command implements PluginIdentifiableCommand {
	private $plugin;
	
	public function __construct(Main $plugin, $name, $description = "", $usageMessage = null, array $aliases = []) {
		parent::__construct($name, $description, $usageMessage, $aliases);
		$this->plugin = $plugin;
	}
	
	/**
	 * @return Main
	 */
	public final function getPlugin() {
		return $this->plugin;
	}
	
}