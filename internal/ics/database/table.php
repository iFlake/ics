<?php
namespace itais\ics\database;

class Table
{
    public $name;

    public function __construct($name)
    {
        global $itais_ics_database_connection;
        
        $prefix = \itais\ics\config\MySQLi::prefix;

        if (!is_string($name)) throw new \itais\ics\exception\ICSException("Expected string for \$name, got " . gettype($name), "native", "DT1");

        $result = $itais_ics_database_connection->query("show tables like '{$prefix}{$name}'");
        $rows = $result->num_rows;

        $result->free();

        if ($rows < 1) throw new \itais\ics\exception\ICSException("Table {$name} does not exist", "native", "D1");
        else if ($rows > 1) throw new \itais\ics\exception\ICSException("Internal database failure", "native", "D2");

        $this->name = $itais_ics_database_connection->escape_string($name);
    }

    
    public static function Create($name, $columns)
    {
        global $itais_ics_database_connection;
        
        $prefix = \itais\ics\config\MySQLi::prefix;

        if (!is_string($name)) throw new \itais\ics\exception\ICSException("Expected string for \$name, got " . gettype($name), "native", "DT2");

        $itais_ics_database_connection->query("create table if not exists {$prefix}{$name} ({$columns})");

        if ($itais_ics_database_connection->error) throw new \itais\ics\exception\ICSException("Failed to create table: {$itais_ics_database_connection->error}", "native", "D3");

        return new Table($name);
    }


    private static function StatementValue($value)
    {
        if (is_string($value)) return "'{$value}'";
        else return "{$value}";
    }


    public function Insert($values)
    {
        global $itais_ics_database_connection;

        $prefix = \itais\ics\config\MySQLi::prefix;

        if (!is_array($values)) throw new \itais\ics\exception\ICSException("Expected array for \$values, got " . gettype($values), "native", "DT3");

        $columns_declaration    = "";
        $values_declaration     = "";

        foreach ($values as $column=>$value)
        {
            if ($columns_declaration != "") $columns_declaration .= ", ";
            if ($values_declaration != "") $values_declaration .= ", ";

            $columns_declaration   .= $itais_ics_database_connection->escape_string($column);
            $values_declaration    .= $itais_ics_database_connection->escape_string(self::StatementValue($value));
        }

        $itais_ics_database_connection->query("insert into {$prefix}{$this->name} ({$columns_declaration}) values ({$values_declaration})");

        if ($itais_ics_database_connection->error) throw new \itais\ics\exception\ICSException("Failed to insert into table: {$itais_ics_database_connection->error}", "native", "D4");
    }

    public function Update($values, $where = null)
    {
        global $itais_ics_database_connection, $prefix;
        
        $prefix = \itais\ics\config\MySQLi::prefix;

        if (!is_array($values)) throw new \itais\ics\exception\ICSException("Expected array for \$values, got " . gettype($values), "native", "DT4");
        if (!is_string($where)) throw new \itais\ics\exception\ICSException("Expected string for \$where, got " . gettype($where), "native", "DT5");

        $set_declaration      = "";
        $where_declaration    = "";

        foreach ($values as $column=>$value)
        {
            if ($set_declaration != "") $set_declaration .= ", ";
            $set_declaration .= $itais_ics_database_connection->escape_string($column) . " = " . $itais_ics_database_connection->escape_string(self::StatementValue($value));
        }

        if ($where != null) $where_declaration = $where->String();

        $itais_ics_database_connection->query("update {$prefix}{$this->name} set {$set_declaration}{$where_declaration}");

        if ($itais_ics_database_connection->error) throw new \itais\ics\exception\ICSException("Failed to update table: {$itais_ics_database_connection->error}", "native", "D5");
    }

    public function Delete($where = null)
    {
        global $itais_ics_database_connection;
        
        $prefix = \itais\ics\config\MySQLi::prefix;

        if (!is_string($where)) throw new \itais\ics\exception\ICSException("Expected string for \$where, got " . gettype($where), "native", "DT6");

        $where_declaration = "";

        if ($where != null) $where_declaration = $where->String();

        $itais_ics_database_connection->query("delete from {$prefix}{$this->name}{$where_declaration}");

        if ($itais_ics_database_connection->error) throw new \itais\ics\exception\ICSException("Failed to delete from table: {$itais_ics_database_connection->error}", "native", "D6");
    }

    public function Drop()
    {
        global $itais_ics_database_connection;
        
        $prefix = \itais\ics\config\MySQLi::prefix;

        $itais_ics_database_connection->query("drop table {$prefix}{$this->name}");

        if ($itais_ics_database_connection->error) throw new \itais\ics\exception\ICSException("Failed to drop table: {$itais_ics_database_connection->error}", "native", "D7");
    }


    public function Select($columns = null, $where = null)
    {
        global $itais_ics_database_connection;
        
        $prefix = \itais\ics\config\MySQLi::prefix;

        if (!is_array($columns)) throw new \itais\ics\exception\ICSException("Expected array for \$columns, got " . gettype($columns), "native", "DT7");
        if (!is_string($where)) throw new \itais\ics\exception\ICSException("Expected string for \$where, got " . gettype($where), "native", "DT8");

        $columns_declaration    = "*";
        $where_declaration      = "";

        if ($columns != null)
        {
            $columns_declaration = "";

            foreach ($columns as $column)
            {
                if ($columns_declaration != "") $columns_declaration .= ", ";
                $columns_declaration .= $mysqli->escape_string($column);
            }
        }

        if ($where != null) $where_declaration = $where->String();

        $result = $itais_ics_database_connection->query("select {$columns_declaration} from {$prefix}{$this->name}{$where_declaration}");
        if ($itais_ics_database_connection->error) throw new \itais\ics\exception\ICSException("Failed to select: {$itais_ics_database_connection->error}", "native", "D8");

        $out = [];

        while ($out[] = $result->fetch_array(MYSQLI_ASSOC))
        {
            $out[] = $i_result;
        }

        $result->free();

        return $out;
    }
}
