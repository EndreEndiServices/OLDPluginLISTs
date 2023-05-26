<?php

namespace PrestigeSociety\Levels\Task;

use pocketmine\entity\Entity;
use pocketmine\entity\object\ItemEntity;
use pocketmine\item\Item;
use pocketmine\level\Location;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\HappyVillagerParticle;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;
use PrestigeSociety\Levels\PrestigeSocietyLevels;

class CreateAnimateTask extends PluginTask {
	/** @var Entity[] */
	protected $entities = [];
	/** @var Item[] */
	protected $items = [];
	/** @var Item[] */
	protected $items2 = [];
	/** @var Player */
	protected $player;
	private $radius = 1;
	private $particles = 100;
	private $location, $level;
	private $sphere = false;
	/** @var int */
	private $ran = 0;

	/**
	 *
	 * CreateAnimateTask constructor.
	 * @param PrestigeSocietyCore $core
	 * @param Player $player
	 * @param array $items
	 * @param Location $location
	 * @param bool $sphere
	 */
	public function __construct(PrestigeSocietyCore $core, Player $player, array $items, Location $location, bool $sphere){
		parent::__construct($core);
		$this->core = $core;
		$this->level = $location->getLevel();
		$this->location = &$location;
		$this->sphere = $sphere;
		$this->items = $items;
		$this->items2 = $items;
		$this->player = $player;
	}

	public function onRun(int $tick){

		/** @var PrestigeSocietyCore $owner */
		$owner = $this->getOwner();
		$count = count($this->items2);

		if(count($this->items) <= 0){
			$this->getOwner()->getScheduler()->cancelTask($this->getTaskId());
			for($i = 0; $i < $this->particles; ++$i){
				$vector = self::getRandomVector()->multiply(3);
				$this->level->addParticle(new FlameParticle($this->location->add($vector->x, $vector->y + 5, $vector->z)));
				$this->location->add($vector->x, $vector->y, $vector->z);
			}

			$this->location->level->broadcastLevelSoundEvent($this->location, LevelSoundEventPacket::SOUND_EXPLODE);

			foreach($this->items2 as $item){
				for($i = 0; $i <= 5; ++$i){
					$this->entities[] = $this->animation2($item);
				}
			}

			$owner->getInfoParticles()->updateCrateParticleNames(RandomUtils::colorMessage("&a-=[&dPrestigeSociety &fCrate&a]=-"), RandomUtils::colorMessage(
				"&eUse this to claim crates!\n" .
				"&eWays to get crates:\n" .
				"&7- Our Web Store, visit at: &apresoc.me/store\n" .
				"&7- Vote for us at: &apresoc.me/vote\n" .
				"&7- You can go mining, the more you level up\n" .
				"&7the higher the chance of receiving crates."
			));

			$owner->getScheduler()->scheduleDelayedTask(new DespawnEntitiesTask($owner, $this->entities), 30);
			PrestigeSocietyLevels::$onCoolDown = false;

			return;
		}

		if($this->ran > round($count / 2, 0)){
			$this->radius -= 1;
			$this->particles -= 20;
		}else{
			$this->radius += 1;
			$this->particles += 20;
		}

		for($i = 0; $i < $this->particles; ++$i){
			$vector = self::getRandomVector()->multiply($this->radius);
			if(!$this->sphere){
				$vector->y = abs($vector->getY());
			}
			$this->level->addParticle(new HappyVillagerParticle($this->location->add($vector->x, $vector->y + 5, $vector->z)));
			$this->location->add($vector->x, $vector->y, $vector->z);
		}

		$this->location->level->broadcastLevelSoundEvent($this->location, LevelSoundEventPacket::SOUND_LAUNCH);
		$this->location->level->broadcastLevelSoundEvent($this->location, LevelSoundEventPacket::SOUND_LAUNCH);
		$this->location->level->broadcastLevelSoundEvent($this->location, LevelSoundEventPacket::SOUND_LAUNCH);

		$item = array_shift($this->items);

		$owner->getInfoParticles()->updateCrateParticleNames(RandomUtils::colorMessage("&a-=[&dPrestigeSociety &fCrate&a]=-"), RandomUtils::colorMessage("&a> &eYou've received: &6" . $item->getName() . "&a!"));

		$this->entities[] = $this->animation($item);

		$this->player->getInventory()->addItem($item);

		$this->ran++;
	}

	/**
	 *
	 * @return Vector3
	 *
	 */
	private static function getRandomVector(): Vector3{
		$x = rand() / getrandmax() * 2 - 1;
		$y = rand() / getrandmax() * 2 - 1;
		$z = rand() / getrandmax() * 2 - 1;
		$v = new Vector3($x, $y, $z);

		return $v->normalize();
	}

	/**
	 * @param Item $item
	 * @return null|ItemEntity|Item
	 */
	protected function animation2(Item $item){
		$motion = new Vector3(mt_rand(-1, 1), 1, mt_rand(-1, 1));
		$item = $this->level->dropItem($this->location, $item, $motion, 20 * 30);

		return $item;
	}

	/**
	 * @param Item $item
	 * @return null|ItemEntity|Item
	 */
	protected function animation(Item $item){
		$motion = new Vector3(0, 2, 0);
		$item = $this->level->dropItem($this->location, $item, $motion, 20 * 30);

		return $item;
	}

	/**
	 * @return Item
	 */
	protected function getNextItem(): ?Item{
		if(count($this->items) > 0){
			return array_shift($this->items);
		}

		return null;
	}
}