<?php

namespace mcg76\plugin\worldedit;

use pocketmine\block\Block;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\math\Vector3;
use pocketmine\network\protocol\UpdateBlockPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\item\Item;
use pocketmine\block\Sapling;
use pocketmine\math\Math;
use pocketmine\utils\Random;
use pocketmine\level\generator\object\Tree;

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
class WorldEditBuilder {
	private $pgin;
	
	/**
	 * Constructor
	 *
	 * @param UndoMain $pg        	
	 */
	public function __construct(WorldEditPlugIn $pg) {
		$this->pgin = $pg;
	}
	public static function lengthSq($x, $y, $z) {
		return ($x * $x) + ($y * $y) + ($z * $z);
	}
	public function generateTree(Level $level, $pos, $type, $a, $b, $c) {
		$pos1 = new Position ( $pos->x + $a, $pos->y, $pos->z + $c, $level );
		$level->setBlock ( $pos1, new Sapling ( $type ), true, true );
		Tree::growTree ( $level, $pos1->x, $pos1->y, $pos1->z, new Random ( mt_rand () ), 0 & 0x07 );
	}
	public function W_plants(Level $level, Position $pos, $planttype, $radius, $spaces = 0, &$output) {
		$invalid = false;
		
		switch (strtolower ( $planttype )) {
			case "stone" :
				$type = Item::STONE;
				break;
			case "water" :
				$type = Item::STILL_WATER;
				break;
			case "lava" :
				$type = Item::STILL_LAVA;
				break;
			case "grass" :
				$type = Item::GRASS;
				break;
			case "tallgrass" :
				$type = Item::TALL_GRASS;
				break;
			case "tree" :
				$type = Sapling::OAK;
				break;
			case "oak" :
				$type = Sapling::OAK;
				break;
			case "darkoak" :
				$type = Sapling::DARK_OAK;
				break;
			case "birch" :
				$type = Sapling::BIRCH;
				break;
			case "acacia" :
				$type = Sapling::ACACIA;
				break;
			case "redwood" :
				$type = Sapling::SPRUCE;
				break;
			case "jungle" :
				$type = Sapling::JUNGLE;
				break;
			case "spruce" :
				$type = Sapling::SPRUCE;
				break;
			case "redmushroom" :
				$type = Item::RED_MUSHROOM;
				break;
			case "brownmushroom" :
				$type = Item::BROWN_MUSHROOM;
				break;
			case "rose" :
				$type = Item::ROSE;
				break;
			case "dandelion" :
				$type = Item::DANDELION;
				break;
			case "leave" :
				$type = Item::LEAVES;
				break;
			case "log" :
				$type = Item::LOG;
				break;
			case "pumpkin" :
				$type = Item::PUMPKIN;
				break;
			case "lantern" :
				$type = Item::JACK_O_LANTERN;
				break;
			case "cobweb" :
				$type = Item::COBWEB;
				break;
			default :
				$invalid = true;
				break;
		}
		if ($invalid) {
			$output = "[WE] Invalid plant type!";
			return;
		}
		
		if ($radius > 1) {
			$changed = 0;
			for($a = - $radius; $a <= $radius; $a ++) {
				for($b = - $radius; $b <= $radius; $b ++) {
					for($c = - $radius; $c <= $radius; $c ++) {
						if ($a * $a + $b * $b + $c * $c <= $radius * $radius) {
							if ($type == item::GRASS || $type == item::TALL_GRASS || $type == item::STILL_LAVA || $type == item::STILL_WATER || $type == item::STONE || $type == item::JACK_O_LANTERN || $type == item::PUMPKIN || $type == item::COBWEB || $type == item::LEAVE || $type == item::LOG || $type == item::RED_MUSHROOM || $type == item::BROWN_MUSHROOM || $type == item::ROSE || $type == item::DANDELION) {
								$pos1 = new Position ( $pos->x + $a * rand ( 1, $radius ), $pos->y, $pos->z + $c * rand ( 1, $radius ), $level );
								$bid = $level->getBlockIdAt ( $pos1->x, $pos1->y, $pos1->z );
								if ($bid == item::AIR) {
									$level->setBlock ( $pos1, Item::get ( $type )->getBlock (), false, false );
									$changed ++;
								}
							} else {
								$bid = $level->getBlockIdAt ( $pos->x, $pos->y - 1, $pos->z );
								if (($bid == iTEM::GRASS || $bid == iTEM::DIRT || $bid == iTEM::PODZOL)) {
									if ($spaces == 0) {
										$this->generateTree ( $level, $pos, $type, $a * rand ( 1, $radius ), $b, $c * rand ( 1, $radius ) );
									} else {
										$this->generateTree ( $level, $pos, $type, $a * $spaces, $b, $c * $spaces );
									}
									$changed ++;
								}
							}
						}
					}
				}
			}
			$output = $changed . " " . $planttype . " created.";
		} else {
			if ($type == item::GRASS || $type == item::TALL_GRASS || $type == item::RED_MUSHROOM || $type == item::BROWN_MUSHROOM || $type == item::ROSE || $type == item::DANDELION) {
				$pos1 = new Position ( $pos->x + $a * rand ( 1, $radius ), $pos->y, $pos->z + $c * rand ( 1, $radius ) );
				$level->setBlock ( $pos1, Item::get ( $type )->getBlock (), false, false );
			} else {
				$this->generateTree ( $level, $pos, $type, 0, 0, 0 );
			}
			$output = $planttype . " created.";
		}
	}
	
