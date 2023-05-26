<?php

namespace PrestigeSociety\Chat;

use _64FF00\PurePerms\PurePerms;
use factions\entity\Faction;
use factions\entity\IMember;
use factions\FactionsPE;
use factions\manager\Factions;
use pocketmine\Player;
use PrestigeSociety\Chat\Handle\Sessions;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\exc;
use PrestigeSociety\Core\Utils\RandomUtils;

class PrestigeSocietyChat {


	/** @var PrestigeSocietyCore */
	protected $core;
	/** @var array */
	protected $chatFormat;

	public function __construct(PrestigeSocietyCore $core){
		$this->core = $core;
		$this->chatFormat = yaml_parse_file($core->getDataFolder() . "chat_format.yml");
	}

	public function reloadChatFormat(){
		$this->chatFormat = yaml_parse_file($this->core->getDataFolder() . "chat_format.yml");
	}

	/**
	 *
	 * @param Player $p
	 * @param int $coolDown
	 *
	 * @return bool
	 *
	 */
	public function filterSpam(Player $p, $coolDown = 1){
		if(!Sessions::isOnCoolDown($p)){

			Sessions::addCoolDown($p);

			return false;
		}else{
			if(time() - Sessions::getCoolDown($p) <= $coolDown){
				Sessions::refreshCoolDown($p);

				return true;
			}else{
				Sessions::refreshCoolDown($p);

				return false;
			}
		}
	}

	/**
	 *
	 * @param $message
	 *
	 * @return bool
	 *
	 */
	public function filterBadWords($message){
		$c = false;
		foreach($this->core->getConfig()->getAll()["anti_swear"]["profanity"] as $pf){
			$message = exc::replaceAllKeepLetters($message);
			$message = exc::cleanString($message);
			$message = str_replace(" ", "", $message);
			if(stripos($message, $pf) !== false){
				$c = true;
			}
		}

		return $c;
	}

	/**
	 *
	 * @param Player $player
	 *
	 * @return string
	 *
	 */
	public function formatDisplayName(Player $player){
		$group = 'unknown';

		$kills = $this->core->PrestigeSocietyLevels->getKills($player);
		$deaths = $this->core->PrestigeSocietyLevels->getDeaths($player);
		$level = $this->core->PrestigeSocietyLevels->getLevel($player);
		$rank = $this->core->PrestigeSocietyRanks->getRank($player);
		$faction = "";
		$rankfaction = "";
		if(Factions::getForPlayer($player) == null){
			$faction = " ";
			$rankfaction = " ";
		}else{
			foreach(Factions::getForPlayer($player)->getMembers() as $member){
				$rankfaction = $member->getRole();
			}
			if($leader = Factions::getForPlayer($player)->getLeader() == $player){
				$rankfaction = "**";
			}
		}
		$pp = $player->getServer()->getPluginManager()->getPlugin("PurePerms");

		if($pp instanceof PurePerms){
			$group = $pp->getUserDataMgr()->getGroup($player);
			if($group !== null){
				$group = $group->getName();
			}
		}

		$name = $player->getName();

		$job = 'unknown';
		if($this->core->PrestigeSocietyJobs->hasJob($player)){
			$job = $this->core->PrestigeSocietyJobs->getJob($player);
		}else{
			$job = " ";
		}

		if($this->core->PrestigeSocietyNicks->hasNick($player)){
			$name = "~" . $this->core->PrestigeSocietyNicks->getNick($player);
		}

		$groupFormat = $this->chatFormat['chat_format'][$group]['display'];
		$message = str_replace(["@kills", "@deaths", "@level", "@rank", "@group", "@player", "@faction", "@rank_faction", "@job"],
			[$kills, $deaths, $level, $rank, $group, $name, $faction, $rankfaction, $job],
			$groupFormat);
		$message = RandomUtils::colorMessage($message);

		return $message;
	}

	/**
	 *
	 * @param Player $player
	 * @param string $message
	 * @return string
	 *
	 * @throws \InvalidStateException
	 */
	public function formatMessage(Player $player, string $message){
		$group = 'unknown';

		$kills = $this->core->PrestigeSocietyLevels->getKills($player);
		$deaths = $this->core->PrestigeSocietyLevels->getDeaths($player);
		$level = $this->core->PrestigeSocietyLevels->getLevel($player);
		$rank = $this->core->PrestigeSocietyRanks->getRank($player);
		$pp = $player->getServer()->getPluginManager()->getPlugin("PurePerms");
		$faction = "";
		$rankfaction = "";
		if(Factions::getForPlayer($player) == null){
			$faction = " ";
			$rankfaction = " ";
		}else{
			foreach(Factions::getForPlayer($player)->getMembers() as $member){
				$faction = Factions::getForPlayer($player)->getName();
				$rankfaction = $member->getRole();
			}
			if($leader = Factions::getForPlayer($player)->getLeader() == $player){
				$rankfaction = "**";
			}
		}

		if($pp instanceof PurePerms){
			$group = $pp->getUserDataMgr()->getGroup($player);
			if($group !== null){
				$group = $group->getName();
			}
		}

		$name = $player->getName();

		if($this->core->PrestigeSocietyNicks->hasNick($player)){
			$name = "~" . $this->core->PrestigeSocietyNicks->getNick($player);
		}

		if(!$player->hasPermission("chat.format")){
			$message = exc::clearColors($message);
		}

		$job = 'unknown';
		if($this->core->PrestigeSocietyJobs->hasJob($player)){
			$job = $this->core->PrestigeSocietyJobs->getJob($player);
		}else{
			$job = " ";
		}

		$groupFormat = $this->chatFormat['chat_format'][$group]['chat'];
		$message = str_replace(["@kills", "@deaths", "@level", "@rank", "@group", "@player", "@faction", "@rank_faction", "@message", "@job"],
			[$kills, $deaths, $level, $rank, $group, $name, $faction, $rankfaction, $message, $job],
			$groupFormat);
		$message = RandomUtils::colorMessage($message);

		return $message;
	}
}