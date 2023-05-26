<?php

namespace PrestigeSociety\Enchants;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use PrestigeSociety\Core\PrestigeSocietyCore;

class EnchantListener implements Listener {

	/** @var PrestigeSocietyCore */
	protected $loader;

	/**
	 *
	 * EventListener constructor.
	 *
	 * @param PrestigeSocietyCore $loader
	 *
	 */
	public function __construct(PrestigeSocietyCore $loader){
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
			if($data !== null){
				$this->loader->PrestigeSocietyEnchants->handleFormResponse($player, $data, $pk->formId);
			}
		}
	}
}