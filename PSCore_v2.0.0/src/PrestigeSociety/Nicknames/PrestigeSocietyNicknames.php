<?php

namespace PrestigeSociety\Nicknames;

use pocketmine\Player;

class PrestigeSocietyNicknames {
	/** @var \SQLite3 */
	protected $db;

	/**
	 *
	 * PrestigeSocietyVaults constructor.
	 *
	 * @param \SQLite3 $db
	 *
	 */
	public function __construct(\SQLite3 $db){
		$this->db = $db;

		$db->exec("CREATE TABLE IF NOT EXISTS nicknames (original VARCHAR, nick VARCHAR)");
	}

	/**
	 *
	 * @param Player $player
	 * @param string $nick
	 *
	 */
	public function setNick(Player $player, string $nick){
		if(!$this->hasNick($player)){
			$q = $this->db->prepare("INSERT INTO nicknames (original, nick) VALUES (?, ?)");
			$q->bindValue(1, $player->getXuid());
			$q->bindParam(2, $nick);
			$q->execute();
		}else{
			$q = $this->db->prepare("UPDATE nicknames SET nick = ? WHERE original = '{$player->getXuid()}'");
			$q->bindParam(1, $nick);
			$q->execute();
		}
	}

	/**
	 *
	 * @param Player $player
	 *
	 * @return bool
	 *
	 */
	public function hasNick(Player $player){
		$query = $this->db->query("SELECT nick FROM nicknames WHERE original = {$player->getXuid()}");
		$fetch = $query->fetchArray(SQLITE3_ASSOC);

		if(is_array($fetch) && count($fetch) != 0){
			return true;
		}

		return false;
	}

	/**
	 *
	 * @param Player $player
	 *
	 * @return mixed
	 *
	 */
	public function getNick(Player $player){
		if($this->hasNick($player)){
			$query = $this->db->query("SELECT nick FROM nicknames WHERE original = '{$player->getXuid()}'");
			$fetch = $query->fetchArray(SQLITE3_ASSOC);

			return $fetch['nick'];
		}

		return null;
	}

	/**
	 *
	 * @param Player $player
	 *
	 * @return bool
	 *
	 */
	public function resetNick(Player $player){
		if($this->hasNick($player)){
			$this->db->querySingle("DELETE FROM nicknames WHERE original = '{$player->getXuid()}'");

			return true;
		}

		return false;
	}
}