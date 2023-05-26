package Annihilation;

import java.sql.Connection;
import java.sql.*;
import java.util.HashMap;

public class MySQLManager {

    public Annihilation plugin;
    public Connection database;

    public MySQLManager(Annihilation plugin) {
        this.plugin = plugin;
        this.createMySQLConnection();
    }

    private void createMySQLConnection() {
        String url = "93.91.250.135";
        String dbName = "180532_mysql_db";
        String driver = "com.mysql.jdbc.Driver";
        String userName = "180532_mysql_db";
        String password = "kaktus01";

        try {
            Class.forName("com.mysql.jdbc.Driver");
            Connection e = DriverManager.getConnection("jdbc:mysql://93.91.250.135:3306/180532_mysql_db", userName, password);
            this.setDatabase(e);
            this.plugin.getLogger().info("§2Navazano pripojeni k §3MySQL §2Serveru!");
        } catch (SQLException var7) {
            this.plugin.getLogger().critical("Nepodarilo se navazat pripojeni s databazi");
        } catch (ClassNotFoundException var8) {
            this.plugin.getLogger().critical("Nepodarilo se navazat pripojeni s databazi: Trida nenalezena");
        }

    }

    public void registerPlayer(String player) {
        String name = player.toLowerCase().trim();

        try {
            PreparedStatement e = this.getDatabase().prepareStatement("INSERT INTO annihilation ( name, kills, deaths, wins, losses, nexuses, nexusdmg, kits) VALUES ('" + name + "', '" + 0 + "', ' " + 0 + "', ' " + 0 + "', ' " + 0 + "', ' " + 0 + "', ' " + 0 + "', 'civilian|handyman')");
            e.executeUpdate();
        } catch (SQLException var4) {
            var4.printStackTrace();
        }

    }

    public HashMap<String, Object> getPlayer(String player) {
        try {
            PreparedStatement e = this.getDatabase().prepareStatement("SELECT * FROM annihilation WHERE name = \'" + player + "\'");
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

    public void addKill(String player) {
        try {
            PreparedStatement e = this.getDatabase().prepareStatement("UPDATE annihilation SET kills = kills + '" + 1 + "' WHERE name = \'" + player + "\'");
            e.executeUpdate();
        } catch (SQLException var4) {
            var4.printStackTrace();
        }
    }

    public void addDeath(String player) {
        try {
            PreparedStatement e = this.getDatabase().prepareStatement("UPDATE annihilation SET deaths = deaths + '" + 1 + "' WHERE name = \'" + player + "\'");
            e.executeUpdate();
        } catch (SQLException var4) {
            var4.printStackTrace();
        }
    }

    public void addWin(String player) {
        try {
            PreparedStatement e = this.getDatabase().prepareStatement("UPDATE annihilation SET wins = wins + '" + 1 + "' WHERE name = \'" + player + "\'");
            e.executeUpdate();
        } catch (SQLException var4) {
            var4.printStackTrace();
        }
    }

    public void addLoss(String player) {
        try {
            PreparedStatement e = this.getDatabase().prepareStatement("UPDATE annihilation SET losses = losses + '" + 1 + "' WHERE name = \'" + player + "\'");
            e.executeUpdate();
        } catch (SQLException var4) {
            var4.printStackTrace();
        }
    }

    public void addNexus(String player) {
        try {
            PreparedStatement e = this.getDatabase().prepareStatement("UPDATE annihilation SET nexuses = nexuses + '" + 1 + "' WHERE name = \'" + player + "\'");
            e.executeUpdate();
        } catch (SQLException var4) {
            var4.printStackTrace();
        }
    }

    public void addNexusDmg(String player) {
        try {
            PreparedStatement e = this.getDatabase().prepareStatement("UPDATE annihilation SET nexusdmg = nexusdmg + '" + 1 + "' WHERE name = \'" + player + "\'");
            e.executeUpdate();
        } catch (SQLException var4) {
            var4.printStackTrace();
        }
    }

    public int getKills(String p) {
        return (int) this.getPlayer(p).get("kills");
    }

    public int getDeaths(String p) {
        return (int) this.getPlayer(p).get("deaths");
    }

    public int getWins(String p) {
        return (int) this.getPlayer(p).get("wins");
    }

    public int getLosses(String p) {
        return (int) this.getPlayer(p).get("losses");
    }

    public int getNexuses(String p) {
        return (int) this.getPlayer(p).get("nexuses");
    }

    public int getNexusDmg(String p) {
        return (int) this.getPlayer(p).get("nexusdmg");
    }

    public void addKit(String p, String kit) {
        try {
            PreparedStatement e = this.getDatabase().prepareStatement("UPDATE annihilation SET kits = " + this.getKits(p) + "|" + kit + " WHERE name = \'" + p + "\'");
            e.executeUpdate();
        } catch (SQLException var4) {
            var4.printStackTrace();
        }
    }

    public String getKits(String p) {
        return (String) this.getPlayer(p).get("kits");
    }
}