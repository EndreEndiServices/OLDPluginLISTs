<?php

namespace Alert;

use pocketmine\Server;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat as C;
use KingdomCore\Main;

class AlertTask extends PluginTask {

    public function __construct(Main $plugin){
        parent::__construct($plugin);
        $this->plugin = $plugin;
    }

    public function onRun($currentTick){
    	$this->plugin = $this->getOwner();
        $input = array(
        "Follow us on Twitter: @KingdomCraft33",
        "More Games are in the will Come soon!",
        "Check out Our Website: kcmcpe.net",
        "Hope you have Fun!",
        "Hacking is NOT allowed on KingdomCraft servers, Disable client mods before playing",
        "You will always be by my side, oh sorry I was singing",

        "We know are server is not perfect we will always try to improve :)",
        "Any Ideas for the server? Message us on Twitter @KingdomCraft33",
        "It works better if you plug it in.",
        "I have not lost my mind, it's backed up on disk",
        "Thanks for playing on KingdomCraft Network! Join a game!",

        "/msg send a private message to the given player",
        "To return to the lobby at any time, use /hub, /lobby or /spawn",
        "Do witches use Spell Check?",
        "With Great power, Comes Great Electricity Bil",
        "Everyone has good qualities",

        "I think my IPhone is broken, I pressed the home button and I'm still at work",
        "Sometimes we all have those lazy moments",
        "Lazy Rule: if I can't Reach it, Then I do not need it",
        "Follow your heart, But Take your brain with you",
        "Nothing is as easy as it looks.",

        "Everything takes longer than you think.",
        "Whenever possible blame the Hardware",
        "Pirate software comes with a treasure map",
        "Admins can be annoying sometimes, but we all love them :)",
        "1000 Followers on Mobcrush can get you a Cool Mobcrush Rank",

        "Do not ask to be admin",
        "Need Help? Try typing §f/help",
        "Invite your friends to play :)",
        "Thank you for helping us Grow",
        "Speak kindly to one another",

        "Proofread carefully to see if you any words out!",
        "Some days you're the windshield, some days the bug",
        "We're lost but we're making good time",
        "What if there were no hypothetical questions?",
        "What was the best thing Before sliced bread?",

        "You used to be indecisive. Now you're not sure",
        "You will become rich and famous unless you don't",
        "You will never be younger then you are today..",
        "Sometimes you might not be the best at PvP, But remember there is always someone worse at PvP than you",
        "Never leave your Guard down"
        ); 
        $messages = array_rand($input);
    	Server::getInstance()->broadcastMessage(C::GRAY . $input[$messages]);
    	}
}
?>
