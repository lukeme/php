<?php
$pgConf = [
	'driver'=>'pgsql',
	'host'=>'localhost',
	'port'=>'8432',
	'name'=>'test',
	'user'=>'root',
	'pass'=>'123456',
];
$myConf = [
	'driver'=>'mysql',
	'host'=>'localhost',
	'port'=>'23306',
	'name'=>'test',
	'user'=>'root',
	'pass'=>'12456',
];
$pgDB = new DB($pgConf);
$myDB = new DB($myConf);

$count = $pgDB->count("SELECT count(*) FROM users WHERE name='luke'");
$count = $myDB->count("SELECT count(*) FROM users WHERE name='luke'");

print_r($count);


class DB {
	private $dbh;
	function __construct(array $conf){
		$dsn = sprintf('%s:host=%s;port=%s;dbname=%s;',$conf['driver'], $conf['host'], $conf['port'],$conf['name'],);
		$this->dbh = new PDO($dsn, $conf['user'], $conf['pass']);
	}
	
	function fetchAll($sql, $mode=PDO::FETCH_ASSOC){
		$sth = $this->dbh->prepare($sql);
		$sth->execute();
		return $sth->fetchAll($mode);
	}
	
	function fetchOne($sql, $mode=PDO::FETCH_BOTH){
		return $this->fetchAll($sql, $mode)[0]??null;
	}
	
	function count($sql){
		return $this->fetchOne($sql)[0]??null;
	}
	
	function exec($sql){
		return $this->dbh->exec($sql);
	}
	
	function errorInfo(){
		return $this->dbh->errorInfo();
	}
	
	//beginTransaction,commit,rollBack
	function __call($method, $param){
		return $this->dbh->$method($param);
	}

}
