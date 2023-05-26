package Annihilation.MySQL;

import Annihilation.Annihilation;
import MTCore.MTCore;
import MTCore.Mysql.AsyncQuery;
import cn.nukkit.Player;
import cn.nukkit.Server;
import cn.nukkit.utils.TextFormat;

import java.util.HashMap;

public class JoinTeamQuery extends AsyncQuery {

    private String color;

    private String name;

    public JoinTeamQuery(Annihilation plugin, String player, String color) {
        this.player = player;
        this.color = color;

        plugin.getServer().getScheduler().scheduleAsyncTask(this);
    }

    public void onQuery(HashMap<String, Object> data) {
        String rank = (String) data.get("rank");

        name = MTCore.getDisplayRank(rank) + " " + color + player + TextFormat.RESET;
    }

    public void onCompletion(Server server) {
        Player p = server.getPlayerExact(player);

        if (p == null || !p.isOnline()) {
            return;
        }

        p.setNameTag(color + player);
        p.setDisplayName(name);
    }
}