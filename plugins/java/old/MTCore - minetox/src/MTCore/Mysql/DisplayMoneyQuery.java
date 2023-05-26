package MTCore.Mysql;


import MTCore.MTCore;
import MTCore.MySQLManager;
import cn.nukkit.Player;
import cn.nukkit.Server;
import cn.nukkit.utils.TextFormat;

import java.util.HashMap;

public class DisplayMoneyQuery extends AsyncQuery {

    public DisplayMoneyQuery(MTCore plugin, String player) {
        this.player = player;

        plugin.getServer().getScheduler().scheduleAsyncTask(this);
    }

    private int coins = 0;

    @Override
    public void onQuery(HashMap<String, Object> data) {
        coins = (int) data.get("tokens");
    }

    @Override
    public void onCompletion(Server server) {
        Player p = server.getPlayerExact(this.player);

        if (p != null && p.isOnline()) {
            p.sendMessage(TextFormat.YELLOW + "Tokens: " + TextFormat.BLUE + coins);
        }
    }
}