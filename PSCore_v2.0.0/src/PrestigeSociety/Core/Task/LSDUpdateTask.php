<?php

namespace PrestigeSociety\Core\Task;

use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\level\particle\DustParticle;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use PrestigeSociety\Core\PrestigeSocietyCore;

class LSDUpdateTask extends PluginTask {

	/** @var array */
	protected $rgb = 0;
	/** @var int */
	protected $next = 0;
	/** @var int */
	protected $len = 0;
	/** @var array */
	protected $colors = [];
	/** @var Block[] */
	protected $processedBlocks = [];
	/** @var bool */
	protected $increasing = true;
	/** @var Player */
	private $player = null;

	/**
	 *
	 * WelcomePlayerTask constructor.
	 *
	 * @param PrestigeSocietyCore $owner
	 * @param Player $player
	 *
	 */
	public function __construct(PrestigeSocietyCore $owner, Player &$player){
		parent::__construct($owner);
		$this->owner = $owner;
		$this->player = $player;
		$this->colors = $owner->FunBox->generateColors();
		$this->len = count($this->colors) - 1;
	}

	/**
	 *
	 * Called upon task cancel
	 *
	 */
	public function onCancel(){
		foreach($this->processedBlocks as $index => $block){
			$block->getLevel()->setBlock($block, $block);
			unset($this->processedBlocks[$index]);
		}
	}

	/**
	 * Actions to execute when run
	 *
	 * @param int $currentTick
	 *
	 * @return void
	 *
	 */
	public function onRun(int $currentTick){
		if($this->player->getLevel()->getName() == "pvp") return;

		if($this->next >= $this->len){
			$this->next = 0;
		}

		$player = $this->player;

		/** @var Vector3[] $positions */
		$positions = [];

		if($player->isSneaking()){
			$positions[] = $player->getLevel()->getBlock($player->subtract(0, 2, 0));
		}else{
			$positions[] = $player->getLevel()->getBlock($player->subtract(0, 1, 0));
		}

		$player->level->addParticle(new DustParticle($player->add(0, -1, 0), mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)));

		foreach($this->processedBlocks as $index => $block){
			$block->getLevel()->setBlock($block, $block);
			unset($this->processedBlocks[$index]);
		}

		$block = Block::get(Block::WOOL);

		foreach($positions as $position){
			$block->setDamage(mt_rand(1, 14));
			$this->processedBlocks[] = $player->level->getBlock($position);
			$player->level->setBlock($position, $block);
		}

		$helmet = Item::get(Item::LEATHER_HELMET, 0, 1);
		$chest = Item::get(Item::LEATHER_CHESTPLATE, 0, 1);
		$legs = Item::get(Item::LEATHER_PANTS, 0, 1);
		$feet = Item::get(Item::LEATHER_BOOTS, 0, 1);

		$nbt = new CompoundTag("", []);

		$nbt->setInt("customColor", $this->colors[$this->next++]);

		//$nbt->customColor = new IntTag("customColor", $this->colors[$this->next++]);

		$helmet->setCompoundTag($nbt);
		$chest->setCompoundTag($nbt);
		$legs->setCompoundTag($nbt);
		$feet->setCompoundTag($nbt);

		$this->player->getArmorInventory()->setContents([$helmet, $chest, $legs, $feet]);

	}
}