	public function Y(Level $level, $x, $z, $maxY, $minY) {
		for($y = $maxY; $y >= $minY; $y --) {
			if ($level->getBlockIdAt ( $x, $y, $z ) != 0) {
				return $y;
				break;
			}
		}
	}
	public function W_overlay(Level $level, $selection, $block, &$output) {
		if (! is_array ( $selection ) or $selection [0] === false or $selection [1] === false or $selection [0] [3] !== $selection [1] [3]) {
			$output .= "Make a selection first.\n";
			return array ();
		}
		$changed = 0;
		$minX = min ( $selection [0] [0], $selection [1] [0] );
		$maxX = max ( $selection [0] [0], $selection [1] [0] );
		$minY = min ( $selection [0] [1], $selection [1] [1] );
		$maxY = max ( $selection [0] [1], $selection [1] [1] );
		$minZ = min ( $selection [0] [2], $selection [1] [2] );
		$maxZ = max ( $selection [0] [2], $selection [1] [2] );
		
		for($a = $minX; $a <= $maxX; $a ++) {
			for($c = $minZ; $c <= $maxZ; $c ++) {
				$bid1 = $level->getBlockIdAt ( $a, $this->Y ( $level, $a, $c, $maxY, $minY ), $c );
				$bid2 = $level->getBlockIdAt ( $a, $this->Y ( $level, $a, $c, $maxY, $minY ) + 1, $c );
				if ($bid1 != 0 && $bid2 == 0) {
					$level->setBlock ( new Position ( $a, $this->Y ( $level, $a, $c, $maxY, $minY ) + 1, $c, $level ), $block, false, true );
					$changed ++;
				}
			}
		}
		$output = $changed + " block(s) have been added.";
	}
	public function W_wall(Level $level, $selection, $block, &$output) {
		if (! is_array ( $selection ) or $selection [0] === false or $selection [1] === false or $selection [0] [3] !== $selection [1] [3]) {
			$output .= "Make a selection first.\n";
			return array ();
		}
		
		$changed = 0;
		$minX = min ( $selection [0] [0], $selection [1] [0] );
		$maxX = max ( $selection [0] [0], $selection [1] [0] );
		$minY = min ( $selection [0] [1], $selection [1] [1] );
		$maxY = max ( $selection [0] [1], $selection [1] [1] );
		$minZ = min ( $selection [0] [2], $selection [1] [2] );
		$maxZ = max ( $selection [0] [2], $selection [1] [2] );
		
		for($a = $minX; $a <= $maxX; $a ++) {
			for($b = $minY; $b <= $maxY; $b ++) {
				for($c = $minZ; $c <= $maxZ; $c ++) {
					if ($a == $minX || $a == $maxX || $c == $minZ || $c == $maxZ) {
						$level->setBlock ( new Position ( $a, $b, $c ,$level), $block, false, true );
						$changed ++;
					}
				}
			}
		}
		$output = $changed + " block(s) have been created.";
	}
	public function W_cylinder(Position $pos, $block, $radius, $height, &$output) {
		$changed = 0;
		for($a = - $radius; $a <= $radius; $a ++) {
			for($b = 0; $b < $height; $b ++) {
				for($c = - $radius; $c <= $radius; $c ++) {
					if ($a * $a + $c * $c <= $radius * $radius) {
						// $block = Item::get ( $blockid )->getBlock ();
						$pos->getLevel ()->setBlock ( new Position ( $pos->x + $a, $pos->y + $b, $pos->z + $c , $pos->getLevel () ), $block, true, false );
						$changed ++;
					}
				}
			}
		}
		$output = $changed . " block(s) have been created.";
	}
	public function W_holocylinder(Position $pos, $block, $radius, $height, &$output) {
		$changed = 0;
		for($a = - $radius; $a <= $radius; $a ++) {
			for($b = 0; $b < $height; $b ++) {
				for($c = - $radius; $c <= $radius; $c ++) {
					if ($a * $a + $c * $c >= ($radius - 1) * ($radius - 1)) {
						// $block = Item::get ( $blockid )->getBlock ();
						$pos->getLevel ()->setBlock ( new Position ( $pos->x + $a, $pos->y + $b, $pos->z + $c, $pos->getLevel () ), $block, true, false );
						$changed ++;
					}
				}
			}
		}
		$output = $changed . " block(s) have been created.";
	}
	
