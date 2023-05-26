<?php

namespace ParadoxUHC\Commands;

use ParadoxUHC\UHC;
use ParadoxUHC\Commands\BaseCommand;
use pocketmine\block\Block;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\tile\Tile;
use pocketmine\utils\TextFormat as TF;
use pocketmine\tile\Chest;
use pocketmine\utils\Config;
use pocketmine\item\Item;

class MainInfoCommand extends BaseCommand {
    private $plugin;
    public $config;

    /**
     * MainUHCCommand constructor.
     * @param UHC $plugin
     */
    public function __construct(UHC $plugin) {
        $this->plugin = $plugin;
        parent::__construct($plugin, "info", "See extended info about the UHC", "/info", ["inf", "infoo", "inffo"]);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, $commandLabel, array $args)
    {
            $this->config = new Config($this->plugin->getDataFolder()."config.yml");
            $sender->sendMessage(TF::GOLD."-----Info-----");
            $rate = $this->config->get("apple");
            $sender->sendMessage(TF::GOLD."Apple Rate: ".round(1/$rate)."%");
            $sender->sendMessage(TF::GOLD."--------------");
            if($this->config->get("nether") == true){
                $sender->sendMessage(TF::GOLD."Nether Enabled: true");
            }
            if($this->config->get("nether") == false){
                $sender->sendMessage(TF::GOLD."Nether Enabled: true");
            }
            $sender->sendMessage(TF::GOLD."----------------");

    }

    /**
     * @return mixed
     */
    public function getPlugin()
    {
        return $this->plugin;
    }
}