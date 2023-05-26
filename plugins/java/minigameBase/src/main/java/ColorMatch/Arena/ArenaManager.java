package main.java.ColorMatch.Arena;

import cn.nukkit.Player;
import cn.nukkit.blockentity.BlockEntitySign;
import cn.nukkit.potion.Effect;
import cn.nukkit.utils.TextFormat;

public abstract class ArenaManager extends Configuration {

    protected Arena plugin;

    public boolean inArena(Player p) {
        return plugin.players.containsKey(p.getName().toLowerCase());
    }

    public boolean isSpectator(Player p) {
        return plugin.spectators.containsKey(p.getName().toLowerCase());
    }

    public boolean checkLobby() {
        return plugin.players.size() >= 10;
    }

    public void onDeath(Player p) {

    }

    public void resetPlayer(Player p) {
        p.removeAllEffects();
        p.getInventory().clearAll();
    }

    public void messageArenaPlayers(String msg) {
        for (Player p : plugin.players.values()) {
            p.sendMessage(msg);
        }
    }

    /*public boolean checkAlive(){
        return plugin.players.size() > 0;
    }*/

    public void updateJoinSign() {
        BlockEntitySign sign = (BlockEntitySign) level.getBlockEntity(getJoinSign());

        if (sign == null) {
            return;
        }

        sign.setText();
    }

    public void checkAlive() {
        if (plugin.players.size() <= 1) {
            plugin.endGame();
        }
    }
}
