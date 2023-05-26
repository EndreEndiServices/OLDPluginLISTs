package BedWars.Shop;

import cn.nukkit.block.BlockWool;
import cn.nukkit.item.Item;
import cn.nukkit.item.ItemBlock;
import lombok.Getter;

import java.util.LinkedHashMap;
import java.util.Map;

public class ItemWindow extends Window {

    @Getter
    private boolean main = false;

    private Window previousWindow;

    public Map<Integer, Window> windows = new LinkedHashMap<>();

    public ItemWindow() {
        this(false);
    }

    public ItemWindow(boolean main) {
        this.main = main;
    }

    public void setWindows(Map<Item, Window> list) {
        setWindows(list, null);
    }

    public void setWindows(Map<Item, Window> list, Window previousWindow) {
        int i = 0;

        for (Map.Entry<Item, Window> entry : list.entrySet()) {
            Item item = entry.getKey();
            Window win = entry.getValue();

            setItem(i, item);
            windows.put(i, win);
            i++;
        }

        if (!isMain()) {
            Item item = new ItemBlock(new BlockWool(), 14);
            setItem(getSize() - 1, item);
            windows.put(getSize() - 1, previousWindow);
        }

        this.previousWindow = previousWindow;
        setSize(windows.size());
    }

    @Override
    public Window getWindow(int slot) {
        return windows.get(slot);
    }
}
