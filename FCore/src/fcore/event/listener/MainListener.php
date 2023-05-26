<?php

declare(strict_types = 1);

namespace fcore\event\listener;

use fcore\event\ListenerManager;
use fcore\FCore;
use fcore\profile\ProfileManager;
use fcore\profile\RankManager;
use fcore\Settings;
use fcore\task\MysteryChestTask;
use pocketmine\entity\Human;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\cheat\PlayerIllegalMoveEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\tile\EnderChest;

class MainListener implements Listener {

	/** @var ListenerManager $plugin */
	public $plugin;

	public $hide = [];

	public $muted = [];

	/**
	 * MainListener constructor.
	 * @param ListenerManager $plugin
	 */
	public function __construct(ListenerManager $plugin){
		$this->plugin = $plugin;
	}

	public function onChat(PlayerChatEvent $event){
		if(isset($this->muted[$event->getPlayer()->getName()])){
			$event->setFormat("");

			return;
		}
		$msg = RankManager::getChatFormat($event->getPlayer());
		$msg = str_replace("%name", $event->getPlayer()->getName(), $msg);
		$msg = str_replace("%msg", $event->getMessage(), $msg);
		$event->setFormat($msg);
	}

	/**
	 * @param PlayerJoinEvent $event
	 */
	public function onJoin(PlayerJoinEvent $event){
		$this->plugin->plugin->scheduleMgr->runJoinTask($event->getPlayer());
		$event->setJoinMessage("");
	}

	/**
	 * @param PlayerQuitEvent $event
	 */
	public function onQuit(PlayerQuitEvent $event){
		$rank = ProfileManager::getPlayerRank($event->getPlayer());
		if($rank != "guest"){
			$rank = RankManager::$displayRanks[$rank];
		}else{
			$rank = "";
		}
		$event->setQuitMessage("§7> $rank §r§7{$event->getPlayer()->getName()} §7left the game!");
		$this->plugin->plugin->lobbyUtilsMgr->removeUtils($event->getPlayer());
	}

	public function onBreak(BlockBreakEvent $event){
		$player = $event->getPlayer();
		if(in_array($player->getLevel()->getFolderName(), Settings::PROTECTED_LEVELS)){
			if(!$player->isOp()) $event->setCancelled(true);
		}
		if(in_array($player->getLevel()->getFolderName(), Settings::MAX_PROTECTED_LEVELS)){
			$event->setCancelled(true);
		}
	}

	public function onPlace(BlockPlaceEvent $event){
		$player = $event->getPlayer();
		if(in_array($player->getLevel()->getFolderName(), Settings::PROTECTED_LEVELS)){
			if(!$player->isOp()) $event->setCancelled(true);
		}
		if(in_array($player->getLevel()->getFolderName(), Settings::MAX_PROTECTED_LEVELS)){
			$event->setCancelled(true);
		}
	}

	public function onExhaust(PlayerExhaustEvent $event){
		$player = $event->getPlayer();
		if($player->getLevel()->getFolderName() == FCore::DEFAULT_LEVEL_NAME){
			$event->setCancelled(true);
			$player->setFood(20);
		}
	}

	public function onIllegalMove(PlayerIllegalMoveEvent $event){
		$player = $event->getPlayer();
		if($player->getLevel()->getFolderName() == FCore::DEFAULT_LEVEL_NAME || $player->getLevel()->getFolderName() == FCore::PARKOUR_LEVEL){
			$event->setCancelled(true);
		}
	}

	public function onLevelChange(EntityLevelChangeEvent $event){
		$entity = $event->getEntity();
		if(!$entity instanceof Player){
			return;
		}
		if($event->getOrigin()->getFolderName() == FCore::DEFAULT_LEVEL_NAME){
			$this->plugin->plugin->lobbyUtilsMgr->removeUtils($entity);
		}
		if($event->getTarget()->getFolderName() != FCore::DEFAULT_LEVEL_NAME){

		}else{
			if($entity->getGamemode() != $entity::CREATIVE){
				$this->plugin->plugin->scheduleMgr->runJoinTask($entity, false, true);
			}
		}
	}

