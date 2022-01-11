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
 * The Learning path report viewed event.
 *
 * @package   report_learnpaths
 * @copyright 2020 Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace report_learnpaths\event;

/**
 * The learning paths report viewed event.
 *
 * @package   report_learnpaths
 * @copyright 2020 Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_viewed extends \core\event\base {

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventreportviewed', 'report_learnpaths');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        switch ($this->contextlevel) {
            case CONTEXT_USER:
                return "The user with id '$this->userid' viewed the learning path of the user with id '$this->relateduserid'.";
            case CONTEXT_COURSE:
                return "The user with id '$this->userid' viewed the learning path of the course with id '$this->courseid'.";
            case CONTEXT_COURSECAT:
                return "The user with id '$this->userid' viewed learning path of the category with id $this->contextinstanceid.";
            default:
                return "The user with id '$this->userid' viewed all possible learning paths.";
        }
    }

    /**
     * Returns relevant URL.
     *
     * @return \moodle_url
     */
    public function get_url() {
        switch ($this->contextlevel) {
            case CONTEXT_USER:
               return new \moodle_url('/report/learnpaths/index.php',  ['userid' => $this->relateduserid]);
            case CONTEXT_COURSE:
               return new \moodle_url('/report/learnpaths/index.php', ['courseid' => $this->courseid]);
            case CONTEXT_COURSECAT:
                return new \moodle_url('/report/learnpaths/index.php', ['categoryid' => $this->contextinstanceid]);
            default:
               return new \moodle_url('/report/learnpaths/index.php');
        }
    }
}
