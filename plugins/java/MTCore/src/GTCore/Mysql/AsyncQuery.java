package GTCore.Mysql;

import GTCore.MySQLManager;
import cn.nukkit.scheduler.AsyncTask;
import cn.nukkit.Server;
import com.gmail.holubvojtech.gthttpapi.api.GTApi;
import com.gmail.holubvojtech.gthttpapi.api.GTHttpApi;
import lombok.Getter;

import java.sql.*;

import java.util.HashMap;

public abstract class AsyncQuery extends AsyncTask {

    public static final String MYSQLI_KEY = "GTCore.MySQL";

    protected String table = null;

    @Getter
    protected GTApi api = GTHttpApi.getApi();

    @Getter
    protected String player;

    /*public AsyncQuery(Plugin plugin) {
        plugin.getServer().getScheduler().scheduleAsyncTask(this);

        //GTCore::getInstance()->tasks[time()] = $this;
    }*/

    public void onRun() {
        if (table != null) {
            HashMap<String, Object> data = this.getPlayer(this.player.toLowerCase(), this.table);

            this.onQuery(data);
        }
    }

    protected void onQuery(HashMap<String, Object> data) {

    }

    public void onCompletion(Server server) {

    }
/*
    protected String checkBan(HashMap data) {
        if (data == null) {
            return null;
        }

        int expiration = (int) data.get("expiration");

        if ((expiration - GTCore.getTime()) > 0 && (int) data.get("forever") <= 0) {
            //$p->kick(TextFormat::RED."You are banned for ".TextFormat::RED.round($expiration / 86400, 1).TextFormat::RED." days\n".TextFormat::RED."Reason: ".TextFormat::AQUA.$data["reason"], false);
            return TextFormat.RED + "You are banned for " + TextFormat.RED + Math.round(expiration / 86400) + TextFormat.RED + " days\n" + TextFormat.RED + "Reason: " + TextFormat.AQUA + data.get("reason");
        }

        if (((int) data.get("forever")) > 0) {
            //$p->kick(TextFormat::RED."You are banned forever\n".TextFormat::RED."Reason: ".TextFormat::AQUA.$data["reason"], false);
            return TextFormat.RED + "You are banned forever\n" + TextFormat.RED + "Reason: " + TextFormat.AQUA + data.get("reason");
        }

        return null;
    }*/

    protected Connection getMysqli() {
        Connection mysqli = (Connection) this.getFromThreadStore(MYSQLI_KEY);

        if (mysqli != null) {
            return mysqli;
        }

        mysqli = MySQLManager.getMysqlConnection();

        this.saveToThreadStore(MYSQLI_KEY, mysqli);
        return mysqli;
    }

    public HashMap<String, Object> getPlayer(String player) {
        return getPlayer(player, "annihilation");
    }

    public HashMap<String, Object> getPlayer(String player, String table) {
        try {
            PreparedStatement e = getMysqli().prepareStatement("SELECT * FROM " + table + " WHERE name = \'" + player + "\'");
            ResultSet result = e.executeQuery();
            ResultSetMetaData md = result.getMetaData();
            int columns = md.getColumnCount();
            HashMap<String, Object> row = new HashMap<>();

            while (result.next()) {
                for (int i = 1; i <= columns; ++i) {
                    row.put(md.getColumnName(i), result.getObject(i));
                }
            }

            return row;
        } catch (SQLException var9) {
            var9.printStackTrace();
            return null;
        }
    }/*

    public HashMap<String, Object> registerPlayer(String player) {
        String name = player.toLowerCase().trim();

        try {
            PreparedStatement e = getMysqli().prepareStatement("INSERT INTO freezecraft ( name, rank, doba, tokens, heslo, ip, id) VALUES ('" + name + "', 'hrac', '" + 0 + "', ' " + 0 + "', '', '', '')");
            e.executeUpdate();

            HashMap<String, Object> data = new HashMap<>();
            data.put("name", name);
            data.put("rank", "hrac");
            data.put("doba", 0);
            data.put("tokens", 0);
            data.put("heslo", "");
            data.put("ip", "");
            data.put("id", "");

            return data;
        } catch (SQLException var4) {
            var4.printStackTrace();
            return null;
        }
    }

    public void setRank(String player, String rank) {
        try {
            PreparedStatement e = getMysqli().prepareStatement("UPDATE freezecraft SET rank = \'" + rank + "\' WHERE name = \'" + player + "\'");
            e.executeUpdate();
        } catch (SQLException var4) {
            var4.printStackTrace();
        }

    }

    public void setTime(String player, int time) {
        try {
            PreparedStatement e = getMysqli().prepareStatement("UPDATE freezecraft SET doba = \'" + time + "\' WHERE name = \'" + player + "\'");
            e.executeUpdate();
        } catch (SQLException var4) {
            var4.printStackTrace();
        }

    }

    public void addTokens(String p, int tokens) {
        try {
            PreparedStatement e = getMysqli().prepareStatement("UPDATE freezecraft SET tokens = tokens + \'" + tokens + "\' WHERE name = \'" + p + "\'");
            e.executeUpdate();
        } catch (SQLException var4) {
            var4.printStackTrace();
        }

    }

    public void setPassword(String p, String heslo) {
        try {
            PreparedStatement e = getMysqli().prepareStatement("UPDATE freezecraft SET heslo = \'" + heslo + "\' WHERE name = \'" + p + "\'");
            e.executeUpdate();
        } catch (SQLException var4) {
            var4.printStackTrace();
        }

    }

    public void setIP(String p, String heslo) {
        try {
            PreparedStatement e = getMysqli().prepareStatement("UPDATE freezecraft SET ip = \'" + heslo + "\' WHERE name = \'" + p + "\'");
            e.executeUpdate();
        } catch (SQLException var4) {
            var4.printStackTrace();
        }

    }

    public void setUUID(String p, String heslo) {
        try {
            PreparedStatement e = getMysqli().prepareStatement("UPDATE freezecraft SET id = \'" + heslo + "\' WHERE name = \'" + p + "\'");
            e.executeUpdate();
        } catch (SQLException var4) {
            var4.printStackTrace();
        }

    }*/

    public void addKit(String p, String kit) {
        try {
            PreparedStatement e = getMysqli().prepareStatement("UPDATE annihilation SET kits = " + this.getPlayer(p, "annihilation").get("kits") + "|" + kit + " WHERE name = \'" + p + "\'");
            e.executeUpdate();
        } catch (SQLException var4) {
            var4.printStackTrace();
        }
    }
}