<?php

namespace PrestigeSociety\Shop\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use PrestigeSociety\Core\PrestigeSocietyCore;

class RemoveShopCommand extends Command implements PluginIdentifiableCommand {

	/** @var PrestigeSocietyCore */
	protected $plugin;

	/**
	 *
	 * SBanCommand constructor.
	 *
	 * @param PrestigeSocietyCore $c
	 *
	 */
	public function __construct(PrestigeSocietyCore $c){
		parent::__construct('removeshop', 'Remove a shop item', 'Usage: /removeshop', []);
		$this->setPermission("command.removeshop");
		$this->plugin = $c;
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
		if(!$this->testPermission($sender)){
			return false;
		}
		if($sender instanceof Player){
			$this->plugin->PrestigeSocietyShop->queue[$sender->getXuid()]['action'] = 1;
			$this->plugin->PrestigeSocietyShop->getSelectCategoryUI($sender);
			//$sender->sendMessage(RandomUtils::colorMessage($this->plugin->getMessage('shop', 'cannot_have_air')));
		}

		return true;
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