package MTCore.Mysql;

import MTCore.MTCore;
import cn.nukkit.item.Item;
import cn.nukkit.Player;
import cn.nukkit.Server;
import cn.nukkit.utils.TextFormat;

import java.util.HashMap;

public class LoginQuery extends AsyncQuery
{

    private String password;
    private String ip;
    private String uuid;

    public LoginQuery(MTCore plugin, String player, String pass, String ip, String uuid)
    {
        this.password = pass;
        this.ip = ip;
        this.uuid = uuid;
        this.player = player;

        plugin.getServer().getScheduler().scheduleAsyncTask(this);
    }

    private String msg = "";
    private boolean success = true;

    @Override
    public void onQuery(HashMap<String, Object> data)
    {
        if (data == null) {

            success = false;
            msg = MTCore.getPrefix() + TextFormat.RED + "You are not registered\n§cUse /register [password] [password]";
        } else {

            String pass = (String) data.get("heslo");

            if (!this.password.equals(pass)) {

                msg = MTCore.getPrefix() + TextFormat.RED + "Wrong password";
                success = false;
            } else {

                msg = MTCore.getPrefix() + TextFormat.GREEN + "You have been successfully logged in";

                this.setIP(this.player, this.ip);
                this.setUUID(this.player, this.uuid);
            }
        }
    }

    @Override
    public void onCompletion(Server server)
    {
        Player p = server.getPlayerExact(this.player);

        if (p == null || !p.isOnline()) {
            return;
        }

        MTCore plugin = MTCore.getInstance();

        if (plugin == null || !plugin.isEnabled()) {
            return;
        }

        if (success) {
            if(plugin.unauthed.containsKey(p.getName().toLowerCase())){
                plugin.unauthed.remove(p.getName().toLowerCase());
            }

            p.removeAllEffects();

            if(p.getInventory() != null) {
                    p.getInventory().clearAll();
                    p.getInventory().setItem(0, Item.get(Item.CLOCK, 0, 1)/*.setCustomName("§r§eDisplay players")*/);
                    p.getInventory().setItem(1, Item.get(Item.GOLD_INGOT, 0, 1));
                p.getInventory().setHotbarSlotIndex(0, 0);
                p.getInventory().setHotbarSlotIndex(1, 1);
                    p.getInventory().sendContents(p);
            }
        }

        p.sendMessage(msg);
    }

}