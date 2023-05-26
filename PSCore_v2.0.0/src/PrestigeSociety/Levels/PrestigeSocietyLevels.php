<?php

namespace PrestigeSociety\Levels;

use _64FF00\PurePerms\PurePerms;
use pocketmine\Player;
use PrestigeSociety\Core\PrestigeSocietyCore;

class PrestigeSocietyLevels {

	/** @var bool */
	public static $onCoolDown = false;

	/** @var String[] */
	public $players = [];

	/** @var \SQLite3 */
	public $db;

	/** @var PrestigeSocietyCore */
	protected $core;

	/**
	 *
	 * PrestigeSocietyLevels constructor.
	 *
	 * @param PrestigeSocietyCore $core
	 * @param \SQLite3 $db
	 *
	 */
	public function __construct(PrestigeSocietyCore $core, \SQLite3 $db){
		$this->core = $core;
		$this->db = $db;
		$this->db->exec("CREATE TABLE IF NOT EXISTS levels (name VARCHAR, level INT, deaths INT, kills INT);");
	}

	/**
	 *
	 * @API
	 *
	 * @param $p
	 *
	 * @param $level
	 *
	 */
	public function setLevel($p, $level){
		if($p instanceof Player){
			$name = $p->getName();
		}else{
			$name = $p;
		}

		if($this->playerExists($p)){
			$this->db->querySingle("UPDATE levels SET level = '{$level}' WHERE name = '{$name}' COLLATE NOCASE;", true);
		}
	}

	/**
	 *
	 * @API
	 *
	 * @param $p
	 *
	 * @return bool
	 *
	 */
	public function playerExists($p){
		if($p instanceof Player){
			$name = $p->getName();
		}else{
			$name = $p;
		}

		$query = $this->db->query("SELECT * FROM levels WHERE name='{$name}' COLLATE NOCASE;");
		$fetch = $query->fetchArray(SQLITE3_ASSOC);

		if(is_array($fetch) && count($fetch) != 0){
			return true;
		}

		return false;
	}

	/**
	 *
	 * @API
	 *
	 * @param $p
	 *
	 * @param $deaths
	 *
	 */
	public function setDeaths($p, $deaths){
		if($p instanceof Player){
			$name = $p->getName();
		}else{
			$name = $p;
		}

		if($this->playerExists($p)){
			$this->db->querySingle("UPDATE levels SET deaths = '{$deaths}' WHERE name = '{$name}' COLLATE NOCASE;", true);
		}
	}

	/**
	 *
	 * @API
	 *
	 * @param $p
	 *
	 * @param $kills
	 *
	 */
	public function setKills($p, $kills){
		if($p instanceof Player){
			$name = $p->getName();
		}else{
			$name = $p;
		}

		if($this->playerExists($p)){
			$this->db->querySingle("UPDATE levels SET kills = '{$kills}' WHERE name = '{$name}' COLLATE NOCASE;", true);
		}
	}

	/**
	 *
	 * @API
	 *
	 * @param $p
	 *
	 * @return int
	 *
	 */
	public function getLevel($p){
		if($this->playerExists($p)){
			if($p instanceof Player){
				$name = $p->getName();
			}else{
				$name = $p;
			}
			$query = $this->db->query("SELECT * FROM levels WHERE name = '{$name}' COLLATE NOCASE;");
			while($row = $query->fetchArray(SQLITE3_ASSOC)){
				return $row['level'];
			}
		}
		$this->addNewPlayer($p);

		return 0;
	}

	/**
	 *
	 * @API
	 *
	 * @param $p
	 *
	 */
	public function addNewPlayer($p){
		if($p instanceof Player){
			$name = $p->getName();
		}else{
			$name = $p;
		}
		$zero = 0;

		$query = $this->db->prepare("INSERT INTO levels (name, level, deaths, kills) VALUES (:name, :level, :deaths, :kills);");
		$query->bindParam(':name', $name);
		$query->bindParam(':level', $zero);
		$query->bindParam(':deaths', $zero);
		$query->bindParam(':kills', $zero);
		$query->execute();
	}

	/**
	 *
	 * @API
	 *
	 * @param $p
	 *
	 * @return int
	 *
	 */
	public function getDeaths($p){
		if($this->playerExists($p)){
			if($p instanceof Player){
				$name = $p->getName();
			}else{
				$name = $p;
			}
			$query = $this->db->query("SELECT * FROM levels WHERE name = '{$name}' COLLATE NOCASE;");
			while($row = $query->fetchArray(SQLITE3_ASSOC)){
				return $row['deaths'];
			}
		}
		$this->addNewPlayer($p);

		return 0;
	}

	/**
	 *
	 * @API
	 *
	 * @param $p
	 *
	 * @return int
	 *
	 */
	public function getKills($p){
		if($this->playerExists($p)){
			if($p instanceof Player){
				$name = $p->getName();
			}else{
				$name = $p;
			}
			$query = $this->db->query("SELECT * FROM levels WHERE name = '{$name}' COLLATE NOCASE;");
			while($row = $query->fetchArray(SQLITE3_ASSOC)){
				return $row['kills'];
			}
		}
		$this->addNewPlayer($p);

		return 0;
	}

	/**
	 *
	 * @param int $amount
	 *
	 * @return int[]
	 *
	 */
	public function getTopKills(int $amount){
		$out = [];

		$f = $this->db->query("SELECT name, kills FROM levels ORDER BY kills DESC LIMIT $amount;");
		while($row = $f->fetchArray(SQLITE3_ASSOC)){
			$out[] = $row;
		}

		return $out;
	}

	/**
	 *
	 * @param int $amount
	 *
	 * @return int[][]
	 *
	 */
	public function getTopDeaths(int $amount){
		$out = [];

		$f = $this->db->query("SELECT name, deaths FROM levels ORDER BY deaths DESC LIMIT $amount;");
		while($row = $f->fetchArray(SQLITE3_ASSOC)) $out[] = $row;

		return $out;
	}
}