<?php

namespace PrestigeSociety\Levels\Commands;

use _64FF00\PurePerms\PurePerms;
use FaigerSYS\GameTime\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class StatsCommand extends Command implements PluginIdentifiableCommand {

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
		parent::__construct('stats', 'Allows you to set a player\'s statistics', '/stats [player]', ['kills', 'deaths']);
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
		if(count($args) < 1){
			if($sender instanceof Player){
				$level = $this->plugin->PrestigeSocietyLevels()->getLevel($sender);
				$kills = $this->plugin->PrestigeSocietyLevels()->getKills($sender);
				$deaths = $this->plugin->PrestigeSocietyLevels()->getDeaths($sender);
				$exp = $this->plugin->PrestigeSocietyExperience->getExp($sender);
				$needed = $this->plugin->PrestigeSocietyNeeded->getNecesary($sender);
				$coins = $this->plugin->PrestigeSocietyEconomy->getMoney($sender);
				$credits = $this->plugin->PrestigeSocietyCredits->getCredits($sender);
				$tag = 'unknown';
				$job = 'unknown';
				if($this->plugin->PrestigeSocietyJobs->hasJob($sender)){
					$job = $this->plugin->PrestigeSocietyJobs->getJob($sender);
				}else{
					$job = " ";
				}
				switch(PurePerms::getAPI()->getUserDataMgr()->getGroup($sender)){
					case "Member":
						$tag = "&r&8[&7Member&8]";
						break;
					case "Admin":
						$tag = "&r&8[&4Admin&8]";
						break;
					case "Moderator":
						$tag = "&r&8[&bModerator&8]";
						break;
					case "Helper":
						$tag = "&r&8[&aHelper&8]";
						break;
					case "VIP":
						$tag = "&r&8[&9&k:&r&l&3VIP&r &7Member&9&k:&r&8]";
						break;
					case "Gold":
						$tag = "&r&8[&3Gold&r &7Member&r&8]";
						break;
				}
				$now = explode(':', Main::getAPI()->getSessionTime($sender->getName(), '%H%:%i%:%s%'));
				$all = explode(':', Main::getAPI()->getAllTime($sender->getName(), '%H%:%i%:%s%'));
				$sender->sendMessage(RandomUtils::colorMessage(str_replace(['@player', '@level', '@kills', '@deaths', '@exp', '@needed', '@coins', '@credits', '@tag', '@job',
					'@nowh', '@allh', '@nowm', '@allm', '@nows', '@alls'],
					[$sender->getName(), $level, $kills, $deaths, $exp, $needed, $coins, $credits, $tag, $job,
						$now[0], $all[0], $now[1], $all[1], $now[2], $all[2]], $this->plugin->getMessage('levels', 'player_stats'))));
			}else{
				$sender->sendMessage('Run Command in-game.');
			}

			return;
		}

		$player = Server::getInstance()->getPlayer($args[0]);

		$kills = $this->plugin->PrestigeSocietyLevels()->getKills($player);
		$deaths = $this->plugin->PrestigeSocietyLevels()->getDeaths($player);
		$level = $this->plugin->PrestigeSocietyLevels()->getLevel($player);
		$exp = $this->plugin->PrestigeSocietyExperience->getExp($player);
		$needed = $this->plugin->PrestigeSocietyNeeded->getNecesary($player);
		$coins = $this->plugin->PrestigeSocietyEconomy->getMoney($player);
		$credits = $this->plugin->PrestigeSocietyCredits->getCredits($player);
		$tag = 'unknown';
		$job = 'unknown';
		if($this->plugin->PrestigeSocietyJobs->hasJob($player)){
			$job = $this->plugin->PrestigeSocietyJobs->getJob($player);
		}else{
			$job = " ";
		}
		switch(PurePerms::getAPI()->getUserDataMgr()->getGroup($player)){
			case "Member":
				$tag = "&r&8[&7Member&8]";
				break;
			case "Admin":
				$tag = "&r&8[&4Admin&8]";
				break;
			case "Moderator":
				$tag = "&r&8[&bModerator&8]";
				break;
			case "Helper":
				$tag = "&r&8[&aHelper&8]";
				break;
			case "VIP":
				$tag = "&r&8[&9&k:&r&l&3VIP&r &7Member&9&k:&r&8]";
				break;
			case "Gold":
				$tag = "&r&8[&3Gold&r &7Member&r&8]";
				break;
		}
		$now = explode(':', Main::getAPI()->getSessionTime($player->getName(), '%H%:%i%:%s%'));
		$all = explode(':', Main::getAPI()->getAllTime($player->getName(), '%H%:%i%:%s%'));
		$sender->sendMessage(RandomUtils::colorMessage(str_replace(['@player', '@level', '@kills', '@deaths', '@exp', '@needed', '@coins', '@credits', '@tag', '@job',
			'@nowh', '@allh', '@nowm', '@allm', '@nows', '@alls'],
			[$player, $level, $kills, $deaths, $exp, $needed, $coins, $credits, $tag, $job,
				$now[0], $all[0], $now[1], $all[1], $now[2], $all[2]], $this->plugin->getMessage('levels', 'player_stats_other'))));
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