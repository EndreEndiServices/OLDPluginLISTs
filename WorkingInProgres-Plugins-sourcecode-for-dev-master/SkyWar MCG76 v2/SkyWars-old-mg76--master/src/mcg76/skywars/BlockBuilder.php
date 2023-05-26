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
use pocketmine\event\Listener;
use pocketmine\math\Vector3 as Vector3;
use pocketmine\math\Vector2 as Vector2;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\utils\Utils;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\protocol\UpdateBlockPacket;
use pocketmine\network\protocol\DataPacket;
use pocketmine\network\protocol\Info;
use pocketmine\network\protocol\LoginPacket;
use pocketmine\block\Block;
use pocketmine\block\Iron;
use pocketmine\block\Cobblestone;
use pocketmine\block\Air;
use pocketmine\utils\Cache;
use pocketmine\network\protocol\SetEntityMotionPacket;
use pocketmine\network\protocol\AddMobPacket;
use pocketmine\network\protocol\AddPlayerPacket;
use pocketmine\entity\Zombie;
use pocketmine\entity\Villager;
use pocketmine\item\Item;
use pocketmine\entity\Human;
use pocketmine\nbt\NBT;
use pocketmine\network\protocol\ContainerSetContentPacket;
use pocketmine\network\protocol\ContainerSetSlotPacket;
use pocketmine\network\protocol\PlayerArmorEquipmentPacket;
use pocketmine\network\protocol\PlayerEquipmentPacket;
use pocketmine\network\protocol\RemovePlayerPacket;

/**
 * MCG76 BlockBuilder
 *
 * Copyright (C) 2014 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 @author minecraftgenius76@gmail.com
 *        
 */
class BlockBuilder {
	private $pgin;
	private $peblock;
	public function __construct(SkyWarsPlugIn $pg) {
		$this->pgin = $pg;
		$this->peblock = new PEBlock();
	}
	
	public function buildShell(Player $player, $radius, $dataX, $dataY, $dataZ) {
		$this->log ( TextFormat::BLUE . "build Player location : " . $player->x . " " . $player->y . " " . $player->z );
		$status = false;
		try {
			$x =$dataX;
			$level = $player->getLevel ();
			for($rx = 0; $rx < $radius; $rx ++) {
				$y = $dataY;
				for($ry = 0; $ry < $radius; $ry ++) {
					$z = $dataZ;
					for($rz = 0; $rz < $radius; $rz ++) {
						$rb = $level->getBlock ( new Vector3 ( $x, $y, $z ) );
						$this->removeBlocks2 ( $rb, $player);
						if ($rx == ($radius - 1) || $rz == ($radius - 1) || $rx == 0 || $rz == 0 || $ry == ($radius - 1) || $ry == 0) {
							if ($rx == 2 && $ry > 0 && $ry < ($radius - 1)) {
								$this->renderBlocks ( $rb, $player, 20);
							} else if ($ry == 0) {
								$this->renderBlocks ( $rb, $player, 20 );
							} else if ($ry == ($radius - 1)) {
								$this->renderBlocks ( $rb, $player, 20 );
							} else if ($rx == 0 || $rz == 0) {
								$this->renderBlocks ( $rb, $player, 20 );
							} else if ($rx == ($radius - 1)) {
								$this->renderBlocks ( $rb, $player, 20 );
							} else {
								$this->renderBlocks ( $rb, $player, 20 );
							}
						}
						$z ++;
					}
					$y ++;
				}
				$x ++;
			}
			// update status
			$status = true;
		} catch ( \Exception $e ) {
			$this->log ( "Error:" . $e->getMessage () );
		}
		return $status;
	}
	
	public function removeShell(Level $level, $radius, $dataX, $dataY, $dataZ) {
		$this->log ( TextFormat::BLUE . "remove player shell at location : " . $dataX . " " . $dataY . " " . $dataZ );
		$status = false;
		try {
			$x =$dataX;
			//$level = $player->getLevel ();
			for($rx = 0; $rx < $radius; $rx ++) {
				$y = $dataY;
				for($ry = 0; $ry < $radius; $ry ++) {
					$z = $dataZ;
					for($rz = 0; $rz < $radius; $rz ++) {
						$rb = $level->getBlock ( new Vector3 ( $x, $y, $z ) );
						$this->removeBlocks3 ( $rb, $level);
						$z ++;
					}
					$y ++;
				}
				$x ++;
			}
			// update status
			$status = true;
		} catch ( \Exception $e ) {
			$this->log ( "Error:" . $e->getMessage () );
		}
		return $status;
	}
	
