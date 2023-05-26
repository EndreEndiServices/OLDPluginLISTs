<?php

namespace athletic;

use pocketmine\plugin\PluginBase;
use pocketmine\Player;

use pocketmine\utils\Config;

use athletic\utils\PlayerModule;
use athletic\game\AthleticManager;

class Main extends PluginBase{

    protected function onEnable(): void{
        new EventListener($this);

        if (!file_exists($this->getDataFolder())){
            @mkdir($this->getDataFolder());
        }
        $config = new Config($this->getDataFolder() . "Athletics.yml", Config::YAML);
        AthleticManager::init($config);
        AthleticManager::register();

        PlayerModule::init($this);
    }

    protected function onDisable(): void{
        AthleticManager::unregister();
    }
}
