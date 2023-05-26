<?php

namespace Adrenaline\Managers;

use Adrenaline\Commands\ClearInvCommand;
use Adrenaline\Commands\ClearLaggCommand;
use Adrenaline\Commands\LoginCommand;
use Adrenaline\Commands\NickCommand;
use Adrenaline\Commands\NPCCommand;
use Adrenaline\Commands\RegisterCommand;
use Adrenaline\Commands\ScaleCommand;
use Adrenaline\Commands\UHCCommand;
use Adrenaline\CoreLoader;

class CommandManager {

    public $plugin;

    public function __construct(CoreLoader $loader) {
        $this->plugin = $loader;
        $this->init();
    }

    private function init() {
        $commandMap = $this->plugin->getServer()->getCommandMap();

        $commandMap->registerAll("uhc", [
            new ClearLaggCommand($this->plugin),
            new ClearInvCommand($this->plugin),
            new NickCommand($this->plugin),
            //new ScaleCommand($this->plugin), Held off due to constant abuse
            new UHCCommand($this->plugin),
            //new LoginCommand($this->plugin),
            //new RegisterCommand($this->plugin),
            //new NPCCommand($this->plugin), Useless like always
        ]);
    }
}