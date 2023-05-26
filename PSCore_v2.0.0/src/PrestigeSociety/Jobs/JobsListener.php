<?php

namespace PrestigeSociety\Jobs;

use _64FF00\PurePerms\PurePerms;
use factions\engine\CombatEngine;
use factions\engine\MainEngine;
use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Player;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;
use revivalpmmp\pureentities\entity\animal\walking\Cow;
use sex\guard\Manager;
use slapper\entities\SlapperCow;
use slapper\entities\SlapperHuman;

class JobsListener implements Listener {

	/** @var PrestigeSocietyCore */
	private $core;

	/**
	 *
	 * CombatLoggerListener constructor.
	 *
	 * @param PrestigeSocietyCore $c
	 *
	 */
	public function __construct(PrestigeSocietyCore $c){
		$this->core = $c;
	}

	public function onJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		if(!$this->core->PrestigeSocietyNeeded->playerExists($player)){
			$this->core->PrestigeSocietyNeeded->addNewPlayer($player);
			$this->core->PrestigeSocietyNeeded->setNecesary($player, 250);
		}
		if(!$this->core->PrestigeSocietyExperience->playerExists($player)){
			$this->core->PrestigeSocietyExperience->addNewPlayer($player);
		}
		if(!$this->core->PrestigeSocietyLang->playerExists($player)){
			$this->core->PrestigeSocietyLang->addNewPlayer($player);
		}
		$this->core->getServer()->dispatchCommand($player, "spawn");
	}

	public function onChat(PlayerChatEvent $event){
		if($event->getMessage() == "/home") $event->setCancelled();
	}

	public function dataPacket(DataPacketReceiveEvent $event){
		$packet = $event->getPacket();
		if($packet instanceof ModalFormResponsePacket){
			$data = json_decode($packet->formData, true);
			if($data !== null){
				$this->core->PrestigeSocietyJobs->handleFormResponse($event->getPlayer(), $data, $packet->formId);
			}
		}
	}

	public function onMove(PlayerMoveEvent $event){
		$player = $event->getPlayer();
		$x = -194;
		$y = 83;
		$z = 422;
		if(
			($player->asPosition()->distance(new Vector3($x, $y, $z)) >= 4500) and
			(
				($player->getLevel()->getName() == "world") or
				($player->getLevel()->getName() == "load1") or
				($player->getLevel()->getName() == "teanether")
			)) $event->setCancelled();
	}

	public function onDrop(PlayerDropItemEvent $event){
		$item = $event->getItem();
		if($item->getId() == Item::END_BRICKS) $event->setCancelled();
		if($item->getId() == Item::BUCKET and $item->getDamage() == 20) $event->setCancelled();
		if($item->getId() == Item::BUCKET and $item->getDamage() == 1 and $item->hasEnchantments()) $event->setCancelled();
	}

	public function onInventoryOthers(InventoryTransactionEvent $event){
		$actions = $event->getTransaction()->getActions();
		$item = new Item(0);
		foreach($actions as $action){
			$item = $action->getTargetItem();
		}
		$bucket = Item::get(Item::BUCKET, 20, 1);
		$benchant = Item::get(Item::BUCKET, 1, 1);
		if($item === $bucket) $event->setCancelled();
		if(($item === $benchant) and $benchant->hasEnchantments()) $event->setCancelled();
	}

	public function onInventoryChange(InventoryPickupItemEvent $event){
		$item = $event->getItem();
		$bucket = Item::get(Item::BUCKET, 20, 1);
		$benchant = Item::get(Item::BUCKET, 1, 1);
		if($item === $bucket) $event->setCancelled();
		if(($item === $benchant) and $benchant->hasEnchantments()) $event->setCancelled();
	}

	public function onPlayerDeath(PlayerDeathEvent $event){
		$player = $event->getEntity();
		$contents = $player->getInventory()->getContents();
		foreach($contents as $item){
			if($item->getId() == Block::END_BRICKS){
				$items = $item;
				$player->getInventory()->removeItem($items);
			}
		}
		if(($player->getLastDamageCause() instanceof EntityDamageByEntityEvent)){
			/** @var EntityDamageByEntityEvent $damager */
			$damager = $player->getLastDamageCause();
			if($damager->getDamager() instanceof Player){
				$killer = $damager->getDamager();
				$random = mt_rand(2, 5);
				$group = PurePerms::getAPI()->getUserDataMgr()->getGroup($player);
				if($group == "Member"){
					$this->core->PrestigeSocietyExperience->addExp($killer, $random);
				}elseif($group == "VIP"){
					$this->core->PrestigeSocietyExperience->addExp($killer, $random + (25 / 100) * $random);
				}elseif($group == "Gold"){
					$this->core->PrestigeSocietyExperience->addExp($killer, $random + (35 / 100) * $random);
				}else{
					$this->core->PrestigeSocietyExperience->addExp($killer, $random);
				}
				$this->core->PrestigeSocietyExperience->checkLevel($killer);
			}
		}
	}

	public function onHurt(EntityDamageEvent $event){
		if(!CombatEngine::canDamageHappen($event)) return;
		$eventEntity = $event->getEntity();
		if($eventEntity == null) return;
		$damager = null;
		if($event instanceof EntityDamageByEntityEvent){
			/** @var EntityDamageByEntityEvent $ev */
			$ev = $event;
			if($ev instanceof EntityDamageByChildEntityEvent){
				$evc = $ev;
				if($evc->getDamager() instanceof Player) $damager = $evc->getDamager();
			}elseif($ev->getDamager() instanceof Player) $damager = $ev->getDamager();

			if($damager == null) return;

			/** @var Player $player */
			$player = $damager;
			if($this->core->PrestigeSocietyJobs->getJob($player) == "CowBoy"){
				$inventory = $player->getInventory();
				$item = $inventory->getItemInHand();
				if($eventEntity instanceof SlapperCow){
					$event->setCancelled();
					if($item->getId() == Item::BUCKET and $item->getDamage() == 20){
						$player->setImmobile();
						$this->core->getScheduler()->scheduleRepeatingTask(new JobsTask($this->core, $player), 20);
					}
				}
				if($eventEntity instanceof SlapperHuman){
					$event->setCancelled();
					if($item->getId() == Item::BUCKET and $item->hasEnchantments()){
						$inventory->removeItem($item);
						$this->core->PrestigeSocietyEconomy->addMoney($player, 250);
						$message = "&6[!] &eYou got &6250 silver coins &ebecause you gave me this bucket!\n&aThanks! &c<3";
						$message = RandomUtils::colorMessage($message);
						$player->sendMessage($message);
					}
				}
			}
		}
	}

	public function onPlace(BlockPlaceEvent $event){
		$block = $event->getBlock();
		if($block->getId() == Block::END_BRICKS) $event->setCancelled();
	}

	public function onBreak(BlockBreakEvent $event){
		$player = $event->getPlayer();
		$block = $event->getBlock();
		if(!MainEngine::canPlayerUseBlock($player, $block)) return;
		$item = $player->getInventory()->getItemInHand();
		if($this->core->PrestigeSocietyJobs->getJob($player) == "CowBoy"){
			if($block->getId() == Block::END_BRICKS){
				$inventory = $player->getInventory();
				$bucky = Item::get(Item::BUCKET, 20, 1);
				if($inventory->contains($bucky)){
					$contains = $inventory->getContents();
					foreach($contains as $item){
						if($item->getId() == Item::BUCKET){
							if(count($contains) >= 2){
								if($item->getDamage() == 20){
									$event->setCancelled();
									$event->setDrops([]);
									$message = "&6[!] &eYou can't have more buckets in Inventory!\n&ePlease go to Barn!";
									$message = RandomUtils::colorMessage($message);
									$player->sendMessage($message);
								}
							}
						}
					}
				}
				if($inventory->contains(Item::get(Item::END_BRICKS))){
					$contents = $inventory->getContents();
					foreach($contents as $item){
						if($item->getId() == Block::END_BRICKS){
							$items = $item;
							$count = $items->getCount();
							if($count >= 35){
								$event->setCancelled();
								$message = "&6[!] &eYou can't have more blocks in Inventory!\n&eI'm gonna set your blocks count to 30.";
								$message = RandomUtils::colorMessage($message);
								$player->sendMessage($message);
								$slot = array_search($items, $contents);
								$index = null;
								if($slot === false){
								}else{
									$index = $slot;
								}
								$inventory->removeItem($items);
								$inventory->setItem($index, Item::get(Item::END_BRICKS, 0, 30));
							}else{
								foreach($event->getDrops() as $drop){
									$inventory->addItem($drop);
								}
								$event->setDrops([]);
							}
							if(is_integer($count / 16)){
								$integer = $count / 16;
								$inventory->removeItem($items);
								$inventory->addItem(Item::get(Item::BUCKET, 20, $integer));
								$random = mt_rand(200, 250);
								$group = PurePerms::getAPI()->getUserDataMgr()->getGroup($player);
								if($group == "Member"){
									$this->core->PrestigeSocietyExperience->addExp($player, $random);
								}elseif($group == "VIP"){
									$this->core->PrestigeSocietyExperience->addExp($player, $random + (25 / 100) * $random);
								}elseif($group == "Gold"){
									$this->core->PrestigeSocietyExperience->addExp($player, $random + (35 / 100) * $random);
								}else{
									$this->core->PrestigeSocietyExperience->addExp($player, $random);
								}
								//$this->core->PrestigeSocietyExperience->checkLevel($player);
							}
						}
					}
				}else{
					if($block->getId() == Block::END_BRICKS){
						foreach($event->getDrops() as $drop){
							$inventory->addItem($drop);
						}
						$event->setDrops([]);
					}
				}
			}
		}else{
			if($block->getId() == Block::END_BRICKS){
				$event->setCancelled();
			}
		}
		if(Manager::getInstance()->getRegion($player->getPosition()) !== null) return;
		if(!$this->core->PrestigeSocietyJobs->hasJob($player)) return;
		if($this->core->PrestigeSocietyJobs->getJob($player) == "LumberJack"){
			switch($item->getId()){
				case Item::WOODEN_AXE:
				case Item::STONE_AXE:
				case Item::IRON_AXE:
				case Item::GOLD_AXE:
				case Item::DIAMOND_AXE:
					break;
				default:
					return;
			}
			switch($block->getId()){
				case Block::WOOD:
				case Block::WOOD2:
					$random = mt_rand(1, 3);
					$group = PurePerms::getAPI()->getUserDataMgr()->getGroup($player);
					if($group == "Member"){
						$this->core->PrestigeSocietyExperience->addExp($player, $random);
					}elseif($group == "VIP"){
						$this->core->PrestigeSocietyExperience->addExp($player, $random + (25 / 100) * $random);
					}elseif($group == "Gold"){
						$this->core->PrestigeSocietyExperience->addExp($player, $random + (35 / 100) * $random);
					}else{
						$this->core->PrestigeSocietyExperience->addExp($player, $random);
					}
					//$this->core->PrestigeSocietyExperience->checkLevel($player);
					$this->core->PrestigeSocietyEconomy->addMoney($player, 7.5);
					break;
			}
		}elseif($this->core->PrestigeSocietyJobs->getJob($player) == "Miner"){
			switch($item->getId()){
				case Item::WOODEN_PICKAXE:
				case Item::STONE_PICKAXE:
				case Item::IRON_PICKAXE:
				case Item::GOLD_PICKAXE:
				case Item::DIAMOND_PICKAXE:
					break;
				default:
					return;
			}
			switch($block->getId()){
				case Block::COBBLESTONE:
				case Block::STONE:
				case Block::COAL_ORE:
				case Block::IRON_ORE:
				case Block::GOLD_ORE:
				case Block::DIAMOND_ORE:
				case Block::LAPIS_ORE:
				case Block::REDSTONE_ORE:
					$random = mt_rand(1, 3);
					$group = PurePerms::getAPI()->getUserDataMgr()->getGroup($player);
					if($group == "Member"){
						$this->core->PrestigeSocietyExperience->addExp($player, $random);
					}elseif($group == "VIP"){
						$this->core->PrestigeSocietyExperience->addExp($player, $random + (25 / 100) * $random);
					}elseif($group == "Gold"){
						$this->core->PrestigeSocietyExperience->addExp($player, $random + (35 / 100) * $random);
					}else{
						$this->core->PrestigeSocietyExperience->addExp($player, $random);
					}
					//$this->core->PrestigeSocietyExperience->checkLevel($player);
					$this->core->PrestigeSocietyEconomy->addMoney($player, 5);
					break;
			}
		}elseif($this->core->PrestigeSocietyJobs->getJob($player) == "Farmer"){
			switch($block->getId()){
				case 59:
				case 104:
				case 105:
				case 244:
				case 141:
				case 142:
					switch($block->getDamage()){
						case 7:
							$random = mt_rand(1, 3);
							$group = PurePerms::getAPI()->getUserDataMgr()->getGroup($player);
							if($group == "Member"){
								$this->core->PrestigeSocietyExperience->addExp($player, $random);
							}elseif($group == "VIP"){
								$this->core->PrestigeSocietyExperience->addExp($player, $random + (25 / 100) * $random);
							}elseif($group == "Gold"){
								$this->core->PrestigeSocietyExperience->addExp($player, $random + (35 / 100) * $random);
							}else{
								$this->core->PrestigeSocietyExperience->addExp($player, $random);
							}
							//$this->core->PrestigeSocietyExperience->checkLevel($player);
							$this->core->PrestigeSocietyEconomy->addMoney($player, 2.5);
							break;
					}
					break;
				case Block::PUMPKIN:
				case Block::MELON_BLOCK:
				case Block::SUGARCANE_BLOCK:
				case Block::CACTUS:
				case 175:
					$random = mt_rand(1, 3);
					$group = PurePerms::getAPI()->getUserDataMgr()->getGroup($player);
					if($group == "Member"){
						$this->core->PrestigeSocietyExperience->addExp($player, $random);
					}elseif($group == "VIP"){
						$this->core->PrestigeSocietyExperience->addExp($player, $random + (25 / 100) * $random);
					}elseif($group == "Gold"){
						$this->core->PrestigeSocietyExperience->addExp($player, $random + (35 / 100) * $random);
					}else{
						$this->core->PrestigeSocietyExperience->addExp($player, $random);
					}
					//$this->core->PrestigeSocietyExperience->checkLevel($player);
					$this->core->PrestigeSocietyEconomy->addMoney($player, 2.5);
					break;
				case 127:
					switch($block->getDamage()){
						case 2:
							$random = mt_rand(1, 3);
							$group = PurePerms::getAPI()->getUserDataMgr()->getGroup($player);
							if($group == "Member"){
								$this->core->PrestigeSocietyExperience->addExp($player, $random);
							}elseif($group == "VIP"){
								$this->core->PrestigeSocietyExperience->addExp($player, $random + (25 / 100) * $random);
							}elseif($group == "Gold"){
								$this->core->PrestigeSocietyExperience->addExp($player, $random + (35 / 100) * $random);
							}else{
								$this->core->PrestigeSocietyExperience->addExp($player, $random);
							}
							//$this->core->PrestigeSocietyExperience->checkLevel($player);
							$this->core->PrestigeSocietyEconomy->addMoney($player, 2.5);
							break;
					}
					break;
			}
		}
		$this->core->PrestigeSocietyExperience->checkLevel($player);
	}

}