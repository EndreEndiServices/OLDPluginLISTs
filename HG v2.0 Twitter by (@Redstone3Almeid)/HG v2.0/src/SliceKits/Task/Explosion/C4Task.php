<?php

namespace SliceKits\Task\Explosion;

use pocketmine\level\Position;
use SliceKits\Necessary\Explosion;
use pocketmine\math\Vector3;

use pocketmine\scheduler\PluginTask;
use pocketmine\plugin\Plugin;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\entity\Effect;

use SliceKits\Loader;

class C4Task extends PluginTask{
    
    private $entityblock;
    
    public function __construct(Loader $plugin, $block) {
		parent::__construct ($plugin);
		$this->plugin = $plugin;
                $this->entityblock = $block;
	}
        public function onRun($currentTick) {
		$explosion = new Explosion(new Position($this->entityblock->x, ($this->entityblock->y), $this->entityblock->z, $this->entityblock->getLevel()), 4);
		$explosion->explodeB();
                
	}
    
}

