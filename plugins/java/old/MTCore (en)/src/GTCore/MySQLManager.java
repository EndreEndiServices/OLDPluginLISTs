//
// Source code recreated from a .class file by IntelliJ IDEA
// (powered by Fernflower decompiler)
//

package GTCore;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.ResultSetMetaData;
import java.sql.SQLException;
import java.util.HashMap;

public class MySQLManager {
    public MTCore plugin;
    public Connection database = null;

    public MySQLManager(MTCore plugin) {
        this.plugin = plugin;
        //this.createMySQLConnection();
    }

    public static Connection getMysqlConnection() {
        String url = "db2.gameteam.cz";
        String dbName = "pe_stats";
        String userName = "pe_stats";
        String password = "4aHHtzCPjZUtKdjS";

        Connection e = null;

        try {
            Class.forName("com.mysql.jdbc.Driver");
            e = DriverManager.getConnection("jdbc:mysql://" + url + ":3306/" + dbName, userName, password);
        } catch (SQLException var7) {
            System.out.println("Nepodarilo se navazat pripojeni s databazi");
            return null;
        } catch (ClassNotFoundException var8) {
            System.out.println("Nepodarilo se navazat pripojeni s databazi: Trida nenalezena");
            return null;
        }

        return e;
    }

    public void registerPlayer(String player) {
        String name = player.toLowerCase().trim();

        try {
            PreparedStatement e = this.getDatabase().prepareStatement("INSERT INTO freezecraft ( name, rank, doba, tokens, heslo, ip, id) VALUES ('" + name + "', 'hrac', '" + 0 + "', ' " + 0 + "', '', '', '')");
            e.executeUpdate();
        } catch (SQLException var4) {
            var4.printStackTrace();
        }

    }

    public HashMap<String, Object> getPlayer(String player) {
        return this.getPlayer(player, "freezecraft");
    }

    public HashMap<String, Object> getPlayer(String player, String database) {
        try {
            PreparedStatement e = this.getDatabase().prepareStatement("SELECT * FROM " + database + " WHERE name = \'" + player + "\'");
            ResultSet result = e.executeQuery();
            ResultSetMetaData md = result.getMetaData();
            int columns = md.getColumnCount();
            HashMap row = new HashMap();

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
    }

    public void setDatabase(Connection database) {
        this.database = database;
    }

    public Connection getDatabase() {
        return this.database;
    }

    public boolean isPlayerRegistered(String player) {
        return !this.getPlayer(player).isEmpty();
    }

    public void setRank(String player, String rank) {
        try {
            PreparedStatement e = this.getDatabase().prepareStatement("UPDATE freezecraft SET rank = \'" + rank + "\' WHERE name = \'" + player + "\'");
            e.executeUpdate();
        } catch (SQLException var4) {
            var4.printStackTrace();
        }

    }

    public void setTime(String player, int time) {
        try {
            PreparedStatement e = this.getDatabase().prepareStatement("UPDATE freezecraft SET doba = \'" + time + "\' WHERE name = \'" + player + "\'");
            e.executeUpdate();
        } catch (SQLException var4) {
            var4.printStackTrace();
        }

    }

    public String getRank(String p) {
        HashMap data = this.getPlayer(p);
        return (String) data.get("rank");
    }

    public int getTime(String p) {
        HashMap data = this.getPlayer(p);
        return (int) data.get("doba");
    }

    public void addTokens(String p, int tokens) {
        try {
            PreparedStatement e = this.getDatabase().prepareStatement("UPDATE freezecraft SET tokens = tokens + \'" + tokens + "\' WHERE name = \'" + p + "\'");
            e.executeUpdate();
        } catch (SQLException var4) {
            var4.printStackTrace();
        }

    }

    public void takeTokens(String p, int tokens) {
        try {
            PreparedStatement e = this.getDatabase().prepareStatement("UPDATE freezecraft SET tokens = tokens - \'" + tokens + "\' WHERE name = \'" + p + "\'");
            e.executeUpdate();
        } catch (SQLException var4) {
            var4.printStackTrace();
        }

    }

    public int getTokens(String p) {
        HashMap data = this.getPlayer(p);
        return ((Integer) data.get("tokens")).intValue();
    }

    public void setPassword(String p, String heslo) {
        try {
            PreparedStatement e = this.getDatabase().prepareStatement("UPDATE freezecraft SET heslo = \'" + heslo + "\' WHERE name = \'" + p + "\'");
            e.executeUpdate();
        } catch (SQLException var4) {
            var4.printStackTrace();
        }

    }

    public void setIP(String p, String heslo) {
        try {
            PreparedStatement e = this.getDatabase().prepareStatement("UPDATE freezecraft SET ip = \'" + heslo + "\' WHERE name = \'" + p + "\'");
            e.executeUpdate();
        } catch (SQLException var4) {
            var4.printStackTrace();
        }

    }

    public void setUUID(String p, String heslo) {
        try {
            PreparedStatement e = this.getDatabase().prepareStatement("UPDATE freezecraft SET id = \'" + heslo + "\' WHERE name = \'" + p + "\'");
            e.executeUpdate();
        } catch (SQLException var4) {
            var4.printStackTrace();
        }

    }

    public String getPassword(String p) {
        HashMap data = this.getPlayer(p);
        return (String) data.get("heslo");
    }

    public String getIP(String p) {
        HashMap data = this.getPlayer(p);
        return (String) data.get("ip");
    }

    public String getUUID(String p) {
        HashMap data = this.getPlayer(p);
        return (String) data.get("id");
    }
}
