<?php

namespace Kits;

use LbCore\event\PlayerAuthEvent;
use LbCore\player\exceptions\PlayerBaseException;
use LbCore\player\LbPlayer;
use Kits\Kit;
use Kits\task\GetKitsTask;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\block\Block;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerRespawnAfterEvent;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;


/**
 * Listener for kits add-on, checks for player kits when he is logged in
 * controls interactions with kit signs,
 * handles some specific buffed player options (damage, etc)
 */
class KitListener implements Listener {
	/** @var Plugin */
	private $plugin;

	/**
	 *
	 * @param lbcore $plugin
	 */
	public function __construct($plugin) {
		$this->plugin = $plugin;
	}

	/**
	 * Add existed kits to player object
	 * 
	 * @param PlayerLoginEvent $event
	 */
	public function onPlayerLogin(PlayerLoginEvent $event) {
		$player = $event->getPlayer();
		$task = new GetKitsTask($player->getName());
		Server::getInstance()->getScheduler()->scheduleAsyncTask($task);
	}

	/**
	 * Activate kits when player is authorized
	 * set to player's object one random kit (but not activate it)
	 * 
	 * @param PlayerAuthEvent $event
	 */
	public function onPlayerAuth(PlayerAuthEvent $event) {
		$player = $event->getPlayer();
		//check if player have not already get saved kit for today
		if (empty($player->getKits())) {
			//play random and save kit to db
			Kit::giveRandomKit($player, true);
		} else {
			//inform about existing kit
			$kitId = $player->getKits();
			$kitName = KitData::getKitName($kitId);
			$player->sendLocalizedMessage("GOT_SAVED_KIT", array($kitName));
		}
	}

	/**
	 * Reactivate kits when player is respawned
	 * 
	 * @param PlayerRespawnAfterEvent $event
	 */
	public function onPlayerRespawnAfter(PlayerRespawnAfterEvent $event) {
		$player = $event->getPlayer();
//		try {
//			Kit::activateKitsForPlayer($player);
//		} catch(KitBaseException $e) {
//			$this->plugin->getLogger()->warning($e->getMessage());
//		}
	}

	/**
	 * Interaction with kits items: egg, planks, TNT, flint and steel to use them
	 * also here interaction with kit signs to buy them
	 * 
	 * @param PlayerInteractEvent $event
	 */
	public function onInteract(PlayerInteractEvent $event) {
		$player = $event->getPlayer();
		if ($player->isSpectator()) {
			return;
		}
		// mystery check before player use kit item
		if ($event->getFace() === 0xff) {
			//allowed only on arena
			if ($player->getState() === LbPlayer::IN_LOBBY) {
				return;
			}
			$kitId = $player->getKits();
			if($kitId == 0) {				
				return;
			}	
			
			if (!$player->isAuthorized()) {
				$player->sendLocalizedMessage("NEEDS_LOGIN");
				return;
			}
			

			// teleport logic
			if ($player->haveKit(KitData::TELEPORTER)) {
                if(!$player->isInDeathmatch()){
                    if ($event->getItem()->getID() === Item::SPAWN_EGG) {
                        try {
                            $lastTeleportUse = $player->getKitAdditionalData('lastTeleportUse');
                        } catch (PlayerBaseException $e) {
                            $this->plugin->getLogger()->warning($e->getMessage());
                            return;
                        }

                        $timeDiff = time() - $lastTeleportUse;
                        if ($timeDiff >= Kit::TELEPORT_COOLDOWN) {
                            $this->launchTeleport($player);
                            $player->setKitAdditionalData('lastTeleportUse', time());
                        } else {
                            $player->sendTip(TextFormat::GREEN . "COOLDOWN : " . (Kit::TELEPORT_COOLDOWN - $timeDiff));
                        }
                    }
                }else{
                    $player->sendLocalizedPopup("CANT_TELEPORT_IN_DEATHMATCH");
                }
			}
			// tnt launcher logic
			if ($player->haveKit(KitData::CREEPER)) {
				if(!$player->isInDeathmatch()){
					if ($event->getItem()->getID() === Block::TNT) {
						try {
							$lastTntUse = $player->getKitAdditionalData('lastTntUse');
						} catch (PlayerBaseException $e) {
							$this->plugin->getLogger()->warning($e->getMessage());
							return;
						}

						$timeDiff = time() - $lastTntUse;
						if ($timeDiff >= Kit::TNT_COOLDOWN) {
							$this->launchTnt($player, $event->getTouchVector());
							$player->setKitAdditionalData('lastTntUse', time());
						} else {
							$player->sendTip(TextFormat::GREEN . "COOLDOWN : " . (Kit::TNT_COOLDOWN - $timeDiff));
						}
					}
				}
			}
		}
	}

