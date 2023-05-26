<?php

namespace Kits;

use Kits\KitListener;
use Kits\exceptions\KitsNotEnableException;
use Kits\exceptions\PlayerNotAuthException;
use LbCore\player\LbPlayer;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\entity\Effect;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\Item;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use Kits\task\SaveKitsTask;

/**
 * Base class for kit logic, enable listener and tasks
 */
class Kit {
	// contains cooldown for skills (in seconds)
	const TELEPORT_COOLDOWN = 60;
	const TNT_COOLDOWN = 90;
	const PLANK_COOLDOWN = 60;
	
	// kits db connection part
	protected static $dbHost = 'p:accessory.lbsg.net';
	protected static $dbName = 'ingamekits';
	protected static $dbUsername = 'ingamekits';
	protected static $dbPass = 'jdyhu7c7olaP3';
	
	/**@var bool*/
	protected static $isEnabled = false;

	/**
	 * @param Plugin $plugin
	 */
	public static function enable(Plugin $plugin) {
		if (!self::$isEnabled) {
			self::$isEnabled = true;
			Server::getInstance()->getPluginManager()->registerEvents(
					new KitListener($plugin), $plugin
			);
			KitData::enable();
		}
	}

	public static function isEnable() {
		return self::$isEnabled;
	}

	/**
	 * 
	 * @param string $kitName
	 * @return bool
	 */
//	public static function isKitExist($kitName) {
//		$methodName = '_' . strtolower($kitName);
//		return method_exists(__CLASS__, $methodName);
//	}

	/**
	 *
	 * Main function apply kits to player
	 *
	 * @param LbPlayer $player
	 * @param int $kitId
	 * @return boolean
	 */
	public static function activateKitsForPlayer(LbPlayer $player, $kitId = 0) {
		if (!self::$isEnabled) {
			throw new KitsNotEnableException();
		}		
		if (!$kitId) {
			$kitId = $player->getKits();
		}
		//new logic for kits
		$kit = KitData::getKit($kitId);
		if(!$kit) {
			return false;
		}		
		if (!$player->isAuthorized()) {
			throw new PlayerNotAuthException();
		}
		self::giveInventory($player, $kit);
		//add specific kit options and effects
		switch($kitId){
            case KitData::CREEPER:
				$player->setKitAdditionalData('lastTntUse', time() - self::TNT_COOLDOWN);
				$player->setKitAdditionalData('tntPosition', array());
                break;
            case KitData::ATHLETE:
				$jumpAmplifier = $kit->effects->jump - 1;
				$player->addEffect(Effect::getEffect(Effect::JUMP)->setAmplifier($jumpAmplifier)->setDuration(20 * 60 * 60 * 24));
                break;
			case KitData::TELEPORTER:
				$player->setKitAdditionalData('lastTeleportUse', time() - self::TELEPORT_COOLDOWN);
				break;
        }
	}
	
	/**
	 * Setup weapons and armor for player by specified kit
	 * 
	 * @param Player $player
	 * @param stdClass $kit
	 */
	private static function giveInventory(Player $player, $kit) {
		if(count($kit->inventory) > 0){
			// Loop through the 'inventory' that is in the kitData.json
			foreach($kit->inventory as $item) {

				if(!isset($item->damage)){
					$item->damage = 0;
				}
				if ($item->hotbar) {
					$itemIndex = $player->getInventory()->firstEmpty();
					$player->setHotbarItem($itemIndex, Item::get($item->id, $item->damage, $item->amount));
				} else {
					$player->getInventory()->addItem(Item::get($item->id, $item->damage, $item->amount));
				}
			}
        }
        if(count($kit->armor) > 0){
			// Loop through the 'armor' that is in the kitData.json
			foreach($kit->armor as $item){
				if($item->hotbar && is_int($item->slot)){
					self::setArmorItem($player, $item->slot, Item::get($item->id, 0));
				} else {
					$player->getInventory()->addItem(Item::get($item->id, 0, $item->amount));
				}
			}
        }
	}

