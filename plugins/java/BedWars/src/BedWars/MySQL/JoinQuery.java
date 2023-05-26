package BedWars.MySQL;

import BedWars.BedWars;
import GTCore.Mysql.AsyncQuery;
import cn.nukkit.Server;

import java.sql.PreparedStatement;
import java.sql.SQLException;
import java.util.HashMap;

public class JoinQuery extends AsyncQuery {

    public JoinQuery(BedWars plugin, String player) {
        this.player = player;
        this.table = "bedwars";

        plugin.getServer().getScheduler().scheduleAsyncTask(this);
    }

    boolean registered = false;

    public void onQuery(HashMap<String, Object> data) {
        if (data == null || data.isEmpty()) {
            registerPlayer(this.player);
        }
    }

    public HashMap<String, Object> registerPlayer(String player) {
        String name = player.toLowerCase().trim();

        HashMap<String, Object> data = new HashMap<>();
        data.put("name", name);
        data.put("kills", 0);
        data.put("deaths", 0);
        data.put("wins", 0);
        data.put("losses", 0);
        data.put("beds", 0);

        try {
            PreparedStatement e = this.getMysqli().prepareStatement("INSERT INTO bedwars ( name, kills, deaths, wins, losses, beds) VALUES ('" + name + "', '" + 0 + "', ' " + 0 + "', ' " + 0 + "', ' " + 0 + "', ' " + 0 + "')");
            e.executeUpdate();
            registered = true;
            return data;
        } catch (SQLException e) {
            e.printStackTrace();
            return data;
        }
    }

    @Override
    public void onCompletion(Server server) {
        if (registered) {
            server.getLogger().info(BedWars.getPrefix() + "§aZaregistrován hráč §e" + this.player);
        }
    }
}