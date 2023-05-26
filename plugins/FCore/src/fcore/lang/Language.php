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

        "join-msg" => "§7------ === [ §6§5ILOVEMCPE §r§7] === ------\n".
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
            "§6Donatiile se fac pe §9Discord §ela §3LightEnergy#0871",
            "§9Grupul nostru de Facebook §eeste §6www§7.§9facebook§7.§bcom§7/§9groups§7/§5ilovemcpe", 
            "§9Grupul de §1Discord §cbit.do/ilmcpediscord", 
            "§4§oSunteti §ein §6sectiunea §4Sky§fPvP§7.", 
            "§4§aDaca iti place acest server te rog intra pe §5bit.do/ilVOTE §asi voteaza serverul daca chiar iti place§7, §efiecare vot iti va aduce cate un §bdiamant §estrange-i si fa-te cel mai tare din parcare§4!!", "§9Vrei §5rank§7? §ascrie §e/donez §asi iti va scrie §5Rankurile §7si §ePreturile§7.",
            "§aVrei rank §4You§fTuber§7? §aTrebuie sa ai peste 250 abonati.", 
            "§eVezi un hacker? Scrie §e/report si un Admin online, sau ulterior o sa verifice un Admin§7."
        ],

        "join" => "a intrat pe server §9:3",
        "quit" => "a parasit jocul §9:(",

        "join-msg" => "§7------ === [ §6§5ILOVEMCPE §r§7] === ------\n".
        "§aBine ai revenit pe server§7,§e %player§7, §cpe §5ILOVEMCPE §7[[§4BETA§7]] §cServers §6Network §bCommunity.\n".
        "§5Noutati din ultimul UPDATE:\n".
        "     §8- §7Nimic nou. \n".
        "     §8- §7Nimic nou. \n".
        "     §8- §7Nimic nou. \n".
        "     §8- §7Nimic nou. \n".
        "     §8- §7Nimic nou. \n".
        "§6Iti uram distractie placuta!"
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
