package GTCore.Mysql;

import GTCore.MTCore;

public class SetRankQuery extends AsyncQuery {

    private String rank;
    private Integer time;

    public SetRankQuery(MTCore plugin, String player, String rank, Integer time) {
        this.player = player;
        this.time = time;

        //plugin.getServer().getScheduler().scheduleAsyncTask(this);
    }

    /*@Override
    public void onQuery(HashMap<String, Object> data) {
        if (data == null) {
            this.registerPlayer(this.player);
        }

        this.setRank(this.player, this.rank);
        this.setTime(this.player, this.time);
    }*/
}