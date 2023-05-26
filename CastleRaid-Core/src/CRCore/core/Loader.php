<?php
/**
 * -==+CastleRaid Core+==-
 * Originally Created by QuiverlyRivarly
 * Originally Created for CastleRaidPE
 *
 * @authors: CastleRaid Developer Team
 */
declare(strict_types=1);

namespace CRCore\core;

// Commands
use CRCore\commands\ClearInventoryCommand;
use CRCore\commands\CustomPotionsCommand;
use CRCore\commands\PingCommand;
use CRCore\commands\WildCommand;
use CRCore\commands\StatsCommand;
use CRCore\commands\FeedbackCommand;
use CRCore\commands\SpawnCommand;
use CRCore\commands\SetSpawnCommand;
use CRCore\commands\FeedCommand;
use CRCore\commands\FlyCommand;
use CRCore\commands\StaffModeCommand;
use CRCore\commands\HealCommand;
use CRCore\commands\MailCommand;
use CRCore\commands\MenuCommand;
use CRCore\commands\OpHelpCommand;
use CRCore\commands\HomeCommand;
use CRCore\commands\TpaCommand;
use CRCore\commands\TpAcceptCommand;
use CRCore\commands\TpDenyCommand;
use CRCore\events\EventListener;
use CRCore\events\PotionListener;
use CRCore\events\HeadListener;
use CRCore\events\TeleportListener;
use CRCore\events\RelicListener;
use CRCore\events\KillMoneyListener;
use CRCore\tasks\ClearLagTask;
use CRCore\core\api\TPAPI;
use CRCore\core\api\HomeAPI;
use CRCore\core\api\API;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use SQLite3;

class Loader extends PluginBase{
    public $PrestigeSocietyStaffMode;

    const CORE_VERSION = "v1.4.6";

    public static $instance;

    public function getPlugin(): Loader {
        return $this;
}


    public function onLoad() : void{
        $this->getLogger()->info("§aCRCore enabled");
        API::$main = $this;
        self::$instance = $this;
    }
    public function onEnable() : void{
        $this->registerCommands();
        $this->registerEvents();
        $this->registerTasks();


        if(!file_exists($this->databasesFolder())){
        mkdir($this->databasesFolder());

                }
        $this->TeleportListener = new TeleportListener($this);
        $this->TeleportListener->init();
    }

    public function registerTasks() : void{
        $this->getScheduler()->scheduleRepeatingTask(new ClearLagTask($this), 20 * 60);
    }

    public function registerEvents() : void{
        new EventListener($this);
        new PotionListener($this);
        new HeadListener($this);
        new RelicListener($this);
        new KillMoneyListener($this);
    }
    public function databasesFolder(){
        return $this->getDataFolder() . "database/";
    }

    public function registerCommands() : void{
        $this->getServer()->getCommandMap()->registerAll("CRCore", [
            new ClearInventoryCommand($this),
            new CustomPotionsCommand($this),
            new FlyCommand($this),
            new SpawnCommand($this),
            new SetSpawnCommand($this),
            new HealCommand($this),
            new FeedCommand($this),
            new PingCommand($this),
            new WildCommand($this),
            new StatsCommand($this),
            new OPHelpCommand($this),
        ]);

}
    public static function getInstance() : self{
        return self::$instance;
    }
}
