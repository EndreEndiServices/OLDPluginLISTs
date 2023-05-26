<?php

namespace PrestigeSociety\Credits\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\plugin\Plugin;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class CreditsCommand extends Command implements PluginIdentifiableCommand {

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
		parent::__construct('credits', 'See credits of player or yourself', 'Usage: /credits [player]', ['credits']);
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
			$message = $this->c->getMessage('credits', 'my_balance');
			$message = str_replace('@coins', $this->c->PrestigeSocietyCredits->getCredits($sender), $message);
			$sender->sendMessage(RandomUtils::colorMessage($message));

			return;
		}else{
			$player = $args[0];
			if(!$this->c->PrestigeSocietyCredits->playerExists($player)){
				$message = $this->c->getMessage('credits', 'no_player');
				$sender->sendMessage(RandomUtils::colorMessage($message));

				return;
			}
			$message = $this->c->getMessage('credits', 'player_balance');
			$message = str_replace(['@player', '@coins'], [$player, $this->c->PrestigeSocietyCredits->getCredits($player)], $message);
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