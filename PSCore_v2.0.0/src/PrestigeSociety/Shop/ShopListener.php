<?php

namespace PrestigeSociety\Shop;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use PrestigeSociety\Core\PrestigeSocietyCore;

class ShopListener implements Listener {

	/** @var PrestigeSocietyCore */
	private $core;

	/**
	 *
	 * CombatLoggerListener constructor.
	 *
	 * @param PrestigeSocietyCore $c
	 *
	 */
	public function __construct(PrestigeSocietyCore $c){
		$this->core = $c;
	}

	public function dataPacket(DataPacketReceiveEvent $event){
		$packet = $event->getPacket();
		if($packet instanceof ModalFormResponsePacket){
			$data = json_decode($packet->formData, true);
			if($data !== null){
				$this->core->PrestigeSocietyShop->handleFormResponse($event->getPlayer(), $data, $packet->formId);
			}
		}
	}

}