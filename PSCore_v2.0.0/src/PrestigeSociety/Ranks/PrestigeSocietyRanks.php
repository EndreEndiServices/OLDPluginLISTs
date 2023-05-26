<?php

namespace PrestigeSociety\Ranks;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use PrestigeSociety\Core\PrestigeSocietyCore;

class PrestigeSocietyRanks {

	/** @var \SQLite3 */
	public $db;

	/** @var PrestigeSocietyCore */
	protected $core;

	/** @var array */
	protected $ranks = [];
	/** @var array */
	protected $rankNames = [];
	/** @var array */
	protected $rankIndexes = [];
	/** @var array */
	protected $rankPrices = [];
	/** @var array */
	protected $rankCommands = [];

	/**
	 *
	 * PrestigeSocietyLevels constructor.
	 *
	 * @param \SQLite3 $db
	 * @param PrestigeSocietyCore $core
	 * @param array $ranks
	 *
	 */
	public function __construct(\SQLite3 $db, PrestigeSocietyCore $core, array $ranks){
		$this->db = $db;
		$this->db->exec("CREATE TABLE IF NOT EXISTS ranks (username VARCHAR, rank VARCHAR);");

		$this->core = $core;

		$i = 0;

		$this->ranks = $ranks;

		foreach($ranks as $rank => $price){
			$this->rankNames[$i] = $rank;
			$this->rankIndexes[$rank] = $i;
			$this->rankPrices[$i] = $price['price'];
			$this->rankCommands[$i] = $price['commands'];
			++$i;
		}
	}


	/**
	 *
	 * @return string
	 *
	 */
	public function getFirstRank(){
		return $this->rankNames[0];
	}

	/**
	 *
	 * @param        $p
	 * @param string $rank
	 *
	 * @return bool
	 *
	 */
	public function playerHasRank($p, string $rank){
		if(!$this->isPlayerRegistered($p)){
			return false;
		}

		if($p instanceof Player){
			$name = $p->getLowerCaseName();
		}else{
			$name = strtolower($p);
		}

		$query = $this->db->query("SELECT rank FROM levels WHERE name='{$name}';");

		while($q = $query->fetchArray(SQLITE3_ASSOC)){
			return ($q['rank'] === $rank);
		}

		return false;
	}

	/**
	 *
	 * @param $p
	 *
	 * @return bool
	 *
	 */
	public function isPlayerRegistered($p){
		if($p instanceof Player){
			$name = $p->getLowerCaseName();
		}else{
			$name = strtolower($p);
		}

		$query = $this->db->query("SELECT * FROM ranks WHERE username = '{$name}';");
		$fetch = $query->fetchArray(SQLITE3_ASSOC);

		if(is_array($fetch) && count($fetch) != 0){
			return true;
		}

		return false;
	}

	/**
	 *
	 * @param $p
	 *
	 * @return int
	 *
	 */
	public function rankUp($p){
		$rank = $this->getRank($p);

		if($rank === null){
			$this->registerPlayer($p);
		}

		$rank = $this->getRank($p);

		$money = $this->core->PrestigeSocietyEconomy->getMoney($p);
		$nextRankPrice = $this->getNextRankPrice($p);

		if($money < $nextRankPrice){
			return 2;
		}

		$last = $this->getLastRank();

		if($last === $rank){
			return 1;
		}

		$nextRank = $this->getNextRank($p);

		$this->setRank($p, $nextRank);

		$index = $this->rankIndexes[$nextRank];

		$this->core->PrestigeSocietyEconomy->subtractMoney($p, $nextRankPrice);

		foreach($this->rankCommands[$index] as $command){
			$this->core->getServer()->dispatchCommand(new ConsoleCommandSender(), str_replace("@player", ($p instanceof Player) ? $p->getName() : $p, $command));
		}

		return 0;
	}

	/**
	 *
	 * @param $p
	 *
	 * @return string|null
	 *
	 */
	public function getRank($p){
		if($p instanceof Player){
			$name = $p->getLowerCaseName();
		}else{
			$name = strtolower($p);
		}

		$query = $this->db->query("SELECT rank FROM ranks WHERE username='{$name}';");

		while($q = $query->fetchArray(SQLITE3_ASSOC)){
			return $q['rank'];
		}

		return null;
	}

	/**
	 *
	 * @param        $p
	 * @param string $defaultRank
	 *
	 */
	public function registerPlayer($p, string $defaultRank = null){
		if($p instanceof Player){
			$name = $p->getLowerCaseName();
		}else{
			$name = strtolower($p);
		}

		$defaultRank = $defaultRank ?? $this->rankNames[0] ?? 'MP1';

		$query = $this->db->prepare("INSERT INTO ranks (username, rank) VALUES (?, ?);");
		$query->bindParam(1, $name);
		$query->bindParam(2, $defaultRank);
		$query->execute();
	}

	/**
	 *
	 * @param $p
	 *
	 * @return mixed|null|string
	 *
	 */
	public function getNextRankPrice($p){
		$rank = $this->getRank($p);
		$rankIndex = $this->rankIndexes[$rank];

		return $this->rankPrices[$rankIndex + 1] ?? 0;
	}

	/**
	 *
	 * @return mixed
	 *
	 */
	public function getLastRank(){
		return end($this->rankNames);
	}

	/**
	 *
	 * @param $p
	 *
	 * @return mixed|null|string
	 *
	 */
	public function getNextRank($p){
		$rank = $this->getRank($p);
		$rankIndex = $this->rankIndexes[$rank];

		return $this->rankNames[$rankIndex + 1] ?? $rank;
	}

	/**
	 *
	 * @param        $p
	 * @param string $rank
	 *
	 * @return bool
	 *
	 */
	public function setRank($p, string $rank){

		if($p instanceof Player){
			$name = $p->getLowerCaseName();
		}else{
			$name = strtolower($p);
		}

		if(!isset($this->rankIndexes[$rank])){
			return false;
		}

		if(!$this->isPlayerRegistered($p)){
			$this->registerPlayer($p, $rank);

			return true;
		}

		$this->db->querySingle("UPDATE ranks SET rank = '{$rank}' WHERE username = '{$name}';");

		return true;
	}
}