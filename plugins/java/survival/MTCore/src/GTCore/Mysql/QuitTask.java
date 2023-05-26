package GTCore.Mysql;

import cn.nukkit.scheduler.AsyncTask;
import com.gmail.holubvojtech.gthttpapi.api.GTHttpApi;

public class QuitTask extends AsyncTask {

    private String player;
    private int time;
    private int money = 0;

    public QuitTask(String player, int time, int money) {
        this.player = player;
        this.time = time;
        this.money = money;
    }

    @Override
    public void onRun() {
        int diff = (int) (System.currentTimeMillis() / 1000) - time;

        //System.out.println(diff);

        if (diff > 0) {
            GTHttpApi.getApi().addPlayTime(player, diff / 60);
        }

        GTHttpApi.getApi().addTokens(player, money);
    }
}
