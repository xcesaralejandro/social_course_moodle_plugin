<?php
    require_once('locallib.php');
    global $COUSE, $USER;
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

    $xd = new local_social_course_publication($COURSE->id, $USER->id);
    
    echo $OUTPUT->header();
    echo $OUTPUT->render_from_template('local_social_course/publications', $content);
    echo $OUTPUT->footer();