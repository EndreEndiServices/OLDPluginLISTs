<?php

namespace PrestigeSociety\Factions;

use pocketmine\Player;

class PrestigeSocietyFactions {

	/** @var \SQLite3 */
	public $db;

	/**
	 *
	 * PrestigeSocietyLevels constructor.
	 *
	 * @param \SQLite3 $db
	 *
	 */
	public function __construct(\SQLite3 $db){
		$this->db = $db;
		$this->db->exec("CREATE TABLE IF NOT EXISTS factions (name VARCHAR, slots INT);");
	}

	/**
	 *
	 * @param $p
	 * @param $slots
	 *
	 */
	public function addSlots($p, $slots){
		$this->setSlots($p, $this->getSlots($p) + $slots);
	}

	/**
	 *
	 * @API
	 *
	 * @param $p
	 *
	 * @param $slots
	 *
	 */
	public function setSlots($p, $slots){
		if($p instanceof Player){
			$name = $p->getLowerCaseName();
		}else{
			$name = strtolower($p);
		}

		if($this->playerExists($p)){
			$this->db->querySingle("UPDATE factions SET slots = '{$slots}' WHERE name = '{$name}';", true);
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

		$query = $this->db->query("SELECT * FROM factions WHERE name='{$name}';");
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
	public function getSlots($p){
		if($this->playerExists($p)){
			if($p instanceof Player){
				$name = $p->getLowerCaseName();
			}else{
				$name = strtolower($p);
			}
			$query = $this->db->query("SELECT * FROM factions WHERE name = '{$name}';");
			while($row = $query->fetchArray(SQLITE3_ASSOC)){
				return $row['slots'];
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
		$zero = 5;

		$query = $this->db->prepare("INSERT INTO factions (name, slots) VALUES (:name, :slots);");
		$query->bindParam(':name', $name);
		$query->bindParam(':slots', $zero);
		$query->execute();
	}

	/**
	 *
	 * @param $from
	 * @param $to
	 * @param $slots
	 *
	 * @return bool
	 *
	 */
	public function paySlots($from, $to, $slots){
		$fromCredits = $this->getSlots($from);
		if(!($fromCredits <= $slots)){
			$this->setSlots($to, $slots);
			$this->subtractSlots($from, $slots);

			return true;
		}

		return false;
	}

	/**
	 *
	 * @param $p
	 * @param $slots
	 *
	 * @return bool
	 *
	 */
	public function subtractSlots($p, $slots){
		if($this->getSlots($p) - $slots >= 0){
			$this->setSlots($p, $this->getSlots($p) - $slots);

			return true;
		}

		return false;
	}

}