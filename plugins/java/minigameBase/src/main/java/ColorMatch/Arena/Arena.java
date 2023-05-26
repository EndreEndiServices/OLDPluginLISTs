package main.java.ColorMatch.Arena;

import cn.nukkit.Player;
import cn.nukkit.math.Vector3;
import cn.nukkit.utils.Config;
import cn.nukkit.utils.TextFormat;
import lombok.Getter;
import main.java.ColorMatch.ColorMatch;
import cn.nukkit.event.Listener;

import java.util.HashMap;

public class Arena extends ArenaManager implements Listener {

    public static final int LOBBY = 0;
    public static final int GAME = 1;

    @Getter
    protected int phase = LOBBY;

    @Getter
    protected boolean enabled = false;

    @Getter
    protected HashMap<String, Player> players = new HashMap<>();

    @Getter
    protected HashMap<String, Player> spectators = new HashMap<>();

    protected ColorMatch plugin;
    protected ArenaListener listener;
    protected ArenaSchedule scheduler;

    public HashMap<String, Vector3> data = new HashMap<>();

    @Getter
    protected String name = "";

    public Arena(ColorMatch plugin, String name, HashMap<String, Vector3> data) {
        this.name = name;
        super.plugin = this;
        this.data = data;
        this.listener = new ArenaListener(this);
        this.scheduler = new ArenaSchedule(this);
        this.plugin = plugin;
        this.plugin.getServer().getPluginManager().registerEvents(listener, plugin);
    }

    public void start() {
        start(false);
    }

    public void start(boolean force) {
        if (players.size() < 10 && !force) {
            return;
        }

        for (Player p : players.values()) {
            p.teleport(start);
            p.sendMessage(ColorMatch.getPrefix() + TextFormat.AQUA + "Game started!");
        }

        this.phase = GAME;
        updateJoinSign();
    }

    public void stop() {
        for (Player p : players.values()) {
            removeFromArena(p);
        }

        this.phase = LOBBY;
        updateJoinSign();
    }

    public void endGame() {

    }

    public void addToArena(Player p) {
        if (phase == GAME) {
            this.addSpectator(p);
            return;
        }

        if (players.size() >= 10 && !p.hasPermission("colormatch.joinfullarena")) {
            p.sendMessage(ColorMatch.getPrefix() + TextFormat.RED + "This game is full");
            return;
        }

        String msg = ColorMatch.getPrefix() + TextFormat.YELLOW + p.getDisplayName() + TextFormat.GRAY + " has joined (" + (TextFormat.BLUE + players.size() + 1) + TextFormat.YELLOW + "/" + TextFormat.BLUE + 10;
        for (Player pl : players.values()) {
            pl.sendMessage(msg);
        }

        this.players.put(p.getName().toLowerCase(), p);
        updateJoinSign();

        p.teleport(getStartPos());
        p.sendMessage(ColorMatch.getPrefix() + TextFormat.GRAY + "Joining to arena " + TextFormat.YELLOW + this.name + TextFormat.GRAY + "...");

        checkLobby();
    }

    public void removeFromArena(Player p) {
        this.players.remove(p.getName().toLowerCase());
        updateJoinSign();
        resetPlayer(p);

        String msg = ColorMatch.getPrefix() + TextFormat.YELLOW + p.getDisplayName() + TextFormat.GRAY + " has left (" + (TextFormat.BLUE + players.size() + 1) + TextFormat.YELLOW + "/" + TextFormat.BLUE + 10;
        for (Player pl : players.values()) {
            pl.sendMessage(msg);
        }

        if (p.isOnline()) {
            p.sendMessage(ColorMatch.getPrefix() + TextFormat.GRAY + "Leaving arena...");
        }

        p.teleport(plugin.getMtcore().lobby);
    }

    public void addSpectator(Player p) {
        p.teleport(getSpectatorPos());
        p.sendMessage(ColorMatch.getPrefix() + TextFormat.GRAY + "Joining to as spectator...");
        this.spectators.put(p.getName().toLowerCase(), p);
    }

    public void removeSpectator(Player p) {
        this.spectators.remove(p.getName().toLowerCase());
        p.teleport(plugin.getMtcore().lobby);
    }
}
