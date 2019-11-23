<?php
  require_once(dirname(__FILE__) . '/../../config.php');
  
  define('MAX_RECORDS_PER_QUERY', 15);
  define('AJAX_POLLING_SECONDS', 30);
  define('VISIBLE_COMMENTS', 3);

  function dd($var){
    print_object($var);
    die();
  }

  function dump($var){
    print_object($var);
  }

  function local_social_course_set_page($course, $url){
    global $PAGE;
    $url = new moodle_url($url);
    $url->param('courseid', $course->id);

    $plugin_name = get_string('pluginname', 'local_social_course');
    $PAGE->set_url($url);
    require_login($course, false);
    $PAGE->set_title($plugin_name);
    $PAGE->set_pagelayout('standard');
    $PAGE->set_heading($course->fullname);
    local_social_course_render_styles();
  }

  function local_social_course_render_styles(){
    global $PAGE;
    $PAGE->requires->css('/local/social_course/css/googlefonts.css');
    $PAGE->requires->css('/local/social_course/css/materialicons.css');
    $PAGE->requires->css('/local/social_course/css/emojionearea.min.css');
    $PAGE->requires->css('/local/social_course/css/vuetify.css');
    $PAGE->requires->css('/local/social_course/styles.css');
  }

  function local_social_course_ajax_response($data = array(), $message = null, $valid = true){
    local_social_course_set_api_headers();
    $response = [
      'valid' => $valid,
      'message' => $message,
      'data' => $data
    ];
    echo json_encode($response);
  }
  
  function local_social_course_set_api_headers(){
    header('Access-Control-Allow-Origin: *');
    header('Content-type: application/json');
  }

  function local_social_course_file_exist($file){
    $file_storage = get_file_storage();
    $exist = $file_storage->file_exists($file['contextid'], $file['component'], $file['filearea'],
                                        $file['itemid'], $file['filepath'], $file['filename']); 
    return $exist;
  }

  function get_local_social_course_file($courseid, $publicationid) {
    $context = context_course::instance($courseid);
    $fs = get_file_storage();
    $file = $fs->get_area_files($context->id, 'local_social_course', 'social_course_attachment',
                                 $publicationid, $sort = false, $includedirs = false);
    if (empty($file)){
      return false;
    }
    return array_shift($file);
  }

  function get_local_social_course_url($courseid, $publicationid, $forcedownload = false) {
    $file = get_local_social_course_file($courseid, $publicationid);
    if (!$file){
      return false;
    }
    $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(),
                                           $file->get_itemid(), $file->get_filepath(), $file->get_filename(),
                                           $forcedownload);
    return $url;
}