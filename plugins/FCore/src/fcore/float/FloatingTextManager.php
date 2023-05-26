<?php

declare(strict_types=1);

namespace fcore\float;

use fcore\FCore;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\level\Position;

/**
 * Class FloatingTextManager
 * @package fcore\float
 */
class FloatingTextManager {

    public $plugin;

    public $particles = [];

    public function __construct(FCore $plugin) {
        $this->plugin = $plugin;
    }

    public function spawnLobbyParticles() {
        $particles = [];
        foreach (FCore::FLOATING_TEXTS as $index => [$head, $text, $pos]) {
            $position = new Position($pos[0], $pos[1], $pos[2], $this->plugin->getServer()->getDefaultLevel()->getSpawnLocation()->getLevel());
            $position->getLevel()->addParticle($particles[] = new FloatingTextParticle($position, $text, $head));
        }
        $this->particles = $particles;
    }

    public function updateSlots() {
        /*$slots = $this->plugin->slotsMgr->slots;

        /** @var FloatingTextParticle $particle * /
        foreach ($this->particles as $index => $particle) {
            switch ($index) {
                case 3:
                    $particle->setText(FCore::FLOATING_TEXTS[3][1].PHP_EOL.FCore::FLOATING_TEXTS[3][3].$slots["subservers"]["minigames"][0]." Online");
                    break;
                case 4:
                    $particle->setText(FCore::FLOATING_TEXTS[3][1].PHP_EOL.FCore::FLOATING_TEXTS[3][3].$slots["subservers"]["factions"][0]." Online");
                    break;
                case 5:
                    $particle->setText(FCore::FLOATING_TEXTS[3][1].PHP_EOL.FCore::FLOATING_TEXTS[3][3].$slots["subservers"]["skyblock"][0]." Online");
                    break;
                case 6:
                    $particle->setText(FCore::FLOATING_TEXTS[3][1].PHP_EOL.FCore::FLOATING_TEXTS[3][3].$slots["subservers"]["prison"][0]." Online");
                    break;
            }
            $newParticle = clone $particle;
            $particle->setInvisible(true);

            $this->plugin->getServer()->getDefaultLevel()->addParticle($newParticle);
        }*/
    }

    /**
     * @param string $alignment
     * @param bool $unsetNull
     * @return string
     */
    public function fixTextAlignment(string $alignment, $unsetNull = false): string {
        $return = "";
        $max = null;
        $args = [];

        foreach (explode(PHP_EOL, $alignment) as $item) {
            if($max === null) $max = strlen($item);
            if($max <= strlen($item)) {
                $max = strlen($item);
            }
            array_push($args, $item);
        }

        foreach ($args as $text) {
            $lenght = strlen($text);
            $return = $return.str_repeat(" ", intval(($max-$lenght)/2)).$text.PHP_EOL;
        }

        if($unsetNull) {
            $array = explode(PHP_EOL, $return);
            array_shift($array);
            $return = implode(PHP_EOL, $array);
        }

        return $return;
    }
}