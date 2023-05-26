<?php

namespace PrestigeSociety\Kits\Special\Kit;

use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use PrestigeSociety\Core\PrestigeSocietyCore;

class Berserker extends Kit {

	/** @var bool */
	public $activated = false;

	/**
	 *
	 * Berserker constructor.
	 *
	 * @param Item $specialItem
	 * @param array $items
	 * @param int $coolDown
	 * @param int $deactivate
	 *
	 */
	public function __construct(Item $specialItem, array $items = [], int $coolDown, int $deactivate = -1){
		parent::__construct("Berserker", $specialItem, $items, Kit::RIGHT_CLICK_MODE, $coolDown, $deactivate);
		DefaultPermissions::registerPermission(new Permission("kit.berserker", "", Permission::DEFAULT_OP));
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

			if(strtolower($data->getString("kit_name")) === "berserker"){
				if($this->checkCoolDown($player)){
					$player->setScale(2);
					$player->level->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_GROWL);
					$player->getScheduler()->scheduleRepeatingTask(new BerserkerTask($this->getSpecialKitsInstance()->getCore(), $player, $this->deactivate), 20);
				}
			}
		}

		return false;
	}
}

class BerserkerTask extends PluginTask {

	protected $seconds = 15;

	/** @var Player */
	protected $player;

	public function __construct(PrestigeSocietyCore $core, Player $player, int $seconds){
		parent::__construct($core);
		$this->player = $player;
		$this->seconds = $seconds;
	}

	public function onRun(int $currentTick){
		if($this->seconds <= 0){
			$this->player->setScale(1);
			$this->getOwner()->getScheduler()->cancelTask($this->getTaskId());

			return;
		}
		--$this->seconds;
	}
}