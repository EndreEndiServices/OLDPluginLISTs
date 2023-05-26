<?php

namespace PrestigeSociety\Credits;

use pocketmine\Player;

class PrestigeSocietyCredits {

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
		$this->db->exec("CREATE TABLE IF NOT EXISTS credits (name VARCHAR, coins INT);");
	}

	/**
	 *
	 * @param $p
	 * @param $credits
	 *
	 */
	public function addCredits($p, $credits){
		$this->setCredits($p, $this->getCredits($p) + $credits);
	}

	/**
	 *
	 * @API
	 *
	 * @param $p
	 *
	 * @param $coins
	 *
	 */
	public function setCredits($p, $coins){
		if($p instanceof Player){
			$name = $p->getLowerCaseName();
		}else{
			$name = strtolower($p);
		}

		if($this->playerExists($p)){
			$this->db->querySingle("UPDATE credits SET coins = '{$coins}' WHERE name = '{$name}';", true);
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

		$query = $this->db->query("SELECT * FROM credits WHERE name='{$name}';");
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
	public function getCredits($p){
		if($this->playerExists($p)){
			if($p instanceof Player){
				$name = $p->getLowerCaseName();
			}else{
				$name = strtolower($p);
			}
			$query = $this->db->query("SELECT * FROM credits WHERE name = '{$name}';");
			while($row = $query->fetchArray(SQLITE3_ASSOC)){
				return $row['coins'];
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

		$query = $this->db->prepare("INSERT INTO credits (name, coins) VALUES (:name, :coins);");
		$query->bindParam(':name', $name);
		$query->bindParam(':coins', $zero);
		$query->execute();
	}

	/**
	 *
	 * @param $from
	 * @param $to
	 * @param $credits
	 *
	 * @return bool
	 *
	 */
	public function payCredits($from, $to, $credits){
		$fromCredits = $this->getCredits($from);
		if(!($fromCredits <= $credits)){
			$this->setCredits($to, $credits);
			$this->subtractCredits($from, $credits);

			return true;
		}

		return false;
	}

	/**
	 *
	 * @param $p
	 * @param $credits
	 *
	 * @return bool
	 *
	 */
	public function subtractCredits($p, $credits){
		if($this->getCredits($p) - $credits >= 0){
			$this->setCredits($p, $this->getCredits($p) - $credits);

			return true;
		}

		return false;
	}

}