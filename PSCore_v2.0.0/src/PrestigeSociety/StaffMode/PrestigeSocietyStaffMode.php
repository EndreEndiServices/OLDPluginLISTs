<?php

namespace PrestigeSociety\StaffMode;

use pocketmine\item\Item;
use pocketmine\Player;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class PrestigeSocietyStaffMode {

	/** @var Player[] */
	public $staff = [];
	/** @var array */
	public $data = [];
	/** @var Player[] */
	protected $frozenPlayers = [];
	/** @var PrestigeSocietyCore */
	private $plugin;

	/**
	 *
	 * PrestigeSocietyStaffMode constructor.
	 *
	 * @param PrestigeSocietyCore $core
	 *
	 */
	public function __construct(PrestigeSocietyCore $core){
		$this->plugin = $core;
	}

	/**
	 *
	 * @param Player $player
	 *
	 */
	public function setStaffModeReady(Player $player){
		$this->setInStaffMode($player);
		$config = $this->plugin->getConfig()->getAll()['staff_mode'];
		$player->setGamemode($config['game_mode']);
		$player->getInventory()->setContents([
			Item::get($config['first_item_id'], $config['first_item_meta'], 1)->setCustomName(RandomUtils::colorMessage($config['first_item_name'])),
			Item::get($config['second_item_id'], $config['second_item_meta'], 1)->setCustomName(RandomUtils::colorMessage($config['second_item_name'])),
			Item::get($config['third_item_id'], $config['third_item_meta'], 1)->setCustomName(RandomUtils::colorMessage($config['third_item_name'])),
			Item::get($config['fourth_item_id'], $config['fourth_item_meta'], 1)->setCustomName(RandomUtils::colorMessage($config['fourth_item_name'])),
		]);
	}

	/**
	 *
	 * @param Player $player
	 *
	 */
	public function setInStaffMode(Player $player){
		$this->data[$player->getXuid()]['inventory'] = $player->getInventory()->getContents();
		$this->data[$player->getXuid()]['armor'] = $player->getArmorInventory()->getContents(true);
		$this->data[$player->getXuid()]['gamemode'] = $player->getGamemode();
		$this->staff[$player->getXuid()] = $player;
	}

	/**
	 *
	 * @param Player $player
	 *
	 */
	public function unsetFromStaffMode(Player $player){
		$this->removeFromStaffMode($player);
		$player->setGamemode($this->plugin->getServer()->getDefaultGamemode());
	}

	/**
	 *
	 * @param Player $player
	 *
	 */
	public function removeFromStaffMode(Player $player){
		if($this->isInStaffMode($player)){
			$player->getInventory()->setContents($this->data[$player->getXuid()]['inventory']);
			$player->getArmorInventory()->setContents($this->data[$player->getXuid()]['armor']);
			$player->setGamemode($this->data[$player->getXuid()]['gamemode']);
			unset($this->data[$player->getXuid()]);
			unset($this->staff[$player->getXuid()]);
		}
	}

	/**
	 *
	 * @param Player $player
	 *
	 * @return bool
	 *
	 */
	public function isInStaffMode(Player $player): bool{
		return isset($this->staff[$player->getXuid()]);
	}

	/**
	 *
	 * @param Player $player
	 *
	 */
	public function freezePlayer(Player $player){
		$this->frozenPlayers[$player->getXuid()] = $player;
	}

	/**
	 *
	 * @param Player $player
	 *
	 */
	public function unfreezePlayer(Player $player){
		if($this->isPlayerFrozen($player)){
			unset($this->frozenPlayers[$player->getXuid()]);
		}
	}

	/**
	 *
	 * @param Player $player
	 *
	 * @return bool
	 *
	 */
	public function isPlayerFrozen(Player $player): bool{
		return isset($this->frozenPlayers[$player->getXuid()]);
	}

}