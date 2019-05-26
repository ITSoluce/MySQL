<?php
namespace MySQL;
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
	
		if (!($this->Ressource = $this->sql_connect()))
                {
                    throw new \MyException\MyException("Erreur connection");
                }
		$this->set_Datebase($this->Database);
	}
	
	function get_Database()
	{
		return $this->Database;
	}
	
	function set_Datebase($Value)
	{
		$this->Datebase = $Value;
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

            } catch (\MyException\MyException $e) {
                    $FileName = 'SQLLoginError.txt';
                    $LifeDelay = 5;
                    if ( (!file_exists($FileName)) || ( filemtime($FileName) < mktime(date("G"),(int)date("i")-$LifeDelay,date("s"),date("m"),date("d"),date("Y")) ) )
                    {
                            error_log("Mail le ".date("Y-m-d H:i:s"), 3, $FileName);
                            /*
                             // Le mail
                             $message = "Acces SQL Impossible ( PHP PDO ) sur ".$LinkSQLH." Utilisateur ".$LinkSQLU." (".$_SERVER['SERVER_NAME']." / ".$_SERVER['COMPUTERNAME'].")";
                             $headers = 'From: support@akeo.fr' . "\r\nReply-To: support@akeo.fr";

                             ini_set( 'sendmail_from', "support@akeo.fr" );
                             ini_set( 'SMTP', "mail.akeo.fr" );
                             ini_set( 'smtp_port', 25 );
                             // Envoi du mail
                             mail('renaud.platel@gmail.com', 'Erreur SQL ( PHP PDO ) '.$_SERVER['COMPUTERNAME'], $message,$headers);
                             */
                    }

                    $this->Ressource = false;
            }

            /*if (!($conn))
             header('Location: http://icmanager.ffbad/maintenance.php');
             */

            return $this->Ressource;
	}	
	
	function sql_query($query)
	{
		try {
			$statement = $this->Ressource->query($query);
			
			if ($statement === FALSE)
                        {
                            throw new \MyException\MyException('Requête non executée');
                        }
                    }
		catch (\MyException\MyException $e) {
			return false;
		}
		return $statement;
	}
	
	function sql_num_rows($statement)
	{	
		if ((!isset($statement->Count))||(is_null($statement->Count)))
		{
			try {
				$statement->Count= count($statement->fetchAll());
				$statement->execute();
			}
			catch (\MyException\MyException $e) {
				$statement->Count = 0;
			}
		}
		
		return $statement->Count;
	}
	
	function sql_result($statement,$Row,$Offset)
	{
		if ($Row == '')
		{
			$Row = 0;	
		}
		if ((!isset($statement->Result))||(is_null($statement->Result)))
		{
			try {
				$Result = $statement->fetchAll(PDO::FETCH_BOTH);
			} catch (\MyException\MyException $e) {
			}
			$i=0;
			foreach ($Result as $row)
			{
				foreach ($row as $key => $value)
				{
					$ResultEnd[$i][strtolower($key)] = $value;
				}
				$i++;
			}
			
			$statement->Result = $ResultEnd;
		}
	
		return $statement->Result[$Row][strtolower($Offset)];
	}
	
	function sql_num_fields($statement)
	{
		if ((!isset($statement->Field))||(is_null($statement->Field)))
		{
			try {
				$rows = $statement->fetch(PDO::FETCH_ASSOC);
			} catch (\MyException\MyException $e) {
			}
			if ($rows)
			{
				foreach ($rows AS $key => $value)
				{
					$row[]=$key;
				}
				$statement->Field=$row;
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
				$rows = $statement->fetch(PDO::FETCH_ASSOC);
			} catch (\MyException\MyException $e) {
			}
			if ($rows)
			{
				foreach ($rows AS $key => $value)
				{
					$row[]=$key;
				}
				$statement->Field=$row;
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
		
	function sql_fetch_object ($statement, $classname = null)
	{
		try {
			if ($classname == null)
				$object = $statement->fetchObject();
			else
				$object = $statement->fetchObject($classname);
		} catch (\MyException\MyException $e) {
		}
		return $object;
	}
	
	function sql_fetch_array($statement)
	{
		try {
			$array = $statement->fetch(PDO::FETCH_BOTH);
		} catch (\MyException\MyException $e) {
		}
		
		return $array;
	}
	
	function sql_fetch_assoc($statement)
	{
		return sql_fetch_array($statement);
	}
	
	function sql_fetch_row($statement)
	{
		try {
			$array = $statement->fetch(PDO::FETCH_BOTH);
		} catch (PDOException $e) {
		}
		return $array;
	}
	
	function sql_get_last_message($objet = null)
	{
		if (is_null($objet))
			Return "Fonctionne plus";
		if (get_class($objet)=='PDO')
			Return $objet->errorInfo();
		if (get_class($objet->statement)=='PDOStatement')
			Return $objet->errorInfo();
	}
	
	function sql_rows_affected($statement)
	{
		return sql_affected_rows($statement);
	}
	
	function sql_field_type($statement, $offset )
	{
		$column = $statement->getColumnMeta($offset);
		return(strtoupper($column["sqlsrv:decl_type"]));
	}
	
	function sql_free_result($statement = null)
	{
	}
	
	function sql_data_seek($statement,$rowid)
	{
		$statement->execute();
		$i=0;
		while ($i<$rowid)
		{
			sql_fetch_array($statement);
			$i++;
		}
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
	
	function sql_error($ressource)
	{
		/*
		if (isnull($ressource))
			return(false);
		
		if ( ( $ressource == false ) && ( ( function_exists($this->errorInfo()) ) || ( method_exists($this->errorInfo()) ) ) )
		{
			return implode( " " ,$this->errorInfo() );
		}
		*/
		return(false);
	}
}