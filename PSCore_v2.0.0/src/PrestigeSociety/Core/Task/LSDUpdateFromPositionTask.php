<?php

namespace PrestigeSociety\Core\Task;

use pocketmine\block\Block;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\scheduler\PluginTask;
use PrestigeSociety\Core\PrestigeSocietyCore;

class LSDUpdateFromPositionTask extends PluginTask {

	/** @var Block[] */
	protected $processedBlocks = [];
	/** @var int */
	protected $time = 0;
	/** @var Position */
	private $position = null;

	/**
	 *
	 * WelcomepositionTask constructor.
	 *
	 * @param PrestigeSocietyCore $owner
	 * @param Position $position
	 *
	 */
	public function __construct(PrestigeSocietyCore $owner, Position $position){
		parent::__construct($owner);
		$this->position = $position;
	}

	/**
	 *
	 * Called upon task cancel
	 *
	 */
	public function onCancel(){
		foreach($this->processedBlocks as $index => $block){
			$block->getLevel()->setBlock($block, $block);
			unset($this->processedBlocks[$index]);
		}
	}

	/**
	 * Actions to execute when run
	 *
	 * @param int $currentTick
	 *
	 * @return void
	 *
	 */
	public function onRun(int $currentTick){

		$position = $this->position;

		/** @var Vector3[] $positions */
		$positions = [];

		for($x = -1; $x <= 1; $x++){
			for($z = -1; $z <= 1; $z++){
				$positions[] = $position->getLevel()->getBlock($position->subtract($x, 1, $z));
			}
		}

		foreach($this->processedBlocks as $index => $block){
			$block->getLevel()->setBlock($block, $block);
			unset($this->processedBlocks[$index]);
		}

		if($this->time % 10 === 0){
			$this->getOwner()->getScheduler()->cancelTask($this->getTaskId());
		}

		echo $this->time;

		++$this->time;

	}
}