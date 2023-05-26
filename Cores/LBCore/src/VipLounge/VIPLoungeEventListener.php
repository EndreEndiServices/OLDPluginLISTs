<?php

namespace VipLounge;

use LbCore\data\PluginData;
use LbCore\player\LbPlayer;
use pocketmine\Server;
use pocketmine\entity\Effect;
use pocketmine\item\Item;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\math\Vector3;


class VIPLoungeEventListener implements Listener {
	
	/** @var string, contains current game plugin name*/
	private $gameType;
	/*@var array of strict coords*/
	private $strictAreas;
	
	/**
	 * calls strict areas initialization
	 */
	public function __construct() {
		$this->gameType = PluginData::getGameType();
		$this->initStrictAreas();
	}

	/**
	 * calls on every step,
	 * if non-vip player is in VIP lounge, this code pull him away
	 *
	 * @param PlayerMoveEvent $event
	 */
	public function onPlayerMove(PlayerMoveEvent $event) {
		$player = $event->getPlayer();
		if (!is_null($this->strictAreas) &&
			(!$player->isAuthorized() || 
			($player->isAuthorized() &&
			!$player->isVip()))) {

			//pull away from the gates
			foreach ($this->strictAreas->guardAreas as $area) {
				if ($player->x > $area->minX && $player->x < $area->maxX) {
					if ($player->z > $area->minZ && $player->z < $area->maxZ) {
						$player->setMotion(new Vector3(
							$area->knockbackVector->x,
							$area->knockbackVector->y,
							$area->knockbackVector->z
						));
						$player->updatePushStatus(true);
						return;
					}
				}
			}

			//teleport from caffee zone to safe lobby point
			$lounge = $this->strictAreas->lounge;
			if ($player->x > $lounge->minX && $player->x < $lounge->maxX) {
				if ($player->z > $lounge->minZ && $player->z < $lounge->maxZ) {
					$player->teleport(new Vector3(
						$lounge->teleportVector->x,
						$lounge->teleportVector->y,
						$lounge->teleportVector->z
					));
					$player->updatePushStatus(true);
				}
			}
		}

	}


	/**
	 * method holds interaction with food signs in VIP lounge
	 *
	 * @param PlayerInteractEvent $event
	 * @return type
	 */
	public function onPlayerInteract(PlayerInteractEvent $event) {
		$block = $event->getBlock();
		$player = $event->getPlayer();
		
		//cafe signs
		if ($this->isCafeSign($block)) {
		    $this->setCureEffect($player);
			return;
		}

		if (strpos(get_class($block), 'SignPost') &&
			$block->getFloorY() === VIPLounge::VIP_TILE_COORD_Y &&
			$block->getFloorZ() === VIPLounge::VIP_TILE_COORD_Z) {
			
			//check for authorization
			if ($player->isAuthorized() == false) {
				$player->sendLocalizedMessage("NEEDS_LOGIN");
				return;
			}
			
			//check for vip status
			if (!$player->isVip()) {
				$player->sendLocalizedMessage("ONLY_FOR_VIP");
				return;
			}
			
			//collect bonus name and count
			$bonusInfo = $this->getSignInfo($block);			
			if ($bonusInfo && count($bonusInfo) > 1) {
				$bonusName = $this->getBonusNameAsConstant($bonusInfo[1]);
			}
			
			//set food or some fun effects
			$this->setBonus($player, $bonusName, $bonusInfo);			

		}

	}
	
	
	/**
	 * Collect array with info from bonus sign
	 * @param Block $block
	 * @return array
	 */
	private function getSignInfo($block) {
		$bonusInfo = array();
		
		//get interacted tile
		$tile = Server::getInstance()->getDefaultLevel()->getTile(new Vector3(
			$block->getFloorX(), $block->getFloorY(), $block->getFloorZ()
		));
		
		if ($tile) {		
			$bonusInfo = $tile->getText();//4 strings by default

			//prepare count of food items
			if (isset($bonusInfo[2][3]) && is_numeric(substr($bonusInfo[2], -1))) {
				$bonusInfo[2] = substr($bonusInfo[2], -1);
			}
			else {
				$bonusInfo[2] = null;
			}
		}
		
		return $bonusInfo;
	}
	
	
	/**
	 * Format bonus name to use like a constant
	 * 
	 * @param string $bonusInfo
	 * @return string
	 */
	private function getBonusNameAsConstant(string $bonusInfo = '') {
		$bonusName = "";
		// remove text color and prepare constant
		if (ord($bonusInfo[0]) === 194) {
			$bonusName = substr($bonusInfo, 6);
			$bonusName = strtoupper($bonusName);
			$bonusName = str_replace(" ", "_", $bonusName);
		}
		return $bonusName;
	}
	
	
	/**
	 * set player's bonus 
	 * 
	 * @param LbPlayer $player
	 * @param string $bonusName
	 * @param array $bonusInfo
	 */
	private function setBonus($player, string $bonusName, array $bonusInfo = null) {
		//set food items
		if ($bonusInfo[2] && !empty($bonusName)) {
			$item = Item::get(constant('\pocketmine\item\Item::' . $bonusName), 0, $bonusInfo[2]);
			$maxItem = Item::get(constant('\pocketmine\item\Item::' . $bonusName), 0, VIPLounge::FOOD_LIMIT);

			//check if allowed food limit exceeded
			if ($player->getInventory()->contains($maxItem)) {
				$player->sendLocalizedMessage("GOT_ENOUGH_FOOD", array($bonusInfo[1]));
				return;
			}

			//add into the same slot if these items are already in inventory
			if ($player->getInventory()->contains($item)) {
				$player->getInventory()->addItem($item);
			} else {//set first item
				$targetSlot = $player->getInventory()->firstEmpty();
				$player->getInventory()->clear($targetSlot);
				$player->getInventory()->setItem($targetSlot, $item);
				$player->getInventory()->setHotbarSlotIndex($targetSlot, $targetSlot);
			}
			
			//update shown inventory and send message
			$player->getInventory()->sendContents($player);
			$player->sendLocalizedMessage("GOT_FOOD", array($bonusInfo[2], $bonusInfo[1]));
		}
		else {
			//set drunk effect, some fun
			$this->setCureEffect($player, VIPLounge::CURE_TYPE_DRINK);
		}
	}
	
