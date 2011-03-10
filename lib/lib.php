<?php

require('phpass-0.3/PasswordHash.php');

class Password {
  var $pw;
  var $hasher;
  var $db;

  function __construct() {
    $this->db = db_connect();
    $res = $this->db->querySingle('SELECT pw FROM password');
    
    if($res === false)
      throw new Exception("Couldn't select password from database");
    else
      $this->pw = $res;

    $this->hasher = new PasswordHash(8, false);
  }

  function exists() {
    return ($this->pw != "");
  }

  function isValid($entered) {
    return $this->hasher->CheckPassword($entered, $this->pw);
  }

  function set($new, $db) {
    $hashed_pw = $this->hasher->HashPassword($new);
    if(!$this->db->exec("UPDATE password SET pw='$hashed_pw'"))
      throw new Exception("Couldn't set the password in the database.");
    else
      return true;
  }
}


class Auth extends Exception {
  var $db;
  var $anums;

  function __construct() {
    $this->db = db_connect();

    $this->anums = array();
    $res = $this->db->query('SELECT anum FROM auth_nums');

    if($res === false)
      throw new Exception("Couldn't get authentication numbers from database.");
    else
      while($row = $res->fetchArray())
        array_push($this->anums, $row['anum']);
  }

  function create($num) {
    if(!$this->db->exec("INSERT INTO auth_nums VALUES ('$num')"))
      throw new Exception("Couldn't add authentication number to the database.");
    else
      return true;
  }

  function isValid($num) {
    foreach($this->anums as $anum)
      if($anum == $num)
        return true;

    return false;
  }
}


function db_connect() {
  return new SQLite3('../db/scraps.db');
}


function set_auth_cookie() {
  setcookie("auth", "", time() - 3600);

  $randmax = pow(2, 31) - 1;
  $rand1 = base_convert(mt_rand(0, $randmax), 10, 36);
  $rand2 = base_convert(mt_rand(0, $randmax), 10, 36);
  $cookieval = $rand1.$rand2;

  setcookie("auth", $cookieval, time()+3600*24*365, '/scraps/');
  return $cookieval;
}
