<?php

namespace mcg76\skywars\map;

use pocketmine\level\generator\Generator;
use pocketmine\level\generator\ChunkManager;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\block\Block;
use pocketmine\block\CoalOre;
use pocketmine\block\DiamondOre;
use pocketmine\block\Dirt;
use pocketmine\block\GoldOre;
use pocketmine\block\Gravel;
use pocketmine\block\IronOre;
use pocketmine\block\LapisOre;
use pocketmine\block\RedstoneOre;
use pocketmine\level\generator\noise\Simplex;
use pocketmine\level\generator\object\OreType;
use pocketmine\level\generator\populator\Ore;
use pocketmine\level\generator\populator\Populator;
use pocketmine\level\generator\populator\TallGrass;
use pocketmine\level\generator\populator\Tree;
use pocketmine\item\Item;
use mcg76\skywars\populator\SkyTree;

/**
 * MCG76 SkyBlockGenerator
 *
 * Copyright (C) 2014 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 @author minecraftgenius76@gmail.com
 *
 */

class SkyBlockGenerator extends Generator {
	private $normalp, $level, $options, $random, $floatSeed, $total, $cump, $gridlength;
	
	/**
	 *
	 * @var Populator[]
	 */
	private $populators = [ ];
	public function pickBlock($size) {
		$r = $this->random->nextFloat () * $size;
		foreach ( $this->cump as $key => $value ) {
			if ($r >= $value [0] and $r < $value [1]) {
				return $key;
			}
		}
	}
	public function getSettings() {
		return $this->options;
	}
	public function getName() {
		return "skyblock";
	}
	public function __construct(array $options = []) {
		$this->gridlength = 1;
		$this->options = $options;
		$this->normalp = [ 
				Block::STONE => 120,
				Block::GRASS => 80,
				Block::DIRT => 20,
				Block::STILL_WATER => 10,
				Block::STILL_LAVA => 5,
				Block::SAND => 20,
				Block::GRAVEL => 10,
				Block::GOLD_ORE => 10,
				Block::IRON_ORE => 20,
				Block::COAL_ORE => 40,
				Block::LAPIS_ORE => 5,
				Block::SANDSTONE => 10,
				Block::TALL_GRASS => 3,
				Block::DEAD_BUSH => 3 
		];
	}
	public function init(ChunkManager $level, Random $random) {
		$this->level = $level;
		$this->random = $random;
		$this->floatSeed = $this->random->nextFloat ();
		$this->total = 0;
		$this->cump = [ ];
		
		foreach ( $this->normalp as $key => $value ) {
			$this->cump [$key] = [ 
					$this->total,
					$this->total + $value 
			];
			$this->total += $value;
		}
		
		$ores = new Ore ();
		$ores->setOreTypes ( [ 
				new OreType ( new CoalOre (), 20, 16, 0, 128 ),
				new OreType ( new IronOre (), 20, 8, 0, 64 ),
				new OreType ( new RedstoneOre (), 8, 7, 0, 16 ),
				new OreType ( new LapisOre (), 1, 6, 0, 32 ),
				new OreType ( new GoldOre (), 2, 8, 0, 32 ),
				new OreType ( new DiamondOre (), 1, 7, 0, 16 ),
				new OreType ( new Dirt (), 20, 32, 0, 128 ),
				new OreType ( new Gravel (), 10, 16, 0, 128 ) 
		] );
		$this->populators [] = $ores;
		
		$trees = new SkyTree ();
		$trees->setBaseAmount ( 2 );
		$trees->setRandomAmount ( 2 );
		$this->populators [] = $trees;
	}
	public function generateChunk($chunkX, $chunkZ) {
		$this->random->setSeed ( ( int ) (($chunkX * 0xdead + $chunkZ * 0xbeef) * $this->floatSeed) );		
		$chunk = $this->level->getChunk ( $chunkX, $chunkZ );		
		for($y = 50; $y <= 56; $y += $this->gridlength) {
			for($z = 0; $z < 6; $z += $this->gridlength) {
				for($x = 0; $x < 6; $x += $this->gridlength) {
					$blockId = $this->pickBlock ( $this->total );
					if ($y == 52) {
						$chunk->setBlockId ( $x, $y, $z, $this->randomOreBlocks () );
					} elseif ($y == 56) {
						$i = rand ( 0, 15 );
						if ($i == 0) {
							$chunk->setBlockId ( $x, $y, $z, Block::GRASS );
							$chunk->setBlockId ( $x, $y + 1, $z, Block::TALL_GRASS );
						} elseif ($i == 1) {
							$k = rand ( 0, 10 );
							if ($k == 0) {
								$chunk->setBlockId ( $x, $y, $z, Block::GRASS );
								$chunk->setBlockId ( $x, $y + 1, $z, Block::DANDELION );
							} elseif ($k == 1) {
								$chunk->setBlockId ( $x, $y, $z, Block::SAND );
								$chunk->setBlockId ( $x, $y + 1, $z, Block::CACTUS );
							} else {
								$chunk->setBlockId ( $x, $y, $z, Block::GRASS );
							}
						} elseif ($i == 2) {
							$k = rand ( 0, 10 );
							if ($k == 0) {
								$chunk->setBlockId ( $x, $y, $z, Block::GRASS );
								$chunk->setBlockId ( $x, $y + 1, $z, Block::ROSE );
							} else {
								$chunk->setBlockId ( $x, $y, $z, Block::GRASS );
							}
						} elseif ($i == 3) {
							$k = rand ( 0, 10 );
							if ($k == 0) {
								$chunk->setBlockId ( $x, $y, $z, Block::DIRT );
							} else {
								$w = rand ( 0, 8 );
								if ($w == 1) {
									$chunk->setBlockId ( $x, $y, $z, Block::FARMLAND );
									$chunk->setBlockId ( $x, $y + 1, $z, Block::BROWN_MUSHROOM );
								} elseif ($w == 2) {
									$chunk->setBlockId ( $x, $y, $z, Block::FARMLAND );
									$chunk->setBlockId ( $x, $y + 1, $z, Block::RED_MUSHROOM );
								} else {
									$chunk->setBlockId ( $x, $y, $z, Block::GRASS );
								}
							}
						} elseif ($i == 4) {
							$k = rand ( 0, 10 );
							if ($k == 0) {
								$chunk->setBlockId ( $x, $y, $z, Block::GRASS );
								$chunk->setBlockId ( $x, $y + 1, $z, Block::DANDELION );
							} else {
								$chunk->setBlockId ( $x, $y, $z, Block::GRASS );
							}
						} elseif ($i == 5) {
							$k = rand ( 0, 10 );
							if ($k == 0) {
								$chunk->setBlockId ( $x, $y, $z, Block::PODZOL );
							} elseif ($k == 1) {
								$chunk->setBlockId ( $x, $y, $z, Block::MYCELIUM );
							} else {
								$chunk->setBlockId ( $x, $y, $z, Block::GRASS );
							}
						} elseif ($i == 6) {
							$chunk->setBlockId ( $x, $y, $z, Block::GRASS );
						} else {
							$chunk->setBlockId ( $x, $y, $z, Block::GRASS );
						}
					} elseif ($y == 50 ) {
						$chunk->setBlockId ( $x, $y, $z, Block::BEDROCK );
					} elseif ($y == 51 ) {
						$chunk->setBlockId ( $x, $y, $z, $this->randomOreBlocks() );						
					} else {
						$chunk->setBlockId ( $x, $y, $z, $this->randomSurfaceBlocks () );
					}
				}
			}
		}
	}
	public function randomGrassBlocks() {
		$i = rand ( 0, 18 );
		if ($i < 3) {
			return Block::STONE;
		}
		if ($i == 6) {
			return Block::MOSS_STONE;
		}
		if ($i == 7) {
			return Block::GRAVEL;
		}
		if ($i == 8) {
			return Block::COBBLESTONE;
		}
		return Block::DIRT;
	}
	public function randomSurfaceBlocks() {
		$i = rand ( 0, 20 );
		if ($i == 0) {
			return Block::STONE;
		}
		return Block::DIRT;
	}
	public function randomOreBlocks() {
		$i = rand ( 0, 30 );
		if ($i == 0) {
			return Block::BEDROCK;
		}
		if ($i == 1) {
			$y = rand ( 0, 10 );
			if ($y == 1) {
				return Block::DIAMOND_ORE;
			}
			return Block::STONE;
		}
		if ($i == 2) {
			$y = rand ( 0, 10 );
			if ($y == 2) {
				return Block::EMERALD_ORE;
			}
			return Block::STONE;
		}
		if ($i == 3) {
			return Block::COAL_ORE;
		}
		if ($i == 4) {
			$y = rand ( 0, 10 );
			if ($y == 4) {
				return Block::IRON_ORE;
			}
			return Block::STONE;
		}
		if ($i == 5) {
			$y = rand ( 0, 10 );
			if ($y == 5) {
				return Block::GOLD_ORE;
			}
			return Block::STONE;
		}
		if ($i == 6) {
			$y = rand ( 0, 10 );
			if ($y == 6) {
				return Block::LAPIS_ORE;
			}
			return Block::STONE;
		}
		if ($i == 7) {
			$y = rand ( 0, 10 );
			if ($y == 6) {
				return Block::REDSTONE_ORE;
			}
			return Block::STONE;
		}
		if ($i == 8) {
			return Block::STONE_BRICK;
		}
		if ($i == 9) {
			return Block::COBBLESTONE;
		}
		if ($i == 10) {
			return Block::COBBLESTONE;
		}
		if ($i == 11) {
			return Block::COBBLESTONE;
		}
		if ($i == 12) {
			return Block::COBBLESTONE;
		}
		return Block::STONE;
	}
		public function populateChunk($chunkX, $chunkZ) {
		$this->random->setSeed ( 0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed () );
		foreach ( $this->populators as $populator ) {
			$this->random->setSeed ( 0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed () );
			$populator->populate ( $this->level, $chunkX, $chunkZ, $this->random );
		}
	}
	public function getSpawn() {
		return new Vector3 ( 132, 57, 129 );
	}

