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

class owmining extends PluginBase implements Listener {
	
	public function onEnable() {
		$this->owp = $this->getServer()->getPluginManager()->getPlugin("owperms");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	
	public function JoinPlayer(PlayerJoinEvent $e) {
		$player = $e->getPlayer();
	}
	
	public function give($player, $blockId, $blockMeta, $count) {
	    if($this->owp->getGroup($player->getName()) == "user") {
			$count = $count;
	    } elseif($this->owp->getGroup($player->getName()) == "vip") {
			$count = $count + 1;
	    } elseif($this->owp->getGroup($player->getName()) == "premium") {
			$count = $count + 4;
		}
		$player->getInventory()->addItem(new Item($blockId, $blockMeta, $count));
	}
	
	public function BreakBlock(BlockBreakEvent $e) {
		$player = $e->getPlayer();
		$blockName = $e->getBlock();
		$blockId = $e->getBlock()->getId();
		$blockMeta = $e->getBlock()->getDamage();
		$itemId = $e->getItem()->getId();
		$e->setDrops(array());
        $v = new Vector3($player->getLevel()->getSpawnLocation()->getX(),$player->getPosition()->getY(),$player->getLevel()->getSpawnLocation()->getZ());
        $r = $this->getServer()->getSpawnRadius();
		if(!($player->isOp() && $player->getGamemode() == 1)) {
            if(($player instanceof Player) && !(($player->getPosition()->distance($v) <= $r))) {
			    if($blockId == 1 && ($itemId == 270 || $itemId == 274 || $itemId == 257 || $itemId == 285 || $itemId == 278)) {
				    $this->give($player, 4, 0, 1);
			    }
			    if($blockId == 14 && ($itemId == 257 || $itemId == 285 || $itemId == 278)) {
				    $this->give($player, 266, 0, 1);
			    }
			    if($blockId == 15 && ($itemId == 274 || $itemId == 257 || $itemId == 285 || $itemId == 278)) {
				    $this->give($player, 265, 0, 1);
			    }
			    if($blockId == 16 && ($itemId == 270 || $itemId == 274 || $itemId == 257 || $itemId == 285 || $itemId == 278)) {
				    $this->give($player, 263, 0, 1);
			    }
			    if($blockId == 56 && ($itemId == 257 || $itemId == 285 || $itemId == 278)) {
				    $this->give($player, 264, 0, 1);
			    }
			    if($blockId == 73 && ($itemId == 257 || $itemId == 285 || $itemId == 278)) {
				    $this->give($player, 331, 0, 1);
			    }
			    if($blockId == 129 && ($itemId == 257 || $itemId == 285 || $itemId == 278)) {
				    $this->give($player, 388, 0, 1);
			    }
			
			    if($blockId == 17 && $blockMeta == 0 && $itemId == 0) {
				    $this->give($player, 17, 0, 1);
			    } 
				if($blockId == 17 && $blockMeta == 1 && $itemId == 0) {
				    $this->give($player, 17, 1, 1);
			    } 
			    if($blockId == 17 && $blockMeta == 2 && $itemId == 0) {
				    $this->give($player, 17, 2, 1);
			    } 
			    if($blockId == 17 && $blockMeta == 3 && $itemId == 0) {
				$this->give($player, 17, 3, 1);
			    }
			    if($blockId == 17 && $blockMeta == 0 && ($itemId == 271 || $itemId == 275 || $itemId == 286 || $itemId == 258 || $itemId == 279)) {
			        $this->give($player, 5, 0, 4);
			    } 
			    if($blockId == 17 && $blockMeta == 1 && ($itemId == 271 || $itemId == 275 || $itemId == 286 || $itemId == 258 || $itemId == 279)) {
				    $this->give($player, 5, 1, 4);
			    } 
			    if($blockId == 17 && $blockMeta == 2 && ($itemId == 271 || $itemId == 275 || $itemId == 286 || $itemId == 258 || $itemId == 279)) {
				    $this->give($player, 5, 2, 4);
			    } 
			    if($blockId == 17 && $blockMeta == 3 && ($itemId == 271 || $itemId == 275 || $itemId == 286 || $itemId == 258 || $itemId == 279)) {
				    $this->give($player, 5, 3, 4);
			    } 
			    if($blockId == 162 && $blockMeta == 0 && ($itemId == 271 || $itemId == 275 || $itemId == 286 || $itemId == 258 || $itemId == 279)) {
				    $this->give($player, 5, 4, 4);
			    } 
			    if($blockId == 162 && $blockMeta == 1 && ($itemId == 271 || $itemId == 275 || $itemId == 286 || $itemId == 258 || $itemId == 279)) {
			        $this->give($player, 5, 5, 4);
			    }
			    if($blockId != 1 && $blockId != 14 && $blockId != 15 && $blockId != 16 && $blockId != 56 && $blockId != 73 && $blockId != 129) {
				    if(!($blockId == 17 && $blockMeta == 0 && $itemId == 0)) {
					    $this->give($player, $blockId, $blockMeta, 1);
				    }
			    }
			}
		}
	}
	
}