	/**
	 * Special logic for brawler and highlander kits
	 *
	 * @param EntityDamageEvent $event
	 */
	public function onEntityDamage(EntityDamageEvent $event) {
		$player = $event->getEntity();

		if ($player instanceof LbPlayer) {
			if ($player->isInvincible()) {
				$event->setCancelled(true);
			} else {
				if ($event instanceof EntityDamageByEntityEvent) {
					Kit::brawler($event);
				}
			}
		}
	}

	private function launchTeleport(Player $player) {
		$this->launchEntity($player, "Kits\items\TeleportProjectile");
	}

	private function launchPlanks(Player $player) {
		$this->launchEntity($player, "Kits\items\PlunkLauncher");
	}

	private function launchTnt(Player $player) {
		$nbt = new Compound("", [
			"Pos" => new Enum("Pos", [
				new DoubleTag("", $player->x),
				new DoubleTag("", $player->y + $player->getEyeHeight()),
				new DoubleTag("", $player->z)
					]),
			"Motion" => new Enum("Motion", [
				new DoubleTag("", -sin($player->yaw / 180 * M_PI) * cos($player->pitch / 180 * M_PI)),
				new DoubleTag("", -sin($player->pitch / 180 * M_PI)),
				new DoubleTag("", cos($player->yaw / 180 * M_PI) * cos($player->pitch / 180 * M_PI))
					]),
			"Rotation" => new Enum("Rotation", [
				new FloatTag("", $player->yaw),
				new FloatTag("", $player->pitch)
					]),
		]);

		$f = 1.5;
		$entity = new \Kits\items\TntProjectile($player->chunk, $nbt, $player);
		$entity->shouldExplode = true;
		$entity->setMotion($entity->getMotion()->multiply($f));
		$entity->spawnToAll();
	}

	/**
	 * Launches entity with specified name
	 *
	 * @param Player $player
	 * @param string $entityName
	 */
	private function launchEntity(Player $player, string $entityName) {
		$nbt = new Compound("", [
			"Pos" => new Enum("Pos", [
				new DoubleTag("", $player->x),
				new DoubleTag("", $player->y + $player->getEyeHeight()),
				new DoubleTag("", $player->z)
					]),
			"Motion" => new Enum("Motion", [
				new DoubleTag("", -sin($player->yaw / 180 * M_PI) * cos($player->pitch / 180 * M_PI)),
				new DoubleTag("", -sin($player->pitch / 180 * M_PI)),
				new DoubleTag("", cos($player->yaw / 180 * M_PI) * cos($player->pitch / 180 * M_PI))
					]),
			"Rotation" => new Enum("Rotation", [
				new FloatTag("", $player->yaw),
				new FloatTag("", $player->pitch)
					]),
		]);

		$f = 1.5;
		$entity = new $entityName($player->chunk, $nbt, $player);
		$entity->setMotion($entity->getMotion()->multiply($f));
		$entity->spawnToAll();
	}
	
	public function onBlockPlace(BlockPlaceEvent $event) {
		//crreper kit can not lost tnt item
		$player = $event->getPlayer();
		$block = $event->getBlock();
		if ($player->haveKit(KitData::CREEPER) && ($block->getID() == Block::TNT)) {
			try {
				$lastTntUse = $player->getKitAdditionalData('lastTntUse');
			} catch (PlayerBaseException $e) {
				$this->plugin->getLogger()->warning($e->getMessage());
				$event->setCancelled(true);
				return;
			}

			$timeDiff = time() - $lastTntUse;
			if ($timeDiff >= Kit::TNT_COOLDOWN) {
				$block->onActivate(Item::get(Item::FLINT_STEEL), $player);
				$player->setKitAdditionalData('lastTntUse', time());
			} else {
				$player->sendTip(TextFormat::GREEN . "COOLDOWN : " . (Kit::TNT_COOLDOWN - $timeDiff));
			}
			$event->setCancelled(true);
		}
	}
}
