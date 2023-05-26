<?php

namespace PrestigeSociety\CombatLogger;

use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class CombatLoggerTask extends PluginTask {

	/** @var PrestigeSocietyCore */
	private $core;

	/** @var Player */
	private $player;

	/**
	 *
	 * CombatLoggerTask constructor.
	 *
	 * @param PrestigeSocietyCore $c
	 *
	 * @param Player $p
	 *
	 */
	public function __construct(PrestigeSocietyCore $c, Player $p){
		parent::__construct($c);
		$this->core = $c;
		$this->player = $p;
	}

	/**
	 *
	 * @param $currentTick
	 *
	 */
	public function onRun($currentTick){
		$this->core->PrestigeSocietyCombatLogger()->endTime($this->player);
		$this->player->sendMessage(RandomUtils::colorMessage($this->core->getMessage("combat_logger", "can_log_out")));
	}
}