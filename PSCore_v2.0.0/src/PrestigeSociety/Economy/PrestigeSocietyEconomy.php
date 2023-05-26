<?php

namespace PrestigeSociety\Economy;

use pocketmine\Player;

class PrestigeSocietyEconomy {

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
		$this->db->exec("CREATE TABLE IF NOT EXISTS economy (name VARCHAR, money INT);");
	}

	/**
	 *
	 * @param $p
	 * @param $money
	 *
	 */
	public function addMoney($p, $money){
		$this->setMoney($p, $this->getMoney($p) + $money);
	}

	/**
	 *
	 * @API
	 *
	 * @param $p
	 *
	 * @param $money
	 *
	 */
	public function setMoney($p, $money){
		if($p instanceof Player){
			$name = $p->getLowerCaseName();
		}else{
			$name = strtolower($p);
		}

		if($this->playerExists($p)){
			$this->db->querySingle("UPDATE economy SET money = '{$money}' WHERE name = '{$name}';", true);
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

		$query = $this->db->query("SELECT * FROM economy WHERE name='{$name}';");
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
	public function getMoney($p){
		if($this->playerExists($p)){
			if($p instanceof Player){
				$name = $p->getLowerCaseName();
			}else{
				$name = strtolower($p);
			}
			$query = $this->db->query("SELECT * FROM economy WHERE name = '{$name}';");
			while($row = $query->fetchArray(SQLITE3_ASSOC)){
				return $row['money'];
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

		$query = $this->db->prepare("INSERT INTO economy (name, money) VALUES (:name, :money);");
		$query->bindParam(':name', $name);
		$query->bindParam(':money', $zero);
		$query->execute();
	}

	/**
	 *
	 * @param $from
	 * @param $to
	 * @param $money
	 *
	 * @return bool
	 *
	 */
	public function payMoney($from, $to, $money){
		$fromMoney = $this->getMoney($from);
		if(!($fromMoney <= $money)){
			$this->setMoney($to, $money);
			$this->subtractMoney($from, $money);

			return true;
		}

		return false;
	}

	/**
	 *
	 * @param $p
	 * @param $money
	 *
	 * @return bool
	 *
	 */
	public function subtractMoney($p, $money){
		if($this->getMoney($p) - $money >= 0){
			$this->setMoney($p, $this->getMoney($p) - $money);

			return true;
		}

		return false;
	}

}