<?php

namespace PrestigeSociety\Experience;

use pocketmine\Player;
use PrestigeSociety\Core\PrestigeSocietyCore;
use sex\guard\Manager;

class PrestigeSocietyExperience {

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
	public function __construct(\SQLite3 $db){
		$this->db = $db;
		$this->core = PrestigeSocietyCore::getInstance();
		$this->db->exec("CREATE TABLE IF NOT EXISTS experience (name VARCHAR, exp INT);");
	}

	/**
	 *
	 * @param $p
	 * @param $exp
	 *
	 */
	public function addExp($p, $exp){
		$this->setExp($p, $this->getExp($p) + $exp);
	}

	/**
	 *
	 * @API
	 *
	 * @param $p
	 *
	 * @param $exp
	 *
	 */
	public function setExp($p, $exp){
		if($p instanceof Player){
			$name = $p->getLowerCaseName();
		}else{
			$name = strtolower($p);
		}

		if($this->playerExists($p)){
			$this->db->querySingle("UPDATE experience SET exp = '{$exp}' WHERE name = '{$name}';", true);
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

		$query = $this->db->query("SELECT * FROM experience WHERE name='{$name}';");
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
	public function getExp($p){
		if($this->playerExists($p)){
			if($p instanceof Player){
				$name = $p->getLowerCaseName();
			}else{
				$name = strtolower($p);
			}
			$query = $this->db->query("SELECT * FROM experience WHERE name = '{$name}';");
			while($row = $query->fetchArray(SQLITE3_ASSOC)){
				return $row['exp'];
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

		$query = $this->db->prepare("INSERT INTO experience (name, exp) VALUES (:name, :exp);");
		$query->bindParam(':name', $name);
		$query->bindParam(':exp', $zero);
		$query->execute();
	}

	/**
	 *
	 * @param $from
	 * @param $to
	 * @param $exp
	 *
	 * @return bool
	 *
	 */
	public function payExp($from, $to, $exp){
		$fromCredits = $this->getExp($from);
		if(!($fromCredits <= $exp)){
			$this->setExp($to, $exp);
			$this->subtractExp($from, $exp);

			return true;
		}

		return false;
	}

	/**
	 *
	 * @param $p
	 * @param $exp
	 *
	 * @return bool
	 *
	 */
	public function subtractExp($p, $exp){
		if($this->getExp($p) - $exp >= 0){
			$this->setExp($p, $this->getExp($p) - $exp);

			return true;
		}

		return false;
	}

	public function checkLevel(Player $player){
		$level = $this->core->PrestigeSocietyLevels->getLevel($player);
		if($this->core->PrestigeSocietyLevels->getLevel($player) == 0){
			if($this->getExp($player) >= 250){
				$this->core->PrestigeSocietyLevels->setLevel($player, 1);
				$this->setExp($player, 0);
				$this->core->PrestigeSocietyNeeded->addNecesary($player, 250 + 250 / 4);
			}
		}elseif($this->core->PrestigeSocietyLevels->getLevel($player) > 0){
			$moreneded = ($this->core->PrestigeSocietyNeeded->getNecesary($player)) / 4;
			if($this->getExp($player) >= 250 + $moreneded){
				$this->core->PrestigeSocietyLevels->setLevel($player, $level + 1);
				$this->setExp($player, 0);
				$this->core->PrestigeSocietyNeeded->addNecesary($player, 250 + $moreneded);
			}
		}
	}

}