package Annihilation.MySQL;

import Annihilation.Annihilation;
import MTCore.Mysql.AsyncQuery;

import java.sql.PreparedStatement;
import java.sql.SQLException;
import java.util.HashMap;

public class JoinQuery extends AsyncQuery {

    public JoinQuery(Annihilation plugin, String player) {
        this.player = player;
        this.table = "annihilation";

        plugin.getServer().getScheduler().scheduleAsyncTask(this);
    }

    public void onQuery(HashMap<String, Object> data) {

        if (data == null) {
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
        data.put("nexuses", 0);
        data.put("nexusdmg", 0);
        data.put("kits", "civilian|handyman");

        try {
            PreparedStatement e = this.getMysqli().prepareStatement("INSERT INTO annihilation ( name, kills, deaths, wins, losses, nexuses, nexusdmg, kits) VALUES ('" + name + "', '" + 0 + "', ' " + 0 + "', ' " + 0 + "', ' " + 0 + "', ' " + 0 + "', ' " + 0 + "', 'civilian|handyman')");
            e.executeUpdate();

            return data;
        } catch (SQLException e) {
            e.printStackTrace();
            return data;
        }
    }
}