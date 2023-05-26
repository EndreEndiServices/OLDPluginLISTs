<?php

namespace PrestigeSociety\Ranks\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\plugin\Plugin;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class SeeRankCommand extends Command implements PluginIdentifiableCommand {

	/** @var PrestigeSocietyCore */
	protected $core;

	public function __construct(PrestigeSocietyCore $core){
		parent::__construct("seerank", "See another player's rank!", RandomUtils::colorMessage("&eUsage: /seerank <player>"), ["viewrank", "vrank"]);
		$this->core = $core;
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
			$sender->sendMessage($this->getUsage());

			return;
		}

		$rank = $this->core->PrestigeSocietyRanks->getRank($args[0]);

		if($rank === null){
			$message = $this->core->getMessage('ranks', 'player_not_found');
			$sender->sendMessage(RandomUtils::colorMessage($message));

			return;
		}

		$message = $this->core->getMessage('ranks', 'player_rank');
		$message = str_replace(["@rank", "@player"], [$rank, $args[0]], $message);
		$sender->sendMessage(RandomUtils::colorMessage($message));

		return;
	}

	/**
	 *
	 * @return Plugin
	 *
	 */
	public function getPlugin(): Plugin{
		return $this->core;
	}
}