	public function renderPortal($level, Position $pos, $destination) {
		$this->log ( TextFormat::BLUE . "build portal location : " . $pos->x . " " . $pos->y . " " . $pos->z );
		//$level = $player->level;
		$status = false;
		$dataX = $pos->x+1;
		$dataY = $pos->y-1;
		$dataZ = $pos->z;
		$radius = 4;
		$height = 5;
		try {
			$x = $dataX;
			for($rx = 0; $rx < $radius; $rx ++) {
				$y = $dataY;
				for($ry = 0; $ry < $height; $ry ++) {
					$z = $dataZ;
					// for($rz = 0; $rz < $radius; $rz ++) {
					$rb = $level->getBlock ( new Vector3 ( $x, $y, $z ) );
					$this->setBlockType ( $level, "AIR", $x, $y, $z );					
					//keep portal door
					$xyz =round($x).round($y).round($z);
					$this->pgin->portals[$xyz] =$destination;
					$this->log("  point:".$xyz);					
					if ($rx==0) {
						$this->setBlockType ( $level, "BEDROCK", $x, $y, $z );
						if (isset($this->pgin->portals[$xyz])){
							unset($this->pgin->portals[$xyz]);
						}
					}
					if ($rx==$radius-1) {
						$this->setBlockType ( $level, "BEDROCK", $x, $y, $z );
						if (isset($this->pgin->portals[$xyz])){
							unset($this->pgin->portals[$xyz]);
						}
					}
					if ($ry == ($height-1)) {
						$this->setBlockType ( $level, "BEDROCK", $x, $y, $z );
						if (isset($this->pgin->portals[$xyz])){
							unset($this->pgin->portals[$xyz]);
						}						
					}
					if ($rx>0 && $rx<$radius-1 && $ry == ($height-2)) {
						$this->setBlockType ( $level, "ICE", $x, $y, $z );
					}
					$y ++;
				}
				$x ++;
			}
			// update status
			$status = true;
		} catch ( \Exception $e ) {
			$this->log ( "Error:" . $e->getMessage () );
		}
		return $status;
	}
	
	public function removePortal(Player $player, Position $pos) {
		$level = $player->level;
		$status = false;
		$dataX = $pos->x+1;
		$dataY = $pos->y-1;
		$dataZ = $pos->z;
		$radius = 4;
		$height = 5;
		try {
			$x = $dataX;
			for($rx = 0; $rx < $radius; $rx ++) {
				$y = $dataY;
				for($ry = 0; $ry < $height; $ry ++) {
					$z = $dataZ;
					$rb = $level->getBlock ( new Vector3 ( $x, $y, $z ) );
					$this->setBlockType ( $player, "AIR", $x, $y, $z );
					$xyz =$x.$y.$z;
					if (isset($this->pgin->portals[$xyz])){
						unset($this->pgin->portals[$xyz]);
					}
					$y ++;
				}
				$x ++;
			}
			$status = true;
		} catch ( \Exception $e ) {
			$this->log ( "Error:" . $e->getMessage () );
		}
		return $status;
	}
	
	/**
	 * Render Wall
	 *
	 * @param Player $player        	
	 * @param Block $block        	
	 */
// 	public function renderWall(Player $player, Block $block) {
// 		// $this->log ( TextFormat::RED . " render wall " );
// 		$args = $this->pgin->pmwall [$player->getName ()];
// 		$width = $args [1];
// 		$height = $args [2];
// 		$wallType = null;
		
// 		if (count ( $args ) == 4) {
// 			$wallType = $args [3];
// 		}
// 		if ($wallType == null) {
// 			$wallType = 2;
// 		}
		
// 		$this->buildWall ( $player, $width, $height, $block->x, $block->y, $block->z, $wallType );
// 		// update player location
// 		$player->teleport ( new Position ( $block->getX (), ($block->y + $height), $block->z ) );
// 	}
	
