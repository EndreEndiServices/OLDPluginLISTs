<?php

namespace mcg76\skywars;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\level\Explosion;
use pocketmine\event\block\BlockEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityMoveEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\math\Vector3 as Vector3;
use pocketmine\math\Vector2 as Vector2;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\network\protocol\AddMobPacket;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\UpdateBlockPacket;
use pocketmine\block\Block;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\protocol\DataPacket;
use pocketmine\network\protocol\Info;
use pocketmine\network\protocol\LoginPacket;
use pocketmine\entity\FallingBlock;
use pocketmine\command\defaults\TeleportCommand;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;

/**
 * MCG76 ArenaKits
 *
 * Copyright (C) 2014 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 * @author minecraftgenius76@gmail.com
 *        
 */
class ArenaKit {
	public $name;
	public $cost;
	public $items;
	public static function addGameKit($kitName, Player $p) {
		$p->sendMessage ( "add game kit to player" );
		SkyBlockKit::getSkyblockKit ( $p );
		$p->updateMovement ();
	}
	public static function getSkyblockKit(Player $p) {
		if (! $p->getInventory ()->contains ( new Item ( Item::STRING ) )) {
			$p->getInventory ()->addItem ( new Item ( Item::STRING, 0, 12 ) );
		}
		if (! $p->getInventory ()->contains ( new Item ( Item::ICE ) )) {
			$p->getInventory ()->addItem ( new Item ( Item::ICE, 0, 2 ) );
		}
		if (! $p->getInventory ()->contains ( new Item ( Item::LAVA ) )) {
			$p->getInventory ()->addItem ( new Item ( Item::LAVA, 0, 1 ) );
		}
		if (! $p->getInventory ()->contains ( new Item ( Item::BUCKET ) )) {
			$p->getInventory ()->addItem ( new Item ( Item::BUCKET, 0, 1 ) );
		}
		if (! $p->getInventory ()->contains ( new Item ( Item::SIGN ) )) {
			$p->getInventory ()->addItem ( new Item ( Item::SIGN, 0, 3 ) );
		}
		if (! $p->getInventory ()->contains ( new Item ( Item::CHEST ) )) {
			$p->getInventory ()->addItem ( new Item ( Item::CHEST, 0, 2 ) );
		}
		if (! $p->getInventory ()->contains ( new Item ( Item::ROSE ) )) {
			$p->getInventory ()->addItem ( new Item ( Item::ROSE, 0, 1 ) );
		}
		if (! $p->getInventory ()->contains ( new Item ( Item::CACTUS ) )) {
			$p->getInventory ()->addItem ( new Item ( Item::CACTUS, 0, 1 ) );
		}
		if (! $p->getInventory ()->contains ( new Item ( Item::SEEDS ) )) {
			$p->getInventory ()->addItem ( new Item ( Item::SEEDS, 0, 3 ) );
		}
		if (! $p->getInventory ()->contains ( new Item ( Item::CAKE ) )) {
			$p->getInventory ()->addItem ( new Item ( Item::CAKE, 0, 1 ) );
		}
		if (! $p->getInventory ()->contains ( new Item ( Item::MELON ) )) {
			$p->getInventory ()->addItem ( new Item ( Item::MELON, 0, 1 ) );
		}
		if (! $p->getInventory ()->contains ( new Item ( Item::BONE ) )) {
			$p->getInventory ()->addItem ( new Item ( Item::BONE, 0, 1 ) );
		}
		if (! $p->getInventory ()->contains ( new Item ( Item::SUGAR_CANE ) )) {
			$p->getInventory ()->addItem ( new Item ( Item::SUGAR_CANE, 0, 1 ) );
		}
	}
	public static function getJoinServerKit(Player $p) {
		if (! $p->getInventory ()->contains ( new Item ( Item::SIGN ) )) {
			$p->getInventory ()->addItem ( new Item ( Item::SIGN, 0, 3 ) );
		}
	}
	public static function addBreakBlock(Player $p, $b) {
		// add this to player inventory
		if ($b->getId () == Block::STONE) {
			$p->getInventory ()->addItem ( new Item ( Item::STONE, 0, 1 ) );
		}
		if ($b->getId () == Block::COBBLESTONE) {
			$p->getInventory ()->addItem ( new Item ( Item::COBBLESTONE, 0, 1 ) );
		}
	}
	public static function getSkywarKit(Player $p) {
		// set Iron Amor to player
		$p->getInventory ()->setChestplate ( new Item ( 307, 0, 1 ) );
		$p->getInventory ()->setLeggings ( new Item ( 308, 0, 1 ) );
		$p->getInventory ()->setBoots ( new Item ( 309, 0, 1 ) );
		$p->getInventory ()->setHelmet ( new Item ( 306, 0, 1 ) );
		
		// notify viewers
		$p->getInventory ()->sendArmorContents ( $p );
		
		// fully health
		$p->setHealth ( 20 );
		
		// add iron sword, if not exist
		if (! $p->getInventory ()->contains ( new Item ( 272 ) )) {
			$p->getInventory ()->addItem ( new Item ( 272, 0, 1 ) );
		}
		if (! $p->getInventory ()->contains ( new Item ( 50 ) )) {
			$p->getInventory ()->addItem ( new Item ( 50, 0, 5 ) );
		}
		if (! $p->getInventory ()->contains ( new Item ( 261 ) )) {
			$p->getInventory ()->addItem ( new Item ( 261, 0, 1 ) );
		}
		if (! $p->getInventory ()->contains ( new Item ( 262 ) )) {
			$p->getInventory ()->addItem ( new Item ( 262, 0, 64 ) );
		}
		if (! $p->getInventory ()->contains ( new Item ( 260 ) )) {
			$p->getInventory ()->addItem ( new Item ( 260, 0, 2 ) );
		}
		
		//tnt
		if (! $p->getInventory ()->contains ( new Item ( 46 ) )) {
			$p->getInventory ()->addItem ( new Item ( 46, 0, 5 ) );
		}
		//FLINT_STEEL
		if (! $p->getInventory ()->contains ( new Item ( 259 ) )) {
			$p->getInventory ()->addItem ( new Item ( 259, 0, 1 ) );
		}
		//cobble stones
		if (! $p->getInventory ()->contains ( new Item ( 4 ) )) {
			$p->getInventory ()->addItem ( new Item ( 4, 0, 32 ) );
		}
		
		// chicken
		if (! $p->getInventory ()->contains ( new Item ( 366 ) )) {
			$p->getInventory ()->addItem ( new Item ( 366, 0, 2 ) );
		}
		if (! $p->getInventory ()->contains ( new Item ( 320 ) )) {
			$p->getInventory ()->addItem ( new Item ( 320, 0, 2 ) );
		}
		if (! $p->getInventory ()->contains ( new Item ( 364 ) )) {
			$p->getInventory ()->addItem ( new Item ( 364, 0, 2 ) );
		}
		if (! $p->getInventory ()->contains ( new Item ( 323 ) )) {
			$p->getInventory ()->addItem ( new Item ( 323, 0, 2 ) );
		}
		
		$p->updateMovement ();
		$p->getInventory ()->setHeldItemIndex ( 0 );
	}
	
