<?php

namespace PrestigeSociety\Ranks\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\plugin\Plugin;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class SetRankCommand extends Command implements PluginIdentifiableCommand {

	/** @var PrestigeSocietyCore */
	protected $core;

	public function __construct(PrestigeSocietyCore $core){
		parent::__construct("setrank", "Set a player's rank!", RandomUtils::colorMessage("&eUsage: /setrank <player> <rank>"), []);
		$this->setPermission("command.setrank");
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
		if(!$this->testPermission($sender)){
			return;
		}

		if(count($args) < 2){
			$sender->sendMessage($this->getUsage());

			return;
		}

		$player = $args[0];
		$rank = $args[1];

		$res = $this->core->PrestigeSocietyRanks->setRank($player, $rank);

		if($res){
			$message = $this->core->getMessage('ranks', 'set_rank');
			$message = str_replace(["@player", "@rank"], [$player, $rank], $message);
			$sender->sendMessage(RandomUtils::colorMessage($message));
		}else{
			$message = $this->core->getMessage('ranks', 'invalid_rank_name');
			$sender->sendMessage(RandomUtils::colorMessage($message));
		}

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