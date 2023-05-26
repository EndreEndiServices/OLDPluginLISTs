<?php

namespace Pets;

use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use LbCore\event\PlayerAuthEvent;
use Pets\command\PetCommand;
use pocketmine\Server;

class PetsManager implements Listener {

	public function __construct($plugin) {
		$server = Server::getInstance();
		$server->getCommandMap()->register('pets', new PetCommand('pet'));
		Entity::registerEntity(ChickenPet::class);
		Entity::registerEntity(WolfPet::class);
		Entity::registerEntity(PigPet::class);
		$server->getScheduler()->scheduleRepeatingTask(new task\PetsTick($plugin), 20*60);//run each minute for random pet messages
		$server->getScheduler()->scheduleRepeatingTask(new task\SpawnPetsTick($plugin), 20);
		
	}

	public static function create($type, Position $source, ...$args) {
		$chunk = $source->getLevel()->getChunk($source->x >> 4, $source->z >> 4, true);

		$nbt = new Compound("", [
			"Pos" => new Enum("Pos", [
				new DoubleTag("", $source->x),
				new DoubleTag("", $source->y),
				new DoubleTag("", $source->z)
					]),
			"Motion" => new Enum("Motion", [
				new DoubleTag("", 0),
				new DoubleTag("", 0),
				new DoubleTag("", 0)
					]),
			"Rotation" => new Enum("Rotation", [
				new FloatTag("", $source instanceof Location ? $source->yaw : 0),
				new FloatTag("", $source instanceof Location ? $source->pitch : 0)
					]),
		]);
		return Entity::createEntity($type, $chunk, $nbt, ...$args);
	}

	public static function createPet($player, $type = "", $holdType = "") {
		if (is_null($player->getPet())) {	
			$len = rand(8, 12); 
			$x = (-sin(deg2rad($player->yaw))) * $len  + $player->getX();
			$z = cos(deg2rad($player->yaw)) * $len  + $player->getZ();
			$y = $player->getLevel()->getHighestBlockAt($x, $z);

			$source = new Position($x , $y + 2, $z, $player->getLevel());
			if (empty($type)) {
				$pets = array("ChickenPet", "PigPet", "WolfPet");
				$type = $pets[rand(0, 2)];
			}
			if (!empty($holdType)) {
				$pets = array("ChickenPet", "PigPet", "WolfPet");
				foreach ($pets as $key => $petType) {
					if($petType == $holdType) {
						unset($pets[$key]);
						break;
					}	
				}
				$type = $pets[array_rand($pets)];
			}
			$pet = self::create($type, $source);
			$pet->setOwner($player);
			$player->addPet($pet);
			$pet->spawnToAll();
		}
	}

	public function onPlayerAuth(PlayerAuthEvent $event) {
		$player = $event->getPlayer();
		if ($player->isVip()) {
			$pet = $player->getPet();
			if (is_null($pet)) {
				$player->setPetState('enable', '', 5);
			}			
			if ($player->getState() == \LbCore\player\LbPlayer::IN_LOBBY) {
				$player->setLobbyTime(date('Y-m-d h:i:s'));
			}
		}
	}

	public function onPlayerQuit(PlayerQuitEvent $event) {
		$player = $event->getPlayer();
		$pet = $player->getPet();
		if (!is_null($pet)) {
			$pet->fastClose();
		}
	}
	
	/**
	 * Get last damager name if it's another player
	 * 
	 * @param PlayerDeathEvent $event
	 */
	public function onPlayerDeath(PlayerDeathEvent $event) {
		$player = $event->getEntity();
		$attackerEvent = $player->getLastDamageCause();
		if ($attackerEvent instanceof EntityDamageByEntityEvent) {
			$attacker = $attackerEvent->getDamager();
			if ($attacker instanceof \LbCore\player\LbPlayer) {
				$player->setLastDamager($attacker->getName());
			}
		}
	}


}