	/**
	 * Render Explosion
	 *
	 * @param Player $player        	
	 * @param unknown $x        	
	 * @param unknown $y        	
	 * @param unknown $z        	
	 */
	public function renderExplosion(Player $player, $x, $y, $z) {
		$size = $this->pgin->pmxp [$player->getName ()];
		// $this->log ( TextFormat::RED . "- explosion size =" . $size );
		$player->sendMessage ( "[PowerMining] Explosion Power =" . $size );
		$explosion = new Explosion ( new Position ( $x, $y, $z, $player->getLevel () ), $size );
		$explosion->explode ();
	}
	
	/**
	 * Render Hole
	 *
	 * @param Player $player        	
	 * @param Block $block        	
	 */
// 	public function renderHole(Player $player, Block $block) {
// 		// make hole based on dimension parameters
// 		$holesize = $this->pgin->pmhole [$player->getName ()];
// 		// $this->log ( TextFormat::RED . "- hole size " . $holesize[1] . " x " . $holesize[2] );
// 		$player->sendMessage ( "[PowerMining] Hole Maker =" . $holesize [1] . " x " . $holesize [2] );
// 		$level = $player->getLevel ();
// 		$x = $block->x;
// 		for($rx = 0; $rx < $holesize [1]; $rx ++) {
// 			// item = nulll can break anything
// 			$y = $block->y;
// 			for($ry = 0; $ry < $holesize [2]; $ry ++) {
// 				$z = $block->z;
// 				for($rz = 0; $rz < $holesize [1]; $rz ++) {
// 					$rb = $level->getBlock ( new Vector3 ( $x, $y, $z ) );
// 					$this->removeBlocks ( $rb, $player );
// 					// $this->log ( TextFormat::BLUE . "+ remove X blocks: " . $x . " " . $y . " " . $z );
// 					$z ++;
// 				}
// 				$y --;
// 			}
// 			$x ++;
// 		}
// 	}
	
// 	/**
// 	 * Render Floor
// 	 *
// 	 * @param Player $player        	
// 	 * @param Block $block        	
// 	 */
// 	public function renderFloor(Player $player, Block $block) {
// 		$floorsize = $this->pgin->pmfloor [$player->getName ()];
// 		// $this->log ( TextFormat::RED . "- floor size " . $floorsize[1] . " x " . $floorsize[2] );
// 		$x = $block->x;
// 		if ($floorsize != null) {
// 			$player->sendMessage ( "[PowerMining] Floor Maker =" . $floorsize [1] . " x " . $floorsize [2] );
// 			$level = $player->getLevel ();
// 			for($rx = 0; $rx < $floorsize [1]; $rx ++) {
// 				$y = $block->y;
// 				for($ry = 0; $ry < $floorsize [2]; $ry ++) {
// 					$z = $block->z;
// 					for($rz = 0; $rz < $floorsize [1]; $rz ++) {
// 						$rb = $level->getBlock ( new Vector3 ( $x, $y, $z ) );
// 						if (count ( $floorsize ) == 3) {
// 							$this->renderBlockByType ( $rb, $player, 1 );
// 						} else if (count ( $floorsize ) == 4) {
// 							$btype = $this->getBlockType ( $floorsize [3] );
// 							$this->renderBlockByType ( $rb, $player, $btype );
// 						}
// 						// $this->log ( TextFormat::BLUE . "+ remove X blocks: " . $x . " " . $y . " " . $z );
// 						$z ++;
// 					}
// 					$y ++;
// 				}
// 				$x ++;
// 			}
			
// 			$player->teleport ( new Position ( $player->x, $player->y + $floorsize [1], $player->z ) );
// 		} else {
// 			$player->sendMessage ( "[PowerMining] Floor Maker " );
// 		}
// 	}
	
