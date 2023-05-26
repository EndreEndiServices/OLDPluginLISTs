<?php

namespace crashbang;

class Skills {
    const ZOMBIE = 0;
    const EARTHQUAKE = 1;
    const CONTRACT = 2; // TODO
    const PLAGUE = 3;
    const CREEPER = 4;
    const BERSERKER = 5;
    const VAMPIRE = 6;
    const ASSASSIN = 7;
    const HEAL = 8;
    const STEALTH = 9;
    const BACKSTAB = 10; // TODO
    const EYE_FOR_EYE = 11;
    const IGNITE = 12;
    const STORM = 13;
    const UPGRADE = 14;
    const EQUALITY = 15;
    const REBORN = 16;
    const INVINCIBLE = 17;
    const BIG_EATER = 18;
    const TRACE = 19;
    const POISONED_DAGGER = 20;

    public static $rawdesc = <<<EOT
좀비 - 15초간 구속 II, 10초간 재생 IV를 부여합니다. (이동 속도 -30%, 초당 피 1.66칸 재생)
지진 - 가장 가까운 플레이어 둘에게 피해를 12 줍니다.
계약 - 플레이어를 선택합니다. 자신이 죽으면 그 플레이어를 즉사시킵니다. 그 플레이어가 먼저 죽으면 피해를 10 입습니다.
역병 - 자신을 제외한 모든 플레이어에게 7초간 멀미와 구속 I를 부여합니다.
크리퍼 - 거리 5m 이내의 모든 플레이어(자신 포함)에게 피해를 15 줍니다.
광전사(패시브) - 잃은 피 4당 추가 피해를 2 줍니다.
뱀파이어(패시브) - 상대를 때릴 때마다 체력을 2 회복합니다.
암살 - 30% 확률로 선택한 플레이어를 즉사시킵니다. 70% 확률로 양쪽 모두 피해를 15 입습니다.
회복 - 3초간 회복 V를 얻습니다.(3초에 걸쳐 9 회복)
은신 - 5초간 투명해집니다. 상대를 때리면 즉시 풀립니다.
백스탭(패시브) 상대의 뒤(머리 시야 기준 90~270도)에서 공격할 경우 피해를 4 추가로 입힙니다.
눈에는 눈 - 3번만 받은 피해의 70%를 되돌려줍니다.
점화 - 모든 플레이어가 7초간 불에 탑니다.
콩콩 - 22칸 이내의 모든 플레이어에게 피해를 2+2 줍니다. 채팅에 폭풍저그가 나옵니다.
강화(패시브) - 30초마다 영구적으로 상대를 때릴 때 피해를 1 추가합니다. 최대 5까지 올라갑니다. 사망 시 초기화됩니다.
평등 - 모든 플레이어의 체력을 8로 만듭니다.
환생 - 즉시 자살합니다. 부활할 때 15의 체력만큼을 추가로 가지고 부활합니다.
무적 - 5초간 모든 공격을 무시합니다. 자신도 피해를 입힐 수 없습니다.
식신(패시브) - 케이크 사용 쿨타임을 1.5초로 줄입니다.
추적 - 7초 후 선택한 플레이어의 위치로 텔레포트합니다.
독 묻은 검(자동) - 3초마다 처음 때리는 공격은 피해를 5 추가로 줍니다.
EOT;

    public static $desc, $cooldown, $passive;

    public static function init() {
        self::$desc = explode("\n", self::$rawdesc);
        self::$cooldown = array(
            30, // 좀비 0
            30, // 지진 1
            60,  // 계약 2
            60, // 역병 3
            60, // 크리퍼 4
            0,  // 광전사(패시브) 5
            0,  // 뱀파이어(패시브) 6
            60, // 암살 7
            90, // 회복 8
            60, // 은신 9
            0,  // 백스탭(패시브) 10
            60, // 눈에는 눈 11
            90, // 점화 12
            22, // 콩콩 13
            0,  // 강화(패시브) 14
            90, // 평등 15
            60, // 환생 16
            60, // 무적 17
            0,  // 식신(패시브) 18
            60, // 추적 19
            3,  // 독 묻은 검(패시브) 20
        );
        self::$passive = array(
            self::ZOMBIE => false,
            self::EARTHQUAKE => false,
            self::CONTRACT => false,
            self::PLAGUE => false,
            self::CREEPER => false,
            self::BERSERKER => true,
            self::VAMPIRE => true,
            self::ASSASSIN => false,
            self::HEAL => false,
            self::STEALTH => false,
            self::BACKSTAB => true,
            self::EYE_FOR_EYE => false,
            self::IGNITE => false,
            self::STORM => false,
            self::UPGRADE => true,
            self::EQUALITY => false,
            self::REBORN => false,
            self::INVINCIBLE => false,
            self::BIG_EATER => true,
            self::TRACE => false,
            self::POISONED_DAGGER => false,
        );
    }
}
