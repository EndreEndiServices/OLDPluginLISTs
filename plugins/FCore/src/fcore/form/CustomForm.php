<?php

declare(strict_types=1);

namespace fcore\form;

use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\Player;

class CustomForm {

    /** @var int */
    public $formId;

    /** @var array */
    public $formData = [];

    /** @var string */
    public $player;

    /**
     * @param int $id
     * @param callable $callable
     */
    public function __construct(int $id, ?callable $callable) {
        $this->formData["type"] = "custom_form";
        $this->formData["title"] = "";
        $this->formData["content"] = [];
    }

    /**
     * @param Player $player
     */
    public function send(Player $player) : void {
        $pk = new ModalFormRequestPacket();
        $pk->formId = $this->formId;
        $pk->formData = json_encode($this->formData);
        $player->dataPacket($pk);
        $this->player = $player;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title) : void {
        $this->formData["title"] = $title;
    }

    /**
     * @return string
     */
    public function getTitle() : string {
        return $this->formData["title"];
    }

    /**
     * @param string $text
     */
    public function addLabel(string $text) : void {
        $this->addContent(["type" => "label", "text" => $text]);
    }

    /**
     * @param string $text
     * @param bool|null $default
     */
    public function addToggle(string $text, bool $default = null) : void {
        $content = ["type" => "toggle", "text" => $text];
        if($default !== null){
            $content["default"] = $default;
        }
        $this->addContent($content);
    }

    /**
     * @param string $text
     * @param int $min
     * @param int $max
     * @param int $step
     * @param int $default
     */
    public function addSlider(string $text, int $min, int $max, int $step = -1, int $default = -1) : void {
        $content = ["type" => "slider", "text" => $text, "min" => $min, "max" => $max];
        if($step !== -1){
            $content["step"] = $step;
        }
        if($default !== -1){
            $content["default"] = $default;
        }
        $this->addContent($content);
    }

    /**
     * @param string $text
     * @param array $steps
     * @param int $defaultIndex
     */
    public function addStepSlider(string $text, array $steps, int $defaultIndex = -1) : void {
        $content = ["type" => "step_slider", "text" => $text, "steps" => $steps];
        if($defaultIndex !== -1){
            $content["default"] = $defaultIndex;
        }
        $this->addContent($content);
    }

    /**
     * @param string $text
     * @param array $options
     * @param int $default
     */
    public function addDropdown(string $text, array $options, int $default = null) : void {
        $this->addContent(["type" => "dropdown", "text" => $text, "options" => $options, "default" => $default]);
    }

    /**
     * @param string $text
     * @param string $placeholder
     * @param string $default
     */
    public function addInput(string $text, string $placeholder = "", string $default = null) : void {
        $this->addContent(["type" => "input", "text" => $text, "placeholder" => $placeholder, "default" => $default]);
    }

    /**
     * @param array $content
     */
    private function addContent(array $content) : void {
        $this->formData["content"][] = $content;
    }

}