<?php

namespace athletic;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;

use athletic\Main;

use athletic\utils\PlayerModule;
use athletic\game\AthleticManager;

class EventListener implements Listener{

    public function __construct(Main $plugin){
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
    }

    public function onInteract(PlayerInteractEvent $event): void{
        if ($event->getBlock() === "1"){
            $athletic = AthleticManager::existsAthletic($event->getTouchVector());
            $module = PlayerModule::getInstance();
            $module->setPlayer($event->getPlayer(), $athletic);
            $module->onStart();
        }
    }
}
