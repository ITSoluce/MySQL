<?php
namespace MySQL;

use phpDocumentor\Reflection\Types\Nullable;

/*
 * Class manage sql
 */
class sql
{
	/* Paramaters for this connection */
	private $Server;
	private $User;
	private $Password;
    private $Port;
        
	public $Database;
	
	private $Ressource; // Object/Ressourse for SQL
	
	private $Charset; // charset
	
	public $TransactionMode; // 1 for InnoDB(MySQL)/MsSQL in transaction mode, 0 for MyISAM(MySQL)
	
	public $Debug; // 1 to display all executed request

	function __construct($server, $user, $password, $database, $port = 3306, $transactionmode = 0, $debug = 0, $charset = 'UTF8' )
	{
		$this->Server = $server;
		$this->User = $user;
		$this->Password = $password;
		$this->Database = $database;
        $this->Port = $port;
		$this->Charset = $charset;
		$this->TransactionMode = $transactionmode;
		$this->Debug = $debug;
	
		$this->Ressource = $this->sql_connect();
		$this->set_Database($this->Database);
	}
	
	function get_Database()
	{
		return $this->Database;
	}
	
	function set_Database($Value)
	{
		$this->sql_query("USE `".$Value."`");
		return $Value;
	}
	
	function get_Connection_Type()
	{
		return $this->Connection_Type;
	}
	
	function get_Charset()
	{
		return $this->Charset;
	}
	
	function sql_connect()
	{
		try {
			$connectionstring = 'mysql:host='. $this->Server .';port='. $this->Port .';dbname='. $this->Database.";charset=".$this->Charset;
			$this->Ressource = new \PDO($connectionstring, $this->User, $this->Password);
		} catch (\Exception $e) {
			$FileName = 'SQLLoginError.txt';
			$LifeDelay = 5;
			if ( (!file_exists($FileName)) || ( filemtime($FileName) < mktime(date("G"),(int)date("i")-$LifeDelay,date("s"),date("m"),date("d"),date("Y")) ) )
			{
				error_log("Erreur le ".date("Y-m-d H:i:s")."\n", 3, $FileName);
				throw new \Exception('Erreur connexion SQL');
			}

			$this->Ressource = false;
		}

		if (!($this->Ressource))
			throw new \Exception('Erreur connexion SQL');

		return $this->Ressource;
	}	
	
	function sql_query($query)
	{
		try {
			$statement = $this->Ressource->query($query);
			
			if ($statement === FALSE)
			{
				throw new \Exception('Requête non executée : '.$query.' ('.$this->Ressource->errorInfo()[2].')');
			}
		}
		catch (\Exception $e) {
			throw new \Exception('Requête non executée : '.$query.' ('.$this->Ressource->errorInfo()[2].')');
		}
		return $statement;
	}
	
	function sql_num_rows($statement)
	{	
		return $statement->rowCount();
	}
	
	function sql_result($statement,$ThisRow,$Offset)
	{
		if ($ThisRow == '')
		{
			$ThisRow = 0;
		}

		$row = $this->sql_data_seek($statement,$ThisRow);
		
		if (isset($row[$Offset])) {
			return $row[$Offset];
		}

		throw new \Exception('Row '.$ThisRow.' / Offset '.$Offset.' non trouvé : '.$statement->queryString.' ('.$this->Ressource->errorInfo()[2].')');
	}
	
	function sql_num_fields($statement)
	{
		if ((!isset($statement->Field))||(is_null($statement->Field)))
		{
			try {
				$rows = $statement->fetch(\PDO::FETCH_ASSOC);
			} catch (\Exception $e) {
				throw new \Exception('Erreur dans MySQL Class : sql_num_fields');
			}
			if ($rows)
			{
				foreach ($rows AS $key => $value)
				{
					$row[]=$key;
				}
				$statement->execute();
				return count($rows);
			}
			else
				return 0;
		}
		else
		{
			return(count($statement->Field));
		}
	}
	
