<?php

require "../lib/lib.php";

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
