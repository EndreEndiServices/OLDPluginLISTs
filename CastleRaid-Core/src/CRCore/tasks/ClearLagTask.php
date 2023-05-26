<?php

declare(strict_types=1);

namespace CRCore\tasks;

use CRCore\core\Loader;
use pocketmine\utils\TextFormat;
use pocketmine\entity\Human;
use pocketmine\scheduler\PluginTask;
use pocketmine\Server;

class ClearLagTask extends PluginTask{

    /** @var Core */
    private $plugin;

    public function __construct(Loader $plugin){
        $this->plugin = $plugin;
        parent::__construct($plugin);
    }

    public function onRun(int $currentTick) : void{
    	$c = 0;
        foreach(Server::getInstance()->getLevels() as $level){
			foreach($level->getEntities() as $entity){
				if(!($entity instanceof Human)){
					$entity->close();
					$c++;
				}
        	}
		}
		Server::getInstance()->broadcastMessage(TextFormat::BOLD . TextFormat::RED . "(!)".TextFormat::RESET . TextFormat::RED . " Cleared " . $c . " entities");
    }
}
