<?php
namespace Sergey_Dertan\SClearLagg\Entity;

use pocketmine\entity\Creature;
use pocketmine\entity\Human;
use pocketmine\Server;
use Sergey_Dertan\SClearLagg\SClearLaggMainFolder\SClearLaggMain;

/*
 * Класс в котором будет происходить удаление объектов
 */

/**
 * Class EntityManager
 * @package Sergey_Dertan\SClearLagg\Entity
 */
class EntityManager
{
    function __construct(SClearLaggMain $main)
    {
        $this->main = $main;
    }

    /**
     * @return SClearLaggMain
     */
    function getMain()
    {
        return $this->main;
    }
    /**
     * @return int
     */
    function removeEntities()
    {
        $entitiesCount = 0; //Кол-во удаленных объектов
        foreach (Server::getInstance()->getLevels() as $level) { //Будут удалены все объекты во всеъ мирах
            foreach ($level->getEntities() as $entity) { //Перебор всех объектов
                if (!$entity instanceof Creature and !$entity instanceof Human) { //Проверка на то,не является ли объект экземпляром игрока или моба
                    $entity->close(); //Удалние объекта
                    $entitiesCount++; //Прибавляем 1 к кол-ву удаленных объектов
                }
            }
        }
        return $entitiesCount; //Возвращаем кол-во удаленных объектов
    }

    /**
     * @return int
     */
    function removeMobs()
    {
        $mobsCount = 0;
        foreach (Server::getInstance()->getLevels() as $level) {
            foreach ($level->getEntities() as $entity) {
                if ($entity instanceof Creature && !($entity instanceof Human)) {
                    $entity->kill();
                    $mobsCount++;
                }
            }
        }
        return $mobsCount;
    }
}