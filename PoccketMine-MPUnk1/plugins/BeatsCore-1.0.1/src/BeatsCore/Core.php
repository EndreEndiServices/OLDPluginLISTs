<?php

declare(strict_types=1);

namespace BeatsCore;

use BeatsCore\anti\{
    AntiAdvertising, AntiSwearing
};
use BeatsCore\commands\{
    FlyCommand, HUDCommand, NickCommand, RulesCommand
};
use BeatsCore\tasks\{
    BroadcastTask, HUDTask
};
use pocketmine\plugin\PluginBase;
use BeatsCore\custompotion\CustomPotionEvent;
use BeatsCore\potions\Potions;
use BeatsCore\items\ItemManager;
use BeatsCore\entity\EntityManager;
use pocketmine\utils\Config;

class Core extends PluginBase{

    const PERM_RANK = "§l§8[§c+§8]§r §7You don't have permission to use this command!";
    const PERM_STAFF = "§l§8[§c+§8]§r §7Only staff members can use this command!";
    const USE_IN_GAME = "§l§8[§c+§8]§r §7Please use this command in-game!";

    /** @var int */
    public static $ePearlDamage = 5;
    /** @var int */
    public static $enderPearlCooldown = 8;
    /** @var null */
    private static $instance = null;
    /** @var array */
    public $chat = [];
    /** @var array */
    public $hud = [];
    /** @var Config */
    public $config;

    public static function getInstance() : self{
        return self::$instance;
    }

    public function onEnable() : void{
        // COMMANDS \\
        $this->getServer()->getCommandMap()->registerAll("BeatsCore", [
            new FlyCommand("fly", $this),
            new NickCommand("nick", $this),
            new RulesCommand("rules", $this),
            new CustomPotion("custompotion", $this),
            new WildCommand("wild", $this),
            new HUDCommand($this)
        ]);
        // CONFIGS \\
        @mkdir($this->getDataFolder());
        $this->saveResource("changelog.txt");
        $this->saveResource("rules.txt");
        $this->saveResource("config.yml");
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        // EVENTS \\
        self::$instance = $this;
        $this->getServer()->getPluginManager()->registerEvents(new CustomPotionEvent(), $this);
        $this->regManagers();
        $this->getServer()->getPluginManager()->registerEvents(new Potions(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new AntiAdvertising($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new AntiSwearing($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        // TASKS \\
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new BroadcastTask($this), 2400);
    }

    public function regManagers() : void{
        EntityManager::Start();
        ItemManager::Start();
        new StackEvent($this);
    }
}