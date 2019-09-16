<?php
    require_once('locallib.php');

    $courseid = required_param('courseid', PARAM_INT);
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $context = context_course::instance($course->id);
    require_capability('local/social_course:use_social_course', $context);
    $url = "/local/social_course/view.php";
    local_social_course_set_page($course, $url);

    $content = array(
        "strings" => "Hello world! From: " . get_string("pluginname", "local_social_course")
    );

    $PAGE->requires->js_call_amd('local_social_course/main','init', $content);
    
    echo $OUTPUT->header();
    echo $OUTPUT->render_from_template('local_social_course/publications', $content);
    echo $OUTPUT->footer();