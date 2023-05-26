<?php

namespace mcg76\skywars;

use pocketmine\block\Block;
use pocketmine\item\ItemBlock;
use pocketmine\item\Item;

/**
 * MCG76 PEBlock
 *
 * Copyright (C) 2014 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 @author minecraftgenius76@gmail.com
 *        
 */

class PEBlock {
	
	public $list = [ ];
	public function __construct() {
		$this->init ();
	}
	public function init() {
		
		$this->list ["AIR"] = 0;
		$this->list ["STONE"] = 1;
		$this->list ["GRASS"] = 2;
		$this->list ["DIRT"] = 3;
		$this->list ["COBBLESTONE"] = 4;
		$this->list ["PLANKS"] = 5;
		$this->list ["SAPLING"] = 6;
		$this->list ["BEDROCK"] = 7;
		$this->list ["WATER"] = 8;
		$this->list ["STILL_WATER"] = 9;
		$this->list ["LAVA"] = 10;
		$this->list ["STILL_LAVA"] = 11;
		$this->list ["SAND"] = 12;
		$this->list ["GRAVEL"] = 13;
		$this->list ["GOLD_ORE"] = 14;
		$this->list ["IRON_ORE"] = 15;
		$this->list ["COAL_ORE"] = 16;
		$this->list ["WOOD"] = 17;
		$this->list ["LEAVE"] = 18;
		$this->list ["LEAVES"] = 18;
		$this->list ["SPONGE"] = 19;
		$this->list ["GLASS"] = 20;
		$this->list ["LAPIS_ORE"] = 21;
		$this->list ["LAPIS_BLOCK"] = 22;
		$this->list ["SANDSTONE"] = 24;
		$this->list ["BED_BLOCK"] = 26;
		$this->list ["COBWEB"] = 30;
		$this->list ["TALL_GRASS"] = 31;
		$this->list ["DEAD_BUSH"] = 32;
		$this->list ["WOOL"] = 35;
		$this->list ["DANDELION"] = 37;
		$this->list ["POPPY"] = 38;
		$this->list ["BROWN_MUSHROOM"] = 39;
		$this->list ["RED_MUSHROOM"] = 40;
		$this->list ["GOLD_BLOCK"] = 41;
		$this->list ["GOLD"] = 41;
		$this->list ["IRON_BLOCK"] = 42;
		$this->list ["IRON"] = 42;
		$this->list ["DOUBLE_SLAB"] = 43;
		$this->list ["SLAB"] = 44;
		$this->list ["BRICK"] = 45;
		$this->list ["BRICKS_BLOCK"] = 45;
		$this->list ["TNT"] = 46;
		$this->list ["BOOKSHELF"] = 47;
		$this->list ["MOSS_STONE"] = 48;
		$this->list ["MOSSY_STONE"] = 48;
		$this->list ["OBSIDIAN"] = 49;
		$this->list ["TORCH"] = 50;
		$this->list ["FIRE"] = 51;
		$this->list ["MONSTER_SPAWNER"] = 52;
		$this->list ["OAK_WOOD_STAIRS"] = 53;
		$this->list ["WOOD_STAIRS"] = 53;
		$this->list ["CHEST"] = 54;
		
		$this->list ["DIAMOND_ORE"] = 56;
		$this->list ["DIAMOND_BLOCK"] = 57;
		$this->list ["DIAMOND"] = 57;		
		$this->list ["CRAFTING_TABLE"] = 58;
		$this->list ["WORKBENCH"] = 58;
		$this->list ["WHEAT_BLOCK"] = 59;
		$this->list ["FARMLAND"] = 60;
		$this->list ["FURNACE"] = 61;
		$this->list ["BURNING_FURNACE"] = 62;
		$this->list ["SIGN_POST"] = 63;
		$this->list ["WOOD_DOOR_BLOCK"] = 64;
		$this->list ["LADDER"] = 65;
		
		$this->list ["COBBLESTONE_STAIRS"] = 67;
		$this->list ["WALL_SIGN"] = 68;
		
		$this->list ["IRON_DOOR_BLOCK"] = 71;
		$this->list ["REDSTONE_ORE"] = 73;
		$this->list ["GLOWING_REDSTONE_ORE"] = 74;
		$this->list ["SNOW"] = 78;
		$this->list ["SNOW_LAYER"] = 78;
		$this->list ["ICE"] = 79;
		$this->list ["SNOW_BLOCK"] = 80;
		$this->list ["CACTUS"] = 81;
		$this->list ["CLAY_BLOCK"] = 82;
		$this->list ["SUGARCANE_BLOCK"] = 83;
		
		$this->list ["FENCE"] = 85;
		$this->list ["PUMPKIN"] = 86;
		$this->list ["NETHERRACK"] = 87;
		$this->list ["SOUL_SAND"] = 88;
		$this->list ["GLOWSTONE_BLOCK"] = 89;
		$this->list ["GLOWSTONE"] = 89;
		
		$this->list ["PUMPKIN"] = 91;
		$this->list ["LIT_PUMPKIN"] = 91;
		$this->list ["CAKE_BLOCK"] = 92;
		$this->list ["CAKE"] = 92;
		
		$this->list ["TRAPDOOR"] = 96;
		
		$this->list ["STONE_BRICKS"] = 98;
		$this->list ["IRON_BAR"] = 101;
		$this->list ["IRON_BARS"] = 101;
		$this->list ["GLASS_PANE"] = 102;
		$this->list ["GLASS_PANEL"] = 102;
		$this->list ["MELON_BLOCK"] = 103;
		$this->list ["PUMPKIN_STEM"] = 104;
		$this->list ["MELON_STEM"] = 104;
		$this->list ["VINE"] = 106;
		$this->list ["VINES"] = 106;
		$this->list ["FENCE_GATE"] = 107;
		$this->list ["BRICK_STAIRS"] = 108;
		$this->list ["STONE_BRICK_STAIRS"] = 109;
		
		$this->list ["MYCELIUM"] = 110;
		$this->list ["NETHER_BRICKS"] = 112;
		$this->list ["NETHER_BRICKS_STAIRS"] = 114;
		
		$this->list ["END_PORTAL"] = 120;
		$this->list ["END_STONE"] = 121;
		$this->list ["SANDSTONE_STAIRS"] = 128;
		$this->list ["EMERALD_ORE"] = 129;
		
		$this->list ["EMERALD_BLOCK"] = 133;
		$this->list ["SPRUCE_WOOD_STAIRS"] = 134;
		$this->list ["BIRCH_WOOD_STAIRS"] = 135;
		$this->list ["JUNGLE_WOOD_STAIRS"] = 136;
		$this->list ["STONE_WALL"] = 139;
		
		$this->list ["CARROT_BLOCK"] = 141;
		$this->list ["POTATO_BLOCK"] = 142;
		
		$this->list ["QUARTZ_BLOCK"] = 155;
		$this->list ["QUARTZ_STAIRS"] = 156;
		$this->list ["DOUBLE_WOOD_SLAB"] = 157;
		$this->list ["WOOD_SLAB"] = 158;
		$this->list ["STAINED_CLAY"] = 159;
		
		$this->list ["LEAVES2"] = 161;
		$this->list ["WOOD2"] = 162;
		$this->list ["ACACIA_WOOD_STAIRS"] = 163;
		$this->list ["DARK_OAK_WOOD_STAIRS"] = 164;
		
		$this->list ["HAY_BALE"] = 170;
		$this->list ["CARPET"] = 171;
		$this->list ["HARDENED_CLAY"] = 172;
		$this->list ["COAL_BLOCK"] = 173;
		
		$this->list ["FENCE_GATE_SPRUCE"] = 183;
		$this->list ["FENCE_GATE_BIRCH"] = 184;
		$this->list ["FENCE_GATE_JUNGLE"] = 185;
		$this->list ["FENCE_GATE_DARK_OAK"] = 186;
		$this->list ["FENCE_GATE_ACACIA"] = 187;
		$this->list ["FENCE_SPRUCE"] = 188;
		$this->list ["FENCE_BIRCH"] = 189;
		$this->list ["FENCE_DARK_OAK"] = 191;
		$this->list ["FENCE_JUNGLE"] = 190;
		$this->list ["FENCE_ACACIA"] = 192;
		
		$this->list ["PODZOL"] = 243;
		$this->list ["BEETROOT"] = 244;
		$this->list ["BEETROOT_BLOCK"] = 244;
		$this->list ["STONECUTTER"] = 245;
		$this->list ["GLOWING_OBSIDIAN"] = 246;
		$this->list ["NETHER_REACTOR"] = 247;
	}
	
	public function getItemBlock($name) {

		 if (isset($this->list[strtoupper($name)])) {
		 	$bid = $this->list[strtoupper($name)];
		 	//return $block =  Block::get($bid, 0);
		 	return Item::get($bid);
		 }
		 return null;		 		
	}
	
}