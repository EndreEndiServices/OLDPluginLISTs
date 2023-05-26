package BedWars.MySQL;

import BedWars.Object.BedWarsData;
import cn.nukkit.Player;
import cn.nukkit.scheduler.AsyncTask;
import com.gmail.holubvojtech.gthttpapi.api.GTHttpApi;

import java.util.Collection;

public class NormalQuery extends AsyncTask {

    private int xp = 0;
    private int tokens = 0;
    private String player;

    private Collection<BedWarsData> data = null;

    public NormalQuery(Player player, int xp, int tokens) {
        this(player.getName(), xp, tokens);
    }

    public NormalQuery(Collection<BedWarsData> data) {
        this.data = data;
    }

    public NormalQuery(String player, int xp, int tokens) {
        this.player = player;
        this.xp = xp;
        this.tokens = tokens;
    }

    public void onRun() {
        if (data != null) {
            for (BedWarsData data2 : data) {
                GTHttpApi.getApi().addExp(data2.getPlayer().getName(), data2.points);
            }
            return;
        }

        if (xp > 0) {
            GTHttpApi.getApi().addExp(player, xp);
        }

        if (tokens > 0) {
            GTHttpApi.getApi().addTokens(player, xp);
        }
    }
}
