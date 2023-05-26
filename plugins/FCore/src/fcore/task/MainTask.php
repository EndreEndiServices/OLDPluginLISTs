<?php

declare(strict_types=1);

namespace fcore\task;

use pocketmine\scheduler\Task;

/**
 * Class MainTask
 * @package fcore\task
 */
class MainTask extends Task {

    /** @var ScheduleManager $plugin */
    public $plugin;

    /**
     * MainTask constructor.
     * @param ScheduleManager $plugin
     */
    public function __construct(ScheduleManager $plugin) {
        $this->plugin = $plugin;
    }

    public function onRun(int $currentTick) {
        $this->plugin->plugin->floatingTextMgr->updateSlots();
    }
}