	/**
	 * Render Wall
	 *
	 * @param Player $player        	
	 * @param unknown $radius        	
	 * @param unknown $height        	
	 * @param unknown $dataX        	
	 * @param unknown $dataY        	
	 * @param unknown $dataZ        	
	 * @param unknown $wallType        	
	 * @return boolean
	 */
	public function buildWall(Level $level, $radius, $height, $dataX, $dataY, $dataZ, $wallType) {
		// $this->log ( TextFormat::BLUE . "build Player location : " . $player->x . " " . $player->y . " " . $player->z );
		$status = false;
		try {
			$doorExist = 0;
			$x = $dataX;
			for($rx = 0; $rx < $radius; $rx ++) {
				$y = $dataY;
				for($ry = 0; $ry < $height; $ry ++) {
					$z = $dataZ;
					for($rz = 0; $rz < $radius; $rz ++) {
						$rb = $level->getBlock ( new Vector3 ( $x, $y, $z ) );
						//$this->removeBlocks ( $rb, $level );
						// $this->log ( TextFormat::BLUE . "+ remove X blocks: " . $x . " " . $y . " " . $z );
						// build the wall at edge - $ry control the roof and base
						if ($rx == ($radius - 1) || $rz == ($radius - 1) || $rx == 0 || $rz == 0 || $ry == ($radius - 1) || $ry == 0) {
							if ($rx == 2 && $ry > 0 && $ry < ($radius - 1)) {
								//$this->renderBlockByType ( $rb, $player, $wallType );
								//$this->setBlockType($level,$wallType,$x, $y, $z);	
								//$this->renderBlockByType($rb, $level, 1);
							} else if ($ry == 0) {
								// $this->log ( TextFormat::BLUE . "floor blocks: " . $rb->x . " " . $rb->y . " " . $rb->z );
								// $this->renderBlockByType ( $rb, $player, 0 );
							} else if ($ry == ($radius - 1)) {
								// $this->log ( TextFormat::BLUE . "roof blocks: " . $rb->x . " " . $rb->y . " " . $rb->z );
								//$this->renderBlockByType ( $rb, $player, 0 );
								
							} else if ($rx == 0 || $rz == 0) {
								//$this->renderBlockByType ( $rb, $player, $wallType );
								$this->setBlockType($level,$wallType,$x, $y, $z);
								//$this->renderBlockByType($rb, $level, 1);
							} else if ($rx == ($radius - 1)) {
								//$this->renderBlockByType ( $rb, $player, $wallType );
								$this->setBlockType($level,$wallType,$x, $y, $z);
								//$this->renderBlockByType($rb, $level, 1);
							} else {
								//$this->renderBlockByType ( $rb, $player, $wallType );
								$this->setBlockType($level,$wallType,$x, $y, $z);
								//$this->renderBlockByType($rb, $level, 1);
							}
						}
						$z ++;
					}
					$y ++;
				}
				$x ++;
			}
			// update status
			$status = true;
		} catch ( \Exception $e ) {
			$this->log ( "Error:" . $e->getMessage () );
		}
		return $status;
	}
	
	public function addPlayerChest(Player $player) {
		$tx = $player->x + 1;
		$ty = $player->y ;
		$tz = $player->z;
		$rb = $player->getLevel ()->getBlock ( new Vector3 ( $tx, $ty, $tz ) );
		$this->updateBlock ( $rb, $player->getLevel(), 54 );
	}
	
	public function updateBlock($block, Level $level, $blockType) {
		$players = $level->getPlayers();
		foreach ($players as $p) {
			$pk = new UpdateBlockPacket ();
			$pk->x = $block->getX ();
			$pk->y = $block->getY ();
			$pk->z = $block->getZ ();
			$pk->block = $blockType;
			$pk->meta = 0;
			$p->dataPacket ( $pk );
			$level->setBlockIdAt ( $block->getX (), $block->getY (), $block->getZ (), $pk->block );
	
			$pos = new Position($block->x, $block->y, $block->z);
			$block = $level->getBlock($pos);
			$direct = true;
			$update = true;
			$level->setBlock($pos, $block,$direct, $update);
		}
	}
	
	
	public function renderBlocks(Block $block, Player $p, $blocktype) {
		$this->updateBlock2($block, $p, $blocktype);
	}
	
	public function updateBlock2(Block $block, Player $p, $blockType) {
		//$players = $xp->getLevel()->getPlayers();
		//foreach ($players as $p) {
			$pk = new UpdateBlockPacket ();
			$pk->x = $block->getX ();
			$pk->y = $block->getY ();
			$pk->z = $block->getZ ();
			$pk->block = $blockType;
			$pk->meta = 0;
			$p->dataPacket ( $pk );
			$p->getLevel ()->setBlockIdAt ( $block->getX (), $block->getY (), $block->getZ (), $pk->block );
	
			$pos = new Position($block->x, $block->y, $block->z);
			$block = $p->getLevel()->getBlock($pos);
			$direct = true;
			$update = true;
			$p->getLevel()->setBlock($pos, $block,$direct, $update);
		//}
	}
	
