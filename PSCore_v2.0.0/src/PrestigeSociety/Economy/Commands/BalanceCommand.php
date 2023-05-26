<?php

namespace PrestigeSociety\Economy\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\plugin\Plugin;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class BalanceCommand extends Command implements PluginIdentifiableCommand {

	/** @var PrestigeSocietyCore */
	protected $c;

	/**
	 *
	 * RepairCommand constructor.
	 *
	 * @param PrestigeSocietyCore $c
	 *
	 */
	public function __construct(PrestigeSocietyCore $c){
		parent::__construct('balance', 'See coins of player or yourself', 'Usage: /coins [player]', ['coins', 'bal']);
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

		if(count($args) < 1){
			$message = $this->c->getMessage('economy', 'my_balance');
			$message = str_replace('@money', $this->c->PrestigeSocietyEconomy->getMoney($sender), $message);
			$sender->sendMessage(RandomUtils::colorMessage($message));

			return;
		}else{
			$player = $args[0];
			if(!$this->c->PrestigeSocietyEconomy->playerExists($player)){
				$message = $this->c->getMessage('economy', 'no_player');
				$sender->sendMessage(RandomUtils::colorMessage($message));

				return;
			}
			$message = $this->c->getMessage('economy', 'player_balance');
			$message = str_replace(['@player', '@money'], [$player, $this->c->PrestigeSocietyEconomy->getMoney($player)], $message);
			$sender->sendMessage(RandomUtils::colorMessage($message));
		}
	}

	/**
	 * @return Plugin
	 */
	public function getPlugin(): Plugin{
		return $this->c;
	}
}