package GTCore.Mysql;


import GTCore.MTCore;
import cn.nukkit.utils.TextFormat;

public class AddExperienceQuery extends AsyncQuery {

    private int exp;

    public AddExperienceQuery(MTCore plugin, String player, int experience) {
        this.player = player;
        this.exp = experience;

        plugin.getServer().getScheduler().scheduleAsyncTask(this);
    }

    @Override
    public void onRun() {
        if (!getApi().addExp(getPlayer(), exp)) {
            System.out.println(TextFormat.RED + "Failed to add experience");
        }
    }

}