	/**
 	 * check if player interact with cafe sign
	 * cafeSigns also have Y coords, but no needed here for now
 	 * 
 	 * @param Block $block
 	 * @return bool
 	 */
 	private function isCafeSign($block) {
 		return strpos(get_class($block), 'SignPost') &&
 				$block->getFloorX() >= VIPLounge::$cafeSigns["minX"] && 
 				$block->getFloorX() <= VIPLounge::$cafeSigns["maxX"] &&
 				$block->getFloorZ() >= VIPLounge::$cafeSigns["minZ"] &&
 				$block->getFloorZ() <= VIPLounge::$cafeSigns["maxZ"];
 	}
 	
 	/**
 	 * Set heal effect when player drink coffee, tee or magic drink
 	 * 
 	 * @param Player $player
 	 * @param string $cureType
 	 */
 	private function setCureEffect($player, string $cureType = VIPLounge::CURE_TYPE_COFFEE) {
 		if ($player->isHealed()) {
 			$player->sendLocalizedMessage("ENOUGH_CURE");
 			return;
 		}
		//add health (power depends on drink)
 		$cureEffect = ($cureType === VIPLounge::CURE_TYPE_DRINK) ? 10 : 2;
 		$player->setHealth($player->getHealth() + $cureEffect);
 		$player->setAsHealed();
 		$cureMessage = "COFFEE_EFFECT";
 		if ($cureType === VIPLounge::CURE_TYPE_DRINK) {
 			$player->addEffect(Effect::getEffect(Effect::NAUSEA)->setAmplifier(2)->setDuration(20 * 20)->setVisible(false));
 			$cureMessage = "MAGIC_DRINK_WARNING";
 		}
 		$player->sendLocalizedMessage($cureMessage);
 	}


	/**
	 * creating protected area for VIP lounge, 
	 * calls in Listener construct
	 * 
	 */
	private function initStrictAreas() {
		//default filepath (now is used by Teams and BH plugin cause of similar lobby coords)
		$filePath = __DIR__.'/../Data/'.$this->gameType.'/loungeStrictArea.json';
		//override filepath for special plugins (like SG, not Teams)
//		switch ($this->gameType) {
//			case "SurvivalGames" :
//				$sg = Server::getInstance()->getPluginManager()->getPlugin("SurvivalGames");
//				if (!$sg->useTeams) {					
//					$filePath = './data/sg/lounge_strict_area.json';
//				}
//			break;
//		}
		$this->strictAreas = json_decode(file_get_contents($filePath));
	}

}
