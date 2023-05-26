package Annihilation.MySQL;

import Annihilation.Annihilation;
import MTCore.Mysql.AsyncQuery;

import java.sql.PreparedStatement;
import java.sql.SQLException;
import java.util.HashMap;

public class NormalQuery extends AsyncQuery {

    public static final String KILL = "kills";
    public static final String DEATH = "deaths";
    public static final String WIN = "wins";
    public static final String LOSE = "losses";
    public static final String NEXUS_DESTROY = "nexuses";
    public static final String NEXUS_DAMAGE = "nexusdmg";

    private String[] players;
    private String key;
    private Integer value;

    public NormalQuery(Annihilation plugin, String key, String[] players) {
        this(plugin, key, players, 1, "annihilation");
    }

    public NormalQuery(Annihilation plugin, String key, String[] players, Integer value) {
        this(plugin, key, players, value, "annihilation");
    }

    public NormalQuery(Annihilation plugin, String key, String[] players, Integer value, String table) {
        this.key = key;
        this.value = value;
        this.players = players;
        this.table = table;

        plugin.getServer().getScheduler().scheduleAsyncTask(this);
    }

    public void onQuery(HashMap<String, Object> data) {
        try {
            for (String p : players) {
                PreparedStatement e = getMysqli().prepareStatement("UPDATE " + table.trim() + " SET " + key + " = " + key + "+'" + value + "' WHERE name = '" + p.trim().toLowerCase() + "'");
                e.executeUpdate();
            }
        } catch (SQLException var4) {
            var4.printStackTrace();
        }
    }
}