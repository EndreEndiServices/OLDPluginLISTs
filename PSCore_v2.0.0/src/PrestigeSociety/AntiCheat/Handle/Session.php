<?php

namespace PrestigeSociety\AntiCheat\Handle;

use pocketmine\Player;

class Session {
	/** @var Player[] */
	private static $savedTasks = [];

	/**
	 *
	 * @param Player $p
	 *
	 */
	public static function saveFlyingTask(Player $p){
		self::$savedTasks[$p->getName()] = $p;
	}

	/**
	 *
	 * @param Player $p
	 *
	 */
	public static function deleteFlyingTask(Player $p){
		if(self::isFlyingTaskSaved($p))
			unset(self::$savedTasks[$p->getName()]);
	}

	/**
	 *
	 * @param Player $p
	 *
	 * @return bool
	 *
	 */
	public static function isFlyingTaskSaved(Player $p){
		return isset(self::$savedTasks[$p->getName()]);
	}

	/**
	 * @return \pocketmine\Player[]
	 */
	public function getSavedTasks(){
		return self::$savedTasks;
	}
}