<?php

namespace AuctionHouse;

use AuctionHouse\Chest\{CustomChest, CustomChestInventory};
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\inventory\transaction\action\DropItemAction;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;

use onebone\economyapi\EconomyAPI;

class EventListener implements Listener{

	protected $plugin;

	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}

	public function onBreak(BlockBreakEvent $event){
		if($event->getBlock()->getLevel()->getTile($event->getBlock()) instanceof CustomChest) $event->setCancelled();
	}

	public function onTransaction(InventoryTransactionEvent $event){
		$transactions = $event->getTransaction()->getActions();
		$player = null;
		$chestinv = null;
		$action = null;
		foreach($transactions as $transaction){
			if($transaction instanceof DropItemAction) return;
			if(($inv = $transaction->getInventory()) instanceof CustomChestInventory){
				foreach($inv->getViewers() as $assumed){
					if($assumed instanceof Player){
						$player = $assumed;
						$chestinv = $inv;
						$action = $transaction;
						break 2;
					}
				}
			}
		}

		if(($player ?? $chestinv ?? $action) === null){
			return;
		}

		$event->setCancelled();
		$item = $action->getSourceItem();
		if($item->getId() === Item::AIR){
			return;
		}

		if(isset($item->getNamedTag()->turner)){
			$pagedata = $item->getNamedTag()->turner->getValue();
			$page = $pagedata[0] === 0 ? --$pagedata[1] : ++$pagedata[1];
			$this->plugin->fillInventoryWithShop($chestinv, $page);
			return;
		}

		$data = isset($item->getNamedTag()->ChestShop) ? $item->getNamedTag()->ChestShop->getValue() : null;

		if($data === null){
			return;
		}

		$price = $data[0];
		
		if(!isset($this->plugin->clicks[$player->getId()][$data[1]])){
			$this->plugin->clicks[$player->getId()][$data[1]] = 1;
			return;
		}

		if(EconomyAPI::getInstance()->myMoney($player) >= $price){
	 	 	$item = $this->plugin->getItemFromShop($data[1]);
			$player->sendMessage(Main::PREFIX.TF::GREEN.'Purchased '.TF::BOLD.$item->getName().TF::RESET.TF::GRAY.' (x'.$item->getCount().')'.TF::GREEN.' for $'.$price.'.');
			$player->getInventory()->addItem($item);
			EconomyAPI::getInstance()->reduceMoney($player, $price);
			unset($this->plugin->clicks[$player->getId()]);
			$this->plugin->fillInventoryWithShop($chestinv);
		}else{
			$player->sendMessage(Main::PREFIX.TF::RED.'You cannot afford this item.');
			$chestinv->onClose($player);
		}
	}
}
