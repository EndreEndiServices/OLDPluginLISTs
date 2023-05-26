<?php

declare(strict_types=1);

namespace fcore\slots;

use fcore\FCore;
use pocketmine\utils\Config;

class SlotsUpdate {

    /** @var null|array $servers */
    public $servers = null;

    /** @var array $slots */
    public $slots = [
        "players" => 0,
        "slots" => 0,
        "subservers" => [
            "minigames" => [0, 0],
            "factions" => [0, 0],
            "prison" => [0, 0],
            "skyblock" => [0, 0]
        ]
    ];

    /** @var Config $config */
    public $config;
	/**
	 * @var FCore
	 */
	private $plugin;

	public function __construct(FCore $plugin) {
        $this->plugin = $plugin;
    }

    public function update() {
        #$this->slots["subservers"] = (new Config("/var/www/html/data/slots.json", Config::JSON))->getAll();
    }
}