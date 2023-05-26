<?php

namespace PrestigeSociety\Core\Commands\Server;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class AddInfoCommand extends Command implements PluginIdentifiableCommand {

	/** @var PrestigeSocietyCore */
	private $plugin;

	/**
	 *
	 * EatCommand constructor.
	 *
	 * @param PrestigeSocietyCore $plugin
	 *
	 */
	public function __construct(PrestigeSocietyCore $plugin){
		parent::__construct("addinfo", "Add server info particles", RandomUtils::colorMessage("&eUsage: /addinfo <info name>"), []);
		$this->setPermission("command.addinfo");
		$this->plugin = $plugin;
	}


	/**
	 *
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param string[] $args
	 *
	 * @return mixed
	 *
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!($sender instanceof Player)) return;
		if(!$this->testPermission($sender)){
			return;
		}

		if(count($args) < 1){
			$sender->sendMessage($this->getUsage());

			return;
		}

		$page = $args[0];

		$this->plugin->PrestigeSocietyParticle->saveInfoParticle($page, $sender);
		$this->plugin->PrestigeSocietyParticle->spawnInfoParticle($page);

		$sender->sendMessage(RandomUtils::colorMessage("&aAdded info particle '" . $page . "'!"));

		return;
	}

	/**
	 *
	 * @return Plugin
	 *
	 */
	public function getPlugin(): Plugin{
		return $this->plugin;
	}
}