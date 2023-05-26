<?php

declare(strict_types=1);

namespace fcore\lobbyutils;

use fcore\FCore;
use fcore\form\Button;
use fcore\lobbyutils\gadgets\FlyGadget;
use fcore\lobbyutils\gadgets\Gadget;
use fcore\lobbyutils\gadgets\GiantGadget;
use fcore\lobbyutils\gadgets\RunningGadget;
use fcore\lobbyutils\gadgets\TNTGadget;
use fcore\lobbyutils\particles\HeadcircleParticle;
use fcore\lobbyutils\particles\HelixParticle;
use fcore\lobbyutils\particles\Particle;
use fcore\lobbyutils\particles\RunningParticle;
use fcore\lobbyutils\particles\UnicornParticle;
use fcore\lobbyutils\particles\WingParticle;
use fcore\profile\ProfileManager;
use pocketmine\level\Level;
use pocketmine\Player;

/**
 * Class LobbyUtilsManager
 * @package fcore\lobbyutils
 */
class LobbyUtilsManager {

    /** @var FCore $plugin */
    public $plugin;

    /** @var Level $lobbyLevel */
    private $lobbyLevel;

    /** @var Gadget[] $gadgets */
    public $gadgets = [];

    /** @var Particle[] $particles */
    public $particles = [];

    /**
     * LobbyUtilsManager constructor.
     * @param FCore $plugin
     */
    public function __construct(FCore $plugin) {
        $this->plugin = $plugin;
        $this->lobbyLevel = $this->plugin->getServer()->getDefaultLevel();
        $this->registerGadgets();
        $this->registerParticles();
    }

    public function registerParticles() {
        $this->particles["unicorn"] = new UnicornParticle($this);
        $this->particles["helix"] = new HelixParticle($this);
        $this->particles["wing"] = new WingParticle($this);
        $this->particles["headcircle"] = new HeadcircleParticle($this);
    }


    public function registerGadgets() {
        $this->gadgets["fly"] = new FlyGadget;
        $this->gadgets["tnt"] = new TNTGadget;
        $this->gadgets["giant"] = new GiantGadget;
    }

    /**
     * @api
     *
     * @param Player $player
     *
     * @description: Open form particles shop
     */
    public function openParticleShop(Player $player) {
        $formApi = $this->plugin->formApi;

        $buttons = [];
        foreach ($this->particles as $gadget) {
            array_push($buttons, $gadget->constructButton($player, "shop"));
        }

        $func = function (Player $player, array $data) {
            $result = $data[0];

            if($result === null) return;

            $particles = [];
            foreach (\fcore\FCore::$instance->lobbyUtilsMgr->particles as $particle) {
                array_push($particles, $particle);
            }

            if(empty($particles[$result])) {
                return;
            }

            $particle = $particles[$result];

            if(!$particle instanceof \fcore\lobbyutils\particles\Particle) {
                return;
            }

            $particle->buy($player);
        };

        $form = $formApi->createSimpleForm("§6§lSHOP §r§9>>> §bParticles", "§aSelect particle to buy!", $buttons, $func);
        $form->send($player);
    }

    /**
     * @api
     *
     * @param Player $player
     *
     * @description: Open form gadgets shop
     */
    public function openGadgetShop(Player $player) {
        $formApi = $this->plugin->formApi;

        $buttons = [];
        foreach ($this->gadgets as $gadget) {
            array_push($buttons, $gadget->constructButton($player, "shop"));
        }

        $func = function (Player $player, array $data) {
            $result = $data[0];

            if($result === null) return;

            $gadgets = [];
            foreach (\fcore\FCore::$instance->lobbyUtilsMgr->gadgets as $gadget) {
                array_push($gadgets, $gadget);
            }

            if(empty($gadgets[$result])) {
                return;
            }

            $gadget = $gadgets[$result];

            if(!$gadget instanceof \fcore\lobbyutils\gadgets\Gadget) {
                return;
            }

            $gadget->buy($player);
        };

        $form = $formApi->createSimpleForm("§6§lSHOP §r§9>>> §bGadgets", "§aSelect gadget to buy!", $buttons, $func);
        $form->send($player);
    }

