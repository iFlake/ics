<?php
namespace itais\ics\database;

$itais_ics_database_connection = new \mysqli(\itais\ics\config\MySQLi::host, \itais\ics\config\MySQLi::username, \itais\ics\config\MySQLi::password, \itais\ics\config\MySQLi::database, \itais\ics\config\MySQLi::port);
if ($itais_ics_database_connection->connect_error) throw new \itais\ics\exception\ICSException("Failed to connect to MySQL: {$itais_ics_database_connection->connect_error}", "native", "DC1");