	public static function getRandomChestItems(Player $player, $block) {
		$tile = $player->getLevel ()->getTile ( new Vector3 ( $block->x, $block->y, $block->z ) );
		// $new = [];
		// for($i = 0; $i < 27; $i++){
		// $new[] = array($tile->getItem($i)->getID(),$tile->getItem($i)->count,$tile->getItem($i)->getDamage());
		// }
		// var_dump($tile);
		// if($tile!=null && ($tile instanceof Chest)) {
		if ($tile != null) {
			$inv = $tile->getRealInventory ();
			$inv->setItem ( 1, new Item(Item::ICE, 0, 2));
			$inv->setItem ( 2, new Item(Item::LAVA, 0, 1));
			$inv->setItem ( 3, new Item(Item::BUCKET, 0, 1 ));
			$inv->setItem ( 4, ArenaKit::randomItems() );
			$inv->setItem ( 5, ArenaKit::randomItems() );
			$inv->setItem ( 6, ArenaKit::randomItems() );
			$inv->setItem ( 7, ArenaKit::randomItems() );
		}
	}
	
	public static function randomItems() {
		$i = rand ( 0, 60 );
		if ($i == 0) {
			return new Item ( Item::BOW, 0, 2 );
		}
		if ($i == 1) {
			return new Item ( Item::ARROW, 0, 64 );
		}
		if ($i == 2) {
			return new Item ( Item::APPLE, 0, 5 );
		}
		if ($i == 3) {
			return new Item ( Item::BREAD, 0, 5 );
		}
		if ($i == 4) {
			return new Item ( Item::BED, 0, 1 );
		}
		if ($i == 5) {
			return new Item ( Item::DIAMOND_CHESTPLATE, 0, 1 );
		}
		if ($i == 6) {
			return new Item ( Item::DIAMOND_BOOTS, 0, 1 );
		}
		if ($i == 7) {
			return new Item ( Item::DIAMOND_LEGGINGS, 0, 1 );
		}
		if ($i == 8) {
			return new Item ( Item::DIAMOND_HELMET, 0, 1 );
		}
		if ($i == 9) {
			return new Item ( Item::DIAMOND_SWORD, 0, 1 );
		}
		if ($i == 10) {
			return new Item ( Item::DIAMOND_AXE, 0, 1 );
		}
		if ($i == 11) {
			return new Item ( Item::DIAMOND_PICKAXE, 0, 1 );
		}
		if ($i == 12) {
			return new Item ( Item::IRON_BOOTS, 0, 1 );
		}
		if ($i == 13) {
			return new Item ( Item::IRON_CHESTPLATE, 0, 1 );
		}
		if ($i == 14) {
			return new Item ( Item::IRON_HELMET, 0, 1 );
		}
		if ($i == 15) {
			return new Item ( Item::IRON_LEGGINGS, 0, 1 );
		}
		if ($i == 16) {
			return new Item ( Item::IRON_SWORD, 0, 1 );
		}
		if ($i == 17) {
			return new Item ( Item::IRON_PICKAXE, 0, 1 );
		}
		if ($i == 18) {
			return new Item ( Item::COBBLESTONE, 0, 16 );
		}
		if ($i == 19) {
			return new Item ( 366, 0, 2 );
		}
		if ($i == 20) {
			return new Item ( 320, 0, 2 );
		}
		if ($i == 21) {
			return new Item ( 364, 0, 2 );
		}
		if ($i == 22) {
			return new Item ( 323, 0, 2 );
		}
		
		return new Item ( Item::AIR );
	}
}