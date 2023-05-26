<?php

namespace PrestigeSociety\Kits\Normal;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class KitCommand extends Command implements PluginIdentifiableCommand {

	/** @var PrestigeSocietyCore */
	protected $core;

	/**
	 *
	 * KitCommand constructor.
	 *
	 * @param PrestigeSocietyCore $core
	 *
	 */
	public function __construct(PrestigeSocietyCore $core){
		parent::__construct('kit', "Select a kit!", RandomUtils::colorMessage("&e/kit"), []);
		$this->core = $core;
	}


	/**
	 *
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param string[] $args
	 *
	 * @return mixed
	 *
	 * @throws \InvalidStateException
	 *
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args){

		if($sender instanceof Player){
			if(!$this->core->PrestigeSocietyKits->getNormalKits()->getKitsWindow($sender)){
				$message = $this->core->getMessage('kits', 'no_kits_unlock');
				$sender->sendMessage(RandomUtils::colorMessage($message));

				return;
			}
		}

		return;
	}

	/**
	 * @return Plugin
	 */
	public function getPlugin(): Plugin{
		return $this->core;
	}
}