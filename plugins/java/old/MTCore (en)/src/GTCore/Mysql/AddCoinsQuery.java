package GTCore.Mysql;


import GTCore.MTCore;

public class AddCoinsQuery extends AsyncQuery {

    private int coins;

    public AddCoinsQuery(MTCore plugin, String player, int coins) {
        this.player = player;
        this.coins = coins;

        plugin.getServer().getScheduler().scheduleAsyncTask(this);
    }

    @Override
    public void onRun() {
        getApi().addTokens(getPlayer(), coins);
    }

}