	// move down current selection by Y blocks
	public function W_moveDown($selection, $clipboard, $delta, &$output = null) {
		$this->W_cut ( $selection );
		$pos = new Position ( $selection [0] [0], $selection [0] [1], $selection [0] [2], $selection [0] [3] );
		$pos->y = $pos->y - $delta + 1;
		$this->W_paste ( $clipboard, $pos );
		$output = "moved down by $delta blocks.\n";
		return true;
	}
	public function W_sphere(Position $pos, $block, $radiusX, $radiusY, $radiusZ, $filled = true, &$output = null) {
		$count = 0;
		$level = $pos->getLevel ();
		
		$radiusX += 0.5;
		$radiusY += 0.5;
		$radiusZ += 0.5;
		
		$invRadiusX = 1 / $radiusX;
		$invRadiusY = 1 / $radiusY;
		$invRadiusZ = 1 / $radiusZ;
		
		$ceilRadiusX = ( int ) ceil ( $radiusX );
		$ceilRadiusY = ( int ) ceil ( $radiusY );
		$ceilRadiusZ = ( int ) ceil ( $radiusZ );
		
		// $bcnt = count ( $blocks ) - 1;
		$bcnt = 1; // only use selected block
		
		$nextXn = 0;
		$breakX = false;
		for($x = 0; $x <= $ceilRadiusX and $breakX === false; ++ $x) {
			$xn = $nextXn;
			$nextXn = ($x + 1) * $invRadiusX;
			$nextYn = 0;
			$breakY = false;
			for($y = 0; $y <= $ceilRadiusY and $breakY === false; ++ $y) {
				$yn = $nextYn;
				$nextYn = ($y + 1) * $invRadiusY;
				$nextZn = 0;
				$breakZ = false;
				for($z = 0; $z <= $ceilRadiusZ; ++ $z) {
					$zn = $nextZn;
					$nextZn = ($z + 1) * $invRadiusZ;
					$distanceSq = WorldEditBuilder::lengthSq ( $xn, $yn, $zn );
					if ($distanceSq > 1) {
						if ($z === 0) {
							if ($y === 0) {
								$breakX = true;
								$breakY = true;
								break;
							}
							$breakY = true;
							break;
						}
						break;
					}
					
					if ($filled === false) {
						if (WorldEditBuilder::lengthSq ( $nextXn, $yn, $zn ) <= 1 and WorldEditBuilder::lengthSq ( $xn, $nextYn, $zn ) <= 1 and WorldEditBuilder::lengthSq ( $xn, $yn, $nextZn ) <= 1) {
							continue;
						}
					}
					$blocktype = $block->getId ();
					$this->upsetBlock2 ( $level, $pos->add ( $x, $y, $z ), $block );
					$count ++;
					$this->upsetBlock2 ( $level, $pos->add ( - $x, $y, $z ), $block );
					$count ++;
					$this->upsetBlock2 ( $level, $pos->add ( $x, - $y, $z ), $block );
					$count ++;
					$this->upsetBlock2 ( $level, $pos->add ( $x, $y, - $z ), $block );
					$count ++;
					
					$this->upsetBlock2 ( $level, $pos->add ( - $x, - $y, $z ), $block );
					$count ++;
					$this->upsetBlock2 ( $level, $pos->add ( $x, - $y, - $z ), $block );
					$count ++;
					$this->upsetBlock2 ( $level, $pos->add ( - $x, $y, - $z ), $block );
					$count ++;
					$this->upsetBlock2 ( $level, $pos->add ( - $x, - $y, - $z ), $block );
					$count ++;
				}
			}
		}
		
		$output .= "$count block(s) have been changed.\n";
		// $this->log ( $output );
		return true;
	}
	
