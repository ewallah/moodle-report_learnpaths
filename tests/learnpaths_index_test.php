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
 * Tests for report learnpaths.
 *
 * @package    report_learnpaths
 * @copyright  2020 Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Class for report learnpaths.
 *
 * @package    report_learnpaths
 * @copyright  2020 Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 * @coversDefaultClass report_learnpaths
 */
class report_learnpaths_index_tests extends advanced_testcase {

    /**
     * Setup testcase.
     */
    public function setUp():void {
        global $CFG;
        $this->resetAfterTest(true);
        $this->setAdminUser();
        $CFG->enablecompletion = true;
        $enabled = enrol_get_plugins(true);
        $enabled['coursecompleted'] = true;
        set_config('enrol_plugins_enabled', implode(',', array_keys($enabled)));
    }

    /**
     * Test index file global.
     */
    public function test_index_file_global() {
        global $CFG, $DB, $PAGE;
        chdir($CFG->dirroot . '/report/learnpaths');
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();
        $role = $DB->get_record('role', ['shortname' => 'editingteacher']);
        $generator->enrol_user($user->id, $course->id, $role->shortname);
        $PAGE->set_title($user->id);
        ob_start();
        include($CFG->dirroot . '/report/learnpaths/index.php');
        $html = ob_get_clean();
        $this->assertStringContainsString($course->fullname, $html);
    }

    /**
     * Test index file course.
     */
    public function test_index_file_course() {
        global $CFG, $DB, $PAGE;
        chdir($CFG->dirroot . '/report/learnpaths');
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();
        $role = $DB->get_record('role', ['shortname' => 'editingteacher']);
        $generator->enrol_user($user->id, $course->id, $role->shortname);
        $PAGE->set_title($course->id);
        $_POST['courseid'] = $course->id;
        ob_start();
        include($CFG->dirroot . '/report/learnpaths/index.php');
        $html = ob_get_clean();
        $this->assertStringNotContainsString($course->fullname, $html);
    }

    /**
     * Test index file user.
     */
    public function test_index_file_user() {
        global $CFG, $DB, $PAGE;
        chdir($CFG->dirroot . '/report/learnpaths');
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();
        $role = $DB->get_record('role', ['shortname' => 'editingteacher']);
        $generator->enrol_user($user->id, $course->id, $role->shortname);
        $PAGE->set_title('general');
        $_POST['userid'] = $user->id;
        ob_start();
        include($CFG->dirroot . '/report/learnpaths/index.php');
        $html = ob_get_clean();
        $this->assertStringContainsString($course->fullname, $html);
    }
}
