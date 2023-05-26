<?php

namespace PrestigeSociety\Teleport\Handle;

use pocketmine\Player;

class Sessions {
	/** @var Player[][]|int[][] */
	private static $waiting = [];

	/**
	 *
	 * @param Player $p
	 * @param int $taskId
	 *
	 */
	public static function addToQueue(Player $p, int $taskId){
		self::$waiting[$p->getName()] = [];
		self::$waiting[$p->getName()]['player'] = $p;
		self::$waiting[$p->getName()]['task'] = $taskId;
	}

	/**
	 *
	 * @param Player $p
	 *
	 */
	public static function removeFromQueue(Player $p){
		if(self::isInQueue($p))
			unset(self::$waiting[$p->getName()]);
	}

	/**
	 *
	 * @param Player $p
	 *
	 * @return bool
	 *
	 */
	public static function isInQueue(Player $p){
		return isset(self::$waiting[$p->getName()]);
	}

	/**
	 *
	 * @param Player $player
	 *
	 * @return int[]|null|Player[]
	 *
	 */
	public static function getFromQueue(Player $player){
		if(self::isInQueue($player)){
			return self::$waiting[$player->getName()];
		}

		return null;
	}

	/**
	 *
	 * @return \int[][]|\pocketmine\Player[][]
	 */
	public static function getQueue(){
		return self::$waiting;
	}
}