	/**
	 * Retrieve Blocks
	 *
	 * @param Player $p        	
	 * @return multitype:unknown
	 */
	public function upsetBlock(Level $level, $pos, $blocktype) {
		$block = $level->getBlock ( new Vector3 ( $pos->x, $pos->y, $pos->z ) );
		$this->renderBlocks ( $block, $level, $blocktype );
	}
	public function upsetBlock2(Level $level, $pos, $block) {
		$direct = true;
		$update = true;
		$level->setBlock ( $pos, $block, $direct, $update );
	}
	
	/**
	 * Convert block to update block packets
	 *
	 * @param array $blocks        	
	 * @param Player $p        	
	 * @param unknown $blocktype        	
	 */
	public function renderBlocks(Block $block, Level $level, $blocktype) {
		$players = $level->getPlayers ();
		foreach ( $players as $lp ) {
			$pk = new UpdateBlockPacket ();
			$pk->x = $block->getX ();
			$pk->y = $block->getY ();
			$pk->z = $block->getZ ();
			$pk->block = $blocktype;
			$pk->meta = 0;
			$lp->dataPacket ( $pk );
			$lp->getLevel ()->setBlockIdAt ( $block->getX (), $block->getY (), $block->getZ (), $blocktype );
			
			$pos = new Position ( $block->x, $block->y, $block->z );
			$block = $lp->getLevel ()->getBlock ( $pos );
			$direct = true;
			$update = true;
			$lp->getLevel ()->setBlock ( $pos, $block, $direct, $update );
		}
	}
	public function W_set($selection, $block, &$output = null) {
		if (! is_array ( $selection ) or $selection [0] === false or $selection [1] === false or $selection [0] [3] !== $selection [1] [3]) {
			$output .= "Make a selection first.\n";
			return false;
		}
		$totalCount = $this->countBlocks ( $selection );
		if ($totalCount > 524288) {
			$send = false;
		} else {
			$send = true;
		}
		$level = $selection [0] [3];
		
		$bcnt = 1;
		$startX = min ( $selection [0] [0], $selection [1] [0] );
		$endX = max ( $selection [0] [0], $selection [1] [0] );
		$startY = min ( $selection [0] [1], $selection [1] [1] );
		$endY = max ( $selection [0] [1], $selection [1] [1] );
		$startZ = min ( $selection [0] [2], $selection [1] [2] );
		$endZ = max ( $selection [0] [2], $selection [1] [2] );
		$count = 0; // $count = $this->countBlocks($selection);
		for($x = $startX; $x <= $endX; ++ $x) {
			for($y = $startY; $y <= $endY; ++ $y) {
				for($z = $startZ; $z <= $endZ; ++ $z) {
					$level->setBlock ( new Position ( $x, $y, $z, $level ), $block, false, true );
					$count ++;
				}
			}
		}
		if ($send === false) {
			$forceSend = function ($X, $Y, $Z) {
				$this->changedCount [$X . ":" . $Y . ":" . $Z] = 4096;
			};
			$forceSend->bindTo ( $level, $level );
			for($X = $startX >> 4; $X <= ($endX >> 4); ++ $X) {
				for($Y = $startY >> 4; $Y <= ($endY >> 4); ++ $Y) {
					for($Z = $startZ >> 4; $Z <= ($endZ >> 4); ++ $Z) {
						$forceSend ( $X, $Y, $Z );
					}
				}
			}
		}
		$output .= "$count block(s) have been changed.\n";
		return true;
	}
	public function countBlocks($selection, &$startX = null, &$startY = null, &$startZ = null) {
		if (! is_array ( $selection ) or $selection [0] === false or $selection [1] === false or $selection [0] [3] !== $selection [1] [3]) {
			return false;
		}
		$startX = min ( $selection [0] [0], $selection [1] [0] );
		$endX = max ( $selection [0] [0], $selection [1] [0] );
		$startY = min ( $selection [0] [1], $selection [1] [1] );
		$endY = max ( $selection [0] [1], $selection [1] [1] );
		$startZ = min ( $selection [0] [2], $selection [1] [2] );
		$endZ = max ( $selection [0] [2], $selection [1] [2] );
		return ($endX - $startX + 1) * ($endY - $startY + 1) * ($endZ - $startZ + 1);
	}
	public function W_replace($selection, $block1, $blocks2, &$output = null) {
		if (! is_array ( $selection ) or $selection [0] === false or $selection [1] === false or $selection [0] [3] !== $selection [1] [3]) {
			$output .= "Make a selection first.\n";
			return false;
		}
		$totalCount = $this->countBlocks ( $selection );
		if ($totalCount > 524288) {
			$send = false;
		} else {
			$send = true;
		}
		
		$this->log ( "replace block type :" . $block1 . " with " . $blocks2 );
		$level = $selection [0] [3];
		$id1 = $block1->getId ();
		$bcnt2 = 1;
		$startX = min ( $selection [0] [0], $selection [1] [0] );
		$endX = max ( $selection [0] [0], $selection [1] [0] );
		$startY = min ( $selection [0] [1], $selection [1] [1] );
		$endY = max ( $selection [0] [1], $selection [1] [1] );
		$startZ = min ( $selection [0] [2], $selection [1] [2] );
		$endZ = max ( $selection [0] [2], $selection [1] [2] );
		$count = 0;
		for($x = $startX; $x <= $endX; ++ $x) {
			for($y = $startY; $y <= $endY; ++ $y) {
				for($z = $startZ; $z <= $endZ; ++ $z) {
					$b = $level->getBlock ( new Vector3 ( $x, $y, $z ) );
					if ($b->getId () === $id1) {
						$direct = false;
						$update = true;
						$level->setBlock ( new Position ( $x, $y, $z, $level ), $blocks2, $direct, $update );
						$count ++;
					}
					unset ( $b );
				}
			}
		}
		if ($send === false) {
			$forceSend = function ($X, $Y, $Z) {
				$this->changedCount [$X . ":" . $Y . ":" . $Z] = 4096;
			};
			$forceSend->bindTo ( $level, $level );
			for($X = $startX >> 4; $X <= ($endX >> 4); ++ $X) {
				for($Y = $startY >> 4; $Y <= ($endY >> 4); ++ $Y) {
					for($Z = $startZ >> 4; $Z <= ($endZ >> 4); ++ $Z) {
						$forceSend ( $X, $Y, $Z );
					}
				}
			}
		}
		$output .= "$count block(s) have been changed.\n";
		return true;
	}
	public function W_paste($clipboard, Position $pos, &$output = null) {
		if (count ( $clipboard ) !== 2) {
			$output .= "[PEWE] Copy something first.\n";
			return false;
		}
		
		$clipboard [0] [0] += $pos->x - 0.5;
		$clipboard [0] [1] += $pos->y;
		$clipboard [0] [2] += $pos->z - 0.5;
		$offset = array_map ( "round", $clipboard [0] );
		$count = 0;
		$level = $pos->getLevel ();
		foreach ( $clipboard [1] as $x => $i ) {
			foreach ( $i as $y => $j ) {
				foreach ( $j as $z => $block ) {
					$direct = false;
					$update = true;
					$level->setBlock ( new Position ( $x + $offset [0], $y + $offset [1], $z + $offset [2] , $level), $block, $direct, $update );
					$count ++;
					unset ( $block );
				}
			}
		}
		$output .= "$count block(s) have been changed.\n";
		return true;
	}
	public function W_pasteWithUpdate($clipboard, Position $pos, &$output = null) {
		if (count ( $clipboard ) !== 2) {
			$output .= "[PEWE] Copy something first.\n";
			return false;
		}
		$blocks = [ ];
		$clipboard [0] [0] += $pos->x - 0.5;
		$clipboard [0] [1] += $pos->y;
		$clipboard [0] [2] += $pos->z - 0.5;
		$offset = array_map ( "round", $clipboard [0] );
		$count = 0;
		$level = $pos->getLevel ();
		foreach ( $clipboard [1] as $x => $i ) {
			foreach ( $i as $y => $j ) {
				foreach ( $j as $z => $block ) {
					$direct = false;
					$update = true;
					$level->setBlock ( new Position ( $x + $offset [0], $y + $offset [1], $z + $offset [2], $level), $block, $direct, $update );
					$count ++;
					$blocks [] = $block;
					unset ( $block );
				}
			}
		}
		$output .= "$count block(s) have been changed.\n";
		
		return $blocks;
	}
	public function W_copy($selection, &$output = null) {
		if (! is_array ( $selection ) or $selection [0] === false or $selection [1] === false or $selection [0] [3] !== $selection [1] [3]) {
			$output .= "Make a selection first.\n";
			return array ();
		}
		$level = $selection [0] [3];
		$blocks = array ();
		$startX = min ( $selection [0] [0], $selection [1] [0] );
		$endX = max ( $selection [0] [0], $selection [1] [0] );
		$startY = min ( $selection [0] [1], $selection [1] [1] );
		$endY = max ( $selection [0] [1], $selection [1] [1] );
		$startZ = min ( $selection [0] [2], $selection [1] [2] );
		$endZ = max ( $selection [0] [2], $selection [1] [2] );
		$count = $this->countBlocks ( $selection );
		for($x = $startX; $x <= $endX; ++ $x) {
			$blocks [$x - $startX] = array ();
			for($y = $startY; $y <= $endY; ++ $y) {
				$blocks [$x - $startX] [$y - $startY] = array ();
				for($z = $startZ; $z <= $endZ; ++ $z) {
					$b = $level->getBlock ( new Vector3 ( $x, $y, $z ) );
					$blocks [$x - $startX] [$y - $startY] [$z - $startZ] = $b;
					unset ( $b );
				}
			}
		}
		$output .= "$count block(s) have been copied.\n";
		return $blocks;
	}
	public function W_cut($selection, &$output = null) {
		if (! is_array ( $selection ) or $selection [0] === false or $selection [1] === false or $selection [0] [3] !== $selection [1] [3]) {
			$output .= "Make a selection first.\n";
			return array ();
		}
		$totalCount = $this->countBlocks ( $selection );
		if ($totalCount > 524288) {
			$send = false;
		} else {
			$send = true;
		}
		$level = $selection [0] [3];
		$blocks = array ();
		$startX = min ( $selection [0] [0], $selection [1] [0] );
		$endX = max ( $selection [0] [0], $selection [1] [0] );
		$startY = min ( $selection [0] [1], $selection [1] [1] );
		$endY = max ( $selection [0] [1], $selection [1] [1] );
		$startZ = min ( $selection [0] [2], $selection [1] [2] );
		$endZ = max ( $selection [0] [2], $selection [1] [2] );
		// $count = $this->countBlocks ( $selection );
		$count = 0;
		for($x = $startX; $x <= $endX; ++ $x) {
			$blocks [$x - $startX] = array ();
			for($y = $startY; $y <= $endY; ++ $y) {
				$blocks [$x - $startX] [$y - $startY] = array ();
				for($z = $startZ; $z <= $endZ; ++ $z) {
					$b = $level->getBlock ( new Vector3 ( $x, $y, $z ) );
					$blocks [$x - $startX] [$y - $startY] [$z - $startZ] = $b;
					$air = Block::get ( Block::AIR );
					$direct = true;
					$update = true;
					$level->setBlock ( new Position ( $b->x, $b->y, $b->z ,$level ), $air, $direct, $update );
					$count ++;
					unset ( $b );
				}
			}
		}
		if ($send === false) {
			$forceSend = function ($X, $Y, $Z) {
				$this->changedCount [$X . ":" . $Y . ":" . $Z] = 4096;
			};
			$forceSend->bindTo ( $level, $level );
			for($X = $startX >> 4; $X <= ($endX >> 4); ++ $X) {
				for($Y = $startY >> 4; $Y <= ($endY >> 4); ++ $Y) {
					for($Z = $startZ >> 4; $Z <= ($endZ >> 4); ++ $Z) {
						$forceSend ( $X, $Y, $Z );
					}
				}
			}
		}
		$output .= "$count block(s) have been cut.\n";
		return $blocks;
	}
	
