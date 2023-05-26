package GameTeam.MySQL;

import GameTeam.Survival;
import GTCore.Mysql.AsyncQuery;

import java.sql.PreparedStatement;
import java.sql.SQLException;
import java.util.HashMap;

public class JoinQuery extends AsyncQuery {

    public JoinQuery(Survival plugin, String player) {
        this.player = player;
        this.table = "survival";

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
        data.put("x", 526);
        data.put("y", 56);
        data.put("z", 522);
        data.put("level", "lobby");
        data.put("health", 20);
        data.put("food", 20);
        data.put("gamemode", 0);
        data.put("money", 100);
        data.put("effect", "");
        data.put("inventar", "");

        try {
            PreparedStatement e = this.getMysqli().prepareStatement("INSERT INTO survival ( name, x, y, z, level, health, food, gamemode, money, effect, inventar) VALUES ('" + name + "', '" + data.get("x") + "', ' " + data.get("y") + "', ' " + data.get("z") + "', ' " + data.get("level") + "'," +
                    " ' " + data.get("health") + "', ' " + data.get("food") + "', ' " + data.get("gamemode") + "', ' " + data.get("money") + "', ' " + data.get("effect") + "', '" + data.get("inventar") + "')");
            e.executeUpdate();

            return data;
        } catch (SQLException e) {
            e.printStackTrace();
            return data;
        }
    }
}