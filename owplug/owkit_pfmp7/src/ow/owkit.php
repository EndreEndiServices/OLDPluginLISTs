<?php

namespace ow;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat;
use pocketmine\utils\TextFormat as F;
use pocketmine\block\Block;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\level\particle\ItemBreakParticle;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;

class owkit extends PluginBase implements Listener {
	public $kill;
	
	public function onEnable() {
		$this->owp = $this->getServer()->getPluginManager()->getPlugin("owperms");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	
	public function joins(PlayerJoinEvent $e) {
		$player = $e->getPlayer();
		$mp = $player->getName();
		$this->kill[$mp] = false;
	}
	
	public function deaths(PlayerDeathEvent $e) {
		$player = $e->getEntity();
		$mp = $player->getName();
		$this->kill[$mp] = false;
	}
	
	public function giveKit($player) {
		$group = $this->owp->getGroup($player->getName());
		if(!($this->kill[$player->getName()])) {
			if($group == "user") {
				$player->getInventory()->addItem(new Item(58, 0, 1));
				$player->getInventory()->addItem(new Item(61, 0, 1));
				$player->getInventory()->addItem(new Item(54, 0, 2));
				$player->getInventory()->addItem(new Item(297, 0, 16));
				$player->getInventory()->addItem(new Item(272, 0, 1));
				$player->getInventory()->addItem(new Item(275, 0, 1));
				$player->getInventory()->addItem(new Item(274, 0, 1));
				$player->getInventory()->addItem(new Item(263, 0, 16));
				$player->getInventory()->addItem(new Item(298, 0, 1));
				$player->getInventory()->addItem(new Item(299, 0, 1));
				$player->getInventory()->addItem(new Item(300, 0, 1));
				$player->getInventory()->addItem(new Item(301, 0, 1));
				$player->getInventory()->addItem(new Item(4, 0, 64));
				$player->getInventory()->addItem(new Item(17, 0, 32));
				$this->kill[$player->getName()] = true;
				$player->sendMessage(F::YELLOW. "[OWKit]" .F::GOLD. " Вы получили свой кит.");
			} elseif($group == "vip") {
				$player->getInventory()->addItem(new Item(267, 0, 1));
				$player->getInventory()->addItem(new Item(258, 0, 1));
				$player->getInventory()->addItem(new Item(257, 0, 2));
				$player->getInventory()->addItem(new Item(265, 0, 16));
				$player->getInventory()->addItem(new Item(264, 0, 16));
				$player->getInventory()->addItem(new Item(263, 0, 16));
				$player->getInventory()->addItem(new Item(58, 0, 1));
				$player->getInventory()->addItem(new Item(61, 0, 1));
				$player->getInventory()->addItem(new Item(54, 0, 2));
				$player->getInventory()->addItem(new Item(306, 0, 1));
				$player->getInventory()->addItem(new Item(307, 0, 1));
				$player->getInventory()->addItem(new Item(308, 0, 1));
				$player->getInventory()->addItem(new Item(309, 0, 1));
				$player->getInventory()->addItem(new Item(320, 0, 32));
				$this->kill[$player->getName()] = true;
				$player->sendMessage(F::YELLOW. "[OWKit]" .F::GOLD. " Вы получили свой кит.");
			} elseif($group == "premium" || $group == "admin" || $group == "helper" || $group == "youtube") {
				$player->getInventory()->addItem(new Item(58, 0, 1));
				$player->getInventory()->addItem(new Item(61, 0, 1));
				$player->getInventory()->addItem(new Item(54, 0, 2));
				$player->getInventory()->addItem(new Item(310, 0, 1));
				$player->getInventory()->addItem(new Item(311, 0, 1));
				$player->getInventory()->addItem(new Item(312, 0, 1));
				$player->getInventory()->addItem(new Item(313, 0, 1));
				$player->getInventory()->addItem(new Item(276, 0, 1));
				$player->getInventory()->addItem(new Item(277, 0, 1));
				$player->getInventory()->addItem(new Item(278, 0, 1));
				$player->getInventory()->addItem(new Item(279, 0, 1));
				$player->getInventory()->addItem(new Item(264, 0, 64));
				$player->getInventory()->addItem(new Item(265, 0, 64));
				$player->getInventory()->addItem(new Item(266, 0, 64));
				$player->getInventory()->addItem(new Item(263, 0, 16));
				$player->getInventory()->addItem(new Item(17, 0, 64));
				$this->kill[$player->getName()] = true;
				$player->sendMessage(F::YELLOW. "[OWKit]" .F::GOLD. " Вы получили свой кит.");
			}
		} else {
			$player->sendMessage(F::YELLOW. "[OWKit]" .F::GOLD. " Вы уже взяли свой кит.");
		}
	}
	
}