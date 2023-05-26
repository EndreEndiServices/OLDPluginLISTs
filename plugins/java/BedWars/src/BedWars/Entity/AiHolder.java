package BedWars.Entity;

import cn.nukkit.block.Block;
import cn.nukkit.block.BlockDoor;
import cn.nukkit.level.Level;
import cn.nukkit.math.Vector3;
import jline.internal.Nullable;

public class AiHolder {

    public static Vector3 v = new Vector3();

    public static final double UP = 0;
    public static final double GO = 1;
    public static final double DOWN = 2;
    public static final double FALL = 3;
    public static final double HALF_GO = 4;
    public static final double UP2 = 5;
    public static final double UP_GO = 6;

    public static Double ifjump(Level level, Vector3 v3) {
        return ifjump(level, v3, false, false);
    }

    public static Double ifjump(Level level, Vector3 v3, boolean hate) {
        return ifjump(level, v3, hate, false);
    }

    @Nullable
    public static Double ifjump(Level level, Vector3 v3, boolean hate, boolean reason) {  //boybook Y轴算法核心函数
        int x = v3.getFloorX();
        int y = v3.getFloorY();
        int z = v3.getFloorZ();
        //echo ($y." ");
        if (whatBlock(level, v.setComponents(x, y, z)) == BLOCK_AIR) {
            //echo "前方空气 ";
            if (whatBlock(level, v.setComponents(x, y - 1, z)) == BLOCK_BLOCK || whatBlock(level, v.setComponents(x, y - 1, z)) == BLOCK_CLIMB) {  //方块
                //echo "考虑向前 ";
                if (whatBlock(level, v.setComponents(x, y + 1, z)) == BLOCK_BLOCK || whatBlock(level, v.setComponents(x, y + 1, z)) == BLOCK_HALF | whatBlock(level, v.setComponents(x, y + 1, z)) == BLOCK_HIGH) {  //上方一格被堵住了
                    //echo "上方卡住 \n";
                    if (reason) return UP;
                    return null;  //上方卡住
                } else {
                    //echo "GO向前走 \n";
                    if (reason) return GO;
                    return (double) y;  //向前走
                }
            }/*elseif ($this->whatBlock($level,new Vector3($x,$y-1,$z)) == "water") {  //水
            //echo "下水游泳 \n";
				if ($reason) return 'swim';
				return $y-1;  //降低一格向前走（下水游泳）
			}*/ else if (whatBlock(level, v.setComponents(x, y - 1, z)) == BLOCK_HALF) {  //半砖
                //echo "下到半砖 \n";
                if (reason) return BLOCK_HALF;
                return y - 0.5;  //向下跳0.5格
            } else if (whatBlock(level, v.setComponents(x, y - 1, z)) == BLOCK_LAVA) {  //岩浆
                //echo "前方岩浆 \n";
                if (reason) return BLOCK_LAVA;
                return null;  //前方岩浆
            } else if (whatBlock(level, v.setComponents(x, y - 1, z)) == BLOCK_AIR) {  //空气
                //echo "考虑向下跳 ";
                if (whatBlock(level, v.setComponents(x, y - 2, z)) == BLOCK_BLOCK) {
                    //echo "GO向下跳 \n";
                    if (reason) return DOWN;
                    return (double) y - 1;  //向下跳
                } else { //前方悬崖
                    //echo "前方悬崖 \n";
                    if (reason) return FALL;
                    /*	if ($hate === false) {
                            return false;
						}
						else {
							return $y-1;  //向下跳
						}*/
                }
            }
        }/*
        elseif ($this->whatBlock($level,new Vector3($x,$y,$z)) == "water") {  //水
		//echo "正在水中";
			if ($this->whatBlock($level,new Vector3($x,$y+1,$z)) == "water") {  //上面还是水
			//echo "向上游 \n";
				if ($reason) return 'inwater';
				return $y+1;  //向上游，防溺水
			}
			elseif ($this->whatBlock($level,new Vector3($x,$y+1,$z)) == "block" or $this->whatBlock($level,new Vector3($x,$y+1,$z)) == "half") {  //上方一格被堵住了
				if ($this->whatBlock($level,new Vector3($x,$y-1,$z)) == "block" or $this->whatBlock($level,new Vector3($x,$y-1,$z)) == "half") {  //下方一格被也堵住了
				//echo "上下都被卡住 \n";
					if ($reason) return 'up!_down!';
					return false;  //上下都被卡住
				}
				else {
				//echo "向下游 \n";
					if ($reason) return 'up!';
					return $y-1;  //向下游，防卡住
				}
			}
			else {
			//echo "游泳ing... \n";
				return $y;  //向前游
			}
		}*/ else if (whatBlock(level, v.setComponents(x, y, z)) == BLOCK_HALF) {  //半砖
            //echo "前方半砖 \n";
            if (whatBlock(level, v.setComponents(x, y + 1, z)) == BLOCK_BLOCK || whatBlock(level, v.setComponents(x, y + 1, z)) == BLOCK_HALF || whatBlock(level, v.setComponents(x, y + 1, z)) == BLOCK_HIGH) {  //上方一格被堵住了
                //return false;  //上方卡住
            } else {
                if (reason) return HALF_GO;
                return y + 0.5;
            }
        } else if (whatBlock(level, v.setComponents(x, y, z)) == BLOCK_LAVA) {  //岩浆
            //echo "前方岩浆 \n";
            if (reason) return BLOCK_LAVA;
            return null;
        } else if (whatBlock(level, v.setComponents(x, y, z)) == BLOCK_HIGH) {  //1.5格高方块
            //echo "前方栅栏 \n";
            if (reason) return BLOCK_HIGH;
            return null;
        } else if (whatBlock(level, v.setComponents(x, y, z)) == BLOCK_CLIMB) {  //梯子
            //echo "前方梯子 \n";
            //return $y;
            if (reason) return BLOCK_CLIMB;
            if (hate) {
                return y + 0.7;
            } else {
                return y + 0.5;
            }
        } else {  //考虑向上
            //echo "考虑向上 ";
            if (whatBlock(level, v.setComponents(x, y + 1, z)) != BLOCK_AIR) {  //前方是面墙
                //echo "前方是墙 \n";
                if (reason) return BLOCK_WALL;
                return null;
            } else {
                if (whatBlock(level, v.setComponents(x, y + 2, z)) == BLOCK_BLOCK || whatBlock(level, v.setComponents(x, y + 2, z)) == BLOCK_HALF || whatBlock(level, v.setComponents(x, y + 2, z)) == BLOCK_HIGH) {  //上方两格被堵住了
                    //echo "2格处被堵 \n";
                    if (reason) return UP2;
                    return null;
                } else {
                    //echo "GO向上跳 \n";
                    if (reason) return UP_GO;
                    return (double) y + 1;  //向上跳
                }
            }
        }
        return null;
    }

