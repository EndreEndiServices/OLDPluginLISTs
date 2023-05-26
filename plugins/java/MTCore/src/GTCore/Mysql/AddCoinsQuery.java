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
        if(!getApi().addTokens(getPlayer(), coins)){
            System.out.println("Failed to add coins");
        }

        getApi().addExp(getPlayer(), coins);
    }

}