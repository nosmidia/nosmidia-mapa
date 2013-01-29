
<?php

//http://www.mygeekpal.com/simple-database-class-for-php/
class Simple_DB {

var $connection;
var $database;
var $rows = array();
var $count;
var $result;

function __contruct($dbuser,$dbpass,$dbhost,$dbname) {
	$this->connect($dbuser,$dbpass,$dbhost,$dbname);
}


/* Create a database connection */
function connect ($dbuser,$dbpass,$dbhost,$dbname) {
  $this->connection = mysql_connect($dbhost,$dbuser,$dbpass);
  $this->database = mysql_select_db($dbname);
}

/* Query a MySQL Database */
function query ($query) {
  $this->result = mysql_query($query) or die(mysql_error());
  return $this->result;
}

/* Query a MySQL Dabase
Returns Both arrays Assoc & Indexed */
function getrows ($query) {
  $this->result = mysql_query($query);
  while($r = mysql_fetch_array($this->result,MYSQL_BOTH))
    $this->rows[] = $r;
    mysql_free_result($this->result);
    return $this->rows;
}

/* Count Number of rows query */
function count ($query) {
  $this->result = mysql_query($query);
  $this->count = mysql_num_rows($this->result);
  return $this->count;
}

/* Create a Safe Query */
function escapedata($data) {
    return mysql_real_escape_string($data);
}

/* Close connection */
function __destruct(){ @mysql_close($this->connection); }

}