    public static double getyaw(double mx, double mz) {  //根据motion计算转向角度

        double yaw;

        if (mz == 0) {  //斜率不存在
            if (mx < 0) {
                yaw = -90;
            } else {
                yaw = 90;
            }
        } else {  //存在斜率
            if (mx >= 0 && mz > 0) {  //第一象限
                yaw = (Math.atan(mx / mz) * 180 / Math.PI);
            } else if (mx >= 0 && mz < 0) {  //第二象限
                yaw = 180 - (Math.atan(mx / Math.abs(mz)) * 180 / Math.PI);
            } else if (mx < 0 && mz < 0) {  //第三象限
                yaw = -(180 - (Math.atan(mx / mz) * 180 / Math.PI));
            } else if (mx < 0 && mz > 0) {  //第四象限
                yaw = -(Math.atan(Math.abs(mx) / mz) * 180 / Math.PI);
            } else {
                yaw = 0;
            }
        }
        yaw = -yaw;
        return yaw;
    }

    public static double getpitch(Vector3 from, Vector3 to) {
        double distance = from.distance(to);
        double height = to.y - from.y;
        if (height > 0) {
            return -(Math.asin(height / distance) * 180 / Math.PI);
        } else if (height < 0) {
            return (Math.asin(-height / distance) * 180 / Math.PI);
        } else {
            return 0;
        }
    }

    public static final double BLOCK_AIR = 0;
    public static final double BLOCK_WATER = 1;
    public static final double BLOCK_LAVA = 2;
    public static final double BLOCK_BLOCK = 3;
    public static final double BLOCK_HALF = 4;
    public static final double BLOCK_HIGH = 5;
    public static final double BLOCK_CLIMB = 6;
    public static final double BLOCK_WALL = 7;

    public static Double whatBlock(Level level, Vector3 v3) {  //boybook的y轴判断法 核心 什么方块？
        Block block = level.getBlock(v3);
        int id = block.getId();
        int damage = block.getDamage();
        switch (id) {
            case 0:
            case 6:
            case 27:
            case 30:
            case 31:
            case 37:
            case 38:
            case 39:
            case 40:
            case 50:
            case 51:
            case 63:
            case 66:
            case 68:
            case 78:
            case 111:
            case 141:
            case 142:
            case 171:
            case 175:
            case 244:
            case 323:
            case 70:
            case 72:
            case 147:
            case 148:
                return BLOCK_AIR;
            case 8:
            case 9:
                return BLOCK_WATER;
            case 10:
            case 11:
                return BLOCK_LAVA;
            case 44:
            case 158:
                //半砖
                if (damage >= 8) {
                    return BLOCK_BLOCK;
                } else {
                    return BLOCK_HALF;
                }
            case 64:
                if (((BlockDoor) block).isOpen()) {
                    return BLOCK_AIR;
                } else {
                    return BLOCK_BLOCK;
                }
            case 85:
            case 107:
            case 139:
                return BLOCK_HIGH;
            case 65:
            case 106:
                return BLOCK_CLIMB;
            default:
                return BLOCK_BLOCK;
        }
    }
}
