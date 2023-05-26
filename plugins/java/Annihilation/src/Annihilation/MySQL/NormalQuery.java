package Annihilation.MySQL;

import Annihilation.Annihilation;
import Annihilation.Stat;
import GTCore.Mysql.AsyncQuery;

import java.sql.PreparedStatement;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.Collection;
import java.util.HashMap;

public class NormalQuery extends AsyncQuery {

    private Collection<String> players;
    private Stat key;
    private Integer value;

    public NormalQuery(Annihilation plugin, Stat key, String player) {
        this(plugin, key, new ArrayList<String>(Arrays.asList(player)), 1, "annihilation");
    }

    public NormalQuery(Annihilation plugin, Stat key, Collection<String> players) {
        this(plugin, key, players, 1, "annihilation");
    }

    public NormalQuery(Annihilation plugin, Stat key, Collection<String> players, Integer value) {
        this(plugin, key, players, value, "annihilation");
    }

    public NormalQuery(Annihilation plugin, Stat key, Collection<String> players, Integer value, String table) {
        this.key = key;
        this.value = value;
        this.players = players;
        this.table = table;

        plugin.getServer().getScheduler().scheduleAsyncTask(this);
    }

    public void onQuery(HashMap<String, Object> data) {

        String name = key.getName();
        int xp = key.getXp();
        int tokens = key.getTokens();

        try {
            for (String p : players) {
                if (xp > 0) {
                    getApi().addExp(p, xp);
                }

                if (tokens > 0) {
                    getApi().addTokens(p, tokens);
                }

                PreparedStatement e = getMysqli().prepareStatement("UPDATE " + table.trim() + " SET " + key + " = " + key + "+'" + value + "' WHERE name = '" + p.trim().toLowerCase() + "'");
                e.executeUpdate();
            }
        } catch (SQLException var4) {
            var4.printStackTrace();
        }
    }
}