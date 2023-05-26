<?php

namespace Kits\items;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\Snowball;
use pocketmine\math\Vector3;
use pocketmine\network\protocol\AddItemEntityPacket;
use pocketmine\item\Item;
use LbCore\player\LbPlayer;

/**
 * TNT special bonus for Creeper kit
 */
class TntProjectile extends Snowball {
	
	/**
	 * Explode target with TNT mine
	 * 
	 * @param $currentTick
	 * @return boolean
	 */
    
         public $shouldExplode = false;
         
	public function onUpdate($currentTick) {
		if ($this->closed) {
            return false;
        }
        $this->timings->startTiming();
        $hasUpdate = parent::onUpdate($currentTick);
		
		if ($this->shootingEntity instanceof LbPlayer) {
			$bb = $this->getBoundingBox();
			if (($this->hadCollision || $this->onGround || count($this->level->getCollisionBlocks($bb, true)) > 0) && $hasUpdate) {
				$x = round($this->x);
				$z = round($this->z);
				
				$iterNum = 0;
				while (!($this->level->getBlock(new Vector3($x, round($this->y), $z)) instanceof Air)) {
					$x = $x > $this->shootingEntity->x ? $x - 1 : $x + 1;
					$z = $z > $this->shootingEntity->z ? $z - 1 : $z + 1;
					if ($iterNum < 5) {
						$iterNum++;
					} else {
						break;
					}
				}
				$y = round($this->y + $this->shootingEntity->height / 2);
				
				$position = new Vector3($x, $y, $z);
				Server::getInstance()->getDefaultLevel()->setBlock(
					$position, 
					Block::get(Block::TNT)
				);
				$this->shootingEntity->setKitAdditionalData('tntPosition', $position);
				if($this->shouldExplode){
					$block = Server::getInstance()->getDefaultLevel()->getBlock($position);
					$block->onActivate(Item::get(Item::FLINT_STEEL), $this->shootingEntity);
				}
				$this->kill();
				$hasUpdate = false;
			}
		}
		
		$this->timings->stopTiming();
        
        return $hasUpdate;
	}
	
	/**
	 * Save TNT packet and spawn it to player
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
		$pk->item = Item::get(Block::TNT);
		$pk->speedX = $this->motionX;
		$pk->speedY = $this->motionY;
		$pk->speedZ = $this->motionZ;
		$player->dataPacket($pk);

		$this->item = $pk->item;

		Entity::spawnTo($player);
	}
}
