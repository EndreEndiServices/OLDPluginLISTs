<?php

namespace fcore\form;

use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\Player;

/**
 * Class SimpleForm
 * @package fcore\form
 */
class SimpleForm {

    /** @var int $formId */
    public $formId;

    /** @var array $formData */
    public $formData;

    /** @var callable $callable */
    public $callable;

    /** @var Player $player */
    public $player;

    /**
     * SimpleForm constructor.
     * @param string $title
     * @param string $content
     * @param Button[] $buttons
     */
    public function __construct(string $title = "", string $content = "", array $buttons = [], callable $callable = null) {
        if(count($buttons) == 0) {
            $this->formData["buttons"] = null;
        }
        $this->formData["title"] = $title;
        $this->formData["content"] = $content;
        $this->formData["type"] = "form";
        foreach ($buttons as $button) {
            $this->formData["buttons"][] = $button->getData();
        }
        $this->callable = $callable;
    }

    public function send(Player $player) {
        $pk = new ModalFormRequestPacket;
        $pk->formId = $this->formId;
        $pk->formData = json_encode($this->formData);
        $this->player = $player;
        $player->dataPacket($pk);
    }
}