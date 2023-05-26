<?php

declare(strict_types=1);

namespace fcore\command;

use fcore\FCore;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Human;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;

class NpcCommand extends Command {

    /** @var FCore $plugin */
    public  $plugin;

    /** @var array $rm */
    public $rm = [];

    /**
     * NpcCommand constructor.
     * @param FCore $plugin
     */
    public function __construct(FCore $plugin) {
        $this->plugin = $plugin;
        parent::__construct("npc", "Npc commands", null, []);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed|void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(empty($args[0])) {
            return;
        }
        if(!$sender instanceof Player) {
            return;
        }
        if($sender->getName() != "VixikCZ") {
            return;
        }
        switch ($args[0]) {
            case "spawn":
                $sender->saveNBT();

                $nbt = new CompoundTag;

                $nbt->Pos = new ListTag("Pos", [
                    new DoubleTag("", $sender->getX()),
                    new DoubleTag("", $sender->getY()),
                    new DoubleTag("", $sender->getZ())
                ]);

                $nbt->Motion = new ListTag("Motion", [
                    new DoubleTag("", 0),
                    new DoubleTag("", 0),
                    new DoubleTag("", 0)
                ]);

                $nbt->Rotation = new ListTag("Rotation", [
                    new FloatTag("", $sender->getYaw()),
                    new FloatTag("", $sender->getPitch())
                ]);

                $nbt->Health = new ShortTag("Health", 10);

                $nbt->Skin = clone $sender->namedtag->Skin;

                switch ($args[1]) {
                    case "minigames":
                        $nbt->Minigame = new StringTag("Minigame", "minigames");
                        break;
                    case "factions":
                        $nbt->Minigame = new StringTag("Minigame", "factions");
                        break;
                    case "prison":
                        $nbt->Minigame = new StringTag("Minigame", "prison");
                        break;
                    case "skyblock":
                        $nbt->Minigame = new StringTag("Minigame", "skyblock");
                        break;
                }

                $human = new Human($sender->getLevel(), $nbt);

                if(!in_array($args[1], ["minigames", "factions", "prison", "skyblock"])) {
                    $human->setNameTag($args[1]);
                    $human->setNameTagVisible(true);
                    $human->setNameTagAlwaysVisible(true);
                }

                else {
                    switch ($args[1]) {
                        case "minigames":
                            $human->setNameTag("  §e§lMiniGames§r\n§a§oclick to join!");
                            break;
                        case "factions":
                            $human->setNameTag("  §e§lFactions§r\n§a§oclick to join!");
                            break;
                        case "prison":
                            $human->setNameTag("§e§lPrison§r\n§c§oclosed.");
                            break;
                        case "skyblock":
                            $human->setNameTag("§e§lSkyBlock§r\n§o§c closed.");
                    }
                }

                $human->spawnTo($sender);
                break;
            case "remove":
                $this->rm["VixikCZ"] = "j";
                break;
        }


    }
}