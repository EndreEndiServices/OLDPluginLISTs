<?php

namespace mcg76\skywars\populator;

use pocketmine\block\Block;
use pocketmine\block\Sapling;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\object\Tree as ObjectTree;
use pocketmine\utils\Random;
use pocketmine\level\generator\populator\Populator;

/**
 * MCG76 SkyTree
 *
 * Copyright (C) 2014 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 @author minecraftgenius76@gmail.com
 *
 */
class SkyTree extends Populator{
	private $level;
	private $randomAmount;
	private $baseAmount;

	public function setRandomAmount($amount){
		$this->randomAmount = $amount;
	}

	public function setBaseAmount($amount){
		$this->baseAmount = $amount;
	}

	public function populate(ChunkManager $level, $chunkX, $chunkZ, Random $random){
		$this->level = $level;
		$amount = $random->nextRange(0, $this->randomAmount + 1) + $this->baseAmount;
		for($i = 0; $i < $amount; ++$i){
			$x = $random->nextRange($chunkX << 4, ($chunkX << 4) + 15);
			$z = $random->nextRange($chunkZ << 4, ($chunkZ << 4) + 15);
			$y = $this->getHighestWorkableBlock($x, $z);
			if($y == -1 || $y == 0){
				continue;
			}
			$fi = $random->nextFloat();
			if($fi > 0.5 && $fi < 0.75){
				$meta = Sapling::SPRUCE;
			} elseif($fi > 0.75){
				$meta = Sapling::BIRCH;				
			}else{
				$meta = Sapling::OAK;
			}
			if ($y<5) {
				continue;
			}
			ObjectTree::growTree($this->level, $x, $y, $z, $random, $meta);
		}
	}

	private function getHighestWorkableBlock($x, $z){
		for($y = 128; $y > 1; --$y){
			$b = $this->level->getBlockIdAt($x, $y, $z);
			if($b !== Block::DIRT and $b !== Block::GRASS){
				if(--$y <= 0){
					return -1;
				}
			}else{
				break;
			}
		}

		return ++$y;
	}
}