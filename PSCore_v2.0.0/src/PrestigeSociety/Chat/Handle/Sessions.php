<?php

namespace PrestigeSociety\Chat\Handle;

use pocketmine\Player;

class Sessions {
	/** @var int[] */
	private static $coolDown = [];

	/**
	 *
	 * @param Player $p
	 *
	 */
	public static function refreshCoolDown(Player $p){
		self::removeCoolDown($p);
		self::addCoolDown($p);
	}

	/**
	 *
	 * @param Player $p
	 *
	 */
	public static function removeCoolDown(Player $p){
		if(self::isOnCoolDown($p)) unset(self::$coolDown[$p->getName()]);
	}

	/**
	 *
	 * @param Player $p
	 *
	 * @return bool
	 *
	 */
	public static function isOnCoolDown(Player $p){
		return isset(self::$coolDown[$p->getName()]);
	}

	/**
	 *
	 * @param Player $p
	 *
	 */
	public static function addCoolDown(Player $p){
		self::$coolDown[$p->getName()] = time();
	}

	/**
	 *
	 * @param Player $p
	 *
	 * @return int|null
	 *
	 */
	public static function getCoolDown(Player $p){
		if(self::isOnCoolDown($p)) return self::$coolDown[$p->getName()];

		return null;
	}
}