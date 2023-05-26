<?php

declare(strict_types=1);

namespace fcore\form;

use fcore\FCore;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

/**
 * Class FormAPI
 * @package fcore\form
 */
class FormAPI implements Listener {

    /** @var int $index */
    public $index = 0;

    /** @var array $forms */
    public $forms = [];

    /** @var FCore $plugin */
    public $plugin;

    /**
     * FormAPI constructor.
     */
    public function __construct(FCore $plugin) {
        $this->plugin = $plugin;
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
    }

    /**
     * @param string $title
     * @param string $content
     * @param Button[] $buttons
     * @param callable $callable
     * @return SimpleForm
     */
    public function createSimpleForm(string $title, string $content, array $buttons, callable $callable = null): SimpleForm {
        $form = $this->forms[$this->index] = new SimpleForm($title, $content, $buttons, $callable);
        $form->formId = $this->index;
        $this->index++;
        return $form;
    }

    /**
     * @param DataPacketReceiveEvent $event
     */
    public function onDataPacketRecieve(DataPacketReceiveEvent $event) {
        $pk = $event->getPacket();
        if($pk instanceof ModalFormResponsePacket){
            $player = $event->getPlayer();
            $formId = $pk->formId;
            $data = json_decode($pk->formData, true);
            if(isset($this->forms[$formId])){
                /** @var SimpleForm $form */
                $form = $this->forms[$formId];
                if($form->player->getName() !== $player->getName()) {
                    return;
                }
                /** @var callable $callable */
                $callable = $form->callable;
                if(!is_array($data)){
                    $data = [$data];
                }
                if($callable !== null) {
                    $callable($event->getPlayer(), $data);
                }
                unset($this->forms[$formId]);
                $event->setCancelled();
            }
        }
    }
}