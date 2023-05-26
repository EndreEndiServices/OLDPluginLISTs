<?php

namespace athletic\game;

use pocketmine\utils\Config;
use pocketmine\math\Vector3;

class AthleticManager{

    private static $config;

    public static function init(Config $config){
        self::$config = $config;
    }

    private static $athletics = [];

    public static function register(){
        $list = self::$config->getAll(true);
        foreach ($list as $name => $data){
            self::$athletics[$name] = $data;
        }
    }

    public static function unregister(){
        unset(self::$athletics);
        unset(self::$config);
    }

    public static function setData(string $name, array $data): void{
        self::$config->set($name, $data);
        self::$config->save();
    }

    public static function getData(string $name): ?array{
        $athletic = self::$athletics[$name] ?? null;
        if ($athletic === null){
            return null;
        }
        return self::$athletics[$name];
    }

    public static function existsAthletic(Vecotr3 $vector): ?string{
        $athletics = self::$config->getAll(true);
        foreach ($athletics as $name => $data){
            if ($data["Start"]["X"] === $vector->getX()
            && $data["Start"]["Y"] === $vector->getY()
            && $data["Start"]["Z"] === $vector->getZ()){
                return $name;
            }
        }
    }
}
