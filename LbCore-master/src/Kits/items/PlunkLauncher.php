<?php

namespace Kits\items;

use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\entity\Arrow;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\network\protocol\AddItemEntityPacket;

/**
 * Special plunk gun for lumberman kit
 */
class PlunkLauncher extends Arrow {
	/**@var float*/
	protected $gravity = 0.04;
	/**@var float*/
	protected $drag = 0.02;
	/**@var float*/
	protected $damage = 8;

	/**
	 * Spawns plank for player with lumberman kit active
	 * 
	 * @param Player $player
	 */
	public function spawnTo(Player $player) {
		$pk = new AddItemEntityPacket;
		$pk->eid = $this->getID();
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->yaw = $this->yaw;
		$pk->pitch = $this->pitch;
		$pk->item = Item::get(Block::PLANKS);
		$pk->speedX = $this->motionX;
		$pk->speedY = $this->motionY;
		$pk->speedZ = $this->motionZ;
		$player->dataPacket($pk);

		Entity::spawnTo($player);
	}

}
