<?php

namespace MCFTWARS;

use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\Player;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\level\Position;
use MCFTWARS\task\TeleportTask;

class EventListener implements Listener {
	private $plugin;
	public $touchinfo = array ();
	public function __construct(MCFTWARS $plugin) {
		$this->plugin = $plugin;
		$plugin->getServer ()->getPluginManager ()->registerEvents ( $this, $plugin );
	}
	public function onAttack(EntityDamageEvent $event) {
		if ($event instanceof EntityDamageByEntityEvent) {
			$damager = $event->getDamager ();
			$player = $event->getEntity ();
			if ($damager instanceof Player and $player instanceof Player) {
				if ($this->plugin->war->getSoldier ( $player ) == null or $this->plugin->war->getSoldier ( $damager ) == null) {
					return true;
				} else {
					if ($this->is_sameTeam ( $this->plugin->war->getSoldier ( $player ), $this->plugin->war->getSoldier ( $damager ) )) {
						$event->setCancelled ( true );
					}
				}
			}
		}
	}
	public function is_sameTeam(soldier $soldier1, soldier $soldier2) {
		if ($soldier1->getTeam ()->getTeamName () == $soldier2->getTeam ()->getTeamName ()) {
			return true;
		} else {
			return false;
		}
	}
	public function onTouch(PlayerInteractEvent $event) {
		$player = $event->getPlayer ();
		$block = $event->getBlock ();
		if ($this->plugin->war->getSoldier ( $player ) != null) {
			if ($block->getId () == 54) {
				$event->setCancelled ();
				$block = $event->getBlock ();
				if (! isset ( $this->touchinfo [$player->getName ()] )) {
					$this->giveRandomItem ( $player );
					$this->touchinfo [$player->getName ()] = [ ];
					array_push ( $this->touchinfo [$player->getName ()], "{$block->getX()}.{$block->getY()}.{$block->getZ()}" );
					$this->plugin->message ( $player, $this->plugin->get ( "get-item-from-chest" ) );
				} else {
					foreach ( $this->touchinfo [$player->getName ()] as $stringpos ) {
						if ($stringpos == "{$block->getX()}.{$block->getY()}.{$block->getZ()}") {
							$this->plugin->alert ( $player, $this->plugin->get ( "already-get-item" ) );
							return true;
						}
					}
					$this->giveRandomItem ( $player );
					array_push ( $this->touchinfo [$player->getName ()], "{$block->getX()}.{$block->getY()}.{$block->getZ()}" );
					$this->plugin->message ( $player, $this->plugin->get ( "get-item-from-chest" ) );
				}
			}
		}
	}
	public function giveRandomItem(Player $player) {
		$inventory = $player->getInventory ();
		$repeat = mt_rand ( 1, 3 );
		for($i = 0; $i < $repeat; $i ++) {
			$itemid = $this->plugin->itemlist ["item"] [mt_rand ( 0, count ( $this->plugin->itemlist ["item"] ) - 1 )];
			if ($itemid == 262) {
				$count = mt_rand ( 3, 10 );
			} else {
				$count = 1;
			}
			$inventory->addItem ( Item::get ( $itemid, 0, $count ) );
		}
	}
	public function onRespawn(PlayerRespawnEvent $event) {
		$soldier = $this->plugin->war->getSoldier($event->getPlayer());
		if ($soldier != null) {
			if ($soldier->getTeam()->getTeamName() == "레드팀") {
				$color = TextFormat::RED;
			} else {
				$color = TextFormat::BLUE;
			}
			$soldier->getPlayer()->setNameTag($color."[{$soldier->getTeam()->getTeamName()}] {$soldier->getPlayer()->getName()}");
			$this->plugin->getServer()->getScheduler()->scheduleDelayedTask(new TeleportTask($this->plugin, $soldier->getPlayer(), $soldier->getTeam()->getSpawnPoint()), 10);
		} else {
			if (isset($this->plugin->warDB["spawn"]["lobby"])){
				$this->plugin->getServer()->getScheduler()->scheduleDelayedTask(new TeleportTask($this->plugin, $event->getPlayer(), $this->plugin->war->getLobby()), 10);
			}
		}
	}
	public function onDeath(PlayerDeathEvent $event) {
		$player = $event->getEntity()->getPlayer();
		if($player != null) {
			if(isset($this->touchinfo[$player->getName()])) {
				unset($this->touchinfo[$player->getName()]);
			}
		}
	}
	public function onQuit(PlayerQuitEvent $event) {
		$player = $event->getPlayer();
		$this->plugin->war->leaveWar($player);
	}
	public function teleport(Player $player, Position $pos) {
		$player->teleport($pos);
	}
}