<!DOCTYPE html>
<html>
<head><title>set scraps password</title></head>
<body>


<?php

require('phpass-0.3/PasswordHash.php');


class Password {
  var $pw;
  var $hasher;

  function __construct($db) {
    $res = $db->querySingle('SELECT pw FROM password');
    
    if($res === false)
      throw new Exception("Couldn't select password from database");
    else
      $this->pw = $res;

    $this->hasher = new PasswordHash(8, false);
  }

  function passExists() {
    return ($this->pw != "");
  }

  function isValid($entered) {
    return $this->hasher->CheckPassword($entered, $this->pw);
  }

  function setPassword($new, $db) {
    $hashed_pw = $this->hasher->HashPassword($new);
    if(!$db->exec("UPDATE password SET pw='$hashed_pw'"))
      throw new Exception("Couldn't set the password in the database.");
    else
      return true;
  }
}


try {
  $db = new SQLite3('../db/scraps.db');
  $P = new Password($db);
  $pass_exists = $P->passExists();
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
        $set_pass = $P->setPassword($_POST['newpass'], $db);
      } catch(Exception $e) {
        die($e->getMessage());
      }
    }

  }

}

?>

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
  <input name="password" type="password" placeholder="Current password" \>
  <?php endif; ?>

  <input name="newpass" type="password" placeholder="New password"\>
  <input name="passconfirm" type="password" placeholder="Confirm new password" \>
  <input type="submit" value="Set Password" />
  </form>

<?php endif; ?>

</body>
</html>
