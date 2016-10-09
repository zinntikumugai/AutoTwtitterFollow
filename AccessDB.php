<?php

$link = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if( $link->connect_errno ) {
  echo "DB ERROR";
  exit();
} else {
  $link->set_charset('UTF-8');
}

function addBlackList($link, $result, $userID, $T_NAME) {
  $Code = $result->errors[0]->code;
  $Message = $result->errors[0]->message;
  $ALLString = mysqli_real_escape_string( $link, json_encode( $result) );
  $query = "INSERT INTO {$T_NAME} (USERID, Code, MESSAGE, ALLString, Date) VALUES ({$userID}, {$Code}, \"{$Message}\", \"{$ALLString}\", NOW() )" ;
  /*echo "\n";
  var_dump( $query );
  echo "\n\n";*/
  if($result =  $link->query( $query ) ) {
  }
}

function getBlackList( $link, $T_NAME ) {
  $data = array();

  $query = "SELECT DISTINCT USERID FROM {$T_NAME} ";
  if($result = $link->query( $query )) {
    while( $row = $result->fetch_assoc()) {
    //  var_dump( $row );
      //http://php.net/manual/ja/function.array-push.php
      array_push( $data, $row['USERID']);
    }
  }
  //var_dump( $data );
  return $data;
}


?>
