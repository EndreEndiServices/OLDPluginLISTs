<?php

namespace athletic\commands;

use pocketmine\Player;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use athletic\Main;

use athletic\game\AthleticManager;

class CreateCommand extends Command{

    private $plugin;
    private $isSetting = [];

    public function __construct(Main $plugin){
        $command = "dev";
        $description = "create athletic";
        $usage = "/dev <start/finish>";
        parent::__construct($command, $description, $usage);

        $this->plugin = $plugin;
        $this->isSetting = [
            "Start" => false,
            "Finish" => false
        ];
    }

    private $start;

    public function execute(CommandSender $sender, string $label, array $args): bool{
        if (!($sender instanceof Player)){
            return false;
        }

        if (!($sender->isOp())){
            return false;
        }

        if (!($sender->getLevel() === "world")){
            return false;
        }

        if (!(isset($args[0])) || !(isset($args[1]))){
            return false;
        }

        switch ($args[0]){
            case "start":
                $level = $this->plugin->getServer()->getLevelByName("world");
                $block = $level->getBlock($sender->subtract(0, 1));
                if (!($block->getId() === 1)){
                    return false;
                }
                if (!($this->isSetting["Start"])){
                    $this->start = [
                        "Start" => [
                            "X" => $block->getX(),
                            "Y" => $block->getY(),
                            "Z" => $block->getZ()
                        ]
                    ];
                }
                return true;
            case "finish":
                if (!$this->isSetting["Start"]){
                    return false;
                }
                $finish = [
                    "Finish" => [
                        "X" => $block->getX(),
                        "Y" => $block->getY(),
                        "Z" => $block->getZ()
                    ]
                ];
    
                AthleticManager::setData($args[1], [
                    $this->start,
                    $finish,
                    "Ranking" => [
                        "1" => "unknown" => [
                            0
                        ],
                        "2" => "unknown" => [
                            0
                        ],
                        "3" => "unknown" => [
                            0
                        ]
                    ]
                ]);
                unset($this->isSetting);
                unset($this->start);
                return true;
            case "cancel":
                unset($this->isSetting);
                unset($this->start);
                return true;
        }
    }
}
