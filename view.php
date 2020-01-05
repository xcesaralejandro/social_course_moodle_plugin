<?php
    require_once('locallib.php');
    global $COUSE, $USER;
    $courseid = required_param('courseid', PARAM_INT);
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $context = context_course::instance($course->id);
    require_capability('local/social_course:use_social_course', $context);
    $url = "/local/social_course/view.php";
    local_social_course_set_page($course, $url);
    // $core = new local_social_course_core($COURSE->id, $USER->id);
    $participants = new local_social_course_participant($USER->id, $COURSE->id);
    $publications = new stdClass();
    $publications->mine = local_social_course_publication::created_by($USER->id, $COURSE->id);
    $publications->shared_with_me = local_social_course_publication::shared_with($USER->id, $COURSE->id);
    $enrolled = $participants->enrolled();
    $content = array(
        "enrolled" => $enrolled,
        "groups" => $participants->all_groups(),
        "available_roles" => $participants->extract_different_roles_from($enrolled),
        "user" => $USER,
        "course" => $COURSE,
        "publications" => $publications,
        "strings" => [
        ],
    );
    $PAGE->requires->js_call_amd('local_social_course/main','init', ["groups" => $content]);

    echo $OUTPUT->header();
    echo $OUTPUT->render_from_template('local_social_course/publications', ["content" => $content]);
    echo $OUTPUT->footer();