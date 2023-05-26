<?php

declare(strict_types=1);

namespace fcore\task;

use fcore\FCore;
use fcore\profile\ProfileManager;
use pocketmine\level\particle\AngryVillagerParticle;
use pocketmine\level\particle\BubbleParticle;
use pocketmine\level\particle\DustParticle;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\HeartParticle;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\Task;

class MysteryChestTask extends Task {

    /** @var null $player */
    public $player = null;

    public $tick = 0;

    public $plugin;

    public $vectors = [];

    public $price = 0;

    public $wins = [];

    /**
     * MysteryChestTask constructor.
     * @param ScheduleManager $plugin
     */
    public function __construct(ScheduleManager $plugin) {
        $this->plugin = $plugin;
    }

    public function setPlayer(Player $player) {
        if(ProfileManager::$players[$player->getName()]["chest"] == gmdate("m:d", time())) {
            $player->sendMessage("§c> You are already opened the chest today!");
            return;
        }
        if($this->player !== null) {
            $player->sendMessage("§c> There are player opening chest.");
            return;
        }
        $player->sendMessage("§a> Opening chest...");
        $this->player = $player;
        $this->tick = 0;
    }

    #public $color = [rand(1,255), rand(1,255), rand(1,255)];

    public function onRun(int $currentTick) {
        if($this->player === null) {
            $this->tick = 0;
            return;
        }

        $pos = new Position(FCore::ENDER_CHEST[0], FCore::ENDER_CHEST[1], FCore::ENDER_CHEST[2], $this->plugin->plugin->getServer()->getDefaultLevel());
        $pos = Position::fromObject($pos->add(0.5, 1, 0.5), $pos->getLevel());

        $vectors = $this->vectors;


        // PARTICLE TABLE
        switch ($this->tick) {
            case 0:
                $vectors[] = $pos->add(-1, 0.1);
                $vectors[] = $pos->add(-1, 0.2);
                break;
            case 1:
                $vectors[] = $pos->add(-1, 0.3);
                $vectors[] = $pos->add(-1, 0.4);
                break;
            case 2:
                $vectors[] = $pos->add(-1, 0.5);
                $vectors[] = $pos->add(-1, 0.6);
                break;
            case 3:
                $vectors[] = $pos->add(-1, 0.7);
                $vectors[] = $pos->add(-1, 0.8);
                break;
            case 4:
                $vectors[] = $pos->add(-0.9, 0.8);
                $vectors[] = $pos->add(-0.8, 0.8);
                break;
            case 5:
                $vectors[] = $pos->add(-0.7, 0.8);
                $vectors[] = $pos->add(-0.6, 0.8);
                break;
            case 6:
                $vectors[] = $pos->add(-0.5, 0.8);
                $vectors[] = $pos->add(-0.4, 0.8);
                break;
            case 7:
                $vectors[] = $pos->add(-0.3, 0.8);
                $vectors[] = $pos->add(-0.2, 0.8);
                break;
            case 8:
                $vectors[] = $pos->add(-0.1, 0.8);
                $vectors[] = $pos->add(0, 0.8);
                break;
            case 9:
                $vectors[] = $pos->add(0.1, 0.8);
                $vectors[] = $pos->add(0.2, 0.8);
                break;
            case 10:
                $vectors[] = $pos->add(0.3, 0.8);
                $vectors[] = $pos->add(0.4, 0.8);
                break;
            case 11:
                $vectors[] = $pos->add(0.5, 0.8);
                $vectors[] = $pos->add(0.6, 0.8);
                break;
            case 12:
                $vectors[] = $pos->add(0.7, 0.8);
                $vectors[] = $pos->add(0.8, 0.8);
                break;
            case 13:
                $vectors[] = $pos->add(0.9, 0.8);
                $vectors[] = $pos->add(1, 0.8);
                break;
            case 14:
                $vectors[] = $pos->add(1, 0.7);
                $vectors[] = $pos->add(1, 0.6);
                break;
            case 15:
                $vectors[] = $pos->add(1, 0.5);
                $vectors[] = $pos->add(1, 0.4);
                break;
            case 16:
                $vectors[] = $pos->add(1, 0.3);
                $vectors[] = $pos->add(1, 0.2);
                break;
            case 17:
                $vectors[] = $pos->add(1, 0.1);
                $vectors[] = $pos->add(0.9, 0.1);
                break;
            case 18:
                $vectors[] = $pos->add(0.8, 0.1);
                $vectors[] = $pos->add(0.7, 0.1);
                break;
            case 19:
                $vectors[] = $pos->add(0.6, 0.1);
                $vectors[] = $pos->add(0.5, 0.1);
                break;
            case 20:
                $vectors[] = $pos->add(0.4, 0.1);
                $vectors[] = $pos->add(0.3, 0.1);
                break;
            case 21:
                $vectors[] = $pos->add(0.2, 0.1);
                $vectors[] = $pos->add(0.1, 0.1);
                break;
            case 22:
                $vectors[] = $pos->add(0, 0.1);
                $vectors[] = $pos->add(-0.1, 0.1);
                break;
            case 23:
                $vectors[] = $pos->add(-0.2, 0.1);
                $vectors[] = $pos->add(-0.3, 0.1);
                break;
            case 24:
                $vectors[] = $pos->add(-0.4, 0.1);
                $vectors[] = $pos->add(-0.5, 0.1);
                break;
            case 25:
                $vectors[] = $pos->add(-0.6, 0.1);
                $vectors[] = $pos->add(-0.7, 0.1);
                break;
            case 26:
                $vectors[] = $pos->add(-0.8, 0.1);
                $vectors[] = $pos->add(-0.9, 0.1);
                break;
            default:

                // first

                if($this->tick < 50) {
                    $rand = rand(1, 2);
                    switch ($rand) {
                        case 1:
                            $pos->getLevel()->addParticle(new HeartParticle($pos->add(-0.5, 0.2)));
                            break;
                        case 2:
                            $pos->getLevel()->addParticle(new AngryVillagerParticle($pos->add(-0.5, 0.2)));
                            break;
                    }
                    if($this->tick == 49) {
                        if($rand == 1) {
                            $this->price += rand(51, 500);
                            $this->player->sendMessage("§a> First spin -> §2LUCKY§a!");
                            $this->wins[0] = true;
                        }
                        else {
                            $this->price += rand(1, 50);
                            $this->player->sendMessage("§a> First spin -> §cUNLUCKY§a!");
                            $this->wins[0] = false;
                        }
                    }
                }

                // second

                if($this->tick >= 50 && $this->tick < 75) {
                    $rand = rand(1, 2);
                    switch ($rand) {
                        case 1:
                            $pos->getLevel()->addParticle(new HeartParticle($pos->add(0, 0.2)));
                            break;
                        case 2:
                            $pos->getLevel()->addParticle(new AngryVillagerParticle($pos->add(0, 0.2)));
                            break;
                    }
                    if ($this->tick == 74) {
                        if ($rand == 1) {
                            $this->price += rand(51, 500);
                            $this->player->sendMessage("§a> Second spin -> §2LUCKY§a!");
                            $this->wins[1] = true;
                        } else {
                            $this->price += rand(1, 50);
                            $this->player->sendMessage("§a> Second spin -> §cUNLUCKY§a!");
                            $this->wins[1] = false;
                        }
                    }
                }

                // third

                if($this->tick >= 75 && $this->tick < 100) {
                    $rand = rand(1, 2);
                    switch ($rand) {
                        case 1:
                            $pos->getLevel()->addParticle(new HeartParticle($pos->add(-0.5, 0.2)));

                            break;
                        case 2:
                            $pos->getLevel()->addParticle(new AngryVillagerParticle($pos->add(-0.5, 0.2)));

                            break;
                    }
                    if ($this->tick == 99) {
                        if ($rand == 1) {
                            $this->price += rand(51, 500);
                            $this->player->sendMessage("§a> Third spin -> §2LUCKY§a!");
                            $this->wins[2] = true;
                        } else {
                            $this->price += rand(1, 50);
                            $this->player->sendMessage("§a> Third spin -> §cUNLUCKY§a!");
                            $this->wins[2] = false;
                        }
                    }
                }
                break;
        }

        /** @var Vector3 $vector */
        foreach ($vectors as $vector) {
            $pos->getLevel()->addParticle(new FlameParticle($vector));
        }

        if(isset($this->wins[0])) {
            if($this->wins[0]) {
                $pos->getLevel()->addParticle(new HeartParticle($pos->add(-0.5, 0.2)));
            }
            else {
                $pos->getLevel()->addParticle(new AngryVillagerParticle($pos->add(-0.5, 0.2)));
            }
        }

        if(isset($this->wins[1])) {
            if($this->wins[1]) {
                $pos->getLevel()->addParticle(new HeartParticle($pos->add(0, 0.2)));
            }
            else {
                $pos->getLevel()->addParticle(new AngryVillagerParticle($pos->add(0, 0.2)));
            }
        }

        if(isset($this->wins[2])) {
            if($this->wins[2]) {
                $pos->getLevel()->addParticle(new HeartParticle($pos->add(0.5, 0.2)));
            }
            else {
                $pos->getLevel()->addParticle(new AngryVillagerParticle($pos->add(0.5, 0.2)));
            }
        }


        $this->tick++;
        $this->vectors = $vectors;

        if($this->tick == 105) {
            ProfileManager::$players[$this->player->getName()]["chest"] = gmdate("m:d", time());
            $this->player->sendMessage("§a> You are won {$this->price} coins!");
            ProfileManager::addCoins($this->player, floatval($this->price));
            $this->player = null;
            $this->tick = 0;
            $this->vectors = [];
            $this->wins = [];
            $this->price = 0;
        }


    }
}
