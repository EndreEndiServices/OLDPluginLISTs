<?php

namespace mcg76\plugin\worldedit;

use pocketmine\block\Block;
use pocketmine\item\ItemBlock;
use pocketmine\item\Item;

/**
 * MCPE World Edit - Made by minecraftgenius76
 *
 * You're allowed to use for own usage only "as-is". 
 * you're not allowed to republish or resell or for any commercial purpose.
 *
 * Thanks for your cooperate!
 *
 * Copyright (C) 2015 minecraftgenius76
 * 
 * Web site: http://www.minecraftgenius76.com/
 * YouTube : http://www.youtube.com/user/minecraftgenius76
 *
 * @author minecraftgenius76
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
		
		//inverse
		$this->list ["0"] = 0; //"AIR";
		$this->list ["1"] = 1;//"STONE";
		$this->list ["2"] = 2;//"GRASS";
		$this->list ["3"] = 3;//"DIRT";
		$this->list ["4"] = 4;//"COBBLESTONE";
		$this->list ["5"] = 5;//"PLANKS";
		$this->list ["6"] = 6;//"SAPLING";
		$this->list ["7"] = 7;//"BEDROCK";
		$this->list ["8"] = 8;//"WATER";
		$this->list ["9"] = 9;//"STILL_WATER";
		$this->list ["10"] = 10;//"LAVA";
		$this->list ["11"] = 11;//"STILL_LAVA";
		$this->list ["12"] = 12;//"SAND";
		$this->list ["13"] = 13;//"GRAVEL";
		$this->list ["14"] = 14;//"GOLD_ORE";
		$this->list ["15"] = 15;//"IRON_ORE";
		$this->list ["16"] = 16;//"COAL_ORE";
		$this->list ["17"] = 17;//"WOOD";
		$this->list ["18"] = 18;//"LEAVE";
		$this->list ["19"] = 19;//"SPONGE";
		$this->list ["20"] = 20;//Glass;
		$this->list ["21"] = 21;//"LAPIS_ORE";
		$this->list ["22"] = 22;//"LAPIS_BLOCK";
		$this->list ["24"] = 24;//"SANDSTONE";
		$this->list ["26"] = 26;//"BED_BLOCK";
		$this->list ["30"] = 30;//"COBWEB";
		$this->list ["31"] = 31;//"TALL_GRASS";
		$this->list ["32"] = 32;//"DEAD_BUSH";
		$this->list ["35"] = 35;//"WOOL";
		$this->list ["37"] = 37;//"DANDELION";
		$this->list ["38"] = 38;//"POPPY";
		$this->list ["39"] = 39;//"BROWN_MUSHROOM";
		$this->list ["40"] = 40;//"RED_MUSHROOM";
		$this->list ["41"] = 41;//"GOLD_BLOCK";
		$this->list ["42"] = 42;//"IRON_BLOCK";
		$this->list ["43"] = 43;//"DOUBLE_SLAB";
		$this->list ["44"] = 44;//"SLAB";
		$this->list ["45"] = 45;//"BRICK";
		$this->list ["46"] = 46;//"TNT";
		$this->list ["47"] = 47;//"BOOKSHELF";
		$this->list ["48"] = 48;//"MOSS_STONE";
		$this->list ["49"] = 49;//"OBSIDIAN";
		$this->list ["50"] = 50;//"TORCH";
		$this->list ["51"] = 51;//"FIRE";
		$this->list ["52"] = 52;//"MONSTER_SPAWNER";
		$this->list ["53"] = 53;//"WOOD_STAIRS";
		$this->list ["54"] = 54;//"CHEST";		
		$this->list ["56"] = 56;//"DIAMOND_ORE";
		$this->list ["57"] = 57;//"DIAMOND_BLOCK";
		$this->list ["58"] = 58;//"CRAFTING_TABLE";
		$this->list ["59"] = 59;//"WHEAT_BLOCK";
		$this->list ["60"] = 60;//"FARMLAND";
		$this->list ["61"] = 61;//"FURNACE";
		$this->list ["62"] = 62;//"BURNING_FURNACE";
		$this->list ["63"] = 63;//"SIGN_POST";
		$this->list ["64"] = 64;//"WOOD_DOOR_BLOCK";
		$this->list ["65"] = 65;//"LADDER";		
		$this->list ["67"] = 67;//"COBBLESTONE_STAIRS";
		$this->list ["68"] = 68;//"WALL_SIGN";		
		$this->list ["71"] = 71;//"IRON_DOOR_BLOCK";
		$this->list ["73"] = 73;//"REDSTONE_ORE";
		$this->list ["74"] = 74;//"GLOWING_REDSTONE_ORE";
		$this->list ["78"] = 78;//"SNOW";
		$this->list ["79"] = 79;//"ICE";
		$this->list ["80"] = 80;//"SNOW_BLOCK";
		$this->list ["81"] = 81;//"CACTUS";
		$this->list ["82"] = 82;//"CLAY_BLOCK";
		$this->list ["83"] = 83;//"SUGARCANE_BLOCK";		
		$this->list ["85"] = 85;//"FENCE";
		$this->list ["86"] = 86;//"PUMPKIN";
		$this->list ["87"] = 87;//"NETHERRACK";
		$this->list ["88"] = 88;//"SOUL_SAND";
		$this->list ["89"] = 89;//"GLOWSTONE_BLOCK";		
		$this->list ["91"] = 91;//"PUMPKIN";
		$this->list ["92"] = 92;//"CAKE_BLOCK";		
		$this->list ["96"] = 96;//"TRAPDOOR";		
		$this->list ["98"] = 98;//"STONE_BRICKS";
		$this->list ["101"] = 101;//"IRON_BAR";
		$this->list ["102"] = 102;//"GLASS_PANE";
		$this->list ["103"] = 103;//"MELON_BLOCK";
		$this->list ["104"] = 104;//"PUMPKIN_STEM";
		$this->list ["106"] = 106;//"VINE";
		$this->list ["107"] = 107;//"FENCE_GATE";
		$this->list ["108"] = 108;//"BRICK_STAIRS";
		$this->list ["109"] = 109;//"STONE_BRICK_STAIRS";		
		$this->list ["110"] = 110;//"MYCELIUM";
		$this->list ["112"] = 112;//"NETHER_BRICKS";
		$this->list ["114"] = 114;//"NETHER_BRICKS_STAIRS";		
		$this->list ["120"] = 120;//"END_PORTAL";
		$this->list ["121"] = 121;//"END_STONE";
		$this->list ["128"] = 128;//"SANDSTONE_STAIRS";
		$this->list ["129"] = 129;//"EMERALD_ORE";		
		$this->list ["133"] = 133;//"EMERALD_BLOCK";
		$this->list ["134"] = 134;//"SPRUCE_WOOD_STAIRS";
		$this->list ["135"] = 135;//"BIRCH_WOOD_STAIRS";
		$this->list ["136"] = 136;//"JUNGLE_WOOD_STAIRS";
		$this->list ["139"] = 139;//"STONE_WALL";		
		$this->list ["141"] = 141;//"CARROT_BLOCK";
		$this->list ["142"] = 142;//"POTATO_BLOCK";		
		$this->list ["155"] = 155;//"QUARTZ_BLOCK";
		$this->list ["156"] = 156;//"QUARTZ_STAIRS";
		$this->list ["157"] = 157;//"DOUBLE_WOOD_SLAB";
		$this->list ["158"] = 158;//"WOOD_SLAB";
		$this->list ["159"] = 159;//"STAINED_CLAY";		
		$this->list ["161"] = 161;//"LEAVES2";
		$this->list ["162"] = 162;//"WOOD2";
		$this->list ["163"] = 163;//"ACACIA_WOOD_STAIRS";
		$this->list ["164"] = 164;//"DARK_OAK_WOOD_STAIRS";		
		$this->list ["170"] = 170;//"HAY_BALE";
		$this->list ["171"] = 171;//"CARPET";
		$this->list ["172"] = 172;//"HARDENED_CLAY";
		$this->list ["173"] = 173;//"COAL_BLOCK";		
		$this->list ["183"] = 183;//"FENCE_GATE_SPRUCE";
		$this->list ["184"] = 184;//"FENCE_GATE_BIRCH";
		$this->list ["185"] = 185;//"FENCE_GATE_JUNGLE";
		$this->list ["186"] = 186;//"FENCE_GATE_DARK_OAK";
		$this->list ["187"] = 187;//"FENCE_GATE_ACACIA";
		$this->list ["188"] = 188;//"FENCE_SPRUCE";
		$this->list ["189"] = 189;//"FENCE_BIRCH";
		$this->list ["191"] = 191;//"FENCE_DARK_OAK";
		$this->list ["190"] = 190;//"FENCE_JUNGLE";
		$this->list ["192"] = 192;//"FENCE_ACACIA";		
		$this->list ["243"] = 243;//"PODZOL";
		$this->list ["244"] = 244;//"BEETROOT";
		$this->list ["245"] = 245;//"STONECUTTER";
		$this->list ["246"] = 246;//"GLOWING_OBSIDIAN";
		$this->list ["247"] = 247;//"NETHER_REACTOR";		
	}
	
	public function getItemBlock($name) {
		 if (isset($this->list[strtoupper($name)])) {
		 	$bid = $this->list[strtoupper($name)];
		 	return Item::get($bid);
		 }
		 return null;		 		
	}	
}