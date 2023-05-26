<?php

namespace ParadoxUHC\Commands;

use ParadoxUHC\UHC;
use ParadoxUHC\Commands\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;

class MainEventCommand extends BaseCommand {
    private $plugin;

    /**
     * MainUHCCommand constructor.
     * @param UHC $plugin
     */
    public function __construct(UHC $plugin) {
        $this->plugin = $plugin;
        parent::__construct($plugin, "event", "Debugging purposes only.", "/event [eventname]", ["e"]);
        $this->setPermission("uhc.commands.uhc");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, $commandLabel, array $args)
    {
        if($sender->hasPermission("uhc.commands.uhc")) {
            if (isset($args[0])) {
                if ($args[0] === 'countdown') {
                    $this->plugin->status = UHC::STATUS_COUNTDOWN;
                    return true;
                }
                if ($args[0] === 'waiting') {
                    $this->plugin->status = UHC::STATUS_WAITING;
                    return true;
                }
                if ($args[0] === 'grace') {
                    $this->plugin->status = UHC::STATUS_GRACE;
                    return true;
                }
                if ($args[0] === 'pvp') {
                    $this->plugin->status = UHC::STATUS_PVP;
                    return true;
                }
                if ($args[0] === 'tp1') {
                    $this->plugin->status = UHC::STATUS_TP_1;
                    return true;
                }
                if ($args[0] === 'tele1') {
                    $this->plugin->status = UHC::STATUS_TELE_1;
                    return true;
                }
                if ($args[0] === 'tp2') {
                    $this->plugin->status = UHC::STATUS_TP_2;
                    return true;
                }
                if ($args[0] === 'tele2') {
                    $this->plugin->status = UHC::STATUS_TELE_2;
                    return true;
                }
                if ($args[0] === 'last') {
                    $this->plugin->status = UHC::STATUS_LAST;
                    return true;
                }else {
                    $sender->sendMessage(TF::RED . 'That is an invalid event name!');
                    return true;
                }
            } else {
                $sender->sendMessage(TF::RED . 'Usage: /event [eventname]');
                return false;
            }
        }
        else {
            $sender->sendMessage(TF::RED . 'You do not have permission to use this command!');
            return false;
        }
    }

    /**
     * @return mixed
     */
    public function getPlugin()
    {
        return $this->plugin;
    }
}