<?php

/*
 *               _                      _ _
 *      /\      | |                    | (_)
 *     /  \   __| |_ __ ___ _ __   __ _| |_ _ __   ___
 *    / /\ \ / _` | '__/ _ \ '_ \ / _` | | | '_ \ / _ \
 *   / ____ \ (_| | | |  __/ | | | (_| | | | | | |  __/
 *  /_/    \_\__,_|_|  \___|_| |_|\__,_|_|_|_| |_|\___|
 *
 * This plugin cannot be shared, or used by anyone else.
 * The only people allowed to use this, must have permission by AppleDevelops.
 * If you don't have permission, and use this plugin, I will not be afraid to take action.
 *
 * @author AppleDevelops
 *
 */

namespace Adrenaline\Tasks;

use Adrenaline\CoreLoader;
use pocketmine\level\sound\AnvilUseSound;
use pocketmine\level\sound\PopSound;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;

class TitleTask extends PluginTask{

    public $player;

    public function __construct(CoreLoader $owner, Player $player){
        parent::__construct($owner, $player);
        $this->player = $player;
    }

    public  function onRun($currentTick){
        $this->player->removeTitles();
        $this->player->addTitle(TextFormat::RED . "Adrenaline" . TextFormat::GOLD . "UHC", TextFormat::AQUA . "Beta v2.0");
        $this->player->getLevel()->addSound(new AnvilUseSound(new Vector3($this->player->x, $this->player->y, $this->player->z)));
    }
}