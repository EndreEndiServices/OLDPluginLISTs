<?php

declare(strict_types = 1);

namespace fcore\task;

use fcore\FCore;
use fcore\lang\Language;
use fcore\profile\ProfileManager;
use fcore\profile\RankManager;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\scheduler\Task;

/**
 * Class JoinTask
 * @package fcore\task
 */
class JoinTask extends Task {

	/** @var FCore $plugin */
	public $plugin;

	/** @var Player $player */
	public $player;

	public $join;

	/**
	 * JoinTask constructor.
	 * @param FCore $plugin
	 * @param Player $player
	 */
	public function __construct(FCore $plugin, Player $player, $join = true){
		$this->plugin = $plugin;
		$this->player = $player;
		$this->join = $join;
	}

	/**
	 * @param int $currentTick
	 */
	public function onRun(int $currentTick){
		if(!$this->player->isOnline()){
			$this->plugin->scheduleMgr->runJoinTask($this->player, $this->join, true);

			return;
		}
		if($this->join){
			ProfileManager::onJoin($this->player);
			$this->player->addTitle("§aWELCOME, " . strtoupper($this->player->getName()), "§6ON FACTIONPE [BETA]", 20, 20, 20);

			$msg = str_replace("%player", $this->player->getName(), Language::_(ProfileManager::lang($this->player), "join-msg"));
			$this->player->sendMessage($msg);

			$rank = ProfileManager::getPlayerRank($this->player);
			if($rank != "guest"){
				$rank = RankManager::$displayRanks[$rank];
			}else{
				$rank = "";
			}
			$this->plugin->getServer()->broadcastMessage("§7> $rank §r§7{$this->player->getName()} §7joined the game!");
		}

		FCore::loadFirst(FCore::DEFAULT_LEVEL_NAME);
		$this->player->teleport($this->plugin->getServer()->getLevelByName(FCore::DEFAULT_LEVEL_NAME)->getSafeSpawn());

		$this->player->getArmorInventory()->clearAll();

		$inv = $this->player->getInventory();
		$inv->clearAll();
		$inv->setItem(0, Item::get(Item::MOB_HEAD)->setCustomName("§aProfile"));
		$inv->setItem(1, Item::get(Item::COMPASS)->setCustomName("§r§bTeleporter\n§7§o- EggWars\n- MurderMystery"));
		$inv->setItem(4, Item::get(175)->setCustomName("§r§6Shop"));
		$inv->setItem(5, Item::get(Item::EMERALD)->setCustomName("§r§8§k|§r§7§k|§r§f§k|§r§e§l VIP §r§f§k|§r§7§k|§r§k§8|"));
		$inv->setItem(7, Item::get(Item::NETHER_STAR)->setCustomName("§r§6Cosmetics\n§7§o- Gadgets\n- Particles"));
		$inv->setItem(8, Item::get(Item::DYE, 10)->setCustomName("§r§aHide players"));
		$p = $this->player;
		$p->setGamemode($p::ADVENTURE);
		$p->setFood(20);
		$p->setMaxHealth(40);
		$p->setHealth(40);
	}
}