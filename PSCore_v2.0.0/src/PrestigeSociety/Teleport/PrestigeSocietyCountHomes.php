<?php

namespace PrestigeSociety\Teleport;

use pocketmine\Player;

class PrestigeSocietyCountHomes {

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
		$this->db->exec("CREATE TABLE IF NOT EXISTS counthomes (name VARCHAR, counthome INT);");
	}

	/**
	 *
	 * @param $p
	 * @param $count
	 *
	 */
	public function addCount($p, $count){
		$this->setCount($p, $this->getCount($p) + $count);
	}

	/**
	 *
	 * @API
	 *
	 * @param $p
	 *
	 * @param $count
	 *
	 */
	public function setCount($p, $count){
		if($p instanceof Player){
			$name = $p->getLowerCaseName();
		}else{
			$name = strtolower($p);
		}

		if($this->playerExists($p)){
			$this->db->querySingle("UPDATE counthomes SET counthome = '{$count}' WHERE name = '{$name}';", true);
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

		$query = $this->db->query("SELECT * FROM counthomes WHERE name='{$name}';");
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
	public function getCount($p){
		if($this->playerExists($p)){
			if($p instanceof Player){
				$name = $p->getLowerCaseName();
			}else{
				$name = strtolower($p);
			}
			$query = $this->db->query("SELECT * FROM counthomes WHERE name = '{$name}';");
			while($row = $query->fetchArray(SQLITE3_ASSOC)){
				return $row['counthome'];
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

		$query = $this->db->prepare("INSERT INTO counthomes (name, counthome) VALUES (:name, :counthome);");
		$query->bindParam(':name', $name);
		$query->bindParam(':counthome', $zero);
		$query->execute();
	}

	/**
	 *
	 * @param $from
	 * @param $to
	 * @param $count
	 *
	 * @return bool
	 *
	 */
	public function payCount($from, $to, $count){
		$fromMoney = $this->getCount($from);
		if(!($fromMoney <= $count)){
			$this->setCount($to, $count);
			$this->subtractCount($from, $count);

			return true;
		}

		return false;
	}

	/**
	 *
	 * @param $p
	 * @param $count
	 *
	 * @return bool
	 *
	 */
	public function subtractCount($p, $count){
		if($this->getCount($p) - $count >= 0){
			$this->setCount($p, $this->getCount($p) - $count);

			return true;
		}

		return false;
	}

}