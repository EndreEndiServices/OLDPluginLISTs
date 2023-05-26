<?php

namespace PrestigeSociety\Optimizer\Utils;

use pocketmine\entity\Entity;
use pocketmine\entity\Monster;
use PrestigeSociety\Core\Utils\exc;

class OptimizerInfo {

	/** @var int */
	private static $times_clears = 0;
	/** @var Entity[] */
	private static $entitiesCleared = [];

	/**
	 * @return int
	 */
	public static function getTimesCleared(){
		return self::$times_clears;
	}

	/**
	 *
	 * @return bool
	 *
	 */
	public static function resetTimesCleared(){
		if(self::$times_clears !== 0){
			self::$times_clears = 0;

			return true;
		}

		return false;
	}

	/**
	 *
	 * @param $times
	 *
	 */
	public static function addTimesCleared($times){
		if(exc::checkIsNumber($times)){
			self::$times_clears += $times;
		}
	}

	/**
	 *
	 * @param $times
	 *
	 */
	public static function subtractTimesCleared($times){
		if(exc::checkIsNumber($times)){
			self::$times_clears -= $times;
		}
	}

	/**
	 *
	 * @param $times
	 *
	 */
	public static function divideTimesCleared($times){
		if(exc::checkIsNumber($times)){
			self::$times_clears /= $times;
		}
	}

	/**
	 *
	 * @param $times
	 *
	 */
	public static function multiplyTimesCleared($times){
		if(exc::checkIsNumber($times)){
			self::$times_clears *= $times;
		}
	}

	/**
	 *
	 * @param Entity $e
	 *
	 */
	public static function saveClearedEntity(Entity $e){
		if(!($e instanceof Monster) and !isset(self::$entitiesCleared[$e->getId()])){
			self::$entitiesCleared[$e->getId()] = $e;
		}
	}

	public static function restoreAllEntities(){
		foreach(self::$entitiesCleared as $e){
			if($e instanceof Entity){
				$e->respawnToAll();
			}
		}
		self::$entitiesCleared = [];
	}

	/**
	 *
	 * @return \pocketmine\entity\Entity[]
	 *
	 */
	public static function getClearedEntities(){
		return self::$entitiesCleared;
	}

	/**
	 *
	 * @return int
	 *
	 */
	public static function getClearedEntitiesCount(){
		return count(self::$entitiesCleared);
	}
}