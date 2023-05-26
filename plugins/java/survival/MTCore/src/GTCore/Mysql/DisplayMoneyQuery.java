package GTCore.Mysql;


import GTCore.MTCore;
import cn.nukkit.Player;
import cn.nukkit.Server;
import cn.nukkit.utils.TextFormat;

public class DisplayMoneyQuery extends AsyncQuery {

    public DisplayMoneyQuery(MTCore plugin, String player) {
        this.player = player;

        plugin.getServer().getScheduler().scheduleAsyncTask(this);
    }

    private int coins = 0;

    @Override
    public void onRun() {
        coins = getApi().getTokens(getPlayer());
    }

    @Override
    public void onCompletion(Server server) {
        MTCore plugin = MTCore.getInstance();

        if (plugin == null || !plugin.isEnabled()) {
            return;
        }

        Player p = plugin.getPlayerExact(this.player);

        if (p == null || !p.isOnline()) {
            return;
        }

        if (p.isOnline()) {
            p.sendMessage(MTCore.getPrefix() + TextFormat.YELLOW + "Tokeny: " + TextFormat.BLUE + coins);
        }
    }
}