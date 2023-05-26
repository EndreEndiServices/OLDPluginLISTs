<?php

declare(strict_types = 1);

namespace BeatsCore\entity\projectile;

use pocketmine\Player;
use pocketmine\entity\{Entity, projectile\Projectile};
use pocketmine\level\{Level, sound\EndermanTeleportSound};
use pocketmine\nbt\tag\CompoundTag;

class EnderPearl extends Projectile{

	const NETWORK_ID = self::ENDER_PEARL;

	public $width = 0.25;
	public $height = 0.25;

	protected $gravity = 0.03;
	protected $drag = 0.01;

	private $hasTeleportedShooter = false;

	public function __construct(Level $level, CompoundTag $nbt, Entity $shootingEntity = null){
		parent::__construct($level, $nbt, $shootingEntity);
	}

	public function teleportShooter(){
		if(!$this->hasTeleportedShooter){
			$this->hasTeleportedShooter = true;
			if($this->getOwningEntity() instanceof Player and $this->y > 0){
                $this->getOwningEntity()->teleport($this->getPosition());
                $this->getLevel()->addSound(new EndermanTeleportSound($this->getPosition()), array($this->getOwningEntity()));
            }
			$this->kill();
		}
	}

	public function onUpdate(int $currentTick): bool{
		if($this->closed){
			return false;
		}
		$this->timings->startTiming();
		$hasUpdate = parent::onUpdate($currentTick);
		if($this->age > 1200 or $this->isCollided or $this->hadCollision){
			$this->teleportShooter();
			$hasUpdate = true;
		}
		$this->timings->stopTiming();
		return $hasUpdate;
	}
}