    /**
     * @api
     *
     * @param Player $player
     *
     * @description: Open form with gadgets
     */
    public function openGadgets(Player $player) {
        $buttons = [];

        foreach ($this->gadgets as $gadget) {
            array_push($buttons, $gadget->constructButton($player, "use", ProfileManager::isVip($player)));
        }

        $func = function (Player $player, array $data) {
            $result = $data[0];

            if($result === null) return;

            $gadgets = [];

            foreach (\fcore\FCore::$instance->lobbyUtilsMgr->gadgets as $gadget) {
                array_push($gadgets, $gadget);
            }

            if(empty($gadgets[$result])) {
                \fcore\FCore::dbg("#3");
                return;
            }

            $gadget = $gadgets[$result];

            if(!$gadget instanceof \fcore\lobbyutils\gadgets\Gadget) {
                \fcore\FCore::dbg("#4");
                return;
            }

            $gadget->equip($player);

        };

        $func = $this->plugin->formApi->createSimpleForm("§9GADGETS", "§aSelect gadget.", $buttons, $func);

        $func->send($player);
    }

    /**
     * @api
     *
     * @param Player $player
     *
     * @description: Open form with particles
     */
    public function openParticles(Player $player) {
        $buttons = [];

        foreach ($this->particles as $particle) {
            array_push($buttons, $particle->constructButton($player, "use", ProfileManager::isVip($player)));
        }

        $func = function (Player $player, array $data) {
            $result = $data[0];

            if($result === null) return;

            if($result === null) return;

            $particles = [];

            foreach (\fcore\FCore::$instance->lobbyUtilsMgr->particles as $particle) {
                array_push($particles, $particle);
            }

            if(empty($particles[$result])) {
                \fcore\FCore::dbg("#1");
                return;
            }

            $particle = $particles[$result];

            if(!$particle instanceof \fcore\lobbyutils\particles\Particle) {
                \fcore\FCore::dbg("#2");
                return;
            }

            $particle->equip($player);

        };

        $func = $this->plugin->formApi->createSimpleForm("§9PARTICLES", "§aSelect particle.", $buttons, $func);

        $func->send($player);
    }



    /**
     * @cosmeticMenu
     *
     * @param Player $player
     */
    public function openCosmeticMenu(Player $player) {
        $buttons = [
            new Button("§e§lGadgets", "http://icons.iconarchive.com/icons/chrisl21/minecraft/256/Folder-Tnt-icon.png", "url"),
            new Button("§e§lParticles", "http://icons.iconarchive.com/icons/chrisl21/minecraft/256/Folder-Grass-icon.png", "url")
        ];
        $func = function (Player $player, array $data){
            $result = $data[0];
            if($result === null) return;

            if($result === 0) {
                \fcore\FCore::$instance->lobbyUtilsMgr->openGadgets($player);
            }
            if($result === 1) {
                \fcore\FCore::$instance->lobbyUtilsMgr->openParticles($player);
            }
        };
        $form = $this->plugin->formApi->createSimpleForm("§eCosmeticMenu", "§aSelect section.", $buttons, $func);
        $form->send($player);
    }

    /**
     * @param Player $player
     */
    public function removeUtils(Player $player) {
        foreach ($this->particles as $particle) {
            if(isset($particle->players)) {
                if(isset($particle->players[$player->getName()])) {
                    unset($particle->players[$player->getName()]);
                }
            }
        }
        if(in_array($player->getGamemode(), [$player::SURVIVAL, $player::ADVENTURE])) {
            $player->setAllowFlight(false);
        }
        $player->removeAllEffects();
        $player->setScale(1.0);
    }

    /**
     * @task
     *
     *  > Run particles
     */
    public function runParticles() {
        foreach ($this->particles as $particle) {
            if($particle instanceof RunningParticle) {
                $particle->run();
            }
        }
    }

    /**
     * @task
     *
     *  > Run gadgets
     */
    public function runGadgets() {
        foreach ($this->gadgets as $gadget) {
            if($gadget instanceof RunningGadget) {
                $gadget->run();
            }
        }
    }
}