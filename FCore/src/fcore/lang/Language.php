<?php

declare(strict_types=1);

namespace fcore\lang;

class Language {

    const CZE_MESSAGES = [
        "broadcaster" => [
            "Navštiv naší webovou stránku! §9(http://factionpe.tk)",
            "Nakup si gadgety, particly nebo kity v obchodě na spawnu!",
            "Připoj se na náš discord server! §9(mcpe#7450)",
            "Pokud by jsi měl zájem o zakoupení vip, napiš /vip"
        ],

        "join" => "se připojil do hry.",
        "quit" => "se odpojil ze hry.",

        "join-msg" => "§7------ === [ §6§lFactionPE §r§7] === ------\n".
        "§9Vítej zpět, %player, na §6FactionPE [BETA].\n".
        "§9Od posledního updatu tu máme spoustu novinek:\n".
        "     §8- §7Nové TnTRun mapy\n".
        "     §8- §7Nová minihra MurderMystery\n".
        "     §8- §7New gadgety a particl\n".
        "     §8- §7Lepší PvP systém\n".
        "     §8- §7Nové MiniGame lobby\n".
        "§6 Přejeme ti příjemnou hru!"
    ];

    const ENG_MESSAGES = [
        "broadcaster" => [
            "Visit our new website! §9(http://factionpe.tk)",
            "You can buy new gadgets, particles and kits in shop at the spawn!",
            "Join our discord! §9(mcpe#7450)",
            "If you want to buy VIP, type /vip"
        ],

        "join" => "joined the game.",
        "quit" => "left the game.",

        "join-msg" => "§7------ === [ §6§lFactionPE §r§7] === ------\n".
        "§9Welcome back, %player, on §6FactionPE [BETA] Network.\n".
        "§9There are lots of news:\n".
        "     §8- §7New authentication system (Command: /auth)\n".
        "     §8- §7New MiniGame MurderMystery\n".
        "     §8- §7New gadgets and particles menu\n".
        "     §8- §7Better PvP system\n".
        "     §8- §7New statistic system (Command: /profile)\n".
        "§6 We wish you nice game!"
    ];

    public static function _(string $lang, string $message) {
        switch ($lang) {
            case "ces":
                return self::CZE_MESSAGES[$message];
                break;
            case "eng":
                return self::ENG_MESSAGES[$message];
                break;
        }
    }
}
