<?php

namespace minecombat\gun;

use pocketmine\entity\Entity;
use pocketmine\math\Vector3;

abstract class Gun{

	/**
	 * @var int count of amo
	 */
	protected $ammoCount;

	/**
	 * @var int max count of ammo
	 */
	protected $maxAmmoCount;

	/**
	 * @param int $distance
	 * @return int damage
	 */
	abstract public function getDamage($distance);

	/**
	 * @return string Gun name
	 */
	abstract public function getName();

	/**
	 * @return int required reload time
	 */
	abstract public function getReloadTime();

	/**
	 * @return int required magazine reload time
	 */
	abstract public function getMagazineReloadTime();

	/**
	 * @return int max ammo count
	 */
	public function getMaxAmmoCount(){
		return $this->maxAmmoCount;
	}

	/**
	 * @param int $count
	 */
	public function setAmmoCount($count){
		$this->ammoCount = $count;
	}

	/**
	 * @param int $count
	 */
	public function reduceAmmo($count){
		$this->ammoCount -= $count;
	}

	/**
	 * @param int $count
	 */
	public function addAmmo($count){
		$this->ammoCount += $count;
	}

	public function shoot($x, $y, $z, $yaw, $pitch){ // Can be extended
		if($this->ammoCount <= 0){
			return false;
		}
		// TODO implement shooting feature
	}

	public function onHit(Entity $shoot, Entity $entity){ // Can be extended
		$shootVector = new Vector3($shoot->getX(), $shoot->getY(), $shoot->getZ());
		$targetVector = new Vector3($entity->getX(), $entity->getY(), $entity->getZ());
		$distance = $shootVector->distance($targetVector);
		$entity->attack($this->getDamage($distance));
	}
}