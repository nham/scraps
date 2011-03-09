<?php

require('phpass-0.3/PasswordHash.php');

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


function setpw() {

  try {
    $P = new Password();
    $pass_exists = $P->exists();
  } catch(Exception $e) {
    die($e->getMessage());
  }

  if($_POST['newpass']) {

    if($pass_exists && !$P->isValid($_POST['password'])) {
      $pass_incorrect = true;
    } else {
      $confirm_failed = $_POST['newpass'] != $_POST['passconfirm'];

      // (Try to) set the password
      if(!$confirm_failed) {
        try {
          $set_pass = $P->set($_POST['newpass'], $db);
        } catch(Exception $e) {
          die($e->getMessage());
        }
      }
    }
  }
  ?>

  <!DOCTYPE html>
  <html>
  <head><title>set scraps password</title></head>
  <body>

  <?php if($pass_incorrect): ?>

    <p>The password entered does not match the current password.</p>

  <?php elseif($set_pass): ?>

    <p>Password set successfully.</p>

  <?php else: ?>

    <?php if($confirm_failed): ?>
    <p>The passwords you entered don't match.</p>
    <?php endif; ?>

    <form method="post">

    <?php if($pass_exists): ?>
    <input name="password" type="password" placeholder="Current password?" \>
    <?php endif; ?>

    <input name="newpass" type="password" placeholder="New password"\>
    <input name="passconfirm" type="password" placeholder="Confirm new password" \>
    <input type="submit" value="Set Password" />
    </form>

    </body>
    </html>

  <?php endif;
}


function login() {
  try {
    $A = new Auth();
  } catch(Exception $e) {
    die($e->getMessage());
  }


  if($_POST['password']) {
    $P = new Password();

    if(!$P->isValid($_POST['password'])) {
      $pass_incorrect = true;
    } else {
      $set_cookie = true;

      $cookieval = set_auth_cookie();

      try {
        $A->create($cookieval);
      } catch(Exception $e) {
        die($e->getMessage());
      }
    }
  } else {
    if(isset($_COOKIE['auth']) && $A->isValid($_COOKIE['auth']))
      $already_set = true;
  }
  ?>

  <!DOCTYPE html>
  <html>
  <head><title>set scraps password</title></head>
  <body>

  <?php if($pass_incorrect): ?>

    <p>The password entered does not match the current password.</p>

  <?php elseif($set_cookie): ?>

    <p>Y'all should be logged in now.</p>

  <?php elseif($already_set): ?>

    <p>Y'all is already logged in.</p>

  <?php else: ?>

    <form method="post">
    <input name="password" type="password" placeholder="Password?" \>
    <input type="submit" value="Login" />
    </form>

    </body>
    </html>

  <?php endif;
}


function validate() {
  if(!isset($_COOKIE['auth'])) {
    echo "false";
  } else {
    try {
      $A = new Auth();
    } catch(Exception $e) {
      die($e->getMessage());
    }
 
    if($A->isValid($_COOKIE['auth']))
      echo "true";
    else
      echo "false";
  }
}


if(isset($_GET['a'])) {
  switch($_GET['a']) {
    case "setpw": setpw(); break;
    case "login": login(); break;
  }
} else {
  validate();
}
?>
