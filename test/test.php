<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload
require_once __DIR__ . '/../MyConfig/autoload.php'; // Autoload files using MyConfig autoload

$Connexion = new MySQL\sql(MySQLServer, MySQLUser, MySQLPassword, MySQLDatabase, MySQLPort, MySQLTransactionMode, MySQLDebug, MySQLCharset);

$Resultat = $Connexion->sql_query("SHOW TABLES");
while ($Ligne = $Connexion->sql_fetch_object($Resultat))
{
    echo "<p>";
    print_r($Ligne);
    echo "</p>";
}
echo "<br>";
echo $Connexion->sql_num_rows($Resultat);
echo "<br>";
$tablename = $Connexion->sql_result($Resultat,1,0);
echo $tablename;
echo "<br>";
print_r($Connexion->sql_num_fields($Resultat));
echo "<br>";
echo $Connexion->sql_field_name($Resultat,0);
echo "<br>";

$Connexion->sql_reset_pointer($Resultat);
while ($Ligne = $Connexion->sql_fetch_array($Resultat)) {
}
$Connexion->sql_reset_pointer($Resultat);
while ($Ligne = $Connexion->sql_fetch_assoc($Resultat)) {
}
$Connexion->sql_reset_pointer($Resultat);
while ($Ligne = $Connexion->sql_fetch_row($Resultat)) {
}

print_r($Connexion->errorInfo($Connexion));
echo "<br>";
print_r($Connexion->errorInfo($Resultat));
echo "<br>";

print_r($Connexion->sql_data_seek($Resultat,4));
echo "<br>";
echo $Connexion->sql_table_exists($tablename);