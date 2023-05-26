<?php

declare(strict_types=1);

namespace fcore;

/**
 * Interface Settings
 * @package fcore
 */
interface Settings {

    const SERVER_NAME = "§6§lFACTION PE§r§3 >§9>§b> §eTNT GAMES UPDATE ";

    const IP = "ilovemcpe.ddns.net";
    const PORT = 19132;

    const SERVER = "Lobby";

    const PREFIX = "§6> §7";

    const SOFTWARE = "PocketCore codename [Google] version 1.0.0 for MCPE 1.2";

    const PROTECTED_LEVELS = ["Lobby", "Hub", "Spawn"];
    const MAX_PROTECTED_LEVELS = ["arena", "Parkour"];

    const DEFAULT_LEVEL_NAME = "Hub";
    const PVP_LEVEL_NAME = "arena";

    const EW_LOBBY_POS = [260, 4, 521];
    const TR_LOBBY_POS = [132, 4, 373];

    const PARKOUR_POS = [
        1 => [255, 70, 255],
        2 => [220, 69, 255]
    ];

    const PARKOUR_LEVEL = "Parkour";

    const ENDER_CHEST = [250, 4, 245];

    const FLOATING_TEXTS = [
        [
            "§2---------- ==== [ §5ILOVEMCPE §2] ==== ----------",
            "      §aGet gadgets, particles and coins from new\n".
            "  §bMYSTERY CHEST§a. Create party using /p create\n".
            "  §aClick the NPC to choose the game! We wish you\n".
            "                    §anice playing.",
            [252, 9, 260],
        ],
        [
            "§eMYSTERY CHEST",
            "§a§oclick to open!",
            [250.5, 6, 245]
        ],
        [
            "§2------ §6§lNEWS: §r§2------",
            "  §b- MYSTERY CHEST\n".
            "  §b- Factions server\n".
            "  §b- Prison beta testing",
            [244, 7, 250]
        ],
        /*[
            "      §e§lMiniGames",
            "§r§7§oEggWars, TNTRun, ...",
            [265.5, 8, 247],
            "   "
        ],
        [
            "      §e§lFactions",
            "§r§7§o Factions server",
            [270, 8, 257.8]
        ],
        [
            "  §e§lSkyBlock",
            "§r§c§ocoming soon...",
            [268.5, 8.5, 263.5]
        ],
        [
            "    §e§lPrison",
            "§r§c§ocomming soon...",
            [268.5, 8, 251.5]
        ]*/
    ];
}























