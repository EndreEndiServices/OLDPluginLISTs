<?php

namespace IPECore;

use pocketmine\event\Listener;
use pocketmine\inventory\InventoryHolder;
use pocketmine\block\Block;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityExplodeEvent;

class EventListener implements Listener {

	/** @var $plugin Main */
	private $plugin;

	/**
	 * Construct a new event listener class
	 *
	 * @param Main $plugin
	 */
	public function __construct(Main $plugin) {
		$this->plugin = $plugin;
		$plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
	}

	/**
	 * Get the owning plugin
	 *
	 * @return Main
	 */
	public function getPlugin() {
		return $this->plugin;
	}

	/**
	 * Handles autoinv block breaking
	 *
	 * @param BlockBreakEvent $event
	 *
	 * @return null
	 *
	 * @priority HIGHEST
	 */
	public function onBreak(BlockBreakEvent $event) {
		if($event->isCancelled()) {
			return;
		} else {
			foreach($event->getDrops() as $drop) {
				$event->getPlayer()->getInventory()->addItem($drop);
			}
		}
		$event->setDrops([]);
	}

	/**
	 * Handles autoinv entity death
	 *
	 * @param EntityDeathEvent $event
	 *
	 * @return null
	 *
	 * @priority HIGHEST
	 */
	public function onDeath(EntityDeathEvent $event) {
		$victim = $event->getEntity();
		$cause = $victim->getLastDamageCause();
		if($cause instanceof EntityDamageByEntityEvent) {
			$killer = $cause->getDamager();
			if($killer instanceof InventoryHolder) {
				foreach($event->getDrops() as $drop) {
					$killer->getInventory()->addItem($drop);
				}
			}
		} else {
			$event->setDrops([]);
		}
		return;
	}

	/**
	 * Handles autoinv entity exploding
	 *
	 * @param EntityExplodeEvent $event
	 *
	 * @return null
	 *
	 * @priority HIGHEST
	 */
	public function onExplode(EntityExplodeEvent $event) {
		if($event->isCancelled()) {
			return;
		} else {
			$explosive = $event->getEntity();
			$closest = PHP_INT_MAX;
			$entity = null;
			foreach($explosive->getLevel()->getNearbyEntities($explosive->getBoundingBox()->grow(24, 24, 24)) as $nearby) {
				if($explosive->distance($nearby) <= $closest) {
					$entity = $nearby;
					$closest = $explosive->distance($nearby);
				}
			}
			$disallowed = [
				Block::TNT,
				Block::FIRE,
				Block::LAVA,
				Block::WATER,
				Block::STILL_LAVA,
				Block::STILL_WATER,
			];
			$blocks = $event->getBlockList();
			if($entity instanceof InventoryHolder) {
				foreach($blocks as $key => $block) {
					if(isset($disallowed[$block->getId()])) {
						continue;
					}
					$entity->getInventory()->addItem($block);
					unset($blocks[$key]);
				}
			}
			$event->setBlockList($blocks);
		}
		return;
	}
}