	public function setBlockType(Level $level,$name,$x,$y,$z) {
		$item = $this->peblock->getItemBlock($name);
		$block = $item->getBlock ();
		$direct = true;
		$update = true;
		$pos = new Position ( $x, $y, $z);
		$level->setBlock ( $pos, $block, $direct, $update );
	}

// 	public function setBlockType(Player $player, $name, $x, $y, $z) {
// 		$item = $this->peblock->getItemBlock ( $name );
// 		$block = $item->getBlock ();
// 		$direct = true;
// 		$update = true;
// 		$pos = new Position ( $x, $y, $z );
// 		$player->level->setBlock ( $pos, $block, $direct, $update );
// 	}
	
// 	public function getBlockType($material) {
// 		switch ($material) {
// 			case 1 :
// 				// cooblestone
// 				return "1";
// 				break;
// 			case 2 :
// 				// wood
// 				return "17";
// 				break;
// 			case 3 :
// 				// bedrock
// 				return "7";
// 				break;
// 			case 4 :
// 				// glowstonre
// 				return "87";
// 				break;
// 			case 5 :
// 				// log
// 				return "162";
// 			case 6 :
// 				// coal ore
// 				return "95";
// 				break;
// 			case 7 :
// 				// redstone ore
// 				return "73";
// 				break;
// 			case 8 :
// 				// diamond ore
// 				return "56";
// 				break;
// 			case 9 :
// 				// Iron Ore
// 				return "15";
// 				break;
// 			case 10 :
// 				// gold ore
// 				return "14";
// 				break;
// 			case 11 :
// 				// lapis ore
// 				return "21";
// 				break;
// 			case 12 :
// 				// Glass
// 				return "20";
// 				break;
// 			case 13 :
// 				// Grass
// 				return "2";
// 				break;
// 			case 14 :
// 				// Cactus
// 				return "98";
// 				break;
// 			case 15 :
// 				// Brick
// 				return "45";
// 				break;
// 			case 16 :
// 				// emeral ore
// 				return "48";
// 				break;
// 			case 17 :
// 				// sanstone
// 				return "24";
// 				break;
// 			case 18 :
// 				// sand
// 				return "12";
// 				break;
// 			case 19 :
// 				// gravel
// 				return "13";
// 				break;
// 			case 20 :
// 				// hay block
// 				return "170";
// 				break;
// 			case 21 :
// 				// leaves
// 				return "18";
// 				break;
// 			default :
// 				// cobble stone
// 				return "1";
// 				break;
// 		}
// 	}
	
	/**
	 * remove blocks
	 *
	 * @param array $blocks        	
	 * @param Player $p        	
	 */
	private function renderBlockByType($block, $level, $typeId) {
		// foreach($blocks as $block){
		$players = $level->getPlayers ();
		foreach ( $players as $p ) {
			
			$pk = new UpdateBlockPacket ();
			$pk->x = $block->getX ();
			$pk->y = $block->getY ();
			$pk->z = $block->getZ ();
			$pk->block = $typeId;
			$pk->meta = 0;
			$p->dataPacket ( $pk );
			
			// ensure updates are done
			// $this->log(" set block :".$typeId);
			$level->setBlockIdAt ( $block->getX (), $block->getY (), $block->getZ (), $typeId );
			$pos = new Position ( $block->x, $block->y, $block->z );
			$block = $level->getBlock ( $pos );
			// $this->log(" get block :".$block);
			$direct = true;
			$update = true;
			$level->setBlock ( $pos, $block, $direct, $update );
			$level->updateAround ( $pos );
		}
		
		// $index = Level::chunkHash($block->getX (), $block->getZ ());
		// Cache::remove("world:".($p->getLevel()->getName()).":" . $index);
		// foreach($p->getLevel()->getUsingChunk($block->getX (), $block->getZ ()) as $player){
		// $player->unloadChunk($block->getX (), $block->getZ ());
		// }
	}
	
	/**
	 * remove blocks
	 *
	 * @param array $blocks        	
	 * @param Player $p        	
	 */
	private function removeBlocks($block, Level $level) {
		// foreach($blocks as $block){
		$players = $level->getPlayers ();
		foreach ( $players as $p ) {			
			$pk = new UpdateBlockPacket ();
			$pk->x = $block->getX ();
			$pk->y = $block->getY ();
			$pk->z = $block->getZ ();
			$pk->block = 0;
			$pk->meta = 0;
			$p->dataPacket ( $pk );
			// remove it
			$level->setBlockIdAt ( $block->getX (), $block->getY (), $block->getZ (), 0 );
			
			$pos = new Position ( $block->x, $block->y, $block->z );
			$block = $level->getBlock ( $pos );
			$direct = true;
			$update = true;
			$level->setBlock ( $pos, $block, $direct, $update );
			
			$direct = true;
			$update = true;
			$level->setBlock ( $pos, $block, $direct, $update );
			$level->updateAround ( $pos );
		}
	}
	
