<?php
// if scraps is located at http://example.com/path/to/scraps/, then
// $path_to_scraps = '/path/to/scraps'
$path_to_scraps = '/scraps';

$db = new SQLite3('db/scraps.db');
$id = (int) str_replace($path_to_scraps."/scrapyard/", "", $_SERVER['REQUEST_URI']);

switch($_SERVER['REQUEST_METHOD']) {
  case "GET":
    if(empty($id)) {
      $result = $db->query('SELECT * FROM scraps');

      echo "[";
      echo json_encode($result->fetchArray(SQLITE3_ASSOC));
      while($row = $result->fetchArray(SQLITE3_ASSOC)) {
        echo "," . json_encode($row);
      }
      echo "]";

    } else {
      $result = $db->query("SELECT * FROM scraps WHERE id=$id");
      echo json_encode($result->fetchArray(SQLITE3_ASSOC));
    }

    break;

  case "POST":
    $scrap = json_decode(stripslashes($_POST['model']), true);

    if(!isset($_POST['_method'])) {
      $stmt = $db->prepare('INSERT INTO scraps (created, body) VALUES (:created, :body)');
      $stmt->bindValue(':created', $scrap['created'], SQLITE3_INTEGER);
      $stmt->bindValue(':body', $scrap['body'], SQLITE3_TEXT);
      $stmt->execute();

      $scrap['id'] = $db->lastInsertRowID();
      echo json_encode($scrap);
    } else {
      switch($_POST['_method']) {
        case "PUT":
          $stmt = $db->prepare('UPDATE scraps SET body=:body WHERE id=:id');
          $stmt->bindValue(':body', $scrap['body'], SQLITE3_TEXT);
          $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
          $stmt->execute();
          break;

        case "DELETE":
          $db->exec("DELETE FROM scraps WHERE id=$id");
          break;
      }
    }
    break;     
}