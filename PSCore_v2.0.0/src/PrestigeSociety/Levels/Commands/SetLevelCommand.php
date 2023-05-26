<?php

namespace PrestigeSociety\Levels\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\plugin\Plugin;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class SetLevelCommand extends Command implements PluginIdentifiableCommand {

	/** @var PrestigeSocietyCore */
	protected $plugin;

	/**
	 *
	 * SetLevelCommand constructor.
	 *
	 * @param PrestigeSocietyCore $c
	 *
	 */
	public function __construct(PrestigeSocietyCore $c){
		parent::__construct('setlevel', 'Allows you to set a player\'s level', '/setlevel <player> <level>', ['setlvl']);
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
		if($sender->hasPermission('pl.setlevel')){
			if(count($args) < 2){
				$sender->sendMessage($this->getUsage());

				return;
			}

			$level = intval($args[1]);

			if(!is_numeric($level) or !is_int($level)){
				$sender->sendMessage(RandomUtils::colorMessage('The level must be an integer.'));

				return;
			}

			if($level < 0 or $level > 100){
				$sender->sendMessage(RandomUtils::colorMessage('The level must be between 0 and 100.'));

				return;
			}

			$player = $args[0];
			$this->plugin->PrestigeSocietyLevels()->setLevel($player, $level);
			$sender->sendMessage(RandomUtils::colorMessage(str_replace(['@player', '@level'],
				[$player, $args[1]], $this->plugin->getMessage('levels', 'player_level_set'))));
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