<?php

namespace PrestigeSociety\Teleport\Task;

use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Teleport\Handle\Sessions;

class TPDelayTask extends PluginTask {

	/** @var PrestigeSocietyCore */
	protected $core;

	/** @var Player */
	protected $player;

	/** @var Position */
	protected $location;

	/** @var string */
	protected $message;

	/**
	 *
	 * TPDelayTask constructor.
	 *
	 * @param PrestigeSocietyCore $owner
	 * @param Player $player
	 * @param Position $position
	 * @param string $message
	 *
	 */
	public function __construct(PrestigeSocietyCore $owner, Player $player, Position $position, string $message = null){
		parent::__construct($owner);
		$this->core = $owner;
		$this->player = $player;
		$this->location = $position;
		$this->message = $message;
	}


	/**
	 * Actions to execute when run
	 *
	 * @param int $currentTick
	 *
	 * @return void
	 */
	public function onRun(int $currentTick){
		if($this->message !== null){
			$this->player->sendMessage($this->message);
		}
		$this->player->teleport($this->location);
		Sessions::removeFromQueue($this->player);
	}
}