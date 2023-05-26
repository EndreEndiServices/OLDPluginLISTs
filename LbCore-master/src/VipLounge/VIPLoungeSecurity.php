<?php

namespace VipLounge;

use LbCore\player\LbPlayer;
use pocketmine\entity\Entity;
use pocketmine\level\Level;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\IntArray;
use pocketmine\entity\Human;
use pocketmine\math\Vector3;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\network\protocol\AddPlayerPacket;
use pocketmine\network\protocol\PlayerListPacket;
use pocketmine\Player;

/**
 * Describes VIP Lounge guards: spawnpoints, skins, activity
 */
class VIPLoungeSecurity extends Human {
	const SKIN_NAME = 'CityFolk_Blacksmith';
	/**@var string*/
	private static $skinData = '';
	/**@var array*/
	private $guardArea = array();
	/**@var stdClass*/
	private $spawnPoint;
	/**@var LbPlayer*/
	private $target = null;
	/**@var Vector3*/
	private $knockbackVector;
	
	/**
	 * Init guard by spawnpoint and position
	 * 
	 * @param \stdClass $spawnPoint
	 * @param Level $level
	 * @param \stdClass $guardArea
	 * @param \stdClass $spawnRotation
	 * @param \stdClass $knockbackVector
	 */
	public function __construct(
			\stdClass $spawnPoint, Level $level, \stdClass $guardArea, 
			\stdClass $spawnRotation, \stdClass $knockbackVector) {
				
		// rotation yaw and pitch
		// http://4.bp.blogspot.com/-HKUiumbtFKg/VbD898CHbaI/AAAAAAAAAcA/pc30pPkRYww/s1600/HeadYawPitch.png
		$chunk = $level->getChunk($spawnPoint->x >> 4, $spawnPoint->z >> 4);
		$compound = new Compound("Lounge security", array(
			new IntArray("Pos", array($spawnPoint->x, $spawnPoint->y, $spawnPoint->z)),
			new IntArray("Rotation", array($spawnRotation->yaw, $spawnRotation->pitch)),
			new IntArray("Motion", array(0, 0, 0)),
		));
		parent::__construct($chunk, $compound);
		
		self::initSkin();
		$this->setSkin(self::$skinData, self::SKIN_NAME);
		
		$this->spawnPoint = $spawnPoint;
		$this->guardArea = $guardArea;
		$this->invulnerable = true;
		$this->knockbackVector = new Vector3($knockbackVector->x, $knockbackVector->y, $knockbackVector->z);
	}

	/**
	 * create skin for guard
	 */
	private static function initSkin() {
		$skinFile = __DIR__.'/../Data/skins/Security.dat';
		if (!self::$skinData) {
			$fileHandle = fopen($skinFile, 'r');
			if ($fileHandle) {
				self::$skinData = fread($fileHandle, filesize($skinFile));
				fclose($fileHandle);
			}
		}
	}
	
	/**
	 * Check if player is in guarded zone
	 * 
	 * @param Human $entityPlayer
	 * @return boolean
	 */
	private function onGuardCollideWithPlayer(Human $entityPlayer){
		if ($this->canCollideWith($entityPlayer)) {
			$bb = $this->getBoundingBox();
			$playerBB = $entityPlayer->getBoundingBox();
			
//			$bb->expand($this->width, 0, $this->length);
			return $bb->intersectsWith($playerBB);
		}
		return false;
	}
	
	/**
	 * Guard movings
	 * 
	 * @param type $dx
	 * @param type $dy
	 * @param type $dz
	 */
	public function move($dx, $dy, $dz) {
//		$x = ($this->boundingBox->maxX - $this->boundingBox->minX) / 2;
//		$y = ($this->boundingBox->maxY - $this->boundingBox->minY) / 2;
//		$z = ($this->boundingBox->maxZ - $this->boundingBox->minZ) / 2;
//		
//		$this->boundingBox->expand($x, 0, $z);
		parent::move($dx, $dy, $dz);
	}
	
	/**
	 * no damage to players, just push out
	 * 
	 * @param $damage
	 * @param EntityDamageEvent $source
	 */
	public function attack($damage, EntityDamageEvent $source) {
        $source->setCancelled();
    }
	
