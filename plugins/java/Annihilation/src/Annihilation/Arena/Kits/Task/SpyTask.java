package Annihilation.Arena.Kits.Task;

import Annihilation.Arena.Kits.Spy;
import cn.nukkit.scheduler.Task;
import cn.nukkit.Player;
import cn.nukkit.utils.TextFormat;

public class SpyTask extends Task {

    public Player player;

    public SpyTask(Player p) {
        this.player = p;
    }

    public void onRun(int currentTick) {
        if (Spy.players.contains(this.player.getName().toLowerCase())) {
            this.player.despawnFromAll();
            this.player.sendMessage(TextFormat.YELLOW + "You are now invisible.");
        }
    }
}