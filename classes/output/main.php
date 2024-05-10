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
 * Learning paths report data.
 *
 * @package    report_learnpaths
 * @copyright  Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace report_learnpaths\output;

use core_user;
use renderable;
use renderer_base;
use templatable;

/**
 * Learning paths report data.
 *
 * @package    report_learnpaths
 * @copyright  Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class main implements renderable, templatable {
    /** @var context */
    protected $context;

    /**
     * Construct.
     *
     * @param stdClass $context
     */
    public function __construct($context) {
        $this->context = $context;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        global $DB;
        $nodes = $edges = [];
        $title = '';
        $fields = 'id, courseid, customint1';
        $params = ['enrol' => 'coursecompleted'];
        switch ($this->context->contextlevel) {
            case CONTEXT_USER:
                $user = core_user::get_user($this->context->instanceid);
                $title = $output->user_picture($user, ['class' => 'userpicture']) . fullname($user);
                $courses = enrol_get_all_users_courses($this->context->instanceid);
                foreach ($courses as $course) {
                    $nodes[] = self::buildnode($course);
                }
                foreach ($courses as $course) {
                    $records = $DB->get_records('enrol', ['enrol' => 'coursecompleted', 'customint1' => $course->id], '', $fields);
                    foreach ($records as $record) {
                        $fparams = ['userid' => $this->context->instanceid, 'id' => $record->id];
                        $futu = $DB->count_records('user_enrolments', $fparams) > 0 ? ', dashes: 1' : '';
                        $edges[] = "{id: $record->id, to: $record->courseid, from: $course->id $futu}";
                    }
                }
                break;
            case CONTEXT_COURSE:
                $id = $this->context->instanceid;
                $course = get_course($id);
                $title = $course->fullname;
                $all = [$id];
                $records = $DB->get_records('enrol', ['enrol' => 'coursecompleted', 'customint1' => $id], '', $fields);
                foreach ($records as $record) {
                    $all[] = $record->courseid;
                    $edges[] = "{id: $record->id, to: $record->courseid, from: $id}";
                }
                $records = $DB->get_records('enrol', ['enrol' => 'coursecompleted', 'courseid' => $id], '', $fields);
                foreach ($records as $record) {
                    $all[] = $record->customint1;
                    $edges[] = "{id: $record->id, to: $id, from: $record->customint1}";
                }
                $all = array_unique(array_values($all));
                foreach ($all as $value) {
                    $nodes[] = self::buildnode(get_course($value));
                }
                break;
            default:
                $all = ($this->context->contextlevel == CONTEXT_COURSECAT) ? $this->context->instanceid : 'all';
                foreach (get_courses($all) as $course) {
                    if ($course->id != 1) {
                        $nodes[] = self::buildnode($course);
                    }
                }
                $records = $DB->get_records('enrol', $params, '', $fields);
                foreach ($records as $record) {
                    $params['courseid'] = $record->courseid;
                    $params['customint1'] = $record->customint1;
                    $cnt = $DB->count_records('enrol', $params);
                    $cnt = $cnt > 0 ? $cnt : 1;
                    $edges[] = "{id: $record->id, from: $record->customint1, to: $record->courseid, value: $cnt}";
                }
        }
        return [
            'title' => $title,
            'nodes' => implode(', ', $nodes),
            'edges' => implode(', ', $edges),
            'nodeoptions' => "shape: 'circle', shadow: {enabled: true}, fixed: {x: false, y: false}",
            'edgeoptions' => "length: 300, width: 1, physics: true, smooth: true, arrows: { to: { enabled: true }}",
        ];
    }

    /**
     * Build node for a course
     *
     * @param stdClass $course
     * @return string
     */
    private static function buildnode($course) {
        $str = "{id: $course->id,";
        $str .= "group: $course->category,";
        $str .= "label: '$course->idnumber',";
        $str .= "title: '" . addslashes_js($course->fullname) . "'}";
        return $str;
    }
}
