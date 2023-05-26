<?php

declare(strict_types=1);

namespace fcore\task;

use fcore\task\ScheduleManager;
use pocketmine\scheduler\Task;

class UtilsTask extends Task {

    /** @var ScheduleManager $plugin */
    public $plugin;

    public function __construct(ScheduleManager $plugin) {
        $this->plugin = $plugin;
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick) {
        $this->plugin->plugin->lobbyUtilsMgr->runGadgets();
        $this->plugin->plugin->lobbyUtilsMgr->runParticles();
        if($currentTick%(30*20) == 0) {
            $this->plugin->plugin->slotsMgr->update();
        }
    }
}