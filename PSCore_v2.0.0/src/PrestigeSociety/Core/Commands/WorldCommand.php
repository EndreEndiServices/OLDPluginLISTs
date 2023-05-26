<?php

namespace PrestigeSociety\Core\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use PrestigeSociety\Core\PrestigeSocietyCore;

class WorldCommand extends Command implements PluginIdentifiableCommand {

	/** @var PrestigeSocietyCore */
	protected $plugin;

	public function __construct(PrestigeSocietyCore $c){
		parent::__construct('world', 'teleport to another world', '/world <world>', ['cw']);
		$this->plugin = $c;
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param string[] $args
	 *
	 * @return mixed|void
	 */
	public function execute(CommandSender $sender, $commandLabel, array $args){
		if($sender instanceof Player and $sender->hasPermission('pl.world')){
			if(!isset($args[0])){
				$sender->sendMessage($this->getUsage());

				return;
			}

			if(strtolower($args[0]) === 'list'){
				foreach($this->plugin->getServer()->getLevels() as $lvl){
					$sender->sendMessage('- ' . $lvl->getName() . '');
				}

				return;
			}

			if(!$this->plugin->getServer()->isLevelLoaded($args[0])){
				$this->plugin->getServer()->loadLevel($args[0]);

				$level = $this->plugin->getServer()->getLevelByName($args[0]);
				if($level !== null){
					$sender->sendMessage('Teleporting...');
					$sender->teleport($level->getSpawnLocation());
				}else{
					$sender->sendMessage('Level does not exist.');
				}
			}else{
				$level = $this->plugin->getServer()->getLevelByName($args[0]);
				if($level !== null){
					$sender->sendMessage('Teleporting...');
					$sender->teleport($level->getSpawnLocation());
				}else{
					$sender->sendMessage('Level does not exist.');
				}
			}
		}
	}

	/**
	 * @return PrestigeSocietyCore
	 */
	public function getPlugin(): Plugin{
		return $this->plugin;
	}
}