	private function removeBlocks3($block, Level $level) {
		// foreach($blocks as $block){
		$players = $this->pgin->skyplayers;
		foreach ( $players as $p ) {
			$pk = new UpdateBlockPacket ();
			$pk->x = $block->getX ();
			$pk->y = $block->getY ();
			$pk->z = $block->getZ ();
			$pk->block = 0;
			$pk->meta = 0;
			$p->dataPacket ( $pk );
			// remove it
			$level->setBlockIdAt ( $block->getX (), $block->getY (), $block->getZ (), 0 );
				
			$pos = new Position ( $block->x, $block->y, $block->z );
			$block = $level->getBlock ( $pos );
			$direct = true;
			$update = true;
			$level->setBlock ( $pos, $block, $direct, $update );
				
			$direct = true;
			$update = true;
			$level->setBlock ( $pos, $block, $direct, $update );
			$level->updateAround ( $pos );
		}
	}
	
	private function removeBlocks2($block, Player $p) {
		// foreach($blocks as $block){
// 		$players = $level->getPlayers ();
// 		foreach ( $players as $p ) {
			$pk = new UpdateBlockPacket ();
			$pk->x = $block->getX ();
			$pk->y = $block->getY ();
			$pk->z = $block->getZ ();
			$pk->block = 0;
			$pk->meta = 0;
			$p->dataPacket ( $pk );
			// remove it
			$p->level->setBlockIdAt ( $block->getX (), $block->getY (), $block->getZ (), 0 );
				
			$pos = new Position ( $block->x, $block->y, $block->z );
			$block = $p->level->getBlock ( $pos );
			$direct = true;
			$update = true;
			$p->level->setBlock ( $pos, $block, $direct, $update );
				
// 		}
	}
	
	/**
	 * Send Mods
	 *
	 * @param unknown $modId
	 * @param Player $p
	 */
	public function spawnMods($modId, $pos, Player $p) {
		$pk = new AddMobPacket ();
		$pk->eid = $modId;
		$pk->type = $modId;
		$pk->x = $pos->x;
		$pk->y = $pos->y;
		$pk->z = $pos->z;
		$pk->yaw = 0;
		$pk->pitch = 0;
		$pk->metadata = array ();
		$p->dataPacket ( $pk );
		$pk = new SetEntityMotionPacket ();
		$pk->entities = [
		[
		$modId,
		$p->motionX,
		$p->motionY,
		$p->motionZ
		]
		];
		$p->dataPacket ( $pk );
		$this->log (" spawnMods at - ".$pos->x." ".$pos->y." ".$pos->z);
	}
	
	
	public function removePlayer($pos, Player $p) {
		$pk = new RemovePlayerPacket();		
		$pk->eid = $p->getId();
		$pk->x = $pos->x;
		$pk->y = $pos->y;
		$pk->z = $pos->z;		
		$pk->clientID = 0;
		$p->dataPacket($pk);
	}
		
