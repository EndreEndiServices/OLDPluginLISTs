<?php

namespace PrestigeSociety\Levels\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\plugin\Plugin;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class SetKillsCommand extends Command implements PluginIdentifiableCommand {

	/** @var PrestigeSocietyCore */
	protected $plugin;

	/**
	 *
	 * SetKillsCommand constructor.
	 *
	 * @param PrestigeSocietyCore $c
	 *
	 */
	public function __construct(PrestigeSocietyCore $c){
		parent::__construct('setkills', 'Allows you to set a player\'s kills', '/setkills <player> <kills>', []);
		$this->plugin = $c;
	}

	/**
	 *
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param string[] $args
	 *
	 * @return mixed|void
	 *
	 */
	public function execute(CommandSender $sender, $commandLabel, array $args){
		if($sender->hasPermission('pl.setkills')){
			if(count($args) < 2){
				$sender->sendMessage($this->getUsage());

				return;
			}

			$kills = intval($args[1]);

			if(!is_numeric($kills) or !is_int($kills)){
				$sender->sendMessage(RandomUtils::colorMessage('The kills amount must be an integer.'));

				return;
			}

			if($kills < 0){
				$sender->sendMessage(RandomUtils::colorMessage('The kills amount cannot be less than 0.'));

				return;
			}

			$player = $args[0];
			$this->plugin->PrestigeSocietyLevels()->setKills($player, $kills);
			$sender->sendMessage(RandomUtils::colorMessage(str_replace(['@player', '@kills'],
				[$player, $kills], $this->plugin->getMessage('levels', 'player_kills_set'))));
		}
	}

	/**
	 *
	 * @return PrestigeSocietyCore
	 *
	 */
	public function getPlugin(): Plugin{
		return $this->plugin;
	}
}