<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload
require_once __DIR__ . '/../MyConfig/autoload.php'; // Autoload files using MyConfig autoload

$Connexion = new MySQL\sql(MySQLServer, MySQLUser, MySQLPassword, MySQLDatabase, MySQLPort, MySQLTransactionMode, MySQLDebug, MySQLCharset);

$Resultat = $Connexion->sql_query("SHOW TABLES");
while ($Ligne = $Connexion->sql_fetch_object($Resultat))
{
    print_r($Ligne);
}
