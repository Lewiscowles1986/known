<?php

$conn_uri = getenv('KNOWN_DATABASE_URL');
if(empty($conn_uri)) {
  echo "Unable to release without KNOWN_DATABASE_URL";
  exit(-1);
}
$conn_parsed = parse_url($conn_uri);
try {
    $db_conn_type = str_replace(['postgres'], ['pgsql'], $conn_parsed['scheme']);
    $db = new PDO(
        $db_conn_type.':dbname='.basename($conn_parsed['path']).';host='.$conn_parsed['host'],
        $conn_parsed['user'],
        $conn_parsed['pass']
    );
    $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    if ($sql = @file_get_contents(
      __DIR__.'/warmup/schemas/'.$conn_parsed['scheme'].'/'.$conn_parsed['scheme'].'.sql'
    )) {
      $statements = explode(";\n", $sql); // Explode statements; only mysql can support multiple statements per line, and then not safely.
      foreach ($statements as $sql) {
          $sql = trim($sql);
          if (!empty($sql)) {
              try {
                  $statement = $db->prepare($sql);
                  $statement->execute();
              } catch (\Exception $e) {
                  throw new \RuntimeException(
                    'We couldn\'t automatically install the database schema: ' . $e->getMessage() . PHP_EOL
                  );
              }
          }
      }
    } else {
        throw new \RuntimeException("We couldn't find the schema doc.");
    }
} catch(\Exception $e) {
    echo "could not restore the schema" . PHP_EOL;
    echo $e->getMessage();
    exit(-2);
}
exit(0);