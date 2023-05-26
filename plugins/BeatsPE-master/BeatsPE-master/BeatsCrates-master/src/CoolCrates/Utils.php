<?php

namespace CoolCrates;


use pocketmine\level\Position;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class Utils {
    
    /**
     * @param string $message
     * @return string
     */
    public static function translateColors(string $message): string {
        $message = str_replace("&0", TextFormat::BLACK, $message);
        $message = str_replace("&1", TextFormat::DARK_BLUE, $message);
        $message = str_replace("&2", TextFormat::DARK_GREEN, $message);
        $message = str_replace("&3", TextFormat::DARK_AQUA, $message);
        $message = str_replace("&4", TextFormat::DARK_RED, $message);
        $message = str_replace("&5", TextFormat::DARK_PURPLE, $message);
        $message = str_replace("&6", TextFormat::GOLD, $message);
        $message = str_replace("&7", TextFormat::GRAY, $message);
        $message = str_replace("&8", TextFormat::DARK_GRAY, $message);
        $message = str_replace("&9", TextFormat::BLUE, $message);
        $message = str_replace("&a", TextFormat::GREEN, $message);
        $message = str_replace("&b", TextFormat::AQUA, $message);
        $message = str_replace("&c", TextFormat::RED, $message);
        $message = str_replace("&d", TextFormat::LIGHT_PURPLE, $message);
        $message = str_replace("&e", TextFormat::YELLOW, $message);
        $message = str_replace("&f", TextFormat::WHITE, $message);
        $message = str_replace("&k", TextFormat::OBFUSCATED, $message);
        $message = str_replace("&l", TextFormat::BOLD, $message);
        $message = str_replace("&m", TextFormat::STRIKETHROUGH, $message);
        $message = str_replace("&n", TextFormat::UNDERLINE, $message);
        $message = str_replace("&o", TextFormat::ITALIC, $message);
        $message = str_replace("&r", TextFormat::RESET, $message);
        return $message;
    }
    
    /**
     * The inverse of parse a position
     *
     * @param Position $position
     * @return string
     */
    public static function createPositionString(Position $position) {
        return "{$position->getLevel()->getName()},{$position->getX()},{$position->getY()},{$position->getZ()}";
    }
    /**
     * Return a parsed position
     *
     * @param $string
     * @return null|Position
     */
    public static function parsePosition($string) {
        $array = explode(",", $string);
        if(isset($array[3]) and ($level = Server::getInstance()->getLevelByName($array[0])) != null) {
            return new Position($array[1], $array[2], $array[3], $level);
        } else {
            return null;
        }
    }
    
}