	/**
	 * Remove selection blocks permanently
	 *
	 * @param unknown $selection        	
	 * @param string $output        	
	 * @return multitype:|boolean
	 */
	public function W_remove($selection, &$output = null) {
		if (! is_array ( $selection ) or $selection [0] === false or $selection [1] === false or $selection [0] [3] !== $selection [1] [3]) {
			$output .= "Make a selection first.\n";
			return array ();
		}
		$totalCount = $this->countBlocks ( $selection );
		if ($totalCount > 524288) {
			$send = false;
		} else {
			$send = true;
		}
		$level = $selection [0] [3];
		$startX = min ( $selection [0] [0], $selection [1] [0] );
		$endX = max ( $selection [0] [0], $selection [1] [0] );
		$startY = min ( $selection [0] [1], $selection [1] [1] );
		$endY = max ( $selection [0] [1], $selection [1] [1] );
		$startZ = min ( $selection [0] [2], $selection [1] [2] );
		$endZ = max ( $selection [0] [2], $selection [1] [2] );
		$count = 0;
		for($x = $startX; $x <= $endX; ++ $x) {
			for($y = $startY; $y <= $endY; ++ $y) {
				for($z = $startZ; $z <= $endZ; ++ $z) {
					$b = $level->getBlock ( new Vector3 ( $x, $y, $z ) );
					$air = Block::get ( Block::AIR );
					$direct = false;
					$update = true;
					$level->setBlock ( new Position ( $b->x, $b->y, $b->z , $level), $air, $direct, $update );
					$count ++;
					unset ( $b );
				}
			}
		}
		if ($send === false) {
			$forceSend = function ($X, $Y, $Z) {
				$this->changedCount [$X . ":" . $Y . ":" . $Z] = 4096;
			};
			$forceSend->bindTo ( $level, $level );
			for($X = $startX >> 4; $X <= ($endX >> 4); ++ $X) {
				for($Y = $startY >> 4; $Y <= ($endY >> 4); ++ $Y) {
					for($Z = $startZ >> 4; $Z <= ($endZ >> 4); ++ $Z) {
						$forceSend ( $X, $Y, $Z );
					}
				}
			}
		}
		$output .= "$count block(s) have been removed permanently.\n";
		return $send;
	}
	
	/**
	 * simple log utility function
	 *
	 * @param unknown $msg        	
	 */
	private function log($msg) {
		$this->pgin->getLogger ()->info ( $msg );
	}
}
