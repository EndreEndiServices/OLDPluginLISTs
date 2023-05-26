<?php

declare(strict_types=1);

namespace fcore\profile;

use pocketmine\Player;

class RankManager {

    public static $ranks = [
        "guest" => "§7%name: %msg",
        "vip" => "§3§l[VIP]§r§7 %name: %msg",
        "sponsor" => "§c§l[Sponsor]§r§7 %name: %msg",
        "youtuber" => "§0[§fYou§4Tube§0]§r§7 %name: %msg",
        "helper" => "§9[Helper] §7%name: %msg",
        "builder" => "§a[Builder] §7%name: %msg",
        "admin" => "§b[Admin] §7%name: %msg",
        "hladmin" => "§b[Hd.Admin] §7%name: %msg",
        "coowner" => "§6[CoOwner] §7%name: %msg",
        "owner" => "§2[Owner] §7%name: %msg"
    ];

    public static $displayRanks = [
        "guest" => "§e[Guest]§r",
        "vip" => "§3§l[VIP]§r",
        "sponsor" => "§c§l[Sponsor]§r§7",
        "youtuber" => "§0[§fYou§4Tube§0]§r§7",
        "helper" => "§9[Helper]§r",
        "builder" => "§a[Builder]§r",
        "admin" => "§b[Admin]§r",
        "hladmin" => "§b[Hd.Admin]§r",
        "coowner" => "§6[CoOwner]§r",
        "owner" => "§2[Owner]§r"
    ];

    /**
     * @return string
     */
    public static function getRanksList() {
        $array = [];
        foreach (self::$ranks as $rank => $chat) {
            array_push($array, $rank);
        }
        return "§7Ranks: ".implode(", ", $array);
    }

    /**
     * @param Player $player
     * @return mixed
     */
    public static function getChatFormat(Player $player) {
        return self::$ranks[ProfileManager::$players[$player->getName()]["rank"]];
    }

    /**
     * @param Player $player
     * @param string $rank
     */
    public static function setRank(Player $player, string $rank) {
        if(isset(self::$ranks[$rank])) {
            ProfileManager::$players[$player->getName()]["rank"] = $rank;
        }
    }
}