	public function spawnShopPlayerWithEquipments(Player $p) {
		
		//remove player before spawn				
		$x = $this->pgin->getConfig ()->get ( "skywars_pe_free1_x" );
		$y = $this->pgin->getConfig ()->get ( "skywars_pe_free1_y" );
		$z = $this->pgin->getConfig ()->get ( "skywars_pe_free1_z" );		
		$pos = new Position ( $x, $y, $z);		
		$userName = "FREE - Leather";
		$item = new Item(Item::STONE_SWORD,0,1);
		$slots = [];
		$slots [0] = Item::LEATHER_CAP;
		$slots [1] = Item::AIR;
		$slots [2] = Item::LEATHER_PANTS;
		$slots [3] = Item::LEATHER_BOOTS;
		$this->spawnPlayer($p, $pos, $userName, $item, $slots);
		
		$x = $this->pgin->getConfig ()->get ( "skywars_pe_chain1_x" );
		$y = $this->pgin->getConfig ()->get ( "skywars_pe_chain1_y" );
		$z = $this->pgin->getConfig ()->get ( "skywars_pe_chain1_z" );
		$pos = new Position ( $x, $y, $z);
		$userName = "Chain - 20 pts";
		$item = new Item(Item::STONE_SWORD,0,1);
		$slots = [];
		$slots [0] = Item::CHAIN_HELMET;
		$slots [1] = Item::CHAIN_CHESTPLATE;
		$slots [2] = Item::CHAIN_LEGGINGS;
		$slots [3] = Item::CHAIN_BOOTS;
		$this->spawnPlayer($p, $pos, $userName, $item, $slots);
		
		$x = $this->pgin->getConfig ()->get ( "skywars_pe_iron1_x" );
		$y = $this->pgin->getConfig ()->get ( "skywars_pe_iron1_y" );
		$z = $this->pgin->getConfig ()->get ( "skywars_pe_iron1_z" );
		$pos = new Position ( $x, $y, $z);
		$userName = "Iron - 40 pts";
		$item = new Item(Item:: IRON_SWORD,0,1);
		$slots = [];
		$slots [0] = Item::IRON_HELMET;
		$slots [1] = Item::IRON_CHESTPLATE;
		$slots [2] = Item::IRON_LEGGINGS;
		$slots [3] = Item::IRON_BOOTS;
		$this->spawnPlayer($p, $pos, $userName, $item, $slots);
		
		$x = $this->pgin->getConfig ()->get ( "skywars_pe_gold1_x" );
		$y = $this->pgin->getConfig ()->get ( "skywars_pe_gold1_y" );
		$z = $this->pgin->getConfig ()->get ( "skywars_pe_gold1_z" );
		$pos = new Position ( $x, $y, $z);
		$userName = "Gold - 60 pts";
		$item = new Item(Item::GOLD_SWORD,0,1);
		$slots = [];
		$slots [0] = Item::GOLD_HELMET;
		$slots [1] = Item::GOLD_CHESTPLATE;
		$slots [2] = Item::GOLD_LEGGINGS;
		$slots [3] = Item::GOLD_BOOTS;		
		$this->spawnPlayer($p, $pos, $userName, $item, $slots);
		
		$x = $this->pgin->getConfig ()->get ( "skywars_pe_diamond1_x" );
		$y = $this->pgin->getConfig ()->get ( "skywars_pe_diamond1_y" );
		$z = $this->pgin->getConfig ()->get ( "skywars_pe_diamond1_z" );
		$pos = new Position ( $x, $y, $z);
		$userName = "Diamond - 100 pts";
		$item = new Item(Item::DIAMOND_SWORD,0,1);
		$slots = [];
		$slots [0] = Item::DIAMOND_HELMET;
		$slots [1] = Item::DIAMOND_CHESTPLATE;
		$slots [2] = Item::DIAMOND_LEGGINGS;
		$slots [3] = Item::DIAMOND_BOOTS;
		$this->spawnPlayer($p, $pos, $userName, $item, $slots);		
	}
	
	
	/**
	 * Send NPC Players
	 *
	 * @param unknown $modId
	 * @param Player $p
	 */
	public function spawnPlayer(Player $p, $pos, $userName, $item, $slots) {

			$pk = new AddPlayerPacket();
			$pk->clientID = 0;
			$pk->username = $userName;
			$pk->eid = $p->getId();
			$pk->x = $pos->x;
			$pk->y = $pos->y;
			$pk->z = $pos->z;
			$pk->yaw = $p->yaw;
			$pk->pitch = $p->pitch;			
			$pk->item = $item->getId();
			$pk->meta = $item->getDamage();
			$pk->metadata = [];
			$p->dataPacket($pk);
			$p->addEntityMotion($p->getId(), 0, 290, 0);

			if ($slots!=null) {
				$pk = new PlayerArmorEquipmentPacket();
				$pk->eid =  $p->getId();
				$pk->slots = $slots;
				$pk->encode();
				$pk->isEncoded = true;
				$p->dataPacket($pk);
			}
			
			$this->log (" spawn npc at - ".$pos->x." ".$pos->y." ".$pos->z);
	}
	
	/*
	 * simple logging utility function
	 */
	private function log($msg) {
		$this->pgin->getLogger ()->info ( $msg );
	}
}