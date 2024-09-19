<?php
include 'DataBase.php';
/**
 * ORM для sqlite3
 * $name_bd - путь к файлу sqlite3.
 */
class ORM_lite
{
    private string $name_bd;
    /**
     * @param string $name_bd путь к файлу sqlite3.
     */
    public function __construct(string $name_bd)
    {
        $this->name_bd = $name_bd;
    }

    /**
     * Функция, ищет по значению ячейки всю строку, и выводит ее.
     * @param string $name_table Название таблицы.
     * @param string|int $name_cell Название ячейки в которой ищем.
     * @param string|int $value_cell Значение по которому ищем.
     * @return array|null Выводим всю найденную строку, либо null.
     */
    public function SELECT_FROM(string $name_table, string|int $name_cell, string|int $value_cell)
    {
        $db = Database_lite::getConnection($this->name_bd);
        $shablon = "SELECT * FROM $name_table WHERE $name_cell = :param1";
        $query = $db->prepare($shablon);
        $query->bindParam(':param1', $value_cell);
        $query->execute();
        $row = $query->fetch(PDO::FETCH_ASSOC);
        return $row;
    }


    /** 
     * Функция выдает все строки в таблице
     * @param string $name_table Название таблицы
     * @return array|null
     * */
    public function SELECT_ALL(string $name_table)
    {
        $db = Database_lite::getConnection($this->name_bd);
        $shablon = "SELECT * FROM $name_table";
        $query = $db->prepare($shablon);
        $query->execute();
        $row = $query->fetchAll(PDO::FETCH_ASSOC);
        return $row;
    }

    public static function Stop()
    {
        Database_lite::closeConnection();
    }
}
