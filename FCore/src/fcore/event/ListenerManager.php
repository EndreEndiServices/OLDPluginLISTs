<?php

declare(strict_types=1);

namespace fcore\event;

use fcore\event\listener\MainListener;
use fcore\event\listener\PvPListener;
use fcore\FCore;
use pocketmine\plugin\PluginManager;

/**
 * Class ListenerManager
 * @package fcore\event
 */
class ListenerManager {

    /** @var FCore $plugin */
    public $plugin;

    /** @var array $listeners */
    public $listeners = [];

    /**
     * ListenerManager constructor.
     * @param FCore $plugin
     */
    public function __construct(FCore $plugin) {
        $this->plugin = $plugin;
        $this->registerListeners();
    }

    /**
     * @return PluginManager
     */
    public function getPluginManager(): PluginManager {
        return $this->plugin->getServer()->getPluginManager();
    }

    public function registerListeners() {
        $this->listeners["main"] = new MainListener($this);
        $this->listeners["pvp"] = new PvPListener($this);
        foreach ($this->listeners as $index => $listener) {
            $this->getPluginManager()->registerEvents($listener, $this->plugin);
        }
    }
}