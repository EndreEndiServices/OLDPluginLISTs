<?php

namespace nlog\SmartUI;

use pocketmine\plugin\PluginBase;
use nlog\SmartUI\util\Settings;
use nlog\SmartUI\FormHandlers\FormManager;
use nlog\SmartUI\commands\OpenUICommand;

class SmartUI extends PluginBase{


    const SETTING_VERSION = 1;

    /** @var SmartUI|null */
    private static $instance = null;

    /** @var string */
    public static $prefix = "§c§l[§bSmartUI§c] §7";

    /**
     * @return SmartUI|null
     */
    public static function getInstance(): ?SmartUI {
        return static::$instance;
    }

    /** @var Settings|null */
    private $setting = null;

    /** @var FormManager|null */
    private $formManager = null;

    public function onLoad() {
        static::$instance = $this;
    }

    public function onEnable() {
        @mkdir($this->getDataFolder());
        $this->saveResource("settings.yml");
        $this->setting = new Settings($this->getDataFolder() . "settings.yml", $this);
        $this->formManager = new FormManager($this);

        $this->getServer()->getCommandMap()->register("smart", new OpenUICommand($this));
       
    }

    /**
     * @return Settings|null
     */
    public function getSettings(): ?Settings {
        return $this->setting;
    }

    /**
     * @return FormManager|null
     */
    public function getFormManager(): ?FormManager {
        return $this->formManager;
    }

}//클래스 괄호

?>