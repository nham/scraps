<?php

$db = new SQLite3('db/scraps.db');

$db->EXEC('CREATE TABLE scraps (id integer PRIMARY KEY, created integer, body text)');

?>