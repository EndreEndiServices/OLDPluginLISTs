<?php

declare(strict_types=1);

namespace fcore\profile;

use fcore\FCore;
use pocketmine\Player;
use pocketmine\utils\Config;

/**
 * Class ProfileManager
 * @package fcore\profile
 */
class ProfileManager {

    /** @var FCore $plugin */
    public $plugin;

    /** @var array $players */
    public static $players = [];

    /**
     * ProfileManager constructor.
     * @param FCore $plugin
     */
    public function __construct(FCore $plugin) {
        $this->plugin = $plugin;
        $this->load();
    }

    public function load() {
        @mkdir($this->plugin->getDataFolder());
        if(is_file($this->plugin->getDataFolder() . "/profiles.yml")) {
            $config = new Config($this->plugin->getDataFolder() . "/profiles.yml", Config::YAML);
            self::$players = $config->getAll();
        }
        $config = new Config($this->plugin->getDataFolder() . "/profiles.yml", Config::YAML);
        self::$players = $config->getAll();
    }

    public function save() {
        $config = new Config($this->plugin->getDataFolder() . "/profiles.yml", Config::YAML);
        $config->setAll(self::$players);
        $config->save();
    }

    /**
     * @param Player $player
     */
    public static function onJoin(Player $player) {
        if(empty(self::$players[$player->getName()])) {
            self::newPlayer($player);
        }
        if(empty(self::$players[$player->getName()]["gadgets"]["headcircle"])) {
            self::$players[$player->getName()]["gadgets"]["headcircle"] = [false];
        }
        if(empty(self::$players[$player->getName()]["gadgets"]["wing"])) {
            self::$players[$player->getName()]["gadgets"]["wing"] = [false];
        }
        if(empty(self::$players[$player->getName()]["chest"])) {
            self::$players[$player->getName()]["chest"] = "null";
        }
        if(empty(self::$players[$player->getName()]["lang"])) {
            self::$players[$player->getName()]["lang"] = "eng";
        }
    }

    /**
     * @param Player $player
     */
    public static function newPlayer(Player $player) {
        if(isset(self::$players[$player->getName()])) {
            return;
        }
        self::$players[$player->getName()] = [
            "coins" => 700,
            "vip" => false,
            "rank" => "guest",
            "level" => 0,
            "chest" => "null",
            "lang" => "eng",
            "auth" => [
                "reg" => false,
                "log" => false,
                "pwd" => null
            ],
            "gadgets" => [
                "fly" => [false, 0],
                "tnt" => [true, 5],
                "giant" => [false, 0]
            ],
            "particles" => [
                "unicorn" => [
                    false
                ],
                "helix" => [
                    false
                ],
                "wing" => [
                    false
                ],
                "headcircle" => [
                    false
                ]
            ],
            "kits" => [
                "classic" => false,
                "warrior" => false,
                "archer" => false,
                "bomber" => false,
                "dwarf" => false,
                "vip" => false,
                "troll" => false,
                "wizard" => false,
                "gepl" => false
            ],
            "kit" => null,
            "kills" => 0,
            "deaths" => 0,
            "broken" => 0,
            "placed" => 0,
            "ew" => [

            ],
            "mm" => [

            ]
        ];
        $player->sendMessage(FCore::getPrefix()."§aYou got 700 coins for first join!");
    }

    /**
     * @param Player $player
     * @return array
     */
    public static function getPlayerProfile(Player $player): array {
        return self::$players[$player->getName()];
    }

    /**
     * @param Player $player
     * @param string $index
     * @return mixed
     */
    public static function getPlayerProfileData(Player $player, string $index) {
        return self::getPlayerProfile($player)[$index];
    }

    /**
     * @api
     *
     * @param Player $player
     * @param string $rank
     */
    public static function setPlayerRank(Player $player, string $rank) {
        self::$players[$player->getName()]["rank"] = $rank;
    }

    /**
     * @api
     *
     * @param Player $player
     * @return string
     */
    public static function getPlayerRank(Player $player): string {
        return self::$players[$player->getName()]["rank"];
    }

    /**
     * @api
     *
     * @param Player $player
     * @return  bool
     */
    public static function isVip(Player $player): bool {
        return boolval(self::getPlayerRank($player) == "vip" || $player->isOp() || self::getPlayerRank($player) == "sponsor");
    }

    /**
     * @api
     *
     * @param Player $player
     * @param float $coins
     */
    public static function addCoins(Player $player, float $coins) {
        self::$players[$player->getName()]["coins"] += $coins;
    }

    /**
     * @api
     *
     * @param Player $player
     * @param float $coins
     */
    public static function removeCoins(Player $player, float $coins) {
        self::$players[$player->getName()]["coins"] -= $coins;
    }

    /**
     * @api
     *
     * @param Player $player
     *
     * @return float
     */
    public static function getCoins(Player $player): float {
        return (float)self::$players[$player->getName()]["coins"];
    }

    /**
     * @api
     *
     * @param Player $player
     * @param float $coins
     *
     * @return bool
     */
    public static function hasCoins(Player $player, float $coins): bool {
        return (bool)(self::getCoins($player)-$coins >= 0);
    }

    /**
     * @api
     *
     * @param Player $player
     * @param string $gadget
     *
     * @return bool
     */
    public static function hasGadget(Player $player, string $gadget): bool {
        if(empty(self::$players[$player->getName()]["gadgets"][$gadget])) {
            return false;
        }

        $gadgetArgs = self::$players[$player->getName()]["gadgets"][$gadget];

        return (bool)$gadgetArgs[0];
    }

    /**
     * @api
     *
     * @param Player $player
     * @param string $gadget
     *
     * @return int
     */
    public static function getPlayerGadgetsCount(Player $player, string $gadget): int {
        if(self::hasGadget($player, $gadget) === false) {
            return 0;
        }

        return intval(self::$players[$player->getName()]["gadgets"][$gadget][1]);
    }

    /**
     * @api
     *
     * @param Player $player
     * @param string $gadget
     * @param int|null $coins
     */
    public static function buyGadget(Player $player, string $gadget, int $coins = 0) {
        self::$players[$player->getName()]["gadgets"][$gadget] = [true, ceil(self::getPlayerGadgetsCount($player, $gadget)+1)];
        self::removeCoins($player, floatval($coins));
    }

    /**
     * @api
     *
     * @param Player $player
     * @param string $gadget
     * @param bool $stackable
     */
    public static function removeGadget(Player $player, string $gadget, bool $stackable) {
        if($stackable) {
            $gadgetArgs = self::$players[$player->getName()]["gadgets"][$gadget];
            $bool = true;
            if($gadgetArgs[1] <= 1) {
                $bool = false;
            }
            $count = $gadgetArgs[1]-1;
            self::$players[$player->getName()]["gadgets"][$gadget] = [$bool, $count];
        }
    }

    /**
     * @api
     *
     * @param Player $player
     * @param string $particle
     *
     * @return bool
     */
    public static function hasParticle(Player $player, string $particle): bool {
        if(empty(self::$players[$player->getName()]["particles"][$particle])) {
            return false;
        }

        return boolval(self::$players[$player->getName()]["particles"][$particle][0]);
    }

    /**
     * @api
     *
     * @param Player $player
     * @param string $particle
     * @param int $coins
     */
    public static function buyParticle(Player $player, string $particle, int $coins = 0) {
        self::$players[$player->getName()]["particles"][$particle] = [true];
        self::removeCoins($player, floatval($coins));
    }

    /**
     * @param Player $player
     * @return string
     */
    public static function lang(Player $player) {
        return self::$players[$player->getName()]["lang"];
    }
}