	/**
	 * automatic return to spawnpoint after attack
	 */
	private function returnToSpawn() {
		$dx = $this->spawnPoint->x - $this->x;
		$dy = $this->spawnPoint->y - $this->y;
		$dz = $this->spawnPoint->z - $this->z;
		
		$this->move($dx, $dy, $dz);
	}
	
	/**
	 * Check if entity is inside strict area
	 * @param \stdClass $area
	 * @param Entity $entity
	 * @return bool
	 */
	private static function isEntityInsideArea(\stdClass $area, Entity $entity) {
		return $entity->x < $area->maxX && $entity->x > $area->minX && 
				$entity->y < $area->maxY && $entity->y > $area->minY && 
				$entity->z < $area->maxZ && $entity->z > $area->minZ;
	}

	/**
	 * Get players to push out
	 * 
	 * @return LbPlayer
	 */
	private function getIntruders() {
		$intruders = array();
		$players = $this->level->getPlayers();
				
		foreach ($players as $player) {
			if ($player instanceof LbPlayer) {
				if (!$player->isVip() && 
					self::isEntityInsideArea($this->guardArea, $player)) {
					
					$intruders[] = $player;
				}
			}
		}
		
		return $intruders;
	}
	
	/**
	 * look for intruders and choose the nearest
	 * 
	 * @return Player
	 */
	private function getNearestIntruder() {
		$resId = null;
		$minDistance = 100500;
		
		$intruders = $this->getIntruders();
		foreach ($intruders as $id => $intruder) {
			$distance = sqrt(pow(abs($this->x - $intruder->x), 2) + pow(abs($this->y - $intruder->y), 2) + pow(abs($this->z - $intruder->z), 2));
			if ($distance <= $minDistance) {
				$minDistance = $distance;
				$resId = $id;
			}
		}
		
		return is_null($resId) ? null : $intruders[$resId];
	}
	
	/**
	 * move to nearest player's position
	 */
	private function moveToTarget() {
		if (!is_null($this->target)) {
			$dx = $this->target->x - $this->x;
			$dy = $this->target->y - $this->y;
			$dz = $this->target->z - $this->z;
			
			$this->move($dx, $dy, $dz);			
		}
	}
	
	/**
	 * look for intruders every tick and push them away,
	 * then return to spawnpoint
	 * calls in VIPLoungeGuardsTask
	 */
	public function tick() {
		if (!is_null($this->target)) {
			if ($this->isEntityInsideArea($this->guardArea, $this->target)) {
				$this->moveToTarget();
				if ($this->onGuardCollideWithPlayer($this->target)) {
					$this->target->setMotion($this->knockbackVector);
				}
			} else {
				$this->target = null;
				$this->returnToSpawn();
			}
		} else {
			$this->target = $this->getNearestIntruder();
		}
	}
	
	public function onUpdate($currentTick) {
		parent::onUpdate($currentTick);
		return true;
	}
	
	public function spawnTo(Player $player) {
	   if($player !== $this and !isset($this->hasSpawned[$player->getId()])){
			$pk = new PlayerListPacket();
			$pk->type = PlayerListPacket::TYPE_ADD;
			$pk->entries[] = [$this->getUniqueId(), $this->getId(), $this->getName(), $this->skinName, $this->skin];
					
			$pk2 = new PlayerListPacket();
			$pk2->type = PlayerListPacket::TYPE_REMOVE;
			$pk2->entries[] = [$this->getUniqueId()];			
			
			$this->hasSpawned[$player->getId()] = $player;			
			$pk3 = new AddPlayerPacket();
			$pk3->uuid = $this->getUniqueId();
			$pk3->username = $this->getName();
			$pk3->eid = $this->getId();
			$pk3->x = $this->x;
			$pk3->y = $this->y;
			$pk3->z = $this->z;
			$pk3->speedX = $this->motionX;
			$pk3->speedY = $this->motionY;
			$pk3->speedZ = $this->motionZ;
			$pk3->yaw = $this->yaw;
			$pk3->pitch = $this->pitch;
			$pk3->item = $this->getInventory()->getItemInHand();
			$pk3->metadata = $this->dataProperties;
			
			$this->server->batchPackets([$player], [$pk, $pk3, $pk2]);		   
			$this->inventory->sendArmorContents($player);	
			$this->inventory->sendHeldItem($player);
		}		
	}
	
}
