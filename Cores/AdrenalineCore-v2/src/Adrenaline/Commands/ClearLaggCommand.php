<?php

namespace Adrenaline\Commands;

use Adrenaline\CoreLoader;
use pocketmine\command\CommandSender;
use pocketmine\entity\Human;

class ClearLaggCommand extends BaseCommand{
    public function __construct(CoreLoader $plugin){
        parent::__construct($plugin, "clearlagg", "Removes entities!", "/clearlagg", []);
    }

    public function execute(CommandSender $sender, $commandLabel, array $args){
        $sender->sendMessage($this->getPlugin()->sendPrefix() . "Cleared " . $this->removeAllEntities() . " entities!");
    }

    public function removeAllEntities(){
        $i = 0;
        foreach($this->getPlugin()->getServer()->getLevels() as $level) {
            foreach($level->getEntities() as $entity) {
                if(!($entity instanceof Human)) {
                    $entity->close();
                    $i++;
                }
            }
        }
        return $i;
    }
}