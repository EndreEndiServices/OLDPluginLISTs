<?php

namespace PrestigeSociety\Enchantments;

use pocketmine\Player;

class PrestigeSocietyEnchantments {

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
		$this->db->exec("CREATE TABLE IF NOT EXISTS enchantments (name VARCHAR, counting INT);");
	}

	/**
	 *
	 * @param $p
	 * @param $counting
	 *
	 */
	public function addEnchList($p, $counting){
		$this->setEnchList($p, $this->getEnchList($p) + $counting);
	}

	/**
	 *
	 * @API
	 *
	 * @param $p
	 *
	 * @param $counting
	 *
	 */
	public function setEnchList($p, $counting){
		if($p instanceof Player){
			$name = $p->getLowerCaseName();
		}else{
			$name = strtolower($p);
		}

		if($this->playerExists($p)){
			$this->db->querySingle("UPDATE enchantments SET counting = '{$counting}' WHERE name = '{$name}';", true);
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

		$query = $this->db->query("SELECT * FROM enchantments WHERE name='{$name}';");
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
	public function getEnchList($p){
		if($this->playerExists($p)){
			if($p instanceof Player){
				$name = $p->getLowerCaseName();
			}else{
				$name = strtolower($p);
			}
			$query = $this->db->query("SELECT * FROM enchantments WHERE name = '{$name}';");
			while($row = $query->fetchArray(SQLITE3_ASSOC)){
				return $row['counting'];
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

		$query = $this->db->prepare("INSERT INTO enchantments (name, counting) VALUES (:name, :counting);");
		$query->bindParam(':name', $name);
		$query->bindParam(':counting', $zero);
		$query->execute();
	}

}