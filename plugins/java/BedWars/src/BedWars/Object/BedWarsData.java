package BedWars.Object;

import BedWars.Arena.Arena;
import GTCore.MTCore;
import GTCore.Object.PlayerData;
import cn.nukkit.Player;
import lombok.Getter;
import lombok.Setter;
//import MTCore.Object.BedWarsData;

public class BedWarsData {

    @Getter
    @Setter
    private Arena arena;

    @Getter
    private Player player;

    @Getter
    @Setter
    private Team team = null;

    @Setter
    private long lastHit = 0;

    @Getter
    @Setter
    private String killer = null;

    @Getter
    @Setter
    private String killerColor = null;

    public int points = 0;

    private PlayerData baseData;

    public BedWarsData(Arena arena, Player p) {
        this.arena = arena;
        this.player = p;
        baseData = MTCore.getInstance().getPlayerData(p);
    }

    public boolean canRespawn() {
        return this.team.hasBed();
    }

    public boolean wasKilled() {
        return System.currentTimeMillis() - lastHit <= 10000;
    }

    public PlayerData getBaseData() {
        return baseData;
    }
}
