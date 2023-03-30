<?php
function redirect($page) {
  header("Location: ${page}");
  exit();
}

function already_voted($content, $user) {
  if ($user == null || $content == null) {
    return false;
  }
  foreach ($content as $con) {
      if (in_array($user, $con)) {
          return true;
      }
  }
  return false;
}

function date_compare($element1, $element2) {
  $datetime1 = strtotime($element1['start']);
  $datetime2 = strtotime($element2['start']);
  $datetime3 = strtotime($element1['deadline']);
  $datetime4 = strtotime($element2['deadline']);
  if ($datetime2 == $datetime1) {
    return $datetime4 - $datetime3;
  }
  return $datetime2 - $datetime1;
}

function is_same($data, $content) {
  if (count($data) != count($content)) {
    return false;
  }
  for ($i=0; $i < count($data); $i++) { 
    if ($data[$i] != $content[$i]) {
      return false;
    }
  }
  return true;
}