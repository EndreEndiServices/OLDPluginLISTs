<?php

namespace PrestigeSociety\Jobs\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class JobCommand extends Command implements PluginIdentifiableCommand {

	/** @var PrestigeSocietyCore */
	protected $core;

	public function __construct(PrestigeSocietyCore $core){
		parent::__construct("job", "Set a job!", RandomUtils::colorMessage("&eUsage: /job"), ["jobs"]);
		$this->core = $core;
		$this->setPermission("command.job");
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

		if(count($args) >= 1){
			$sender->sendMessage($this->getUsage());

			return;
		}

		$this->core->PrestigeSocietyJobs->showMenu($sender);

		return;
	}

	/**
	 * @return Plugin
	 */
	public function getPlugin(): Plugin{
		return $this->core;
	}
}