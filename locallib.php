<?php
  require_once(dirname(__FILE__).'/../../config.php');

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
    $PAGE->requires->css('/local/social_course/css/materialicon.css');
    $PAGE->requires->css('/local/social_course/css/materialdesignicons.css');
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