	public function onDamage(EntityDamageEvent $event){
		$entity = $event->getEntity();
		/** @var Player $damager */
		$damager = null;
		if($entity->getLevel()->getFolderName() == FCore::DEFAULT_LEVEL_NAME){
			$event->setCancelled(true);
		}
		if(!$entity instanceof Player && $entity instanceof Human){
			if($event instanceof EntityDamageByEntityEvent){
				if($event->getDamager() instanceof Player){
					$damager = $event->getDamager();
				}
			}
			if($damager === null){
				return;
			}
			if($entity->namedtag->hasTag("Minigame")){
				$minigame = $entity->namedtag->getTagValue("Minigame", StringTag::class);
				switch($minigame){
					case "pvp":
						$this->plugin->plugin->pvpMgr->openPVPMenu($damager);
						break;
					case "minigames":
						$damager->sendMessage(FCore::getPrefix() . "§aYou were transferred to MiniGames #1!");
						$damager->teleport(new Vector3(421, 32, 425));
						$damager->removeAllEffects();
						break;
					default:
						$damager->sendMessage(FCore::getPrefix() . "§cThis MiniGame is under development!");
						break;
				}
			}
			if(isset($this->plugin->plugin->commands["npcCommand"]->rm[$damager->getName()])){
				unset($this->plugin->plugin->commands["npcCommand"]->rm[$damager->getName()]);
				$entity->close();
			}
		}
	}

	public function onInteract(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		if($player->getLevel()->getTile($event->getBlock()) instanceof EnderChest){
			/** @var MysteryChestTask $rc */
			$rc = $this->plugin->plugin->scheduleMgr->repeating["mystery"];
			$rc->setPlayer($player);
			$event->setCancelled(true);
		}
		if($event->getAction() == $event::RIGHT_CLICK_AIR || $event->getAction() == $event::LEFT_CLICK_AIR){
			switch($player->getInventory()->getItemInHand()->getId()){
				case Item::MOB_HEAD:
					$this->plugin->plugin->getServer()->getCommandMap()->dispatch($player, "profile");
					break;
				case Item::COMPASS:
					$player->teleport(new Position(264, 5, 255, $this->plugin->plugin->getServer()->getLevelByName(FCore::DEFAULT_LEVEL_NAME)));
					break;
				case Item::EMERALD:
					$player->sendMessage("§7----- === [ §6VIP §7] === -----\n" .
						"§eYou can buy it on our website\n" .
						"§6http://factionpe.tk §e.\n" .
						"§eIf you bought VIP, you will get\n" .
						"§6All gadgets, kits and particles,\n" .
						"§6You will able to join full server.\n" .
						"§6You will get 10.000 coins");
					break;
				case Item::DOUBLE_PLANT:
					$this->plugin->plugin->shopMgr->open($player);
					break;
				case Item::NETHER_STAR:
					$this->plugin->plugin->lobbyUtilsMgr->openCosmeticMenu($player);
					break;
				case Item::DYE:
					$meta = $player->getInventory()->getItemInHand()->getDamage();
					if($meta == 10){
						foreach($this->plugin->plugin->getServer()->getLevelByName(FCore::DEFAULT_LEVEL_NAME)->getPlayers() as $players){
							$player->hidePlayer($players);
						}
						$player->getInventory()->setItem(8, Item::get(Item::DYE, 5)->setCustomName("§r§2Show players!"));
						$player->sendMessage(FCore::getPrefix() . "§aPlayers hidden!");
					}else{
						foreach($this->plugin->plugin->getServer()->getLevelByName(FCore::DEFAULT_LEVEL_NAME)->getPlayers() as $players){
							$player->showPlayer($players);
						}
						$player->getInventory()->setItem(8, Item::get(Item::DYE, 10)->setCustomName("§r§aHide players!"));
						$player->sendMessage(FCore::getPrefix() . "§aPlayers showed!");
					}
					break;
			}
		}
	}

	/*public function onQuery(QueryRegenerateEvent $event) {
		$s = $this->plugin->plugin->slotsMgr->slots;
		$event->setPlayerCount($s["players"]);
		$event->setMaxPlayerCount($s["slots"]);
	}*/

	/*
	public function onKick(PlayerKickEvent $event) {
		$event->setCancelled();
		$this->plugin->plugin->getLogger()->alert("REASON: ".$event->getReason()."\nMSG: ".$event->getQuitMessage());
	}*/
}