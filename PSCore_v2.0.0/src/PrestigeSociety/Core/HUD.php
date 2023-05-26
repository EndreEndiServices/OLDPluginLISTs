<?php

namespace PrestigeSociety\Core;

use pocketmine\Player;
use PrestigeSociety\Core\Utils\RandomUtils;

class HUD {

	/** @var Player[] */
	protected $players = [];

	/** @var PrestigeSocietyCore */
	protected $core;

	/** @var string */
	protected $message = '';

	/**
	 *
	 * HUD constructor.
	 *
	 * @param PrestigeSocietyCore $core
	 *
	 */
	public function __construct(PrestigeSocietyCore $core){
		$this->core = $core;
		$this->message = implode("\n", $core->getConfig()->get('HUD')['message']);
		$this->message = str_replace("@s", str_repeat(" ", 40), $this->message);
	}

	public function broadcastHUD(){
		foreach($this->players as $player){
			$player->sendTip($this->formatStats($player, $this->message));
		}
	}

	/**
	 *
	 * @param Player $player
	 * @param string $message
	 *
	 * @return mixed|string
	 *
	 */
	public function formatStats(Player $player, string $message){
		$psLevels = $this->core->PrestigeSocietyLevels();
		$kills = $psLevels->getKills($player);
		$deaths = $psLevels->getDeaths($player);
		$level = $psLevels->getLevel($player);
		$ranks = $this->core->PrestigeSocietyRanks;
		$cRank = $ranks->getRank($player);
		$nRank = $ranks->getNextRank($player);
		$nRankCost = $ranks->getNextRankPrice($player);
		$money = $this->core->getPrestigeSocietyEconomy()->getMoney($player);

		$search = ["@kills", "@deaths", "@level", "@rank", "@next_rank_cost", "@next_rank", "@money"];
		$replace = [$kills, $deaths, $level, $cRank, $nRankCost, $nRank, $money];

		$message = str_replace($search, $replace, $message);

		$message = RandomUtils::colorMessage($message);

		return $message;
	}

	/**
	 *
	 * @param Player $player
	 *
	 */
	public function toggleHUD(Player $player){
		if($this->inPlayers($player)){
			$this->removePlayer($player);

			$player->sendPopup(RandomUtils::colorMessage("&6--- &7HUD disabled! &6---"));
		}else{
			$this->addPlayer($player);

			$player->sendPopup(RandomUtils::colorMessage("&6--- &eHUD enabled! &6---"));
		}
	}

	/**
	 *
	 * @param Player $player
	 *
	 * @return bool
	 *
	 */
	public function inPlayers(Player $player){
		return isset($this->players[$player->getXuid()]);
	}

	/**
	 *
	 * @param Player $player
	 *
	 */
	public function removePlayer(Player $player){
		if($this->inPlayers($player)){
			unset($this->players[$player->getXuid()]);
		}
	}

	/**
	 *
	 * @param Player $player
	 *
	 */
	public function addPlayer(Player $player){
		$this->players[$player->getXuid()] = $player;
	}

}