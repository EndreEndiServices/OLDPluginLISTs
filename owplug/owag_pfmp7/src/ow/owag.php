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
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerPreLoginEvent;

class owag extends PluginBase implements Listener {
	
	public function onEnable() {
		$this->owp = $this->getServer()->getPluginManager()->getPlugin("owperms");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	
	public function itemsMove(PlayerMoveEvent $e) {
		$player = $e->getPlayer();
		$inv = $player->getInventory()->getItemInHand()->getId();
		if($inv == 8 or $inv == 9 or $inv == 10 or $inv == 11 or $inv == 46 or $inv == 325 or $inv == 326 or $inv == 327) {
			$e->setCancelled();
			$this->close($player);
		}
	}
	
	public function itemsPlace(PlayerInteractEvent $e) {
		$player = $e->getPlayer();
		$inv = $player->getInventory()->getItemInHand()->getId();
		$x = $e->getBlock()->getX();
		$y = $e->getBlock()->getY();
		$z = $e->getBlock()->getZ();
		if($inv == 8 or $inv == 9 or $inv == 10 or $inv == 11 or $inv == 46 or $inv == 325 or $inv == 326 or $inv == 327) {
			$e->setCancelled();
			$this->getServer()->getDefaultLevel()->setBlock(new Vector3($x, $y + 1, $z), new Block(0));
			$this->close($player);
		}
	}
	
	public function close($player) {
	    $this->getLogger()->info(F::GREEN. $player->getName(). F::GOLD. " использует запрещенные предметы.");
		$player->close("", F::GOLD. "Использование запрещенных предметов.");
	}
	
}