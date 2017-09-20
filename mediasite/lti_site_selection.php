<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * lti_site_selection.php
 * This page is automatically brought up upon loading mod_form.php. It can be brought up
 * again using the 'Search' button on mod_form.php
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once("$CFG->dirroot/mod/mediasite/mod_mediasite_lti_site_selection_form.php");
require_once("$CFG->dirroot/mod/mediasite/locallib.php");
require_once("$CFG->dirroot/mod/mediasite/mediasitesite.php");
require_once("$CFG->dirroot/mod/mediasite/exceptions.php");

global $CFG, $PAGE, $DB;

$courseid = required_param('course', PARAM_INT);          // course

$context = context_course::instance($courseid);

require_login();
require_capability('mod/mediasite:addinstance', $context);

$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot . '/mod/mediasite/lti_site_selection.php');

date_default_timezone_set('UTC');

$configtable = 'mediasite_course_config';

$default_site_id = $DB->get_field($configtable, 'mediasite_site', array('course' => $courseid), IGNORE_MISSING);

$mform  = new mod_mediasite_lti_site_selection_form(strval($courseid));

if (!$default_site_id) {

    $data = $mform->get_data();

    if($data) {
        // save the defaults
        $record = new stdclass();
        $record->course = $courseid;
        $record->mediasite_site = $data->siteid;
        $record->mediasite_courses_enabled = $DB->get_field('mediasite_sites', 'show_integration_catalog', array('id' => $data->siteid));
        $DB->insert_record($configtable, $record);

        // launch the site
        $mform->launchredirect($courseid, $data->siteid);
    }

    html_header();

    $mform->display();

    html_footer();
} else {
    $mform->launchredirect($courseid, $default_site_id);
}

function html_header() {
    GLOBAL $OUTPUT;

    echo $OUTPUT->header();

    echo "<table class=\"yui3-skin-sam\" border=\"0\" style=\"margin-left:auto;margin-right:auto\" cellspacing=\"3\" cellpadding=\"3\" width=\"640\">";
    echo "<tr>";
    echo "<td colspan=\"2\">";
}

function html_footer() {
    global $COURSE, $OUTPUT;

    echo '</td></tr></table>';

    echo $OUTPUT->footer($COURSE);
}

