<?php

declare(strict_types=1);

namespace fcore\shop;

use fcore\FCore;
use fcore\form\Button;
use fcore\form\FormAPI;
use pocketmine\Player;

class ShopManager {

    public $plugin;

    public function __construct(FCore $plugin) {
        $this->plugin = $plugin;
    }

    public function open(Player $player) {
        /** @var FormAPI $formApi */
        $formApi = $this->plugin->formApi;

        $buttons = [];

        array_push($buttons, new Button("§6PvP Kits §7(Classic, Warrior, Archer, ...)"));
        array_push($buttons, new Button("§6Gadgets §7(Fly)"));
        array_push($buttons, new Button("§6Particles §7(Unicorn)"));
        array_push($buttons, new Button("§6Survival Items §7 (Grass, Diamond)"));

        $func = function (Player $player, array $data) {
            $result = $data[0];

            if($result === null) {
                return;
            }

            switch ($result) {
                case 0:
                    $this->plugin->kitMgr->openKitShop($player);
                    break;
                case 1:
                    $this->plugin->lobbyUtilsMgr->openGadgetShop($player);
                    break;
                case 2:
                    $this->plugin->lobbyUtilsMgr->openParticleShop($player);
                    break;
                case 3:
                    $player->sendMessage(FCore::getPrefix()."§cThis part is under developement!");
                    break;
            }
        };

        $form = $formApi->createSimpleForm("§6---==[ §1§lSHOP §r§6]==---", "Choose category", $buttons, $func);
        $form->send($player);
    }
}