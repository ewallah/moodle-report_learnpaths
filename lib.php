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
 * Library functions.
 *
 * @package    report_learnpaths
 * @copyright  2020 Renaat Debleu (www.eWallah.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This function extends the navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param stdClass $context The context of the course
 */
function report_learnpaths_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/learnpaths:viewcourse', $context)) {
        $url = new \moodle_url('/report/learnpaths/index.php', ['courseid' => $course->id]);
        $str = get_string('pluginname', 'report_learnpaths');
        $navigation->add($str, $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}


/**
 * This function extends the category navigation to add learnpath reports.
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param context $context The context of the course category
 */
function report_learnpaths_extend_navigation_category_settings($navigation, $context) {
    if (has_capability('report/learnpaths:viewsystem', $context)) {
        $url = new \moodle_url('/report/learnpaths/index.php', ['courseid' => 1, 'categoryid' => $context->instanceid]);
        $str = get_string('pluginname', 'report_learnpaths');
        $navigation->add($str, $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}

/**
 * Add nodes to myprofile page.
 *
 * @param \core_user\output\myprofile\tree $tree Tree object
 * @param stdClass $user user object
 * @param bool $iscurrentuser
 * @param stdClass $course Course object
 *
 * @return bool
 */
function report_learnpaths_myprofile_navigation(\core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    $return = false;
    if (!isguestuser($user) && has_capability('report/learnpaths:viewuser', context_user::instance($user->id))) {
        $str = get_string('pluginname', 'report_learnpaths');
        $url = new \moodle_url('/report/learnpaths/index.php', ['userid' => $user->id]);
        $node = new \core_user\output\myprofile\node('reports', 'mylearnpaths', $str, null, $url);
        $tree->add_node($node);
        $return = true;
    }
    return $return;
}

/**
 * Return a list of page types
 *
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 * @return array a list of page types
 */
function report_learnpaths_page_type_list($pagetype, $parentcontext, $currentcontext) {
    return [
        '*' => get_string('page-x', 'pagetype'),
        'report-*' => get_string('page-report-x', 'pagetype'),
        'report-learnpaths-*' => get_string('page-report-learnpaths-x', 'report_learnpaths'),
        'report-learnpaths-index' => get_string('page-report-learnpaths-index', 'report_learnpaths'),
        'report-learnpaths-user' => get_string('page-report-learnpaths-user', 'report_learnpaths'), ];
}
