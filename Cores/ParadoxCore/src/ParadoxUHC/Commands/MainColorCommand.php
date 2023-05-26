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

class MainColorCommand extends BaseCommand {
    private $plugin;
    public $config;
    public $tag;

    /**
     * MainUHCCommand constructor.
     * @param UHC $plugin
     */
    public function __construct(UHC $plugin) {
        $this->plugin = $plugin;
        parent::__construct($plugin, "color", "Set your name's color.", "/color [color]", ["colour", "colorr", "collor"]);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, $commandLabel, array $args)
    {
        if($sender->hasPermission("uhc.perms.color")) {
            if (isset($args[0])) {
                if ($sender instanceof Player) {
                    switch (strtolower($args[0])) {
                        case "red":
                            $sender->setDisplayName(TF::RED . $this->getTag($sender));
                            $sender->sendMessage(TF::GRAY . "You have successfully set the color of your name to" . TF::RED . " RED" . TF::GRAY . ".");
                            break;
                        case "blue":
                            $sender->setDisplayName(TF::BLUE . $this->getTag($sender));
                            $sender->sendMessage(TF::GRAY . "You have successfully set the color of your name to" . TF::BLUE . " BLUE" . TF::GRAY . ".");
                            break;
                        case "green":
                            $sender->setDisplayName(TF::GREEN . $this->getTag($sender));
                            $sender->sendMessage(TF::GRAY . "You have successfully set the color of your name to" . TF::GREEN . " GREEN" . TF::GRAY . ".");
                            break;
                        case "darkblue":
                            $sender->setDisplayName(TF::DARK_BLUE . $this->getTag($sender));
                            $sender->sendMessage(TF::GRAY . "You have successfully set the color of your name to" . TF::DARK_BLUE . " DARK BLUE" . TF::GRAY . ".");
                            break;
                        case "darkgreen":
                            $sender->setDisplayName(TF::DARK_GREEN . $this->getTag($sender));
                            $sender->sendMessage(TF::GRAY . "You have successfully set the color of your name to" . TF::DARK_GREEN . "DARK GREEN" . TF::GRAY . ".");
                            break;
                        case "darkaqua":
                            $sender->setDisplayName(TF::DARK_AQUA . $this->getTag($sender));
                            $sender->sendMessage(TF::GRAY . "You have successfully set the color of your name to" . TF::DARK_AQUA . " DARK AQUA" . TF::GRAY . ".");
                            break;
                        case "darkred":
                            $sender->setDisplayName(TF::DARK_RED . $this->getTag($sender));
                            $sender->sendMessage(TF::GRAY . "You have successfully set the color of your name to" . TF::DARK_RED . " DARK RED" . TF::GRAY . ".");
                            break;
                        case "darkgray":
                            $sender->setDisplayName(TF::DARK_GRAY . $this->getTag($sender));
                            $sender->sendMessage(TF::GRAY . "You have successfully set the color of your name to" . TF::DARK_GRAY . " DARK GRAY" . TF::GRAY . ".");
                            break;
                        case "gold":
                            $sender->setDisplayName(TF::GOLD . $this->getTag($sender));
                            $sender->sendMessage(TF::GRAY . "You have successfully set the color of your name to" . TF::GOLD . " GOLD" . TF::GRAY . ".");
                            break;
                        case "aqua":
                            $sender->setDisplayName(TF::AQUA . $this->getTag($sender));
                            $sender->sendMessage(TF::GRAY . "You have successfully set the color of your name to" . TF::AQUA . " AQUA" . TF::GRAY . ".");
                            break;
                        case "yellow":
                            $sender->setDisplayName(TF::YELLOW . $this->getTag($sender));
                            $sender->sendMessage(TF::GRAY . "You have successfully set the color of your name to" . TF::YELLOW . " YELLOW" . TF::GRAY . ".");
                            break;
                        case "lightpurple":
                            $sender->setDisplayName(TF::LIGHT_PURPLE . $this->getTag($sender));
                            $sender->sendMessage(TF::GRAY . "You have successfully set the color of your name to" . TF::LIGHT_PURPLE . " LIGHT PURPLE" . TF::GRAY . ".");
                            break;
                        case "gray":
                            $sender->setDisplayName(TF::GRAY . $this->getTag($sender));
                            $sender->sendMessage(TF::GRAY . "You have successfully set the color of your name to GRAY.");
                            break;
                        default:
                            $sender->sendMessage(TF::GOLD . "--Available Colors/Colours");
                            $sender->sendMessage(TF::GOLD . "| You can use /color or /colour |");
                            $sender->sendMessage(TF::GOLD . "--------------------------");
                            $sender->sendMessage(TF::GOLD . "/color red");
                            $sender->sendMessage(TF::GOLD . "/color blue");
                            $sender->sendMessage(TF::GOLD . "/color green");
                            $sender->sendMessage(TF::GOLD . "/color gold");
                            $sender->sendMessage(TF::GOLD . "/color gray");
                            $sender->sendMessage(TF::GOLD . "/color aqua");
                            $sender->sendMessage(TF::GOLD . "/color yellow");
                            $sender->sendMessage(TF::GOLD . "/color darkred");
                            $sender->sendMessage(TF::GOLD . "/color darkblue");
                            $sender->sendMessage(TF::GOLD . "/color darkgreen");
                            $sender->sendMessage(TF::GOLD . "/color darkgray");
                            $sender->sendMessage(TF::GOLD . "/color darkaqua");
                            $sender->sendMessage(TF::GOLD . "/color lightpurple");
                            $sender->sendMessage(TF::GOLD . "--------------------------");
                            break;
                    }
                } else {
                    $sender->sendMessage(TF::GOLD . "--Available Colors/Colours");
                    $sender->sendMessage(TF::GOLD . "| You can use /color or /colour |");
                    $sender->sendMessage(TF::GOLD . "--------------------------");
                    $sender->sendMessage(TF::GOLD . "/color red");
                    $sender->sendMessage(TF::GOLD . "/color blue");
                    $sender->sendMessage(TF::GOLD . "/color green");
                    $sender->sendMessage(TF::GOLD . "/color gold");
                    $sender->sendMessage(TF::GOLD . "/color gray");
                    $sender->sendMessage(TF::GOLD . "/color aqua");
                    $sender->sendMessage(TF::GOLD . "/color yellow");
                    $sender->sendMessage(TF::GOLD . "/color darkred");
                    $sender->sendMessage(TF::GOLD . "/color darkblue");
                    $sender->sendMessage(TF::GOLD . "/color darkgreen");
                    $sender->sendMessage(TF::GOLD . "/color darkgray");
                    $sender->sendMessage(TF::GOLD . "/color darkaqua");
                    $sender->sendMessage(TF::GOLD . "/color lightpurple");
                    $sender->sendMessage(TF::GOLD . "--------------------------");
                }
            } else {
                $sender->sendMessage(TF::RED . "You do not have permission to use this command!");
            }
        }
        else {
            $sender->sendMessage(TF::RED." The console can't use this command!");
        }
    }
    
    public function getTag(Player $player){
        $name = $player->getDisplayName();
        return TF::clean($name);

    }

    /**
     * @return mixed
     */
    public function getPlugin()
    {
        return $this->plugin;
    }
}