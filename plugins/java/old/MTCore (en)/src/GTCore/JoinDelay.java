package GTCore;

import cn.nukkit.scheduler.Task;
import cn.nukkit.Player;

public class JoinDelay extends Task {

    private MTCore plugin;
    private Player player;

    public JoinDelay(MTCore plugin, Player p) {
        this.plugin = plugin;
        this.player = p;
    }

    @Override
    public void onRun(int currentTick) {
        if (!player.isOnline()) {
            return;
        }

        plugin.setLobby(player);
    }
}