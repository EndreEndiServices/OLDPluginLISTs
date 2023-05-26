<?php

namespace Ragnok123\task;

use pocketmine\math\Vector3;
use pocketmine\level\Position;
use pocketmine\scheduler\PluginTask;
use pocketmine\plugin\Plugin;
use pocketmine\Player;
use pocketmine\item\Item;

use Ragnok123\WallAntiCheat;

class ExecuteTask extends PluginTask
{
	public function __construct(Plugin $owner)
	{
		parent::__construct($owner);
		
	}

	public function onRun($currentTick)
	{
		
	}

	public function cancel()
	{
		if ($this->getHandler() != null)
			$this->getHandler()->cancel();
	}
}