	function sql_field_name($statement,$i)
	{
		if ((!isset($statement->Field))||(is_null($statement->Field)))
		{
			try {
				$rows = $statement->fetch(\PDO::FETCH_ASSOC);
			} catch (\Exception $e) {
				throw new \Exception('Erreur dans MySQL Class : sql_field_name');
			}
			if ($rows)
			{
				foreach ($rows AS $key => $value)
				{
					$row[]=$key;
				}
				$statement->execute();
				return $row[$i];
			}
			else
				return 0;
		}
		else
		{
			return($statement->Field[$i]);
		}
	}
		
	function sql_fetch_object($statement, $classname = null)
	{
		try {
			if ($classname == null)
				$object = $statement->fetchObject();
			else
				$object = $statement->fetchObject($classname);
		} catch (\Exception $e) {
			throw new \Exception('Erreur dans MySQL Class : sql_fetch_object');
		}
		return $object;
	}
	
	function sql_fetch_array($statement)
	{
		try {
			$array = $statement->fetch(\PDO::FETCH_BOTH);
		} catch (\Exception $e) {
			throw new \Exception('Erreur dans MySQL Class : sql_fetch_array');
		}
		
		return $array;
	}
	
	function sql_fetch_assoc($statement)
	{
		return $this->sql_fetch_array($statement);
	}
	
	function sql_fetch_row($statement)
	{
		try {
			$array = $statement->fetch(\PDO::FETCH_BOTH);
		} catch (\Exception $e) {
			throw new \Exception('Erreur dans MySQL Class : sql_fetch_row');
		}
		return $array;
	}
	
	function errorInfo($objet = null) {
		return $this->sql_get_last_message($objet);
	}
	
	function sql_get_last_message($objet = null)
	{
		if (is_null($objet))
			Return "Fonctionne plus ".get_class($objet);
		if (get_class($objet)=='MySQL\sql')
			Return $this->Ressource->errorInfo();
		if (get_class($objet)=='PDOStatement')
			Return $objet->errorInfo();
	}
	
	function sql_rows_affected($statement)
	{
		return $this->sql_affected_rows($statement);
	}
	
	function sql_field_type($statement, $offset )
	{
		$column = $statement->getColumnMeta($offset);
		return(strtoupper($column["sqlsrv:decl_type"]));
	}
	
	function sql_free_result($statement = null)
	{
	}
	
	function sql_reset_pointer($statement)
	{
		$statement->execute();
	}
	
	function sql_data_seek($statement,$rowid)
	{
		$statement->execute();
		$i=0;
		while ($i<$rowid)
		{
			$result = $this->sql_fetch_array($statement);
			$i++;
		}
		return $result;
	}
	
	function sql_close($Variable = null)
	{
	}
	
	function sql_table_exists($tablename)
	{
		$stmt = $this->sql_query("SHOW tables LIKE '".$tablename."'");
		
		if($this->sql_num_rows($stmt) === 0)
		{
			return FALSE;
		}
		elseif($this->sql_affected_rows($stmt) < 1)
		{
			exit("Ma solution ne marche pas SQL::sql_table_exists");
		}
		else 
		{
			return TRUE;
		}
	}
	
	/**
	 * @name PDO statement
	 * @return count delete or update rows
	 */
	function sql_affected_rows($statement)
	{
		if (isset($statement->queryString))
		{
			return $statement->rowCount();
		}
		else
			return 0;
	}
	
	/**
	 * @return last ID insert
	 */
	function sql_insert_id($name = NULL)
	{
		return $this->Ressource->lastInsertId($name);
	}
	
	function lastInsertId() {
		return $this->sql_insert_id();
	}
	
	/**
	 *
	 * @name table
	 * @return name of primarykey if any (mysql)
	 */
	function sql_primary_key($tablename)
	{
		$Result = $this->sql_query("SHOW COLUMNS FROM `".$tablename."`");
	
		while ($row = $this->sql_fetch_object($Result))
		{
			if(trim($row->Key)=='PRI')
			{
				return $row->Field;
			}
		}
	
		return null;
	}
	
	/**
	 * @name Quote PDO
	 * @param unknown $val
	 * @return quoted string
	 */
	function quote($val)
	{
		return $this->Ressource->quote($val);
	}
	
	function sql_error($ressource = null)
	{
	}
	
	function beginTransaction()
	{
		$this->Ressource->beginTransaction();
	}
	
	function rollBack()
	{
		$this->Ressource->rollBack();
	}

	function commit()
	{
		$this->Ressource->commit();
	}
}