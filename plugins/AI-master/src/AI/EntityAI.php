<?php

namespace AI;

use pocketmine\Player;
use pocketmine\math\Vector3;
use pocketmine\entity\Creature;
use pocketmine\network\mcpe\protocol\AddEntityPacket;


abstract class EntityAI extends Creature{

	public function spawnTo(Player $player): void{
		$pk = new AddEntityPacket();

		$pk->entityRuntimeId = $this->getId();
		$pk->type = static::NETWORK_ID;

		$pk->position = new Vector3((int) $this->x, (int) $this->y, (int) $this->z);

		$pk->yaw = $this->yaw;
		$pk->pitch = $this->pitch;

		$player->dataPacket($pk);

		parent::spawnTo($player);

	}


}
