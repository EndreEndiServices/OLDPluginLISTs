//
// Source code recreated from a .class file by IntelliJ IDEA
// (powered by Fernflower decompiler)
//

package MTCore;

import MTCore.MTCore;
import cn.nukkit.Player;
import cn.nukkit.utils.TextFormat;

import java.math.BigInteger;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.util.UUID;

public class Auth {
    /*public MTCore plugin;

    public Auth(MTCore plugin) {
        this.plugin = plugin;
    }

    public void register(Player p, String heslo) {
        if(this.isRegistered(p)) {
            p.sendMessage(MTCore.getPrefix() + "§6" + "You are already registered");
        } else if(this.plugin.isAuthed(p)) {
            p.sendMessage(MTCore.getPrefix() + "§6" + "You are already logged in");
        } else if(heslo.length() >= 4 && heslo.length() <= 20) {
            this.plugin.mysqlmgr.setPassword(p.getName(), heslo);
            this.plugin.mysqlmgr.setIP(p.getName(), p.getAddress());
            this.plugin.mysqlmgr.setUUID(p.getName(), p.getUniqueId().toString());
            this.plugin.unauthed.remove(p.getName().toLowerCase());
            //this.plugin.checkRank(p);
            p.removeAllEffects();
            p.sendMessage(MTCore.getPrefix() + "§a" + "You have been successfully registered");
        } else {
            p.sendMessage(MTCore.getPrefix() + "§c" + "Password lenght must be between 4 and 20 characters");
        }
    }

    public void login(Player p, String heslo) {
        if(!this.isRegistered(p)) {
            p.sendMessage(MTCore.getPrefix() + "§c" + "You are not registered\n§cUse /register [password] [password]");
        } else if(this.plugin.isAuthed(p)) {
            p.sendMessage(MTCore.getPrefix() + "§c" + "You are already logged in");
        } else {
            String pass = this.plugin.mysqlmgr.getPassword(p.getName());
            if(!heslo.equals(pass)) {
                p.sendMessage(MTCore.getPrefix() + "§c" + "Wrong password");
                p.sendMessage(MTCore.getPrefix() + TextFormat.GREEN + "Try joining to another non experimental server and use /changepassword");
            } else {
                this.plugin.mysqlmgr.setIP(p.getName(), p.getAddress());
                this.plugin.mysqlmgr.setUUID(p.getName(), p.getUniqueId().toString());
                this.plugin.unauthed.remove(p.getName().toLowerCase());
                this.plugin.checkRank(p);
                p.removeAllEffects();
                p.sendMessage(MTCore.getPrefix() + "§a" + "You have been successfully logged in");
            }
        }
    }

    public void changePassword(Player p, String old, String neww) {
        String pass = this.plugin.mysqlmgr.getPassword(p.getName());
        if(!pass.equals(old)) {
            p.sendMessage(MTCore.getPrefix() + "§c" + "Wrong password");
        } else if(neww.length() >= 4 && neww.length() <= 20) {
            this.plugin.mysqlmgr.setPassword(p.getName(), neww);
            p.sendMessage(MTCore.getPrefix() + "§a" + "Your password has been successfully changed");
        } else {
            p.sendMessage(MTCore.getPrefix() + "§c" + "Password lenght must be between 4 and 20 characters");
        }
    }

    public boolean isRegistered(Player p) {
        String heslo = this.plugin.mysqlmgr.getPassword(p.getName());
        return heslo != null && heslo.length() >= 4;
    }

    public void checkLogin(Player p) {
        if(!this.plugin.isAuthed(p)) {
            if(!this.isRegistered(p)) {
                p.sendMessage("§7==========================================\n§e>> Welcome to §6Minetox§e, " + p.getDisplayName() + "\n" + "§e" + ">> The account has not been registered\n" + "§e" + ">> You can register him with " + "§c" + "/register\n" + "§7" + "==========================================");
            } else {
                String ip = p.getAddress();
                UUID id = p.getUniqueId();
                if(this.plugin.mysqlmgr.getIP(p.getName()).equals(ip) && this.plugin.mysqlmgr.getUUID(p.getName()).equals(id.toString())) {
                    p.removeAllEffects();
                    this.plugin.unauthed.remove(p.getName().toLowerCase());
                    this.plugin.checkRank(p);
                    p.sendMessage("§7==========================================\n§e>> Welcome to §6Minetox§e, " + p.getDisplayName() + "\n" + "§7" + "==========================================");
                } else {
                    p.sendMessage("§7==========================================\n§e>> Welcome to §6Minetox§e, " + p.getDisplayName() + "\n" + "§e" + ">> This account is already registered" + "\n" + "§e" + ">> Login with " + "§c" + "/login " + "§e" + "or change" + "\n" + "§e" + ">> your name in the MCPE settings." + "\n" + "§7" + "==========================================");
                }
            }
        }
    }

    public String hash(String pass) {
        try {
            MessageDigest e = MessageDigest.getInstance("SHA1");
            e.reset();
            e.update(pass.getBytes());
            String hash = String.format("%064x", new Object[]{new BigInteger(1, e.digest())});
            return hash;
        } catch (NoSuchAlgorithmException var4) {
            var4.printStackTrace(System.err);
            return null;
        }
    }*/
}
