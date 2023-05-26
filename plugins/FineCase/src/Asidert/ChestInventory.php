<?php

namespace Asidert;

use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\tile\Tile;
use pocketmine\inventory\Inventory;
use pocketmine\math\Vector3;

class ChestInventory {
	protected $plugin;
	public function __construct(FineCase $plugin) {
		$this->plugin = $plugin;
	}
	public function getPlugin() {
		return $this->plugin;
	}
	public function refillRandomItems($level, $block) {
		if ($level == null || $block == null) {
			throw new \InvalidStateException ( "level or block may not be null" );
		}
		$tile = $level->getTile ( new Vector3 ( $block->x, $block->y, $block->z ) );
		if ($tile != null) {
			$inv = $tile->getRealInventory ();
			$inv->setItem ( 0, self::randomItems () );
			$inv->setItem ( 1, self::randomItems () );
			$inv->setItem ( 2, self::randomItems () );
			$inv->setItem ( 3, self::randomItems () );

		}
	}
	public function addInventoryRandomItems($level, Inventory $inv) {
		if ($inv != null) {
			$inv->setItem ( 0, self::randomItems () );
			$inv->setItem ( 1, self::randomItems () );
			$inv->setItem ( 2, self::randomItems () );
			$inv->setItem ( 3, self::randomItems () );

		}
	}
	public static function randomItems() {
		$i = mt_rand ( 1, 200 );
		if ($i == 1) {
			return new Item ( Item::STONE_HOE, 0, 1 );
		}
		if ($i == 2) {
			return new Item ( Item::GOLD_HOE, 0, 1 );
		}
		if ($i == 3) {
			return new Item ( Item::IRON_HOE, 0, 1 );
		}
		if ($i == 4) {
			return new Item ( Item::DIAMOND_HOE, 0, 1 );
		}
		if ($i == 5) {
			return new Item ( Item::COAL, 0, 1 );
		}
		if ($i == 6) {
			return new Item ( Item::EMERALD, 0, 1 );
		}
		if ($i == 7) {
			return new Item ( Item::IRON_INGOT, 0, 1 );
		}		
		if ($i == 8) {
			return new Item ( Item::DIAMOND, 0, 1 );
		}
		if ($i == 9) {
			return new Item ( Item::IRON_INGOT, 0, 1 );
		}
		if ($i == 10) {
			return new Item ( Item::BAKED_POTATO, 0, 1 );
		}
		if ($i == 11) {
			return new Item ( Item::COOKED_BEEF, 0, 1 );
		}
		if ($i == 12) {
			return new Item ( Item::COOKED_PORKCHOP, 0, 1 );
		}
		if ($i == 13) {
			return new Item ( Item::APPLE, 0, 1 );
		}
		if ($i == 14) {
			return new Item ( Item::COOKED_CHICKEN, 0, 1 );
		}
		if ($i == 15) {
			return new Item ( Item::DIAMOND_SWORD, 0, 1 );
		}
		if ($i == 16) {
			return new Item ( Item::IRON_SWORD, 0, 1 );
		}
		if ($i == 17) {
			return new Item ( Item::STONE_SWORD, 0, 1 );
		}
		if ($i == 18) {
			return new Item ( Item::WOODEN_SWORD, 0, 1 );
		}
		if ($i == 19) {
			return new Item ( Item::GOLD_SWORD, 0, 1 );
		}
		if ($i == 20) {
			return new Item ( Item::APPLE, 0, 1 );
		}
		if ($i == 21) {
			return new Item ( Item::APPLE, 0, 1 );
		}
		if ($i == 22) {
			return new Item ( Item::APPLE, 0, 1 );
		}
		if ($i == 23) {
			return new Item ( Item::APPLE, 0, 1 );
		}
		if ($i == 24) {
			return new Item ( Item::DIAMOND_HOE, 0, 1 );
		}
		if ($i == 25) {
			return new Item ( Item::DIAMOND_HOE, 0, 1 );
		}
		if ($i == 26) {
			return new Item ( Item::DIAMOND_HOE, 0, 1 );
		}
		if ($i == 27) {
			return new Item ( Item::APPLE, 0, 1 );
		}
		if ($i == 28) {
			return new Item ( Item::FLINT, 0, 1 );
		}
		if ($i == 29) {
			return new Item ( Item::APPLE, 0, 1 );
		}
		if ($i == 30) {
			return new Item ( Item::DIAMOND_HOE, 0, 1 );
		}
		if ($i == 31) {
			return new Item ( Item::FLINT, 0, 2 );
		}
		if ($i == 32) {
			return new Item ( Item::DIAMOND_HOE, 0, 1 );
		}
		if ($i == 33) {
			return new Item ( Item::STONE_HOE, 0, 1 );
		}
		if ($i == 34) {
			return new Item ( Item::STONE_HOE, 0, 1 );
		}
		if ($i == 35) {
			return new Item ( Item::FLINT, 0, 1 );
		}
		if ($i == 36) {
			return new Item ( Item::STONE_HOE, 0, 1 );
		}
		if ($i == 37) {
			return new Item ( Item::FLINT, 0, 1 );
		}
		if ($i == 38) {
			return new Item ( Item::STONE_HOE, 0, 1 );
		}
		if ($i == 39) {
			return new Item ( Item::BREAD, 0, 1 );
		}
		if ($i == 40) {
			return new Item ( Item::STONE_HOE, 0, 1 );
		}
		if ($i == 41) {
			return new Item ( Item::STONE_HOE, 0, 1 );
		}
		if ($i == 42) {
			return new Item ( Item::STONE_HOE, 0, 1 );
		}
		if ($i == 43) {
			return new Item ( Item::APPLE, 0, 1 );
		}
		if ($i == 44) {
			return new Item ( Item::STONE_HOE, 0, 1 );
		}
		if ($i == 45) {
			return new Item ( Item::IRON_HOE, 0, 1 );
		}
		if ($i == 46) {
			return new Item ( Item::IRON_HOE, 0, 1 );
		}
		if ($i == 47) {
			return new Item ( Item::IRON_HOE, 0, 1 );
		}
		if ($i == 48) {
			return new Item ( Item::BREAD, 0, 1 );
		}
		if ($i == 49) {
			return new Item ( Item::IRON_HOE, 0, 1 );
		}
		if ($i == 50) {
			return new Item ( Item::BREAD, 0, 1 );
		}
		if ($i == 51) {
			return new Item ( Item::IRON_HOE, 0, 1 );
		}
		if ($i == 52) {
			return new Item ( Item::BREAD, 0, 1 );
		}
		if ($i == 53) {
			return new Item ( Item::IRON_HOE, 0, 1 );
		}
		if ($i == 54) {
			return new Item ( Item::FLINT, 0, 1 );
		}
		if ($i == 55) {
			return new Item ( Item::GOLD_HOE, 0, 1 );
		}
		if ($i == 56) {
			return new Item ( Item::GOLD_HOE, 0, 1 );
		}
		if ($i == 57) {
			return new Item ( Item::FLINT, 0, 1 );
		}
		if ($i == 58) {
			return new Item ( Item::GOLD_HOE, 0, 1 );
		}
		if ($i == 59) {
			return new Item ( Item::APPLE, 0, 1 );
		}
		if ($i == 60) {
			return new Item ( Item::GOLD_HOE, 0, 1 );
		}
		if ($i == 61) {
			return new Item ( Item::APPLE, 0, 1 );
		}
		if ($i == 62) {
			return new Item ( Item::GOLD_HOE, 0, 1 );
		}
		if ($i == 63) {
			return new Item ( Item::GOLD_HOE, 0, 1 );
		}
		if ($i == 64) {
			return new Item ( Item::GOLD_HOE, 0, 1 );
		}
		if ($i == 65) {
			return new Item ( Item::BREAD, 0, 1 );
		}
		if ($i == 66) {
			return new Item ( Item::GOLD_HOE, 0, 1 );
		}
		if ($i == 67) {
			return new Item ( Item::APPLE, 0, 2 );
		}
		if ($i == 68) {
			return new Item ( Item::EMERALD, 0, 2 );
		}
		if ($i == 69) {
			return new Item ( Item::IRON_INGOT, 0, 2 );
		}		
		if ($i == 70) {
			return new Item ( Item::DIAMOND, 0, 2 );
		}
		if ($i == 71) {
			return new Item ( Item::FLINT, 0, 2 );
		}
		if ($i == 72) {
			return new Item ( Item::EMERALD, 0, 3 );
		}
		if ($i == 73) {
			return new Item ( Item::IRON_INGOT, 0, 3 );
		}		
		if ($i == 74) {
			return new Item ( Item::DIAMOND, 0, 3 );
		}
		if ($i == 75) {
			return new Item ( Item::FLINT, 0, 1 );
		}
		if ($i == 76) {
			return new Item ( Item::EMERALD, 0, 4 );
		}
		if ($i == 77) {
			return new Item ( Item::IRON_INGOT, 0, 4 );
		}		
		if ($i == 78) {
			return new Item ( Item::DIAMOND, 0, 4 );
		}
		if ($i == 79) {
			return new Item ( Item::FLINT, 0, 1 );
		}
		if ($i == 80) {
			return new Item ( Item::EMERALD, 0, 5 );
		}
		if ($i == 81) {
			return new Item ( Item::GOLD_INGOT, 0, 5 );
		}		
		if ($i == 82) {
			return new Item ( Item::DIAMOND, 0, 5 );
		}
		if ($i == 83) {
			return new Item ( Item::FLINT, 0, 2 );
		}
		if ($i == 84) {
			return new Item ( Item::EMERALD, 0, 6 );
		}
		if ($i == 85) {
			return new Item ( Item::IRON_INGOT, 0, 6 );
		}		
		if ($i == 86) {
			return new Item ( Item::DIAMOND, 0, 6 );
		}
		if ($i == 87) {
			return new Item ( Item::FLINT, 0, 2 );
		}
		if ($i == 88) {
			return new Item ( Item::EMERALD, 0, 7 );
		}
		if ($i == 89) {
			return new Item ( Item::IRON_INGOT, 0, 7 );
		}		
		if ($i == 90) {
			return new Item ( Item::DIAMOND, 0, 7 );
		}
		if ($i == 91) {
			return new Item ( Item::FLINT, 0, 1 );
		}
		if ($i == 92) {
			return new Item ( Item::EMERALD, 0, 8 );
		}
		if ($i == 93) {
			return new Item ( Item::IRON_INGOT, 0, 8 );
		}		
		if ($i == 94) {
			return new Item ( Item::DIAMOND, 0, 8 );
		}
		if ($i == 95) {
			return new Item ( Item::FLINT, 0, 2 );
		}
		if ($i == 96) {
			return new Item ( Item::EMERALD, 0, 9 );
		}
		if ($i == 97) {
			return new Item ( Item::IRON_INGOT, 0, 9 );
		}		
		if ($i == 98) {
			return new Item ( Item::DIAMOND, 0, 9 );
		}
		if ($i == 99) {
			return new Item ( Item::FLINT, 0, 2 );
		}
		if ($i == 100) {
			return new Item ( Item::EMERALD, 0, 10 );
		}
		if ($i == 101) {
			return new Item ( Item::IRON_INGOT, 0, 10 );
		}		
		if ($i == 102) {
			return new Item ( Item::DIAMOND, 0, 10 );
		}
		if ($i == 103) {
			return new Item ( Item::FLINT, 0, 1 );
		}
		if ($i == 104) {
			return new Item ( Item::EMERALD, 0, 11 );
		}
		if ($i == 105) {
			return new Item ( Item::IRON_INGOT, 0, 11 );
		}		
		if ($i == 106) {
			return new Item ( Item::DIAMOND, 0, 11 );
		}
		if ($i == 107) {
			return new Item ( Item::FLINT, 0, 1 );
		}
		if ($i == 108) {
			return new Item ( Item::EMERALD, 0, 12 );
		}
		if ($i == 109) {
			return new Item ( Item::IRON_INGOT, 0, 12 );
		}		
		if ($i == 110) {
			return new Item ( Item::DIAMOND, 0, 12 );
		}
		if ($i == 111) {
			return new Item ( Item::FLINT, 0, 2 );
		}
		if ($i == 112) {
			return new Item ( Item::EMERALD, 0, 13 );
		}
		if ($i == 113) {
			return new Item ( Item::IRON_INGOT, 0, 13 );
		}		
		if ($i == 114) {
			return new Item ( Item::DIAMOND, 0, 13 );
		}
		if ($i == 115) {
			return new Item ( Item::BAKED_POTATO, 0, 1 );
		}
		if ($i == 116) {
			return new Item ( Item::COOKED_BEEF, 0, 1 );
		}
		if ($i == 117) {
			return new Item ( Item::COOKED_PORKCHOP, 0, 1 );
		}
		if ($i == 118) {
			return new Item ( Item::APPLE, 0, 1 );
		}
		if ($i == 119) {
			return new Item ( Item::COOKED_CHICKEN, 0, 1 );
		}
		if ($i == 120) {
			return new Item ( Item::BAKED_POTATO, 0, 2 );
		}
		if ($i == 121) {
			return new Item ( Item::COOKED_BEEF, 0, 2 );
		}
		if ($i == 122) {
			return new Item ( Item::COOKED_PORKCHOP, 0, 2 );
		}
		if ($i == 123) {
			return new Item ( Item::APPLE, 0, 2 );
		}
		if ($i == 124) {
			return new Item ( Item::COOKED_CHICKEN, 0, 2 );
		}
		if ($i == 125) {
			return new Item ( Item::BAKED_POTATO, 0, 1 );
		}
		if ($i == 126) {
			return new Item ( Item::COOKED_BEEF, 0, 1 );
		}
		if ($i == 127) {
			return new Item ( Item::COOKED_PORKCHOP, 0, 1 );
		}
		if ($i == 128) {
			return new Item ( Item::APPLE, 0, 1 );
		}
		if ($i == 129) {
			return new Item ( Item::COOKED_CHICKEN, 0, 1 );
		}
		if ($i == 130) {
			return new Item ( Item::BAKED_POTATO, 0, 3 );
		}
		if ($i == 131) {
			return new Item ( Item::COOKED_BEEF, 0, 3 );
		}
		if ($i == 132) {
			return new Item ( Item::COOKED_PORKCHOP, 0, 3 );
		}
		if ($i == 133) {
			return new Item ( Item::APPLE, 0, 3 );
		}
		if ($i == 134) {
			return new Item ( Item::COOKED_CHICKEN, 0, 3 );
		}
		if ($i == 135) {
			return new Item ( Item::BAKED_POTATO, 0, 1 );
		}
		if ($i == 136) {
			return new Item ( Item::COOKED_BEEF, 0, 1 );
		}
		if ($i == 137) {
			return new Item ( Item::COOKED_PORKCHOP, 0, 1 );
		}
		if ($i == 138) {
			return new Item ( Item::APPLE, 0, 1 );
		}
		if ($i == 139) {
			return new Item ( Item::COOKED_CHICKEN, 0, 1 );
		}
		if ($i == 140) {
			return new Item ( Item::BAKED_POTATO, 0, 5 );
		}
		if ($i == 141) {
			return new Item ( Item::COOKED_BEEF, 0, 5 );
		}
		if ($i == 142) {
			return new Item ( Item::COOKED_PORKCHOP, 0, 5 );
		}
		if ($i == 143) {
			return new Item ( Item::APPLE, 0, 5 );
		}
		if ($i == 144) {
			return new Item ( Item::COOKED_CHICKEN, 0, 5 );
		}
		if ($i == 145) {
			return new Item ( Item::BAKED_POTATO, 0, 1 );
		}
		if ($i == 146) {
			return new Item ( Item::COOKED_BEEF, 0, 1 );
		}
		if ($i == 147) {
			return new Item ( Item::COOKED_PORKCHOP, 0, 1 );
		}
		if ($i == 148) {
			return new Item ( Item::APPLE, 0, 1 );
		}
		if ($i == 149) {
			return new Item ( Item::COOKED_CHICKEN, 0, 1 );
		}
		if ($i == 150) {
			return new Item ( Item::BAKED_POTATO, 0, 6 );
		}
		if ($i == 151) {
			return new Item ( Item::COOKED_BEEF, 0, 6 );
		}
		if ($i == 152) {
			return new Item ( Item::COOKED_PORKCHOP, 0, 6 );
		}
		if ($i == 153) {
			return new Item ( Item::APPLE, 0, 6 );
		}
		if ($i == 154) {
			return new Item ( Item::COOKED_CHICKEN, 0, 6 );
		}
		if ($i == 155) {
			return new Item ( Item::BAKED_POTATO, 0, 1 );
		}
		if ($i == 156) {
			return new Item ( Item::COOKED_BEEF, 0, 1 );
		}
		if ($i == 157) {
			return new Item ( Item::COOKED_PORKCHOP, 0, 1 );
		}
		if ($i == 158) {
			return new Item ( Item::APPLE, 0, 1 );
		}
		if ($i == 159) {
			return new Item ( Item::COOKED_CHICKEN, 0, 1 );
		}
		if ($i == 160) {
			return new Item ( Item::BAKED_POTATO, 0, 1 );
		}
		if ($i == 161) {
			return new Item ( Item::COOKED_BEEF, 0, 1 );
		}
		if ($i == 162) {
			return new Item ( Item::COOKED_PORKCHOP, 0, 1 );
		}
		if ($i == 163) {
			return new Item ( Item::APPLE, 0, 1 );
		}
		if ($i == 164) {
			return new Item ( Item::COOKED_CHICKEN, 0, 1 );
		}
		if ($i == 165) {
			return new Item ( Item::BAKED_POTATO, 0, 7 );
		}
		if ($i == 166) {
			return new Item ( Item::COOKED_BEEF, 0, 7 );
		}
		if ($i == 167) {
			return new Item ( Item::COOKED_PORKCHOP, 0, 7 );
		}
		if ($i == 168) {
			return new Item ( Item::APPLE, 0, 7 );
		}
		if ($i == 169) {
			return new Item ( Item::COOKED_CHICKEN, 0, 7 );
		}
		if ($i == 170) {
			return new Item ( Item::BAKED_POTATO, 0, 1 );
		}
		if ($i == 171) {
			return new Item ( Item::COOKED_BEEF, 0, 1 );
		}
		if ($i == 172) {
			return new Item ( Item::COOKED_PORKCHOP, 0, 1 );
		}
		if ($i == 173) {
			return new Item ( Item::APPLE, 0, 1 );
		}
		if ($i == 174) {
			return new Item ( Item::COOKED_CHICKEN, 0, 1 );
		}
		if ($i == 175) {
			return new Item ( Item::BAKED_POTATO, 0, 1 );
		}
		if ($i == 176) {
			return new Item ( Item::COOKED_BEEF, 0, 1 );
		}
		if ($i == 177) {
			return new Item ( Item::COOKED_PORKCHOP, 0, 1 );
		}
		if ($i == 178) {
			return new Item ( Item::APPLE, 0, 1 );
		}
		if ($i == 179) {
			return new Item ( Item::COOKED_CHICKEN, 0, 1 );
		}
		if ($i == 180) {
			return new Item ( Item::BAKED_POTATO, 0, 1 );
		}
		if ($i == 181) {
			return new Item ( Item::COOKED_BEEF, 0, 1 );
		}
		if ($i == 182) {
			return new Item ( Item::COOKED_PORKCHOP, 0, 1 );
		}
		if ($i == 183) {
			return new Item ( Item::APPLE, 0, 1 );
		}
		if ($i == 184) {
			return new Item ( Item::COOKED_CHICKEN, 0, 1 );
		}
		if ($i == 185) {
			return new Item ( Item::BAKED_POTATO, 0, 1 );
		}
		if ($i == 186) {
			return new Item ( Item::COOKED_BEEF, 0, 1 );
		}
		if ($i == 187) {
			return new Item ( Item::COOKED_PORKCHOP, 0, 1 );
		}
		if ($i == 188) {
			return new Item ( Item::APPLE, 0, 1 );
		}
		if ($i == 189) {
			return new Item ( Item::COOKED_CHICKEN, 0, 1 );
		}
		if ($i == 190) {
			return new Item ( Item::BAKED_POTATO, 0, 1 );
		}
		if ($i == 191) {
			return new Item ( Item::COOKED_BEEF, 0, 1 );
		}
		if ($i == 192) {
			return new Item ( Item::COOKED_PORKCHOP, 0, 1 );
		}
		if ($i == 193) {
			return new Item ( Item::APPLE, 0, 1 );
		}
		if ($i == 194) {
			return new Item ( Item::COOKED_CHICKEN, 0, 1 );
		}
		if ($i == 195) {
			return new Item ( Item::BAKED_POTATO, 0, 1 );
		}
		if ($i == 196) {
			return new Item ( Item::COOKED_BEEF, 0, 1 );
		}
		if ($i == 197) {
			return new Item ( Item::COOKED_PORKCHOP, 0, 1 );
		}
		if ($i == 198) {
			return new Item ( Item::APPLE, 0, 1 );
		}
		if ($i == 199) {
			return new Item ( Item::COOKED_CHICKEN, 0, 1 );
		}
		if ($i == 200) {
			return new Item ( Item::STICK, 0, 1 );
		}

		return new Item ( Item::AIR );
	}
}