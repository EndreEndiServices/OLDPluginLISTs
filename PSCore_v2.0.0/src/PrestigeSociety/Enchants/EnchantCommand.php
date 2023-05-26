<?php

namespace PrestigeSociety\Enchants;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class EnchantCommand extends Command implements PluginIdentifiableCommand {

	/** @var PrestigeSocietyCore */
	protected $core;

	public function __construct(PrestigeSocietyCore $core){
		parent::__construct("buyenchant", "Want an enchantment? Buy it here!", RandomUtils::colorMessage("&eUsage: /enchant"), ["buyenchantment", "buyench"]);
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
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!($sender instanceof Player)) return;

		if(!$this->core->PrestigeSocietyEnchants->getChooseEnchantUI($sender)){
			$message = $this->core->getMessage('enchants', 'cannot_enchant_air');
			$sender->sendMessage(RandomUtils::colorMessage($message));
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