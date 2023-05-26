<?php

namespace PrestigeSociety\Kits\Special\Task;

use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;
use PrestigeSociety\Kits\Special\Kit\Kit;

class CoolDownResetTask extends PluginTask {

	/** @var PrestigeSocietyCore */
	protected $core;
	/** @var Player */
	protected $player;
	/** @var int $seconds */
	protected $seconds;

	/**
	 *
	 * CoolDownResetTask constructor.
	 *
	 * @param PrestigeSocietyCore $owner
	 * @param Player $player
	 * @param int $seconds
	 *
	 */
	public function __construct(PrestigeSocietyCore $owner, Player $player, int $seconds){
		parent::__construct($owner);
		$this->core = $owner;
		$this->player = $player;
		$this->seconds = $seconds;
	}


	/**
	 *
	 * Actions to execute when run
	 *
	 * @param int $currentTick
	 *
	 * @return void
	 *
	 */
	public function onRun(int $currentTick){
		if($this->seconds <= 0){
			$kit = $this->core->PrestigeSocietyKits->getSpecialKits()->getVault()->getPlayerKit($this->player);
			if($kit instanceof Kit){
				$kit->setAbilityActive(false);
				$this->core->PrestigeSocietyKits->getSpecialKits()->getVault()->setKit($this->player, $kit);
			}
			$this->getOwner()->getScheduler()->cancelTask($this->getTaskId());
			$message = $this->core->getMessage('special_kits', 'can_use_again');
			$this->player->sendPopup(RandomUtils::colorMessage($message));

			return;
		}

		if($this->core->PrestigeSocietyKits->getSpecialKits()->getVault()->isKitEnabled($this->player)){
			$message = $this->core->getMessage('special_kits', 'time_till_next_use');
			$message = str_replace("@seconds", $this->seconds, $message);
			$this->player->sendPopup(RandomUtils::colorMessage($message));
			--$this->seconds;
		}else{
			$this->core->getScheduler()->cancelTask($this->getTaskId());
		}
	}
}