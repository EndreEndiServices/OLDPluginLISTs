<?php

/**
 * This is EntirelyQuartz property.
 *
 * Copyright (C) 2016 EntirelyQuartz
 *
 * This is private software, you cannot redistribute it and/or modify any way
 * unless otherwise given permission to do so. If you have not been given explicit
 * permission to view or modify this software you should take the appropriate actions
 * to remove this software from your device immediately.
 *
 * @author EntirelyQuartz
 * @twitter EntirelyQuartz
 *
 */

namespace ManualUHC;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use synapse\Player;

class EventListener implements Listener {

    /** @var Main */
    private $plugin;

    public $build = false;

    public $deathban = false;

    /**
     * EventListener constructor
     *
     * @param Main $plugin
     */
    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
    }

    public function onBreak(BlockBreakEvent $event) {
        if(!$this->build) {
            $event->setCancelled();
        }
    }

    public function onPlace(BlockPlaceEvent $event) {
        if(!$this->build) {
            $event->setCancelled();
        }
    }

    public function onDeath(PlayerDeathEvent $event) {
        $entity = $event->getEntity();
        if($entity instanceof Player) {
            if($this->deathban and !($entity->isOp())) {
                $entity->setWhitelisted(false);
            }
        }
    }

}