<?php

namespace PrestigeSociety\CombatLogger;

use pocketmine\Player;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class PrestigeSocietyCombatLogger {

	/** @var Player[] */
	protected $sessions = [];

	/** @var int[] */
	protected $tasks = [];

	/**
	 *
	 * @API
	 *
	 * @param Player $p
	 *
	 * @param $time
	 *
	 * @return bool
	 *
	 */
	public function checkTime(Player $p, $time){
		if(!isset($this->sessions[spl_object_hash($p)])){
			$this->endTask($p);
			$this->startTask($time, $p);
			$this->sessions[spl_object_hash($p)] = time();
			$p->sendMessage(RandomUtils::colorMessage(PrestigeSocietyCore::getInstance()->getMessage("combat_logger", "pvp_danger")));

			return false;
		}

		if((time() - $this->sessions[spl_object_hash($p)]) <= $time){
			$this->endTask($p);
			$this->startTask($time, $p);
			$this->sessions[spl_object_hash($p)] = time();

			return true;
		}

		return false;
	}

	/**
	 *
	 * Not API
	 *
	 * @param Player $p
	 *
	 */
	protected function endTask(Player $p){
		if(isset($this->tasks[spl_object_hash($p)])){
			PrestigeSocietyCore::getInstance()->getScheduler()->cancelTask($this->tasks[spl_object_hash($p)]);
		}
	}

	/**
	 *
	 * Not API
	 *
	 * @param $time
	 *
	 * @param Player $p
	 *
	 */
	protected function startTask($time, Player $p){
		$handler = PrestigeSocietyCore::getInstance()->getScheduler()->
		scheduleDelayedTask($task = new CombatLoggerTask(PrestigeSocietyCore::getInstance(), $p), 20 * $time);
		$task->setHandler($handler);
		$this->tasks[spl_object_hash($p)] = $handler->getTaskId();
	}

	/**
	 *
	 * @API
	 *
	 * @param Player $p
	 *
	 * @return bool
	 *
	 */
	public function inCombat(Player $p){
		return isset($this->sessions[spl_object_hash($p)]);
	}

	/**
	 *
	 * @API
	 *
	 * @param Player $p
	 *
	 */
	public function endTime(Player $p){
		if(isset($this->sessions[spl_object_hash($p)])){
			$this->endTask($p);
			unset($this->sessions[spl_object_hash($p)]);
		}
	}
}