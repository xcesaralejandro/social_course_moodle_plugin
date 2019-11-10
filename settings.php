<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
  require_once($CFG->dirroot . '/local/social_course/locallib.php');
 
  $text = [
      'recordsperquery_title' => get_string('cfg_max_records_per_query_title', 'local_social_course'),
      'recordsperquery_desc' => get_string('cfg_max_records_per_query_desc', 'local_social_course'),
      'ajaxpolling_title' => get_string('cfg_ajax_polling_seconds_title', 'local_social_course'),
      'ajaxpolling_desc' => get_string('cfg_ajax_polling_seconds_desc', 'local_social_course'),
      'comments_title' => get_string('cfg_visible_comments_title', 'local_social_course'),
      'comments_desc' => get_string('cfg_visible_comments_desc', 'local_social_course'),
  ];

  $settings = new admin_settingpage('local_social_course', get_string('pluginname', 'local_social_course'));
  $settings->add(new admin_setting_configtext('local_social_course/maxrecordsperquery', 
                                              $text['recordsperquery_title'], $text['recordsperquery_desc'],
                                              MAX_RECORDS_PER_QUERY , PARAM_INT));
  $settings->add(new admin_setting_configtext('local_social_course/secondstoajaxpolling', 
                                              $text['ajaxpolling_title'], $text['ajaxpolling_desc'],
                                              AJAX_POLLING_SECONDS , PARAM_INT));
  $settings->add(new admin_setting_configtext('local_social_course/visiblecomments', 
                                              $text['comments_title'], $text['comments_desc'],
                                              VISIBLE_COMMENTS , PARAM_INT));

    $ADMIN->add('localplugins', $settings);
}
