<?php
/**
 * -==+CastleRaid Core+==-
 * Originally Created by QuiverlyRivarly
 * Originally Created for CastleRaidPE
 *
 * @authors: CastleRaid Developer Team
 */
declare(strict_types=1);

namespace CRCore\core\api;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class API{
    /** @var Loader $main */
    public static $main;

    /** @var Config $names */
    public static $names;

    /** @var Config $chat */
    public static $chat;

    /** @var Config $msg */
    public static $msg;
    
    const NOT_PLAYER = TextFormat::BOLD . TextFormat::GRAY . "(" . TextFormat::RED . "!" . TextFormat::GRAY . ")" . TextFormat::RED . " Use this command in-game!";
    const NO_PERM = TextFormat::RED . " You don't have permision to use this command";

    const PREFIX = "§l§8(§e!§8)§r ";

    public static function getRandomBcast() : string{
        $b = self::$msg->getAll()["broadcast"];
        return $b[array_rand($b)];
    }

    public static function getRandomColor() : string{
        $ltr = "abcdef";
        return TextFormat::ESCAPE . (mt_rand(0, 1) == 1 ? $ltr[mt_rand(0, strlen($ltr) - 1)] : strval(mt_rand(0, 9)));
    }
}
