<?php 
  define('AJAX_SCRIPT', true);
  require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
  require_once(dirname(__FILE__) . "/locallib.php");
  $action = optional_param('a', false ,PARAM_ALPHA);
  $courseid = optional_param('cid', false, PARAM_INT);
  $publicationid = optional_param('pid', false, PARAM_INT);
  $userid = optional_param('uid', false, PARAM_INT);
  $comment = optional_param('c', false, PARAM_RAW);
  $recipients = optional_param('r', array(), PARAM_INT);
  $groupid_share = optional_param('gs', null, PARAM_RAW);
  $roleid_share = optional_param('rs', null, PARAM_RAW);
  $type_share = optional_param('ts', false, PARAM_RAW);
  $name_share = optional_param('ns', false, PARAM_RAW);

  $params = array();
  $func = null;
  
  if($action == 'createpublication'){
    array_push($params, $courseid);
    array_push($params, $userid);
    array_push($params, $comment);
    array_push($params, $recipients);
    array_push($params, $groupid_share);
    array_push($params, $roleid_share);
    array_push($params, $type_share);
    array_push($params, $name_share);
    if($courseid && $userid && $comment && strlen($comment) > 0 && $type_share && $name_share){
      $func = "local_social_course_create_publication";
    }
  } elseif($action == 'uploadresource'){
    $image = isset($_FILES['resource']) ? $_FILES['resource'] : false;
    array_push($params, $courseid);
    array_push($params, $userid);
    array_push($params, $image);
    if($courseid && $image && $userid){
      $func = "local_social_course_upload_resource";
    }
  } 
  
  if(isset($params) && isset($func)){
    call_user_func_array($func, $params);
  } else {
    $message = get_string('invalid_api_data', 'local_social_course');
    local_social_course_ajax_response(array(), $message, false);
  }

  function local_social_course_create_publication($courseid, $userid, $comment, $destinataries,
                                                  $groupid_share, $roleid_share, $type_share,
                                                  $name_share){
    $share = new local_social_course_share();
    $share->name = $name_share;
    $share->groupid = $groupid_share;
    $share->roleid = $roleid_share;
    $share->type = $type_share;
    $publication = new local_social_course_publication();
    $publication->fill(["courseid" => $courseid, "authorid" => $userid, "comment" => $comment,
                        "share" => $share]);
    if($publication->save()){
      $recipients = array();
      foreach($destinataries as $recipientid){
        $recipient = new local_social_course_recipient();
        $recipient->fill(["recipientid" => $recipientid, "publicationid" => $publication->getProperty("id"), 
                          "timecreated" => time()]);
        $recipient = $recipient->save();
        array_push($recipients, $recipient);
      }
      $publication->fill(["recipients" => $recipients]);
      $publication = $publication->get();
      $status = true;
      $message = null;
    }else{
      $publication = null;
      $status = false;
      $message = get_string('publication_create_error', 'local_social_course');
    }
    local_social_course_ajax_response(["publication" => $publication], $message, $status);
  }

  function local_social_course_upload_resource($courseid, $userid, $resource){
    $resource = new local_social_course_resource();
    $resource->name = $_FILES['resource']['name'];
    $resource->path = $_FILES['resource']['tmp_name'];
    $resource->type = $_FILES['resource']['type'];
    $file = new local_social_course_file();
    $resource = $file->store($resource, $courseid, $userid);
    local_social_course_ajax_response(["resource" => $resource]);
  }