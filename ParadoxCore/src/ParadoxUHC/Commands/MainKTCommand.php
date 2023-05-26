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

class MainKTCommand extends BaseCommand {
    private $plugin;
    public $config;

    /**
     * MainUHCCommand constructor.
     * @param UHC $plugin
     */
    public function __construct(UHC $plugin) {
        $this->plugin = $plugin;
        parent::__construct($plugin, "kt", "See the top kills in a UHC", "/kt", ["killtop"]);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, $commandLabel, array $args)
    {
            array_multisort($this->plugin->kills);
            foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
                if(isset($this->plugin->kills[$player->getName()])){
                    $player->sendMessage($this->plugin->kills[0]);
                    $player->sendMessage($this->plugin->kills[1]);
                    $player->sendMessage($this->plugin->kills[2]);
                    $player->sendMessage($this->plugin->kills[3]);
                    $player->sendMessage($this->plugin->kills[4]);

                }
            }

    }

    /**
     * @return mixed
     */
    public function getPlugin()
    {
        return $this->plugin;
    }
}