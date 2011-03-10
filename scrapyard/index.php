<?php

require "../lib/lib.php";

// if scraps is located at http://example.com/path/to/scraps/, then
// $path_to_scraps = '/path/to/scraps'
$path_to_scraps = '/scraps';

$db = new SQLite3('../db/scraps.db');
$id = (int) str_replace($path_to_scraps."/scrapyard/", "", $_SERVER['REQUEST_URI']);


function serve_all_scraps() {
  global $db;

  $scraps = array();
  $res = $db->query('SELECT * FROM scraps');

  while($row = $res->fetchArray(SQLITE3_ASSOC))
    array_push($scraps, $row);

  return json_encode($scraps);
}


function serve_scrap($id) {
  global $db;
  $res = $db->query("SELECT * FROM scraps WHERE id=$id");
  return json_encode($res->fetchArray(SQLITE3_ASSOC));
}


function save_scrap($scrap) {
  global $db;
  
  $stmt = $db->prepare('INSERT INTO scraps (created, body) VALUES (:created, :body)');
  $stmt->bindValue(':created', $scrap['created'], SQLITE3_INTEGER);
  $stmt->bindValue(':body', $scrap['body'], SQLITE3_TEXT);
  $stmt->execute();

  $scrap['id'] = $db->lastInsertRowID();
  return json_encode($scrap);
}


function modify_scrap($scrap, $id) {
  global $db;
  $stmt = $db->prepare('UPDATE scraps SET body=:body WHERE id=:id');
  $stmt->bindValue(':body', $scrap['body'], SQLITE3_TEXT);
  $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
  $stmt->execute();
}

function delete_scrap($id) {
  global $db;
  $db->exec("DELETE FROM scraps WHERE id=$id");
}


// Handle the request
switch($_SERVER['REQUEST_METHOD']) {
  case "GET":
    echo (empty($id)) ? serve_all_scraps() : serve_scrap($id);
    break;

  case "POST":
    $scrap = json_decode(stripslashes($_POST['model']), true);

    // PUT and DELETE are tunnelled over POST
    if(!isset($_POST['_method']))
      echo save_scrap($scrap);
    else if($_POST['_method'] == "PUT")
      modify_scrap($scrap, $id);
    else if($_POST['_method'] == "DELETE")
      delete_scrap($id);
}