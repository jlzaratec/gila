<?php
/* db.php
Created: 04/10/2016 by Vasilis Zoumpourlis
Updated: 18/10/2016 by Vasilis Zoumpourlis
*/
/**
* A simple class for a mysqli connection
*/
class db {
	private $dbhost, $user, $pass, $dsch;
	private $connected,$link;
	public $insert_id,$result;

	function __construct($host = 'localhost', $user = 'root', $pass = '', $dsch = '')
	{
		if(is_array($host)) {
			$this->dbhost = $host['host'];
			$this->user = $host['user'];
			$this->pass = $host['pass'];
			$this->dsch = $host['name'];
		}
		else {
			$this->dbhost = $host;
			$this->user = $user;
			$this->pass = $pass;
			$this->dsch = $dsch;
		}
		$this->connected = false;
	}

	public function connect ()
	{
		$this->link = mysqli_connect($this->dbhost, $this->user, $this->pass, $this->dsch);
		$this->link->set_charset("utf8");
		$this->connected = true;
	}

	public function close ()
	{
		if ($this->connected) mysqli_close($this->link);
		$this->connected = false;
	}

	public function query($q, $args = null)
	{
		if (!$this->connected) $this->connect();

		if ($args === null) {
            $res = $this->link->query($q);
			$this->insert_id = $this->link->insert_id;
			return $res;
        }
        else if (!is_array($args)) {
            $argsBkp = $args;
            $args = array($argsBkp);
        }

        $stmt = $this->link->prepare($q);
		$dt = "";
		foreach($args as $value) {
            $x=$this->link->real_escape_string($value);
			if(is_int($value)){
				$dt .= 'i';
			}else if(is_double($value)){
				$dt .= 'd';
			}else if(is_string($value)){
				$dt .= 's';
			}else{
				$dt .= 'b';
			}
        }
		array_unshift($args, $dt);
		$refarg = [];
		foreach ($args as $key => $value) $refarg[] =& $args[$key];

		if($stmt) if(call_user_func_array([$stmt,'bind_param'], $refarg)) {
            $stmt->execute();
			$this->insert_id = $this->link->insert_id;
			return $stmt->get_result();
		}
		return false;
	}


	function multi_query($q)
	{
		if (!$this->connected) $this->connect();
		$res = $this->link->multi_query($q);
	  	$this->close();
	  	return $res;
	}

	public function insert($table,$params=[])
	{
		if (!$this->connected) $this->connect();
		$cols = implode(", ", array_keys($params));
		$vals = implode(", ", $params);
		$this->link->query("INSERT INTO $table($cols) VALUES($vals)");
	}

	public function get($q, $args = null)
	{
		$arr = [];
		$res = $this->query($q, $args);
	  	$this->close();
		if($res) while($r=mysqli_fetch_array($res)) $arr[]=$r;
	  	return $arr;
	}

	function getRows($q, $args = null)
	{
		$arr = [];
		$res = $this->query($q, $args);
	 	$this->close();
		if($res) while($r=mysqli_fetch_row($res)) $arr[]=$r;
	  	return $arr;
	}

	function getArray($q)
	{
	  	return $this->getList;
	}

	function getList($q, $args = null)
	{
		$arr=[];
		$garr=$this->getRows($q, $args);
		foreach ($garr as $key => $value) $arr[]=$value[0];
	  	return $arr;
	}

	function getCSV($q)
	{
		$arr = $this->get($q);
		return $arr;
	}

	function param($p) {
		return addslashes($_REQUEST[$p]);
	}
	function request($p) {
		//mysqli_real_escape_string($con, $_POST['age']);
		return addslashes($_REQUEST[$p]);
	}


	function value($q,$p=null)
	{
		//if (!$this->connected) $this->connect();
		$res = $this->query($q,$p);
	  	//$this->close();
		if($res) {
			$r=mysqli_fetch_row($res);
			return $r[0];
		}
		return null;
	}

	function credentials()
	{
		return [$this->dbhost,$this->user,$this->pass,$this->dsch];
	}

}

/**
* @example $db
* @code
* global $db;
* $db = new dbClass('localhost', 'root', '', '');
* @endcode
*/
