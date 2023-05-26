package BedWars.MySQL;

import GTCore.Mysql.AsyncQuery;
import cn.nukkit.Player;
import cn.nukkit.Server;

import java.util.HashMap;
import java.util.Map;

public class ShowStatsQuery extends AsyncQuery {

    public ShowStatsQuery(String player) {
        this.player = player;
        Server.getInstance().getScheduler().scheduleAsyncTask(this);
    }

    Map<String, Object> data = null;

    @Override
    public void onQuery(HashMap<String, Object> data) {
        this.data = data;
    }

    @Override
    public void onCompletion(Server server) {
        Player p = server.getPlayerExact(player);

        if (p == null) {
            return;
        }

        p.sendMessage("§7--------------------\n" +
                "§9> §l§fBed§4Wars§r§9 statistiky §9<\n" +
                "§2Zabití: §5" + data.get("kills") + "\n" +
                "§2Smrti: §5" + data.get("deaths") + "\n" +
                "§2Vyher: §5" + data.get("wins") + "\n" +
                "§2Proher: §5" + data.get("losses") + "\n" +
                "§2Znicenych posteli: §5" + data.get("beds") + "\n" +
                "§7--------------------");

    }

}