<?php
/**
 * @package    social-course
 * @author     César Mora <cesar.mcid@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'local/social_course:use_social_course' => array(
        'captype'      => 'read',  //tipo de capacidad de lectura o escritura (read/write)
        'contextlevel' => CONTEXT_COURSE,
        'archetypes'   => array(
            'student'        => CAP_ALLOW,
            'teacher'        => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager'        => CAP_ALLOW
        )
    ),
);
