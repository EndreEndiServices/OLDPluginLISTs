<?php

namespace PrestigeSociety\Kits\Special;

use pocketmine\Player;
use PrestigeSociety\Kits\Special\Kit\Kit;

class KitManager {
	/**
	 * @var Kit[]
	 */
	public $KITS = [];
	/**
	 * @var SpecialKits
	 */
	private $gears;

	public function __construct(SpecialKits $gears){
		$this->gears = $gears;
	}

	/**
	 *
	 * @param array $kits
	 *
	 */
	public function registerKits($kits){
		foreach($kits as $kit){
			$this->registerKit($kit);
		}
	}

	/**
	 *
	 * @param $kit
	 *
	 * @return bool
	 *
	 */
	public function registerKit($kit){
		if(($kit instanceof Kit) && !($this->isKitRegistered($kit))){
			$kit->setSpecialKitsInstance($this->gears);
			$this->KITS[$kit->getName()] = $kit;

			return true;
		}

		return false;
	}

	/**
	 *
	 * @param $kit
	 *
	 * @return bool
	 *
	 */
	public function isKitRegistered($kit){
		if(is_object($kit)){
			if(($kit instanceof Kit)){
				return isset($this->KITS[$kit->getName()]);
			}
		}elseif(is_string($kit)){
			return isset($this->KITS[$kit]);
		}

		return false;
	}

	/**
	 *
	 * @return bool
	 *
	 */
	public function unRegisterAllKits(){
		$this->KITS = [];

		return true;
	}

	/**
	 *
	 * @param $kit
	 *
	 * @return bool
	 *
	 */
	public function unRegisterKit($kit){
		if($this->isKitRegistered($kit)){
			unset($this->KITS[$kit]);

			return true;
		}

		return false;
	}

	/**
	 *
	 * @return Kit[]
	 *
	 */
	public function getAllKits(){
		return $this->KITS;
	}

	/**
	 *
	 * @param Player $p
	 * @param        $name
	 *
	 * @return bool
	 *
	 */
	public function loadKit(Player $p, $name){
		if($this->gears->getVault()->isKitEnabled($p)) return 3;
		if($p->getInventory()->firstEmpty() === -1 || $p->getArmorInventory()->firstEmpty() === -1) return 2;

		$p->removeAllEffects();

		$contents = $this->getKitItems($name);
		if($contents === null) return 1;

		$this->gears->runKitCommands($p, $name);
		$this->gears->setKitEffects($p, $name);

		$head = array_shift($contents);
		$chest = array_shift($contents);
		$legs = array_shift($contents);
		$boots = array_shift($contents);
		$special = array_pop($contents);

		$p->getArmorInventory()->setContents([$head, $chest, $legs, $boots]);
		$p->getInventory()->setContents(array_merge([$special], $contents));
		$this->gears->getVault()->setKitEnabled($p, clone $this->getKitByName($name));

		return 0;
	}

	/**
	 *
	 * @param $name
	 *
	 * @return array|\pocketmine\item\Item[]
	 *
	 *
	 */
	public function getKitItems($name){
		$kit = $this->getKitByName($name);

		if($kit instanceof Kit){

			$contents = $kit->getItems();
			$contents[] = $kit->getSpecialItem();

			return $contents;
		}

		return null;
	}

	/**
	 *
	 * @param $name
	 *
	 * @return Kit|null
	 *
	 */
	public function getKitByName($name){
		if($this->isKitRegistered($name)){
			return $this->KITS[$name];
		}

		return null;
	}

	/**
	 *
	 * @param Player $p
	 *
	 */
	public function unloadKit(Player $p){
		$kit = $this->gears->getVault()->getPlayerKit($p);
		if($kit instanceof Kit){
			$p->removeAllEffects();
			$kit->onUnload($p);
			$p->getInventory()->clearAll();
			$p->getArmorInventory()->clearAll();
			$this->gears->getVault()->setKitDisabled($p);
		}
	}

	/**
	 *
	 * @param $data
	 * @param $clickMode
	 *
	 */
	public function callEvent($data, $clickMode){

		switch($clickMode){
			case Kit::RIGHT_CLICK_MODE:
				$kit = $this->gears->getVault()->getPlayerKit($data['Player']);
				if(($kit !== null) and ($kit->clickMode == Kit::RIGHT_CLICK_MODE)){
					$kit->onUseSpecialItem($data);
				}
				break;
			case Kit::LEFT_CLICK_MODE:
				$kit = $this->gears->getVault()->getPlayerKit($data['Player']);
				if(($kit !== null) and ($kit->clickMode == Kit::LEFT_CLICK_MODE)){
					$kit->onUseSpecialItem($data);
				}
				break;
			case Kit::ALL_CLICK_MODE:
				$kit = $this->gears->getVault()->getPlayerKit($data['Player']);
				if(($kit !== null) and ($kit->clickMode == Kit::ALL_CLICK_MODE)){
					$kit->onUseSpecialItem($data);
				}
				break;
			case Kit::HIT_PLAYER_MODE:
				$kit = $this->gears->getVault()->getPlayerKit($data['Player']);
				if(($kit !== null) and ($kit->clickMode == Kit::HIT_PLAYER_MODE)){
					$kit->onUseSpecialItem($data);
				}
				break;
			case Kit::MOVE_PLAYER_MODE:
				$kit = $this->gears->getVault()->getPlayerKit($data['Player']);
				if(($kit !== null) and ($kit->clickMode == Kit::MOVE_PLAYER_MODE)){
					$kit->onUseSpecialItem($data);
				}
				break;
			default:
				throw new \BadMethodCallException('Unknown click mode ' . $clickMode . ' on ' . __METHOD__);
		}
	}
}