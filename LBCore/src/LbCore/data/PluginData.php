<?php

namespace LbCore\data;

use pocketmine\Server;

class PluginData {
    // Gametype => [Vip id, Vip+ id]
    private static $gamesVipId = [
        'SurvivalGames'  => [2,3],		// SurvivalGames
        'CaptureTheFlag' => [4,5],		// CaptureTheFlag
        'Skywars'        => [6,7],		// Skywars
        'walls'          => [20,21],	// Walls
        'battles'        => [30,31],	// battles
        'Spleef'         => [40,41],	// Spleef
        'BountyHunter'	 => [50,51],	// BountyHunter
		'Fleet'			 => [60,61],	// Fleet
    ];

    /**
     *  Returns the name of the gametype that is running beside LbCore. e.g. would return "CaptureTheFlag".
     *
     * @return bool|int
     */
    public static function getGameType() {
        $server = Server::getInstance();
        foreach (self::$gamesVipId as $pluginName => $vipIds) {
            if (!is_null($server->getPluginManager()->getPlugin($pluginName))) {
                return $pluginName;
            }
        }
        return false;

    }

    /**
     * Loops through the $game_vips and sees if that plugin is enabled or not. If the plugin is enabled it returns an
     *  array of what the Vip and Vip+ products are for that gametype.
     *
     * @return array|bool
     */
    public static function getVipIds() {
        $gameType = self::getGameType();
        return $gameType === false ? false : self::$gamesVipId[$gameType];
    }
}
