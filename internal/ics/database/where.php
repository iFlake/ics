<?php
namespace \itais\ics\database; 

class Where
{
    public $clause;


    public function __construct($clause)
    {
        if (!is_array($clause)) throw new \itais\ics\exception\ICSException("Expected array for \$clause, got " . gettype($clause), "native", "DWT1");

        $this->clause = $clause;
    }


    public function String()
    {
        global $itais_ics_database_connection;

        $clauses    = [];

        foreach ($this->clause as $comparison)
        {
            $primary     = $comparison[0];
            $operator    = $comparison[1];

            if ($operator != "null") $secondary = $comparison[2];

            switch ($operator)
            {
                case "=":
                    $clauses[] = $itais_ics_database_connection->escape_string(\itais\ics\database\Table::StatementValue($primary)) . " = " . $itais_ics_database_connection->escape_string(\itais\ics\database\Table::StatementValue($secondary));
                    break;

                case "!=":
                    $clauses[] = $itais_ics_database_connection->escape_string(\itais\ics\database\Table::StatementValue($primary)) . " <> " . $itais_ics_database_connection->escape_string(\itais\ics\database\Table::StatementValue($secondary));
                    break;

                case "<":
                    $clauses[] = $itais_ics_database_connection->escape_string(\itais\ics\database\Table::StatementValue($primary)) . " < " . $itais_ics_database_connection->escape_string(\itais\ics\database\Table::StatementValue($secondary));
                    break;

                case ">":
                    $clauses[] = $itais_ics_database_connection->escape_string(\itais\ics\database\Table::StatementValue($primary)) . " > " . $itais_ics_database_connection->escape_string(\itais\ics\database\Table::StatementValue($secondary));
                    break;

                case "<=":
                    $clauses[] = $itais_ics_database_connection->escape_string(\itais\ics\database\Table::StatementValue($primary)) . " <= " . $itais_ics_database_connection->escape_string(\itais\ics\database\Table::StatementValue($secondary));
                    break;

                case ">=":
                    $clauses[] = $itais_ics_database_connection->escape_string(\itais\ics\database\Table::StatementValue($primary)) . " >= " . $itais_ics_database_connection->escape_string(\itais\ics\database\Table::StatementValue($secondary));
                    break;

                case "like":
                    $clauses[] = $itais_ics_database_connection->escape_string(\itais\ics\database\Table::StatementValue($primary)) . " like " . $itais_ics_database_connection->escape_string(\itais\ics\database\Table::StatementValue($secondary));
                    break;

                case "between":
                    $clauses[] = $itais_ics_database_connection->escape_string(\itais\ics\database\Table::StatementValue($primary)) . " between (" . implode(", ",$itais_ics_database_connection->escape_string(\itais\ics\database\Table::StatementValue($secondary))) . ")";
                    break;

                case "!between":
                    $clauses[] = $itais_ics_database_connection->escape_string(\itais\ics\database\Table::StatementValue($primary)) . " not between (" . implode(", ",$itais_ics_database_connection->escape_string(\itais\ics\database\Table::StatementValue($secondary))) . ")";
                    break;

                case "in":
                    $clauses[] = $itais_ics_database_connection->escape_string(\itais\ics\database\Table::StatementValue($primary)) . " in (" . implode(", ",$itais_ics_database_connection->escape_string(\itais\ics\database\Table::StatementValue($secondary))) . ")";
                    break;

                case "!in":
                    $clauses[] = $itais_ics_database_connection->escape_string(\itais\ics\database\Table::StatementValue($primary)) . " not in (" . implode(", ",$itais_ics_database_connection->escape_string(\itais\ics\database\Table::StatementValue($secondary))) . ")";
                    break;

                case "null":
                    $clauses[] = $itais_ics_database_connection->escape_string(\itais\ics\database\Table::StatementValue($primary)) . " is null";
                    break;

                default:
                    throw new \itais\ics\exception\ICSException("Unexpected comparator {$operator}", "native", "DW1");
                    break;
            }
        }

        return implode(" and ", $clauses);
    }
}
