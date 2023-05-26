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

class MainScenariosCommand extends BaseCommand {
    private $plugin;
    public $config;

    /**
     * MainUHCCommand constructor.
     * @param UHC $plugin
     */
    public function __construct(UHC $plugin) {
        $this->plugin = $plugin;
        parent::__construct($plugin, "scenarios", "See all of the scenarios in a UHC.", "/scenarios", ["s", "sc", "scen"]);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, $commandLabel, array $args)
    {
            $this->config = new Config($this->plugin->getDataFolder()."config.yml");
            $sender->sendMessage(TF::GRAY."The scenarios for this UHC are:");
            if($this->config->get("split") === "true"){
                $sender->sendMessage(TF::GOLD."[Split]");
            }
            if($this->config->get("split") === "false"){
                $sender->sendMessage(TF::GOLD."[Non-Split]");
            }
            if($this->config->get("split") === "both"){
                $sender->sendMessage(TF::GOLD."[Both]");
            }
            if($this->config->get("cutclean") == true){
                $sender->sendMessage(TF::GOLD."[Cutclean]");
            }
            if($this->config->get("barebones") == true){
                $sender->sendMessage(TF::GOLD."[Barebones]");
            }
            if($this->config->get("goldless") == true){
                $sender->sendMessage(TF::GOLD."[Goldless]");
            }
            if($this->config->get("diamondless") == true){
                $sender->sendMessage(TF::GOLD."[Diamondless]");
            }
            if($this->config->get("fireless") == true){
                $sender->sendMessage(TF::GOLD."[Fireless]");
            }
            if($this->config->get("blood-diamonds") == true){
                $sender->sendMessage(TF::GOLD."[Blood Diamonds]");
            }
            if($this->config->get("timebomb") == true){
            $sender->sendMessage(TF::GOLD."[Timebomb]");
            }
            if($this->config->get("vampire") == true){
                $sender->sendMessage(TF::GOLD."[Vampire]");
            }
            if($this->config->get("nofall") == true){
                $sender->sendMessage(TF::GOLD."[No Fall]");
            }
            if($this->config->get("amphibian") == true){
                $sender->sendMessage(TF::GOLD."[Amphibian]");
            }
            if($this->config->get("cripple") == true){
                $sender->sendMessage(TF::GOLD."[Cripple]");
            }
            if($this->config->get("chicken") == true){
                $sender->sendMessage(TF::GOLD."[Chicken]");
            }
            if($this->config->get("lights-out") == true){
                $sender->sendMessage(TF::GOLD."[LightsOut]");
            }            
            if($this->config->get("cat-eyes") == true){
                $sender->sendMessage(TF::GOLD."[CatEyes]");
            }
            if($this->config->get("multi-ore") == 2){
                $sender->sendMessage(TF::GOLD."[Double Ore]");
            }
            if($this->config->get("multi-ore") == 3){
                $sender->sendMessage(TF::GOLD."[Triple Ore]");
            }
            if($this->config->get("multi-ore") == 4){
                $sender->sendMessage(TF::GOLD."[Quad Ore]");
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