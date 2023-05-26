<?php

namespace nlog\SmartUI\util;

use pocketmine\level\Level;
use pocketmine\utils\Config;
use pocketmine\Server;
use pocketmine\Player;
use onebone\economyapi\EconomyAPI;
use nlog\SmartUI\SmartUI;

class Settings {

    /** @var Config */
    protected $config;

    /** @var SmartUI */
    private $plugin;

    /** @var Server */
    protected $server;

    /** @var array */
    protected $availableParameter;

    public function __construct(string $path, SmartUI $plugin) {
        $this->plugin = $plugin;
        $this->config = new Config($path, Config::YAML);
        $plugin->saveResource("settings.yml", true); //TODO: 세팅 파일 업데이트 시 보존
        
        $this->server = Server::getInstance();
        $this->availableParameter = [
                "@playername",
                "@playercount",
                "@playermaxcount",
                "@motd",
                "@mymoney",
                "@health",
                "@maxhealth",
                "@year",
                "@month",
                "@day",
                "@hour"
        ];
    }


    public function getItem() {
        return $this->config->get("item", "345:0");
    }

    public function getMessage(Player $player) {
        if (class_exists(EconomyAPI::class, true)) {
            $money = EconomyAPI::getInstance()->myMoney($player);
        } else {
            $money = "@mymoney";
        }
        $msg = $this->config->get("message");
        $msg = str_replace($this->availableParameter, [
                $player->getName(),
                count($this->server->getOnlinePlayers()),
                $this->server->getMaxPlayers(),
                $this->server->getNetwork()->getName(),
                $money,
                $player->getHealth(),
                $player->getMaxHealth(),
                date("Y"),
                date("m"),
                date("d"),
                date("g")
        ], $msg);

        $msg = str_replace('\n', "\n", $msg);

        return $msg;
    }

    public function canUseInWorld(Level $level): bool {
        $return = $this->config->getAll()["worlds"][strtolower($level->getFolderName())] ?? -1;
        if ($return < 0) {
            return true;
        }
        if (count($level->getPlayers()) >= $return) {
            return false;
        }
        return true;
    }

    public function canUse(string $functionIdentifyName): bool {
        $return = $this->config->getAll()["toggle"][$functionIdentifyName] ?? "on";
        $return = $return === "on" ? true : false;
        return $return;
    }

    public function getSetting(string $functionIdentifyName, string $key = "") {
        $function = $this->config->get($functionIdentifyName, null);
        if ($function === null) {
            return null;
        } elseif (!is_array($function) || trim($key) === "") {
            return $function;
        } else {
            return $function[$key] ?? null;
        }
    }
}