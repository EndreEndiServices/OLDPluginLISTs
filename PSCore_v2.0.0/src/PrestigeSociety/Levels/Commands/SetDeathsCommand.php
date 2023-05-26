<?php

namespace PrestigeSociety\Levels\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\plugin\Plugin;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class SetDeathsCommand extends Command implements PluginIdentifiableCommand {

	/** @var PrestigeSocietyCore */
	protected $plugin;

	/**
	 *
	 * SetDeathsCommand constructor.
	 *
	 * @param PrestigeSocietyCore $c
	 *
	 */
	public function __construct(PrestigeSocietyCore $c){
		parent::__construct('setdeaths', 'Allows you to set a player\'s deaths', '/setdeaths <player> <deaths>', []);
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
		if($sender->hasPermission('pl.setdeaths')){
			if(count($args) < 2){
				$sender->sendMessage($this->getUsage());

				return;
			}

			$deaths = intval($args[1]);

			if(!is_numeric($deaths) or !is_int($deaths)){
				$sender->sendMessage(RandomUtils::colorMessage('The deaths amount must be an integer.'));

				return;
			}

			if($deaths < 0){
				$sender->sendMessage(RandomUtils::colorMessage('The deaths amount cannot be less than 0.'));

				return;
			}

			$player = $args[0];
			$this->plugin->PrestigeSocietyLevels()->setDeaths($player, $deaths);
			$sender->sendMessage(RandomUtils::colorMessage(str_replace(['@player', '@deaths'],
				[$player, $deaths], $this->plugin->getMessage('levels', 'player_deaths_set'))));
		}
	}

	/**
	 * @return PrestigeSocietyCore
	 */
	public function getPlugin(): Plugin{
		return $this->plugin;
	}
}