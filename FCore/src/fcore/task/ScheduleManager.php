<?php

declare(strict_types=1);

namespace fcore\task;

use fcore\FCore;
use pocketmine\Player;
use pocketmine\scheduler\Task;

/**
 * Class ScheduleManager
 * @package fcore\task
 */
class ScheduleManager {

    /** @var FCore $plugin */
    public $plugin;

    /** @var Task[] $repeating */
    public $repeating = [];
    
    /**
     * ScheduleManager constructor.
     * @param FCore $plugin
     */
    public function __construct(FCore $plugin) {
        $this->plugin = $plugin;
        $this->registerTasks();
    }

    public function getScheduler() {
        return $this->plugin->getScheduler();
    }

    public function registerTasks() {
        $this->repeating["utils"] = new UtilsTask($this);
        $this->repeating["broadcast"] = new BroadcastTask;
        $this->repeating["main"] = new MainTask($this);
        $this->repeating["mystery"] = new MysteryChestTask($this);
        foreach ($this->repeating as $index => $task) {
            if($index == "utils") $this->getScheduler()->scheduleRepeatingTask($task, 1);
            if($index == "broadcast") $this->getScheduler()->scheduleRepeatingTask($task, 20*60*3);
            if($index == "main") $this->getScheduler()->scheduleRepeatingTask($task, 200);
            if($index == "mystery") $this->getScheduler()->scheduleRepeatingTask($task, 4);
        }
    }

    /**
     * @param Player $player
     */
    public function runJoinTask(Player $player, $join = true, $wait = true) {
        $this->getScheduler()->scheduleDelayedTask(new JoinTask($this->plugin, $player, $join), $wait ? 20 : 0);
    }
}