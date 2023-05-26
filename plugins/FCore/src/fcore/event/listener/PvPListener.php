<?php

declare(strict_types=1);

namespace fcore\event\listener;

use fcore\event\ListenerManager;
use fcore\FCore;
use fcore\profile\ProfileManager;
use pocketmine\block\Block;
use pocketmine\entity\Effect;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\level\particle\ExplodeParticle;
use pocketmine\level\particle\HugeExplodeParticle;
use pocketmine\level\Position;
use pocketmine\math\AxisAlignedBB;
use pocketmine\Player;
use pocketmine\scheduler\Task;

/**
 * Class PvPListener
 * @package fcore\event\listener
 */
class PvPListener implements Listener {

    /** @var ListenerManager $plugin */
    public $plugin;

    /**
     * PvPListener constructor.
     * @param ListenerManager $plugin
     */
    public function __construct(ListenerManager $plugin) {
        $this->plugin = $plugin;
    }

    public function onInteract(PlayerInteractEvent $event) {
        $player = $event->getPlayer();
        if($player->getLevel()->getFolderName() !== FCore::PVP_LEVEL_NAME) {
            return;
        }
        if($event->getAction() !== $event::RIGHT_CLICK_AIR) {
            return;
        }
        if($player->getInventory()->getItemInHand()->getId() == Item::TNT) {
            $player->getInventory()->removeItem(Item::get(Item::TNT));
            $block = $player->getLevel()->getBlock($player);
            $id = $block->getId();
            $meta = $block->getDamage();
            $block->getLevel()->setBlock($block, Block::get(Block::TNT));

            $task = new class($block, $id, $meta) extends Task {

                private $block;
                private $d = [];
                public function __construct(Block $block, $id, $meta) {
                    $this->block = $block;
                    $this->d = [$id, $meta];
                }

                public function onRun(int $currentTick) {
                    $block = $this->block;
                    $bb = new AxisAlignedBB($block->getX()-2, $block->getY()- 2, $block->getZ()-2, $block->getX()+2, $block->getY()+2, $block->getZ()+2);
                    foreach ($block->getLevel()->getNearbyEntities($bb) as $entity) {
                        if($entity instanceof Player) {
                            $entity->knockBack($entity, 2, rand(-1, 1), rand(-1, 1), 1.0);
                        }
                    }
                    $block->getLevel()->setBlock($block->asVector3(), Block::get($this->d[0], $this->d[1]));
                    $block->getLevel()->addParticle(new HugeExplodeParticle($block));
                    $block->getLevel()->addParticle(new ExplodeParticle($block));
                }
            };
            $this->plugin->plugin->getServer()->getScheduler()->scheduleDelayedTask($task, 35);
        }
    }

    /**
     * @param EntityDamageEvent $event
     */
    public function onDamage(EntityDamageEvent $event) {
        $entity = $event->getEntity();
        if(!$entity->getLevel()->getFolderName() == FCore::PVP_LEVEL_NAME) {
            return;
        }
        if(!$entity instanceof Player) {
            return;
        }

        if($entity->getLevel()->getFolderName() !== FCore::PVP_LEVEL_NAME) {
            return;
        }

        /** @var Player $damager */
        $damager = null;
        if($event instanceof EntityDamageByEntityEvent) {
            if($event->getDamager() instanceof Player) {
                $damager = $event->getDamager();
            }
        }

        if($entity->getHealth()-$event->getDamage() <= 0) {
            $event->setCancelled(true);
            if($damager instanceof Player) {
                foreach ($this->plugin->plugin->getServer()->getLevelByName(FCore::PVP_LEVEL_NAME)->getPlayers() as $player) {
                    $player->sendMessage("§7{$entity->getName()} was killed by {$damager->getName()} §6[{$damager->getHealth()}]");
                }
                $this->plugin->plugin->scheduleMgr->runJoinTask($entity, false, false);
                $regen = Effect::getEffect(Effect::REGENERATION);
                $regen->setDuration(200);
                $regen->setAmplifier(2);
                $damager->addEffect($regen);
                ProfileManager::addCoins($damager, 20);
                $damager->sendMessage(FCore::getPrefix()."§aYou recived 20 coins for kill!");
            }
            else {
                foreach ($this->plugin->plugin->getServer()->getLevelByName(FCore::PVP_LEVEL_NAME)->getPlayers() as $player) {
                    $player->sendMessage("§7{$entity->getName()} death.");
                }
                $this->plugin->plugin->scheduleMgr->runJoinTask($entity, false, false);
            }
        }
    }
}