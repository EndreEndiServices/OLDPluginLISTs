<?php

namespace PrestigeSociety\TradeUI;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use PrestigeSociety\TradeUI\Task\OpenPurchasesMessageForm;

class EventListener implements Listener {

	/** @var PrestigeSocietyTradeUI */
	protected $loader;

	/**
	 *
	 * EventListener constructor.
	 *
	 * @param PrestigeSocietyTradeUI $loader
	 *
	 */
	public function __construct(PrestigeSocietyTradeUI $loader){
		$this->loader = $loader;
	}

	/**
	 *
	 * @param DataPacketReceiveEvent $event
	 *
	 */
	public function onDataPacketReceiveEvent(DataPacketReceiveEvent $event){
		$pk = $event->getPacket();
		$player = $event->getPlayer();

		if($pk instanceof ModalFormResponsePacket){
			$data = json_decode($pk->formData, true);
			$this->loader->handleFormResponse($player, $data, $pk->formId);
			/*if($data !== null){
					$this->loader->handleFormResponse($player, $data, $pk->formId);
			}else{
					$this->loader->resetCache($player);
			}*/
		}
	}

	/**
	 *
	 * @param PlayerJoinEvent $event
	 *
	 */
	public function onJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$cache = $this->loader->getCache($player);
		if(isset($cache['purchasesMessage'])){
			$this->loader->core->getScheduler()->scheduleDelayedTask(new OpenPurchasesMessageForm($this->loader, $cache['purchasesMessage'], $player), 20);
			$this->loader->resetCache($player);
		}
	}
}