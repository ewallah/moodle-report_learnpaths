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
 * @copyright  2020 Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace report_learnpaths\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;

/**
 * Learning paths report data.
 *
 * @package    report_learnpaths
 * @copyright  2020 Renaat Debleu <info@eWallah.net>
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
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        global $DB;
        $nodes = $edges = [];
        $nodeoptions = "shape: 'circle', shadow: {enabled: true}, fixed: {x: false, y: false}";
        $edgeoptions = "length: 150, width: 3, arrows: { to: { enabled: true }}";
        switch ($this->context->contextlevel) {
            case CONTEXT_USER:
                $id = $this->context->instanceid;
                if ($courses = enrol_get_all_users_courses($id)) {
                    foreach ($courses as $course) {
                        $nodes[] = $this->buildnode($course);
                        $params = ['enrol' => 'coursecompleted', 'customint1' => $course->id];
                        if ($records = $DB->get_records('enrol', $params, '', 'id, courseid')) {
                            foreach ($records as $record) {
                                $edges[] = "{from: $record->courseid, to: $course->id}";
                            }
                        }
                    }
                }
                break;
            case CONTEXT_COURSE:
                $id = $this->context->instanceid;
                $nodes[] = "{id: $id, label: $id}";
                $params = ['enrol' => 'coursecompleted', 'customint1' => $id];
                if ($records = $DB->get_records('enrol', $params, '', 'id, courseid')) {
                    foreach ($records as $record) {
                        $nodes[] = "{id: $record->courseid, label: $record->courseid}";
                        $edges[] = "{from: $record->courseid, to: $id}";
                    }
                }
                $params = ['enrol' => 'coursecompleted', 'courseid' => $id];
                if ($records = $DB->get_records('enrol', $params, '', 'id, customint1')) {
                    foreach ($records as $record) {
                        $nodes[] = "{id: $record->customint1, label: $record->customint1}";
                        $edges[] = "{from: $id, to: $record->customint1}";
                    }
                }
                break;
            default:
                $courses = get_courses();
                foreach ($courses as $course) {
                    $nodes[] = $this->buildnode($course);
                }
                if ($records = $DB->get_records('enrol', ['enrol' => 'coursecompleted'], '', 'id, courseid, customint1')) {
                    foreach ($records as $record) {
                        $edges[] = "{from: $record->customint1, to: $record->courseid}";
                    }
                }
        }
        return [
            'nodes' => implode(', ', $nodes),
            'edges' => implode(', ', $edges),
            'nodeoptions' => $nodeoptions,
            'edgeoptions' => $edgeoptions
        ];
    }

    /**
     * Build node for a course
     *
     * @param stdClass $course
     * @return string
     */
    private function buildnode($course) {
        $str = "{id: $course->id, ";
        $str .= "group: $course->category, ";
        $str .= "title: '" . addslashes_js($course->fullname) . "'}";
        return $str;
    }
}
