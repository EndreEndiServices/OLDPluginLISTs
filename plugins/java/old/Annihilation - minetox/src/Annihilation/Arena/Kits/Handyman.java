package Annihilation.Arena.Kits;

import cn.nukkit.item.Item;

import java.util.Random;

public class Handyman{

    public static Item[] items;

    public static boolean calculateDamage(int phase){
        Random rand = new Random();

        switch(phase){
            case 2:
                int rnd = rand.nextInt(10) + 1;
                if(rnd == 1 || rnd == 2){
                    return true;
                }
                break;
            case 3:
                rnd = rand.nextInt(10) + 1;
                if(rnd == 1){
                    return true;
                }
                break;
            case 4:
                rnd = rand.nextInt(100) + 1;
                if(rnd >= 1 && rnd <= 7){
                    return true;
                }
                break;
            case 5:
                rnd = rand.nextInt(100) + 1;
                if(rnd >= 1 && rnd <= 5){
                    return true;
                }
                break;
        }
        return false;   
    }
}