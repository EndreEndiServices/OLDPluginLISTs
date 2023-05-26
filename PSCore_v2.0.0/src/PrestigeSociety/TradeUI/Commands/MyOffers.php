<?php

namespace PrestigeSociety\TradeUI\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use PrestigeSociety\TradeUI\PrestigeSocietyTradeUI;

class MyOffers extends Command implements PluginIdentifiableCommand {

	/** @var PrestigeSocietyTradeUI */
	protected $loader;

	/**
	 *
	 * EventListener constructor.
	 *
	 * @param PrestigeSocietyTradeUI $loader
	 *
	 */
	public function __construct(PrestigeSocietyTradeUI $loader){
		parent::__construct("myoffers", "access your offers", "Usage: /myoffers", []);
		$this->loader = $loader;
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
	public function execute(CommandSender $sender, string $commandLabel, array $args): bool{
		if($sender instanceof Player){
			$this->loader->getMyOffersUI($sender);

			return true;
		}else{
			$sender->sendMessage("Can't execute that Command here, please go in-game.");

			return false;
		}
	}

	/**
	 * @return Plugin
	 */
	public function getPlugin(): Plugin{
		return $this->loader->core;
	}
}