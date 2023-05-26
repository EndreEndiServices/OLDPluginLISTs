<?php

namespace PrestigeSociety\Experience;

use pocketmine\Player;
use PrestigeSociety\Core\PrestigeSocietyCore;

class PrestigeSocietyNeeded {

	/** @var \SQLite3 */
	public $db;

	/** @var PrestigeSocietyCore $core */
	protected $core;

	/**
	 *
	 * PrestigeSocietyLevels constructor.
	 *
	 * @param \SQLite3 $db
	 *
	 */
	public function __construct(\SQLite3 $db, PrestigeSocietyCore $core){
		$this->db = $db;
		$this->core = $core;
		$this->db->exec("CREATE TABLE IF NOT EXISTS needed (name VARCHAR, necesary INT);");
	}

	/**
	 *
	 * @param $p
	 * @param $needed
	 *
	 */
	public function addNecesary($p, $needed){
		$this->setNecesary($p, $this->getNecesary($p) + $needed);
	}

	/**
	 *
	 * @API
	 *
	 * @param $p
	 *
	 * @param $necesary
	 *
	 */
	public function setNecesary($p, $necesary){
		if($p instanceof Player){
			$name = $p->getLowerCaseName();
		}else{
			$name = strtolower($p);
		}

		if($this->playerExists($p)){
			$this->db->querySingle("UPDATE needed SET necesary = '{$necesary}' WHERE name = '{$name}';", true);
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
			$name = $p->getLowerCaseName();
		}else{
			$name = strtolower($p);
		}

		$query = $this->db->query("SELECT * FROM needed WHERE name='{$name}';");
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
	 * @return int
	 *
	 */
	public function getNecesary($p){
		if($this->playerExists($p)){
			if($p instanceof Player){
				$name = $p->getLowerCaseName();
			}else{
				$name = strtolower($p);
			}
			$query = $this->db->query("SELECT * FROM needed WHERE name = '{$name}';");
			while($row = $query->fetchArray(SQLITE3_ASSOC)){
				return $row['necesary'];
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
			$name = $p->getLowerCaseName();
		}else{
			$name = strtolower($p);
		}
		$zero = 0;

		$query = $this->db->prepare("INSERT INTO needed (name, necesary) VALUES (:name, :necesary);");
		$query->bindParam(':name', $name);
		$query->bindParam(':necesary', $zero);
		$query->execute();
	}

	/**
	 *
	 * @param $from
	 * @param $to
	 * @param $needed
	 *
	 * @return bool
	 *
	 */
	public function payNecesary($from, $to, $needed){
		$fromCredits = $this->getNecesary($from);
		if(!($fromCredits <= $needed)){
			$this->setNecesary($to, $needed);
			$this->subtractNecesary($from, $needed);

			return true;
		}

		return false;
	}

	/**
	 *
	 * @param $p
	 * @param $needed
	 *
	 * @return bool
	 *
	 */
	public function subtractNecesary($p, $needed){
		if($this->getNecesary($p) - $needed >= 0){
			$this->setNecesary($p, $this->getNecesary($p) - $needed);

			return true;
		}

		return false;
	}

}