<?php

namespace PrestigeSociety\Optimizer\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use PrestigeSociety\Core\PrestigeSocietyCore;

class LagCommand extends Command implements PluginIdentifiableCommand {

	/** @var PrestigeSocietyCore */
	protected $c;

	/**
	 *
	 * LagCommand constructor.
	 *
	 * @param PrestigeSocietyCore $c
	 *
	 */
	public function __construct(PrestigeSocietyCore $c){
		parent::__construct('lag', 'Switch lag of your device', 'Usage: /lag <on/off>');
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
	public function execute(CommandSender $sender, string $commandLabel, array $args){

		if(count($args) > 1){
			$sender->sendMessage(TextFormat::GREEN . $this->getUsage());

			return;
		}

		/** @var Player $player */
		$player = $sender;

		switch($args[0]){
			case "on":
				$player->setViewDistance(2);
				//$message = $this->c->getMessage('lag', 'on');
				//$sender->sendMessage(RandomUtils::colorMessage($message));
				return;
			case "off":
				$player->setViewDistance($this->c->getServer()->getViewDistance());
				//$message = $this->c->getMessage('lag', 'off');
				//$sender->sendMessage(RandomUtils::colorMessage($message));
				return;
		}

		return;
	}

	/**
	 * @return Plugin
	 */
	public function getPlugin(): Plugin{
		return $this->c;
	}
}