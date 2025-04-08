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
 * @copyright  Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */

namespace report_learnpaths;

use advanced_testcase;

/**
 * Class for report learnpaths.
 *
 * @package    report_learnpaths
 * @copyright  Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */
final class index_test extends advanced_testcase {
    /**
     * Setup testcase.
     */
    public function setUp(): void {
        global $CFG;
        parent::setUp();
        $this->resetAfterTest(true);
        $this->setAdminUser();
        $CFG->enablecompletion = true;
        $enabled = enrol_get_plugins(true);
        $enabled['coursecompleted'] = true;
        set_config('enrol_plugins_enabled', implode(',', array_keys($enabled)));
    }

    /**
     * Test index file global.
     * @covers \report_learnpaths\output\main)]
     * @covers \report_learnpaths\output\renderer)]
     */
    public function test_index_file_global(): void {
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
     * Test index file coursecat.
     * @covers \report_learnpaths\output\main)]
     * @covers \report_learnpaths\output\renderer)]
     */
    public function test_index_file_coursecat(): void {
        global $CFG, $PAGE;
        chdir($CFG->dirroot . '/report/learnpaths');
        $generator = $this->getDataGenerator();
        $category = $generator->create_category();
        $user = $generator->create_user();
        $PAGE->set_title($user->id);
        $_POST['categoryid'] = $category->id;
        ob_start();
        include($CFG->dirroot . '/report/learnpaths/index.php');
        $html = ob_get_clean();
        $this->assertStringNotContainsString($category->id, $html);
    }

    /**
     * Test index file course.
     * @covers \report_learnpaths\output\main)]
     * @covers \report_learnpaths\output\renderer)]
     */
    public function test_index_file_course(): void {
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
        $this->assertStringContainsString($course->fullname, $html);
    }

    /**
     * Test index file user.
     * @covers \report_learnpaths\output\main)]
     * @covers \report_learnpaths\output\renderer)]
     */
    public function test_index_file_user(): void {
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
