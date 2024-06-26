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
 * Learnpaths report
 *
 * @package   report_learnpaths
 * @copyright eWallah (www.eWallah.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use core\report_helper;

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$courseid = optional_param('courseid', 1, PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);
$categoryid = optional_param('categoryid', 0, PARAM_INT);

$params = [];
require_login();
if ($userid > 0) {
    $context = \context_user::instance($userid);
    $params['userid'] = $userid;
    require_capability('report/learnpaths:viewuser', $context);
} else if ($courseid > 1) {
    $context = \context_course::instance($courseid);
    require_capability('report/learnpaths:viewcourse', $context);
    $params['courseid'] = $courseid;
    $course = get_course($courseid);
    $PAGE->set_course($course);
} else {
    if ($categoryid > 0) {
        $params['categoryid'] = $categoryid;
        $context = \context_coursecat::instance($categoryid);
    } else {
        $context = \context_system::instance();
    }
    require_capability('report/learnpaths:viewsystem', $context);
}


$renderable = new \report_learnpaths\output\main($context);
$str = get_string('pluginname', 'report_learnpaths');
$url = new moodle_url('/report/learnpaths/index.php', $params);

$PAGE->requires->js(new \moodle_url('/report/learnpaths/js/vis-network.min.js'), true);
$PAGE->requires->css(new \moodle_url('/report/learnpaths/js/vis-network.min.css'));
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
navigation_node::override_active_url($url);

$PAGE->set_title($str);
$PAGE->set_heading($str);
$output = $PAGE->get_renderer('report_learnpaths');
$pluginname = get_string('pluginname', 'report_learnpaths');
echo $output->header();
report_helper::print_report_selector($pluginname);
echo $output->render_main($renderable);
echo $output->footer();

\report_learnpaths\event\report_viewed::create(['context' => $context])->trigger();
