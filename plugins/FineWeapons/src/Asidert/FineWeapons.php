<?php

namespace Asidert;

use pocketmine\plugin\PluginBase;
use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\network\protocol\UseItemPacket;
use pocketmine\Player;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Float;
use pocketmine\item\Item;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\entity\Arrow;
use pocketmine\entity\Snowball;
use pocketmine\event\entity\EntityDespawnEvent;
use pocketmine\scheduler\CallbackTask;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\Server;
use pocketmine\entity\Living;

class FineWeapons extends PluginBase implements Listener {
	public function onEnable() {
		$this->getServer ()->getPluginManager ()->registerEvents ( $this, $this );
	}
	
	public function onPacketReceived(DataPacketReceiveEvent $event){
		$pk = $event->getPacket();
		$player = $event->getPlayer();
		if($pk instanceof UseItemPacket and $pk->face === 0xff) {
		$item = $player->getInventory()->getItemInHand();
		if($item->getId() == 290){
		foreach($player->getInventory()->getContents() as $item){
				if ($item->getID() == 263 && $item->getDamage() == 0 && $item->getCount() > 0){
						$nbt = new Compound ( "", [ 
				"Pos" => new Enum ( "Pos", [ 
						new Double ( "", $player->x ),
						new Double ( "", $player->y + $player->getEyeHeight () ),
						new Double ( "", $player->z ) 
				] ),
				"Motion" => new Enum ( "Motion", [ 
						new Double ( "", - \sin ( $player->yaw / 180 * M_PI ) *\cos ( $player->pitch / 180 * M_PI ) ),
						new Double ( "", - \sin ( $player->pitch / 180 * M_PI ) ),
						new Double ( "",\cos ( $player->yaw / 180 * M_PI ) *\cos ( $player->pitch / 180 * M_PI ) ) 
				] ),
				"Rotation" => new Enum ( "Rotation", [ 
						new Float ( "", $player->yaw ),
						new Float ( "", $player->pitch ) 
				] ) 
		] );
		
		$f = 5.0;
		$snowball = Entity::createEntity ( "Snowball", $player->chunk, $nbt, $player );
		$snowball->setMotion ( $snowball->getMotion ()->multiply ( $f ) );
		$snowball->spawnToAll ();
		$player->getInventory ()->removeItem ( Item::get(263, 0, 1) );
		}
	}
	
						}elseif($item->getId() == 291){
						foreach($player->getInventory()->getContents() as $item){
						if ($item->getID() == 388 && $item->getDamage() == 0 && $item->getCount() > 0 ){
					
						$nbt = new Compound ( "", [ 
				"Pos" => new Enum ( "Pos", [ 
						new Double ( "", $player->x +1),
						new Double ( "", $player->y + $player->getEyeHeight () ),
						new Double ( "", $player->z ) 
				] ),
				"Motion" => new Enum ( "Motion", [ 
						new Double ( "", - \sin ( $player->yaw / 180 * M_PI ) *\cos ( $player->pitch / 180 * M_PI ) ),
						new Double ( "", - \sin ( $player->pitch / 180 * M_PI ) ),
						new Double ( "",\cos ( $player->yaw / 180 * M_PI ) *\cos ( $player->pitch / 180 * M_PI ) ) 
				] ),
				"Rotation" => new Enum ( "Rotation", [ 
						new Float ( "", $player->yaw ),
						new Float ( "", $player->pitch ) 
				] ) 
		] );
		$nbt6 = new Compound ( "", [ 
				"Pos" => new Enum ( "Pos", [ 
						new Double ( "", $player->x -1),
						new Double ( "", $player->y + $player->getEyeHeight () ),
						new Double ( "", $player->z ) 
				] ),
				"Motion" => new Enum ( "Motion", [ 
						new Double ( "", - \sin ( $player->yaw / 180 * M_PI ) *\cos ( $player->pitch / 180 * M_PI ) ),
						new Double ( "", - \sin ( $player->pitch / 180 * M_PI ) ),
						new Double ( "",\cos ( $player->yaw / 180 * M_PI ) *\cos ( $player->pitch / 180 * M_PI ) ) 
				] ),
				"Rotation" => new Enum ( "Rotation", [ 
						new Float ( "", $player->yaw ),
						new Float ( "", $player->pitch ) 
				] ) 
		] );
		$nbt2 = new Compound ( "", [ 
				"Pos" => new Enum ( "Pos", [ 
						new Double ( "", $player->x  ),
						new Double ( "", $player->y + $player->getEyeHeight () ),
						new Double ( "", $player->z +1) 
				] ),
				"Motion" => new Enum ( "Motion", [ 
						new Double ( "", - \sin ( $player->yaw / 180 * M_PI ) *\cos ( $player->pitch / 180 * M_PI ) ),
						new Double ( "", - \sin ( $player->pitch / 180 * M_PI ) ),
						new Double ( "",\cos ( $player->yaw / 180 * M_PI ) *\cos ( $player->pitch / 180 * M_PI ) ) 
				] ),
				"Rotation" => new Enum ( "Rotation", [ 
						new Float ( "", $player->yaw ),
						new Float ( "", $player->pitch ) 
				] ) 
		] );
		$nbt3 = new Compound ( "", [ 
				"Pos" => new Enum ( "Pos", [ 
						new Double ( "", $player->x ),
						new Double ( "", $player->y + $player->getEyeHeight () ),
						new Double ( "", $player->z -1) 
				] ),
				"Motion" => new Enum ( "Motion", [ 
						new Double ( "", - \sin ( $player->yaw / 180 * M_PI ) *\cos ( $player->pitch / 180 * M_PI ) ),
						new Double ( "", - \sin ( $player->pitch / 180 * M_PI ) ),
						new Double ( "",\cos ( $player->yaw / 180 * M_PI ) *\cos ( $player->pitch / 180 * M_PI ) ) 
				] ),
				"Rotation" => new Enum ( "Rotation", [ 
						new Float ( "", $player->yaw ),
						new Float ( "", $player->pitch ) 
				] ) 
		] );
		$nbt4 = new Compound ( "", [ 
				"Pos" => new Enum ( "Pos", [ 
						new Double ( "", $player->x ),
						new Double ( "", $player->y + $player->getEyeHeight () +1 ),
						new Double ( "", $player->z ) 
				] ),
				"Motion" => new Enum ( "Motion", [ 
						new Double ( "", - \sin ( $player->yaw / 180 * M_PI ) *\cos ( $player->pitch / 180 * M_PI ) ),
						new Double ( "", - \sin ( $player->pitch / 180 * M_PI ) ),
						new Double ( "",\cos ( $player->yaw / 180 * M_PI ) *\cos ( $player->pitch / 180 * M_PI ) ) 
				] ),
				"Rotation" => new Enum ( "Rotation", [ 
						new Float ( "", $player->yaw ),
						new Float ( "", $player->pitch ) 
				] ) 
		] );
		$nbt5 = new Compound ( "", [ 
				"Pos" => new Enum ( "Pos", [ 
						new Double ( "", $player->x ),
						new Double ( "", $player->y + $player->getEyeHeight () -1),
						new Double ( "", $player->z ) 
				] ),
				"Motion" => new Enum ( "Motion", [ 
						new Double ( "", - \sin ( $player->yaw / 180 * M_PI ) *\cos ( $player->pitch / 180 * M_PI ) ),
						new Double ( "", - \sin ( $player->pitch / 180 * M_PI ) ),
						new Double ( "",\cos ( $player->yaw / 180 * M_PI ) *\cos ( $player->pitch / 180 * M_PI ) ) 
				] ),
				"Rotation" => new Enum ( "Rotation", [ 
						new Float ( "", $player->yaw ),
						new Float ( "", $player->pitch ) 
				] ) 
		] );
		
		
		$f = 5.0;
		$snowball = Entity::createEntity ( "Snowball", $player->chunk, $nbt, $player );
		$snowball2 = Entity::createEntity ( "Snowball", $player->chunk, $nbt2, $player );
		$snowball3 = Entity::createEntity ( "Snowball", $player->chunk, $nbt3, $player );
		$snowball4 = Entity::createEntity ( "Snowball", $player->chunk, $nbt4, $player );
		$snowball5 = Entity::createEntity ( "Snowball", $player->chunk, $nbt5, $player );
		$snowball6 = Entity::createEntity ( "Snowball", $player->chunk, $nbt6, $player );
		$snowball->setMotion ( $snowball->getMotion ()->multiply ( $f ) );
		$snowball2->setMotion ( $snowball2->getMotion ()->multiply ( $f ) );
		$snowball3->setMotion ( $snowball3->getMotion ()->multiply ( $f ) );
		$snowball4->setMotion ( $snowball4->getMotion ()->multiply ( $f ) );
		$snowball5->setMotion ( $snowball5->getMotion ()->multiply ( $f ) );
		$snowball6->setMotion ( $snowball6->getMotion ()->multiply ( $f ) );
		$snowball->spawnToAll ();
		$snowball2->spawnToAll ();
		$snowball3->spawnToAll ();
		$snowball4->spawnToAll ();
		$snowball5->spawnToAll ();
		$snowball6->spawnToAll ();
		$player->getInventory ()->removeItem ( Item::get(388, 0, 1) );
		if ($snowball instanceof Projectile) {
			$this->server->getPluginManager ()->callEvent ( $projectileEv = new ProjectileLaunchEvent ( $snowball ) );
			if ($projectileEv->isCancelled ()) {
				$snowball->kill ();
			} else {
				
				$this->object_hash [spl_object_hash ( $snowball )] = 1;
				$snowball->spawnToAll ();
			}
		} else {
			$this->object_hash [spl_object_hash ( $snowball )] = 1;
			$snowball->spawnToAll ();
		}
		}
		}
						}elseif($item->getId() == 292){
		foreach($player->getInventory()->getContents() as $item){
				if ($item->getID() == 265 && $item->getDamage() == 0 && $item->getCount() > 0 ){
						$this->getServer ()->getScheduler ()->scheduleDelayedTask ( new CallbackTask ( [ 
					$this,
					"burstSnowball" ], [ 
					$player ] ), 0 );
						$this->getServer ()->getScheduler ()->scheduleDelayedTask ( new CallbackTask ( [ 
					$this,
					"burstSnowball" ], [ 
					$player ] ), 2 );
			$this->getServer ()->getScheduler ()->scheduleDelayedTask ( new CallbackTask ( [ 
					$this,
					"burstSnowball" ], [ 
					$player ] ), 4 );
					$player->getInventory ()->removeItem ( Item::get(265, 0, 1) );
					}
					}
						}elseif($item->getId() == 293){
						foreach($player->getInventory()->getContents() as $item){
				if ($item->getID() == 264 && $item->getDamage() == 0 && $item->getCount() > 0){
						$this->getServer ()->getScheduler ()->scheduleDelayedTask ( new CallbackTask ( [ 
					$this,
					"burstSnowball" ], [ 
					$player ] ), 0 );
						$this->getServer ()->getScheduler ()->scheduleDelayedTask ( new CallbackTask ( [ 
					$this,
					"burstSnowball" ], [ 
					$player ] ), 5 );
			$this->getServer ()->getScheduler ()->scheduleDelayedTask ( new CallbackTask ( [ 
					$this,
					"burstSnowball" ], [ 
					$player ] ), 10 );
					$this->getServer ()->getScheduler ()->scheduleDelayedTask ( new CallbackTask ( [ 
					$this,
					"burstSnowball" ], [ 
					$player ] ), 15 );
			$this->getServer ()->getScheduler ()->scheduleDelayedTask ( new CallbackTask ( [ 
					$this,
					"burstSnowball" ], [ 
					$player ] ), 20 );
					$this->getServer ()->getScheduler ()->scheduleDelayedTask ( new CallbackTask ( [ 
					$this,
					"burstSnowball" ], [ 
					$player ] ), 25 );
					$player->getInventory ()->removeItem ( Item::get(264, 0, 1) );
					}
					}
						}elseif($item->getId() == 294){
						foreach($player->getInventory()->getContents() as $item){
				if ($item->getID() == 318 && $item->getDamage() == 0 && $item->getCount() > 0){
					$this->getServer ()->getScheduler ()->scheduleDelayedTask ( new CallbackTask ( [ 
					$this,
					"burstSnowball" ], [ 
					$player ] ), 0 );
					$this->getServer ()->getScheduler ()->scheduleDelayedTask ( new CallbackTask ( [ 
					$this,
					"burstSnowball" ], [ 
					$player ] ), 5 );
		          	$this->getServer ()->getScheduler ()->scheduleDelayedTask ( new CallbackTask ( [ 
					$this,
					"burstSnowball" ], [ 
					$player ] ), 10 );
					$this->getServer ()->getScheduler ()->scheduleDelayedTask ( new CallbackTask ( [ 
					$this,
					"burstSnowball" ], [ 
					$player ] ), 15 );
		           	$this->getServer ()->getScheduler ()->scheduleDelayedTask ( new CallbackTask ( [ 
					$this,
					"burstSnowball" ], [ 
					$player ] ), 20 );
					$this->getServer ()->getScheduler ()->scheduleDelayedTask ( new CallbackTask ( [ 
					$this,
					"burstSnowball" ], [ 
					$player ] ), 25 );
					$this->getServer ()->getScheduler ()->scheduleDelayedTask ( new CallbackTask ( [ 
					$this,
					"burstSnowball" ], [ 
					$player ] ), 30 );
					$this->getServer ()->getScheduler ()->scheduleDelayedTask ( new CallbackTask ( [ 
					$this,
					"burstSnowball" ], [ 
					$player ] ), 35 );
		          	$this->getServer ()->getScheduler ()->scheduleDelayedTask ( new CallbackTask ( [ 
					$this,
					"burstSnowball" ], [ 
					$player ] ), 40 );
					$this->getServer ()->getScheduler ()->scheduleDelayedTask ( new CallbackTask ( [ 
					$this,
					"burstSnowball" ], [ 
					$player ] ), 45 );
		           	$this->getServer ()->getScheduler ()->scheduleDelayedTask ( new CallbackTask ( [ 
					$this,
					"burstSnowball" ], [ 
					$player ] ), 50 );
					$this->getServer ()->getScheduler ()->scheduleDelayedTask ( new CallbackTask ( [ 
					$this,
					"burstSnowball" ], [ 
					$player ] ), 55 );
					$this->getServer ()->getScheduler ()->scheduleDelayedTask ( new CallbackTask ( [ 
					$this,
					"burstSnowball" ], [ 
					$player ] ), 60 );
					$player->getInventory ()->removeItem ( Item::get(318, 0, 1) );
					}
					}

		         }else{
			}						
		}
		}
	public function burstSnowball(Player $player) {
		$nbt = new Compound ( "", [ 
				"Pos" => new Enum ( "Pos", [ 
						new Double ( "", $player->x ),
						new Double ( "", $player->y + $player->getEyeHeight () ),
						new Double ( "", $player->z ) ] ),
				"Motion" => new Enum ( "Motion", [ 
						new Double ( "", - \sin ( $player->yaw / 180 * M_PI ) *\cos ( $player->pitch / 180 * M_PI ) ),
						new Double ( "", - \sin ( $player->pitch / 180 * M_PI ) ),
						new Double ( "",\cos ( $player->yaw / 180 * M_PI ) *\cos ( $player->pitch / 180 * M_PI ) ) ] ),
				"Rotation" => new Enum ( "Rotation", [ 
						new Float ( "", $player->yaw ),
						new Float ( "", $player->pitch ) ] ) ] );
		
		$f = 5.0;
		$snowball = Entity::createEntity ( "Snowball", $player->chunk, $nbt, $player );
		$snowball->setMotion ( $snowball->getMotion ()->multiply ( $f ) );
		
		if ($snowball instanceof Projectile) {
			$this->server->getPluginManager ()->callEvent ( $projectileEv = new ProjectileLaunchEvent ( $snowball ) );
			if ($projectileEv->isCancelled ()) {
				$snowball->kill ();
			} else {
				
				$this->object_hash [spl_object_hash ( $snowball )] = 1;
				$snowball->spawnToAll ();
			}
		} else {
			$this->object_hash [spl_object_hash ( $snowball )] = 1;
			$snowball->spawnToAll ();
		}
	}
		public function onDamage(EntityDamageEvent $event){
		$player = $event->getEntity();
		$entity = $event->getEntity();
        if($player instanceof Player && $event->getCause() === EntityDamageEvent::CAUSE_PROJECTILE){
                $event->setDamage(6);
        }
	}
}
?>