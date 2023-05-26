package BedWars.MySQL;

import BedWars.BedWars;
import GTCore.Mysql.AsyncQuery;

import java.sql.PreparedStatement;
import java.sql.SQLException;
import java.util.*;

public class StatQuery extends AsyncQuery {

    private Collection<String> players;
    private Stat key;
    private Integer value;

    public StatQuery(BedWars plugin, Stat key, String player) {
        this(plugin, key, new ArrayList<>(Arrays.asList(player)), 1, "bedwars");
    }

    public StatQuery(BedWars plugin, Stat key, Collection<String> players) {
        this(plugin, key, players, 1, "bedwars");
    }

    public StatQuery(BedWars plugin, Stat key, Collection<String> players, Integer value) {
        this(plugin, key, players, value, "bedwars");
    }

    public StatQuery(BedWars plugin, Stat key, Collection<String> players, Integer value, String table) {
        this.key = key;
        this.value = value;
        this.players = players;
        this.player = "";
        this.table = table;

        plugin.getServer().getScheduler().scheduleAsyncTask(this);
    }

    @Override
    public void onRun() {

        String name = key.getName();
        int xp = key.getXp();
        int tokens = key.getTokens();

        try {
            for (String p : players) {
                getApi().addExp(p, xp);
                getApi().addTokens(p, tokens);

                PreparedStatement e = getMysqli().prepareStatement("UPDATE " + table.trim() + " SET " + name + " = " + name + "+'" + value + "' WHERE name = '" + p.trim().toLowerCase() + "'");
                e.executeUpdate();
            }
        } catch (SQLException var4) {
            var4.printStackTrace();
        }
    }
}