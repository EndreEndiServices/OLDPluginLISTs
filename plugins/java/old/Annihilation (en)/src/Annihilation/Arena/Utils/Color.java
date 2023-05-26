package Annihilation.Arena.Utils;

public final class Color {
    private static final int BIT_MASK = 0xff;

    public static final int WHITE = 0xFFFFFF;
    public static final int SILVER = 0xC0C0C0;
    public static final int GRAY = 0x808080;
    public static final int BLACK = 0x000000;
    public static final int RED = 0xFF0000;
    public static final int MAROON = 0x800000;
    public static final int YELLOW = 0xFFFF00;
    public static final int OLIVE = 0x808000;
    public static final int LIME = 0x00FF00;
    public static final int GREEN = 0x008000;
    public static final int AQUA = 0x00FFFF;
    public static final int TEAL = 0x008080;
    public static final int BLUE = 0x0000FF;
    public static final int NAVY = 0x000080;
    public static final int FUCHSIA = 0xFF00FF;
    public static final int PURPLE = 0x800080;
    public static final int ORANGE = 0xFFA500;

    public static int toDecimal(int bgr) {
        int r = bgr >> 16 & BIT_MASK;
        int g = bgr >> 8 & BIT_MASK;
        int b = bgr >> 0 & BIT_MASK;

        return hex2decimal(toHex(r, g, b));
    }

    private static String toHex(int r, int g, int b) {
        return "#" + toBrowserHexValue(r) + toBrowserHexValue(g) + toBrowserHexValue(b);
    }

    private static String toBrowserHexValue(int number) {
        StringBuilder builder = new StringBuilder(Integer.toHexString(number & 0xff));
        while (builder.length() < 2) {
            builder.append("0");
        }
        return builder.toString().toUpperCase();
    }

    private static int hex2decimal(String s) {
        String digits = "0123456789ABCDEF";
        s = s.toUpperCase();
        int val = 0;
        for (int i = 0; i < s.length(); i++) {
            char c = s.charAt(i);
            int d = digits.indexOf(c);
            val = 16 * val + d;
        }
        return val;
    }
}
