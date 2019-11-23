<?php 
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__) . "/locallib.php");

function local_social_course_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
  global $CFG;
  $publicationid = isset($args[0]) ? $args[0] : null;
  $file = get_local_social_course_file($course->id, $publicationid);
  if (!$file){
    return false;
  } 
  send_stored_file($file);
}