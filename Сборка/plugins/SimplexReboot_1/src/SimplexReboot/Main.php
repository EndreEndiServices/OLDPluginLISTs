<?php

namespace SimplexReboot;

use pocketmine\scheduler\CallbackTask;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

class Main extends PluginBase implements Listener
{
    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask(array($this, "Reboot")), 20 * 60 * 20);
    }

    public function Reboot()
    {
        foreach ($this->getServer()->getOnlinePlayers() as $player){
            $player->close("", "§fДорогой друг, §bсервер был перезапущен§f, попробуй
присоединиться к серверу в течение §b15 секунд после рестарта§f.
[§4?§f] Перезагрузка нужна что бы очищать сервер от подлагиваний....");
        }
        $this->getServer()->shutdown();
    }
}
