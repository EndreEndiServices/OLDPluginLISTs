<?php

namespace PrestigeSociety\Kits\Special\Kit;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use PrestigeSociety\Core\PrestigeSocietyCore;

class Scorpio extends Kit {

	/**
	 *
	 * Swapper constructor.
	 *
	 * @param Item $specialItem
	 * @param array $items
	 * @param int $coolDown
	 * @param int $deactivate
	 *
	 */
	public function __construct(Item $specialItem, array $items = [], int $coolDown, int $deactivate = -1){
		parent::__construct("Scorpio", $specialItem, $items, Kit::RIGHT_CLICK_MODE, $coolDown, $deactivate);
		DefaultPermissions::registerPermission(new Permission("kit.scorpio", "", Permission::DEFAULT_OP));
	}

	/**
	 *
	 * @param array $data
	 *
	 * @return bool
	 *
	 */
	public function onUseSpecialItem($data){
		$player = $data['Player'];
		$item = $data['Item'];


		if(($player instanceof Player) and ($item instanceof Item)){

			if(!$item->hasCustomBlockData()) return false;

			/** @var CompoundTag $data */
			$data = $item->getCustomBlockData();

			if(!$data->hasTag("kit_name")) return false;

			if(strtolower($data->getString("kit_name")) === "scorpio"){

				if($this->checkCoolDown($player)){
					$ent = $this->launchItem($player, $item);

					$player->getScheduler()->scheduleRepeatingTask(new ScorpioTask($this->specialKits->getCore(), $ent, $this->deactivate), 20);
					$player->level->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_LAUNCH);
				}

			}
		}

		return false;
	}

	/**
	 *
	 * @param Player $player
	 * @param Item $item
	 *
	 * @return null|Entity
	 *
	 */
	public function launchItem(Player $player, Item $item){
		$aimPos = $player->getDirectionVector();
		$nbt = new CompoundTag("", [
			"Pos"         => new ListTag("Pos", [
				new DoubleTag("", $player->x),
				new DoubleTag("", $player->y + $player->getEyeHeight()),
				new DoubleTag("", $player->z),
			]),
			"Motion"      => new ListTag("Motion", [
				new DoubleTag("", $aimPos->x),
				new DoubleTag("", $aimPos->y),
				new DoubleTag("", $aimPos->z),
			]),
			"Rotation"    => new ListTag("Rotation", [
				new FloatTag("", $player->yaw),
				new FloatTag("", $player->pitch),
			]),
			"Health"      => new ShortTag("Health", 5),
			"Item"        => new CompoundTag("Item", [
				"id"     => new ShortTag("id", $item->getId()),
				"Damage" => new ShortTag("Damage", 0),
				"Count"  => new ByteTag("Count", 1),
			]),
			"PickupDelay" => new ShortTag("PickupDelay", 3),
			"scorpio"     => new ByteTag("scorpio", 1),
			"launcher"    => new StringTag("launcher", $player->getName()),
		]);

		$f = 1.5;
		$tnt = Entity::createEntity("Item", $player->getLevel(), $nbt, $player);
		$tnt->setMotion($tnt->getMotion()->multiply($f));
		$player->getViewers();
		$tnt->spawnToAll();

		return $tnt;
	}
}

class ScorpioTask extends PluginTask {

	protected $seconds = 15;
	/** @var Entity */
	protected $entity;

	public function __construct(PrestigeSocietyCore $core, Entity $entity, int $seconds){
		parent::__construct($core);
		$this->entity = $entity;
		$this->seconds = $seconds;
	}

	public function onRun(int $currentTick){

		if($this->seconds <= 0){
			$this->getOwner()->getScheduler()->cancelTask($this->getTaskId());
			$this->entity->close();

			return;
		}

		--$this->seconds;
	}
}