<?php

namespace AuctionHouse\Chest;

use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\nbt\tag\{CompoundTag, IntTag};
use pocketmine\Player;
use pocketmine\tile\Chest;

class CustomChest extends \pocketmine\tile\Chest{

	private $replacement = [0, 0];

	public function __construct(Level $level, CompoundTag $nbt){
		parent::__construct($level, $nbt);
		$this->inventory = new CustomChestInventory($this);
		$this->replacement = [$this->getBlock()->getId(), $this->getBlock()->getDamage()];
	}

	public function getInventory() : CustomChestInventory{
		return $this->inventory;
	}

	private function getReplacement() : Block{
		return Block::get(...$this->replacement);
	}

	public function sendReplacement(Player $player){
        $block = $this->getReplacement();
        $block->x = (int) $this->x;
        $block->y = (int) $this->y;
        $block->z = (int) $this->z;
        $block->level = $this->getLevel();
        if($block->level !== null){
            $block->level->sendBlocks([$player], [$block]);
        }
    }

	public function addAdditionalSpawnData(CompoundTag $nbt) : void{
    }
}
