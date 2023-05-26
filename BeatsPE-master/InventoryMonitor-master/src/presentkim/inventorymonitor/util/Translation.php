<?php

namespace presentkim\inventorymonitor\util;

use presentkim\inventorymonitor\InventoryMonitor as Plugin;

class Translation{

    /** @var string[string] */
    private static $lang = [];

    /** @var string[string] */
    private static $default = [];

    /**
     * @param string $filename
     * @param bool   $default = false
     */
    public static function load(string $filename, bool $default = false) : void{
        if ($default) {
            self::$default = yaml_parse_file($filename);
        } else {
            self::$lang = yaml_parse_file($filename);
        }
    }

    /**
     * @param string $contents
     * @param bool   $default
     */
    public static function loadFromContents(string $contents, bool $default = false) : void{
        if ($default) {
            self::$default = yaml_parse($contents);
        } else {
            self::$lang = yaml_parse($contents);
        }
    }

    /**
     * @param resource $resource
     * @param bool     $default
     */
    public static function loadFromResource($resource, bool $default = false) : void{
        self::loadFromContents(stream_get_contents($resource), $default);
    }

    /**
     * @param string   $strId
     * @param string[] $params
     *
     * @return string
     */
    public static function translate(string $strId, string ...$params) : string{
        if (isset(self::$lang[$strId])) {
            $value = self::$lang[$strId];
        } elseif (isset(self::$default[$strId])) {
            Plugin::getInstance()->getLogger()->debug("get $strId from default");
            $value = self::$default[$strId];
        } else {
            Plugin::getInstance()->getLogger()->critical("get $strId failed");
            return "Undefined strId : $strId";
        }

        if (is_array($value)) {
            $value = $value[array_rand($value)];
        }
        if (is_string($value)) {
            return empty($params) ? $value : strtr($value, Utils::listToPairs($params));
        } else {
            return "$strId is not string";
        }
    }

    /**
     * @param string $strId
     *
     * @return string[] | null
     */
    public static function getArray(string $strId) : ?array{
        if (isset(self::$lang[$strId])) {
            $value = self::$lang[$strId];
        } elseif (isset(self::$default[$strId])) {
            Plugin::getInstance()->getLogger()->debug("get $strId from default");
            $value = self::$default[$strId];
        } else {
            Plugin::getInstance()->getLogger()->critical("get $strId failed");
            return null;
        }
        return is_array($value) ? $value : null;
    }
}