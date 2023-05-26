<?php

namespace PrestigeSociety\Kits\Special\Kit;

use pocketmine\item\Item;
use pocketmine\Player;
use PrestigeSociety\Kits\Special\SpecialKits;
use PrestigeSociety\Kits\Special\Task\CoolDownResetTask;
use PrestigeSociety\Kits\Special\Utils\KitUtils;
use PrestigeSociety\Kits\Special\Utils\PlayerUtils;

abstract class Kit {

	const RIGHT_CLICK_MODE = 0x01;
	const LEFT_CLICK_MODE = 0x02;
	const ALL_CLICK_MODE = 0x03;
	const HIT_PLAYER_MODE = 0x04;
	const MOVE_PLAYER_MODE = 0x05;

	/**
	 * @var string
	 */
	public $name;
	/**
	 * @var Item
	 */
	public $specialItem;
	/**
	 * @var
	 */
	public $clickMode;
	/**
	 * @var Item[]
	 */
	public $items = [];

	/** @var int */
	public $coolDown = 0;
	/** @var int */
	public $deactivate = 0;

	/** @var SpecialKits */
	public $specialKits;

	/** @var bool */
	protected $abilityActive = true;

	/**
	 *
	 * Kit constructor.
	 *
	 * @param       $name
	 * @param Item $specialItem
	 * @param array $items
	 * @param int $clickMode
	 * @param int $coolDown
	 * @param int $deactivate
	 *
	 */
	public function __construct($name, Item $specialItem, $items = [], $clickMode = Kit::HIT_PLAYER_MODE, int $coolDown = 30, int $deactivate = -1){
		$this->name = $name;
		$this->specialItem = $specialItem;
		$this->items = $items;
		$this->clickMode = $clickMode;
		$this->coolDown = $coolDown;
		$this->deactivate = $deactivate;
	}

	/**
	 *
	 * @return string
	 *
	 */
	public function getName(){
		return $this->name;
	}

	/**
	 *
	 * @return array|Item[]
	 *
	 */
	public function getItems(){
		return $this->items;
	}

	/**
	 *
	 * @param $items
	 *
	 */
	public function setItems($items){
		$this->items = $items;
	}

	/**
	 *
	 * @param Item $item
	 *
	 */
	public function addItem(Item $item){
		$this->items[] = $item;
	}

	/**
	 *
	 * @return Item
	 *
	 */
	public function getSpecialItem(){
		return $this->specialItem;
	}

	/**
	 *
	 * @param Item $specialItem
	 *
	 */
	public function setSpecialItem(Item $specialItem){
		$this->specialItem = $specialItem;
	}

	/**
	 *
	 * @return int
	 *
	 */
	public function getClickMode(){
		return $this->clickMode;
	}

	/**
	 *
	 * @return int
	 *
	 */
	public function getCoolDown(): int{
		return $this->coolDown;
	}


	/**
	 *
	 * @param Player $player
	 *
	 * @return bool
	 *
	 */
	public function checkCoolDown(Player $player){
		if(KitUtils::checkKitCoolDown(PlayerUtils::PlayerHash($player), $this->coolDown)){
			return false;
		}

		$kit = $this->specialKits->getVault()->getPlayerKit($player);
		if($kit instanceof Kit){
			$kit->setAbilityActive(true);
			$this->specialKits->getVault()->setKit($player, $kit);
		}
		$task = new CoolDownResetTask($this->specialKits->getCore(), $player, $this->coolDown);
		$this->specialKits->getCore()->getScheduler()->scheduleRepeatingTask($task, 20);

		return true;
	}

	/**
	 *
	 * @return SpecialKits
	 *
	 */
	public function getSpecialKitsInstance(): SpecialKits{
		return $this->specialKits;
	}

	/**
	 *
	 * @param SpecialKits $specialKits
	 *
	 */
	public function setSpecialKitsInstance(SpecialKits $specialKits): void{
		$this->specialKits = $specialKits;
	}

	/**
	 *
	 * @return bool
	 *
	 */
	public function isAbilityActive(): bool{
		return $this->abilityActive;
	}

	/**
	 *
	 * @param bool $abilityActive
	 *
	 */
	public function setAbilityActive(bool $abilityActive): void{
		$this->abilityActive = $abilityActive;
	}

	/**
	 *
	 * @return int
	 *
	 */
	public function getDeactivateTime(): int{
		return $this->deactivate;
	}

	/**
	 *
	 * @param int $deactivate
	 *
	 */
	public function setDeactivateTime(int $deactivate): void{
		$this->deactivate = $deactivate;
	}

	/**
	 *
	 * @param Player $player
	 *
	 */
	public function onUnload(Player $player){

	}

	/**
	 *
	 * @param Player $player
	 *
	 */
	public function onDeath(Player $player){

	}

	/**
	 *
	 * @param Player $player
	 *
	 */
	public function onQuit(Player $player){

	}

	/**
	 *
	 * @param array $data
	 *
	 */
	abstract public function onUseSpecialItem(array $data);
}