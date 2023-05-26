<?php

namespace SalmonDE\TopVoter\Tasks;

use pocketmine\scheduler\Task;
use SalmonDE\TopVoter\TopVoter;

class UpdateVotesTask extends Task {

	/** @var TopVoter */
	private $plugin;

	public function __construct(TopVoter $owner){
		$this->plugin = $owner;
	}

	/**
	 * Actions to execute when run
	 *
	 * @param int $currentTick
	 *
	 * @return void
	 */
	public function onRun(int $currentTick){
		$data = [
			'Key'    => $this->plugin->getConfig()->get('API-Key'),
			'Amount' => $this->plugin->getConfig()->get('Amount'),
		];
		if($data['Key'] !== null){
			$this->plugin->getServer()->getAsyncPool()->submitTask(new QueryServerListTask($data));
		}else{
			$this->plugin->getLogger()->warning('Invalid API key!');
			$this->plugin->getScheduler()->cancelTask($this->getTaskId());
		}
	}
}
