<?php

namespace PrestigeSociety\Factions;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\level\Position;
use pocketmine\plugin\Plugin;
use PrestigeSociety\Core\PrestigeSocietyCore;

class TpWorldCommand extends Command implements PluginIdentifiableCommand {

	/** @var PrestigeSocietyCore */
	protected $plugin;

	/**na
	 *
	 * StatsCommand constructor.
	 *
	 * @param PrestigeSocietyCore $c
	 *
	 */
	public function __construct(PrestigeSocietyCore $c){
		parent::__construct('tpworld', 'About XP System', '/xpinfo', ['xpi', 'expinfo']);
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
		if($sender instanceof ConsoleCommandSender){
			$player = $this->plugin->getServer()->getPlayer($args[0]);
			$x = $args[1];
			$y = $args[2];
			$z = $args[3];
			$level = $this->plugin->getServer()->getLevelByName($args[4]);
			$player->teleport(new Position($x, $y, $z, $level));

			return;
		}else{
			return;
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