<?php

namespace PrestigeSociety\Kits;

use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\Player;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Kits\Normal\KitCommand;
use PrestigeSociety\Kits\Normal\NormalKits;
use PrestigeSociety\Kits\Special\Commands\SKitCommand;
use PrestigeSociety\Kits\Special\SpecialKits;

class PrestigeSocietyKits {

	/** @var \SQLite3 */
	protected $db;

	/** @var PrestigeSocietyCore */
	protected $core;

	/** @var NormalKits */
	protected $normalKits;

	/** @var SpecialKits */
	protected $specialKits;

	/**
	 *
	 * KitCommand constructor.
	 *
	 * @param PrestigeSocietyCore $core
	 * @param \SQLite3 $db
	 *
	 */
	public function __construct(PrestigeSocietyCore $core, \SQLite3 $db){
		;
		$this->core = $core;
		$this->normalKits = new NormalKits($core);
		$this->specialKits = new SpecialKits($core);

		$this->db = $db;
		$this->db->exec("CREATE TABLE IF NOT EXISTS cooldown (username VARCHAR, timeUsed INT, kit VARCHAR);");

		$this->core->getServer()->getPluginManager()->registerEvents(new KitListener($core), $core);
		$this->core->getServer()->getCommandMap()->register("kit", new KitCommand($core));
		$this->core->getServer()->getCommandMap()->register("skit", new SKitCommand($core));
	}

	/**
	 *
	 * @param InventoryTransaction $transaction
	 * @param bool $cancel
	 *
	 */
	public function callTransaction(InventoryTransaction $transaction, bool &$cancel){
		$this->normalKits->callTransaction($transaction, $cancel);
	}

	/**
	 *
	 * @return NormalKits
	 *
	 */
	public function getNormalKits(): NormalKits{
		return $this->normalKits;
	}

	/**
	 *
	 * @return SpecialKits
	 *
	 */
	public function getSpecialKits(): SpecialKits{
		return $this->specialKits;
	}

	/**
	 *
	 * @param Player $player
	 * @param string $kit
	 * @param int $time
	 *
	 */
	public function setCoolDown(Player $player, string $kit, int $time){
		if(!$this->isOnCoolDown($player, $kit)){
			$f = $this->db->prepare("INSERT INTO cooldown (username, timeUsed, kit) VALUES (?, ?, ?)");
			$f->bindValue(1, $player->getXuid());
			$f->bindParam(2, $time);
			$f->bindParam(3, $kit);
			$f->execute();
		}
	}

	/**
	 *
	 * @param Player $player
	 * @param string $kit
	 *
	 * @return bool
	 *
	 */
	public function isOnCoolDown(Player $player, string $kit){
		$query = $this->db->query("SELECT timeUsed FROM cooldown WHERE username = '{$player->getXuid()}' AND kit = '{$kit}'");
		$fetch = $query->fetchArray(SQLITE3_ASSOC);

		return (is_array($fetch) and count($fetch) !== 0);
	}

	/**
	 *
	 * @param Player $player
	 * @param string $kit
	 *
	 * @return bool
	 *
	 */
	public function checkCoolDown(Player $player, string $kit){
		if(!$this->isOnCoolDown($player, $kit)) return true;

		$query = $this->db->query("SELECT timeUsed FROM cooldown WHERE username = '{$player->getXuid()}' AND kit = '{$kit}'");
		$fetch = $query->fetchArray(SQLITE3_ASSOC);

		$usedTime = $fetch['timeUsed'];

		if(($usedTime - time()) <= 0){

			$this->removeCoolDown($player, $kit);

			return true;
		}

		return false;
	}

	/**
	 *
	 * @param Player $player
	 * @param string $kit
	 *
	 */
	public function removeCoolDown(Player $player, string $kit){
		if(!$this->isOnCoolDown($player, $kit)){
			return;
		}

		$this->db->querySingle("DELETE FROM cooldown WHERE username = {$player->getXuid()} AND kit = '{$kit}'");
	}

	/**
	 *
	 * @param Player $player
	 * @param string $kit
	 *
	 * @return bool
	 *
	 */
	public function getCoolDown(Player $player, string $kit){
		if(!$this->isOnCoolDown($player, $kit)){
			return 0;
		}

		$query = $this->db->query("SELECT timeUsed FROM cooldown WHERE username = '{$player->getXuid()}' AND kit = '{$kit}'");
		$fetch = $query->fetchArray(SQLITE3_ASSOC);

		$usedTime = $fetch['timeUsed'];

		if(($usedTime - time()) <= 0){

			$this->removeCoolDown($player, $kit);

			return 0;

		}

		return ($usedTime - time());
	}

}