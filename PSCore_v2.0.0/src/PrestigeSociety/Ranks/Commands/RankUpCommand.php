<?php

namespace PrestigeSociety\Ranks\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class RankUpCommand extends Command implements PluginIdentifiableCommand {

	/** @var PrestigeSocietyCore */
	protected $core;

	public function __construct(PrestigeSocietyCore $core){
		parent::__construct("rankup", "Try a rank up!", "Usage: /rankup", []);
		$this->setPermission("command.rankup");
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
		if(!$sender instanceof Player){
			$sender->sendMessage(TextFormat::RED . "Please use this command in-game.");

			return;
		}

		if(!$this->testPermission($sender)){
			return;
		}

		$result = $this->core->PrestigeSocietyRanks->rankUp($sender);

		$message = '';

		if($result === 2){
			$message = 'non_sufficient_funds';
		}elseif($result === 1){
			$message = 'already_highest_rank';
		}elseif($result === 0){
			$rank = $this->core->PrestigeSocietyRanks->getRank($sender);
			$message = $this->core->getMessage('ranks', 'ranked_up');
			$message = str_replace(["@player", "@rank"], [$sender->getName(), $rank], $message);
			$sender->getServer()->broadcastMessage(RandomUtils::colorMessage($message));

			return;
		}

		$message = $this->core->getMessage('ranks', $message);
		$message = str_replace("@rank_up_price", $this->core->PrestigeSocietyRanks->getNextRankPrice($sender), $message);
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