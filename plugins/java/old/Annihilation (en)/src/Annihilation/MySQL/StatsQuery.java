package Annihilation.MySQL;


import Annihilation.Annihilation;
import MTCore.MTCore;
import MTCore.Mysql.AsyncQuery;
import cn.nukkit.Player;
import cn.nukkit.Server;
import cn.nukkit.utils.TextFormat;

import java.util.HashMap;

public class StatsQuery extends AsyncQuery {

    private String msg;

    public StatsQuery(Annihilation plugin, String player) {
        this.player = player;
        this.table = "annihilation";

        plugin.getServer().getScheduler().scheduleAsyncTask(this);
    }

    public void onQuery(HashMap<String, Object> data) {
        msg = TextFormat.BLUE + "> Your " + TextFormat.GOLD + TextFormat.BOLD + "Annihilation" + TextFormat.RESET + TextFormat.BLUE + " stats " + TextFormat.BLUE + " <\n"
                + TextFormat.DARK_GREEN + "Kills: " + TextFormat.DARK_PURPLE + data.get("kills") + "\n"
                + TextFormat.DARK_GREEN + "Deaths: " + TextFormat.DARK_PURPLE + data.get("deaths") + "\n"
                + TextFormat.DARK_GREEN + "Wins: " + TextFormat.DARK_PURPLE + data.get("wins") + "\n"
                + TextFormat.DARK_GREEN + "Losses: " + TextFormat.DARK_PURPLE + data.get("losses") + "\n"
                + TextFormat.DARK_GREEN + "Nexuses destroyed: " + TextFormat.DARK_PURPLE + data.get("nexuses") + "\n"
                + TextFormat.DARK_GREEN + "Nexus damaged: " + TextFormat.DARK_PURPLE + data.get("nexusdmg") + "\n"
                + TextFormat.GRAY + "---------------------";
    }

    public void onCompletion(Server server) {
        MTCore plugin = MTCore.getInstance();

        if (plugin == null || !plugin.isEnabled()) {
            return;
        }

        Player p = plugin.getPlayerExact(this.player);

        if (p == null || !p.isOnline()) {
            return;
        }

        p.sendMessage(msg);
    }
}