	/**
	 * Use to deactivate kits when player is not in arena
	 * 
	 * @param LbPlayer $player
	 */
	public static function deactivateKitsForPlayer(LbPlayer $player) {
		if (!self::$isEnabled) {
			throw new KitsNotEnableException();
		}
		$kitId = $player->getKits();
		$kit = KitData::getKit($kitId);
		if(!$kit) {
			return false;
		}
		if(count($kit->inventory) > 0){
			//remove selected items
			foreach($kit->inventory as $item) {
				$searchedItem = Item::get($item->id);
				$searchedItem->setDamage(null);
				$searchedItem->setCompound(null);
				$player->getInventory()->remove($searchedItem);
			}
		}
		if(count($kit->armor) > 0){
			//remove selected items
			foreach($kit->armor as $item) {
				$searchedItem = Item::get($item->id);
				$searchedItem->setDamage(null);
				$searchedItem->setCompound(null);
				$player->getInventory()->remove($searchedItem);
			}
		}
		$player->getInventory()->sendContents($player);
		if($kit->effects->speed) {
			$player->removeEffect(Effect::SPEED);
		}
		if ($kit->effects->jump) {
			$player->removeEffect(Effect::JUMP);
		}
		$player->unsetKitAdditionalData();
	}

	/**
	 * Set armor for player
	 *
	 * @param Player $player
	 * @param type $index (position in inventory)
	 * @param Item $item
	 *
	 * Index:
	 *	0 - head
	 *	1 - chest
	 *	2 - legs
	 *	3 - foots
	 */
	private static function setArmorItem(Player $player, $index, Item $item) {
		$player->getInventory()->setArmorItem($index, $item);
		$player->getInventory()->sendArmorContents($player);
	}

	/**
	 * Attack, defense and knockback logic for brawler and highlander kits
	 *
	 * @param EntityDamageByEntityEvent $event
	 */
	static public function brawler(EntityDamageByEntityEvent $event) {
		$player = $event->getEntity();
		$damager = $event->getDamager();

		$damage = $event->getDamage();
		$addAttack = round($damage * 0.1);
		$addDefense = round($damage * 0.2);

		$knockback = $event->getKnockBack();
		$brawlerKnockback = $knockback * 0.2;

		if ($player instanceof LbPlayer && $player->haveKit(KitData::BRAWLER)) {
			$damage -= $addDefense;
			$knockback -= $brawlerKnockback;
		}
		if ($damager instanceof Player && $damager->haveKit(KitData::BRAWLER)) {
			$damage += $addAttack;
			$knockback += $brawlerKnockback;
		}

//		if ($player instanceof Player && $player->haveKit('highlander')) {
//			$damage = 0;
//		}

		$event->setDamage($damage);
		$event->setKnockBack($knockback);
	}

	/**
	 *  Set weekly random kit for player
	 * Info about kit of the day lies in KitData
	 * This method calls in onPlayerAuth event only to registered players
	 *
	 * @param Player $player
	 * @param bool $sendMessage
	 */
	public static function giveRandomKit(Player $player, $sendMessage = false) {
//		$kitId = KitData::getRandomKit();
		$numKits = KitData::$kitCount;
		$kitId = rand(1, $numKits);
		if ($kitId && $player instanceof LbPlayer) {
			try {
				$player->addKit($kitId);
			} catch (PlayerBaseException $e) {
				Server::getInstance()->getLogger()->warning($e->getMessage());
				return;
			}
		}
		// save kits into db
		$task = new SaveKitsTask($player->getName(), $player->getKits());
		Server::getInstance()->getScheduler()->scheduleAsyncTask($task);
		
		if ($sendMessage) {
			$KitName = KitData::getKitName($kitId);
			$player->sendLocalizedMessage("WON_KIT", array($KitName));
			$player->sendLocalizedMessage(KitData::getKitDesc($kitId), [], TextFormat::YELLOW);
			$player->sendLocalizedMessage("VIP_CHANGE_KIT");
		}
	}

}
