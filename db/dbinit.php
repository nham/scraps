<?php

$db = new SQLite3('scraps.db');

$db->exec('CREATE TABLE scraps (id integer PRIMARY KEY, created integer, body text)');

$db->exec('CREATE TABLE password (pw text)');
$db->exec('INSERT INTO password VALUES ("")')

$db->exec('CREATE TABLE auth_nums (anum text)');

?>