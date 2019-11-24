<?php 
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__) . "/locallib.php");

function local_social_course_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
  global $CFG;
  $id = isset($args[0]) ? $args[0] : null;
  $file = new local_social_course_file();
  $file = $file->find($id);
  if (!$file){
    return false;
  } 
  send_stored_file($file);
}