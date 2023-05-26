package GTCore.Mysql;

import cn.nukkit.scheduler.AsyncTask;
import com.gmail.holubvojtech.gthttpapi.api.GTHttpApi;

public class QuitTask extends AsyncTask {

    private String player;
    private int time;

    public QuitTask(String player, int time) {
        this.player = player;
        this.time = time;
    }

    public void onRun() {
        int diff = (int) (System.currentTimeMillis() / 1000) - time;

        //System.out.println(diff);

        if (diff > 0) {
            GTHttpApi.getApi().addPlayTime(player, diff / 60);
        }
    }
}
