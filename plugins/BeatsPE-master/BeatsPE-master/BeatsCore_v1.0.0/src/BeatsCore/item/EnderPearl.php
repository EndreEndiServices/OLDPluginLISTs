<?php
declare(strict_types = 1);

namespace BeatsCore\item;

use pocketmine\Player;
use pocketmine\item\{Item, ProjectileItem};
use pocketmine\math\Vector3;

class EnderPearl extends ProjectileItem{

	public $lastEnderPearlUse = 0;

	public static $enderPearlCooldown = 2;

	public function __construct(int $meta = 0){
		parent::__construct(Item::ENDER_PEARL, $meta, "Ender Pearl");
	}

	public function getProjectileEntityType(): string{
		return "EnderPearl";
	}

	public function getThrowForce(): float{
		return 1.1;
	}

	public function getMaxStackSize(): int{
		return 16;
	}

	public function onClickAir(Player $player, Vector3 $directionVector): bool{
				if(floor(microtime(true) - $this->lastEnderPearlUse) < self::$enderPearlCooldown){
					return false;
				}else{
					$this->lastEnderPearlUse = time();
				}
			return parent::onClickAir($player, $directionVector);
		return false;
	}
}