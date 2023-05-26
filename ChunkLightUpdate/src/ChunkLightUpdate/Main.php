<?php

namespace ChunkLightUpdate;

use pocketmine\event\level\ChunkLoadEvent;

use pocketmine\event\Listener;

use pocketmine\level\generator\LightPopulationTask;

use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener

{

public function onEnable()

{

$this->getServer()->getPluginManager()->registerEvents($this, $this);

}

public function updateLight(ChunkLoadEvent $event)

{

$this->getServer()->getScheduler()->scheduleAsyncTask(new LightPopulationTask($event->getLevel(), $event->getChunk()));

}

}