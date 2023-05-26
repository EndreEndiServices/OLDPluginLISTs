<?php

namespace PrestigeSociety\Jobs;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use PrestigeSociety\Core\PrestigeSocietyCore;
use PrestigeSociety\Core\Utils\RandomUtils;

class JobsTask extends PluginTask {


	public $seconds = 10;
	public $core;
	/** @var Player */
	public $player;

	/**
	 *
	 * OptimizeTask constructor.
	 *
	 * @param PrestigeSocietyCore $c
	 *
	 */
	public function __construct(PrestigeSocietyCore $c, Player $player){
		parent::__construct($c);

		$this->player = $player;
		$this->core = $c;
	}

	/**
	 *
	 * @param $currentTick
	 *
	 */
	public function onRun($currentTick){
		$inventory = $this->player->getInventory();
		$item = $inventory->getItemInHand();
		if($this->seconds == 10) $inventory->removeItem($item);
		if($this->seconds == 1){
			$milk = Item::get(Item::BUCKET, 1, 1);
			$items = $milk;
			$enchantment = Enchantment::getEnchantment(Enchantment::PROTECTION);
			$enchantment = new EnchantmentInstance($enchantment, 1);
			$items->addEnchantment($enchantment);
			$inventory->addItem($items);
			$this->getHandler()->cancel();
			$this->player->setImmobile(false);
		}
		$title = "&eYou get the milk in";
		$title = RandomUtils::colorMessage($title);
		if($this->seconds > 3){
			$this->player->addTitle($title, RandomUtils::colorMessage("&c" . $this->seconds));
		}elseif($this->seconds >= 2 and $this->seconds <= 3){
			$this->player->addTitle($title, RandomUtils::colorMessage("&e" . $this->seconds));
		}elseif($this->seconds == 1){
			$this->player->addTitle($title, RandomUtils::colorMessage("&a" . $this->seconds));
		}
		--$this->seconds;
	}
}