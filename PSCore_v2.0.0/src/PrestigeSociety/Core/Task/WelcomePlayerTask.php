<?php

namespace PrestigeSociety\Core\Task;

use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class WelcomePlayerTask extends PluginTask {

	/** @var string */
	private $sub, $title, $broad;
	/** @var Player */
	private $player = null;

	/**
	 *
	 * WelcomePlayerTask constructor.
	 *
	 * @param PrestigeSocietyCore $owner
	 * @param                     $title
	 * @param                     $sub
	 * @param                     $broad
	 * @param Player $player
	 *
	 */
	public function __construct(PrestigeSocietyCore $owner, $title, $sub, $broad, Player $player){
		parent::__construct($owner);
		$this->title = $title;
		$this->sub = $sub;
		$this->broad = $broad;
		$this->player = $player;

	}

	/**
	 * Actions to execute when run
	 *
	 * @param int $currentTick
	 *
	 * @return void
	 */
	public function onRun(int $currentTick){
		$this->player->addTitle(RandomUtils::colorMessage($this->title), RandomUtils::colorMessage($this->sub), 20, 100, 20);
		$this->player->getLevel()->broadcastLevelSoundEvent($this->player, LevelSoundEventPacket::SOUND_EXPLODE);
	}
}