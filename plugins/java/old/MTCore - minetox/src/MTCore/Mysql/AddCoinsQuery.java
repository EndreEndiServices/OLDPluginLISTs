package MTCore.Mysql;


import MTCore.MTCore;

import java.util.HashMap;

public class AddCoinsQuery extends AsyncQuery{

    private int coins;

    public AddCoinsQuery(MTCore plugin, String player, int coins){
        this.player = player;
        this.coins = coins;

        plugin.getServer().getScheduler().scheduleAsyncTask(this);
    }

    @Override
    public void onQuery(HashMap<String, Object> data){
        if(data != null){
            this.addTokens(this.player, this.coins);
        }
    }

}