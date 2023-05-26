<?php

namespace PrestigeSociety\Core\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\plugin\Plugin;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;
use PrestigeSociety\Core\Utils\ServerUtils;

class OpHelpCommand extends Command implements PluginIdentifiableCommand {

	/** @var PrestigeSocietyCore */
	private $c;

	/**
	 *
	 * OpHelpCommand constructor.
	 *
	 * @param PrestigeSocietyCore $c
	 *
	 */
	public function __construct(PrestigeSocietyCore $c){
		parent::__construct("ophelp", "Ask for op help!", RandomUtils::colorMessage("&e/ophelp <explain...>"), ["oph"]);
		$this->c = $c;
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
	public function execute(CommandSender $sender, $commandLabel, array $args){
		$reason = implode(" ", $args);
		if(empty($args)){
			$sender->sendMessage($this->getUsage());

			return false;
		}
		$msg = str_replace(["@explanation", "@player"], [$reason, $sender->getName()], PrestigeSocietyCore::getInstance()->getConfig()->getAll()["op_help_format"]);
		$msg = RandomUtils::colorMessage($msg);
		ServerUtils::bcToOps($msg);
		$sender->sendMessage($msg);

		return true;
	}

	/**
	 * @return PrestigeSocietyCore
	 */
	public function getPlugin(): Plugin{
		return $this->c;
	}
}