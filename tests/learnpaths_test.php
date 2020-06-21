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
class report_learnpaths_tests extends advanced_testcase {

    /**
     * Setup testcase.
     */
    public function setUp() {
        global $CFG;
        $this->resetAfterTest(true);
        $this->setAdminUser();
        $CFG->enablecompletion = true;
        $enabled = enrol_get_plugins(true);
        $enabled['coursecompleted'] = true;
        set_config('enrol_plugins_enabled', implode(',', array_keys($enabled)));
    }

    /**
     * Test privacy.
     * @covers report_learnpaths\privacy\provider
     */
    public function test_privacy() {
        $privacy = new report_learnpaths\privacy\provider();
        $this->assertEquals($privacy->get_reason(), 'privacy:metadata');
    }

    /**
     * Test renderer.
     * @covers report_learnpaths\output\renderer
     */
    public function test_renderer() {
        global $PAGE;
        $generator = $this->getDataGenerator();
        $course1 = $generator->create_course();
        $context = context_course::instance($course1->id);
        $renderable = new \report_learnpaths\output\main($context);
        $output = $PAGE->get_renderer('report_learnpaths');
        $this->assertContains('new vis.Network', $output->render_main($renderable));
    }

    /**
     * Test page_type_list.
     */
    public function test_page_type_list() {
        global $CFG;
        require_once($CFG->dirroot . '/report/learnpaths/lib.php');
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $context = context_course::instance($course->id);
        $this->assertCount(5, report_learnpaths_page_type_list('report', context_system::instance(), $context));
    }

    /**
     * Test renderable.
     * @covers report_learnpaths\output\main
     */
    public function test_network1() {
        global $DB, $PAGE;
        $generator = $this->getDataGenerator();
        $course1 = $generator->create_course();
        $course2 = $generator->create_course();
        $plugin = enrol_get_plugin('coursecompleted');
        $plugin->add_instance($course1, ['customint1' => $course2->id]);
        $plugin->add_instance($course2, ['customint1' => $course1->id]);
        $records = $DB->get_records('enrol', ['enrol' => 'coursecompleted'], '', 'id, courseid, customint1');
        $this->assertCount(2, $records);
        $context = context_system::instance();
        $renderable = new \report_learnpaths\output\main($context);
        $output = $PAGE->get_renderer('report_learnpaths');
        $this->assertContains('new vis.Network', $output->render($renderable));
    }

    /**
     * Test renderable.
     * @covers report_learnpaths\output\main
     */
    public function test_network2() {
        global $PAGE;
        $generator = $this->getDataGenerator();
        $course1 = $generator->create_course();
        $course2 = $generator->create_course();
        $plugin = enrol_get_plugin('coursecompleted');
        $plugin->add_instance($course1, ['customint1' => $course2->id]);
        $plugin->add_instance($course2, ['customint1' => $course1->id]);
        $user = $generator->create_user();
        $generator->enrol_user($user->id, $course1->id);
        $generator->enrol_user($user->id, $course2->id);
        $context = context_user::instance($user->id);
        $renderable = new \report_learnpaths\output\main($context);
        $output = $PAGE->get_renderer('report_learnpaths');
        $this->assertContains('new vis.Network', $output->render($renderable));
    }

    /**
     * Test renderable.
     * @covers report_learnpaths\output\main
     */
    public function test_network3() {
        global $PAGE;
        $generator = $this->getDataGenerator();
        $course1 = $generator->create_course();
        $course2 = $generator->create_course();
        $plugin = enrol_get_plugin('coursecompleted');
        $plugin->add_instance($course1, ['customint1' => $course2->id]);
        $plugin->add_instance($course2, ['customint1' => $course1->id]);
        $context = context_course::instance($course1->id);
        $renderable = new \report_learnpaths\output\main($context);
        $output = $PAGE->get_renderer('report_learnpaths');
        $this->assertContains('new vis.Network', $output->render($renderable));
    }

    /**
     * Tests the report navigation as an admin.
     */
    public function test_navigation() {
        global $CFG, $DB, $PAGE, $USER;
        require_once($CFG->dirroot . '/report/learnpaths/lib.php');
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $context = context_course::instance($course->id);
        $PAGE->set_url('/course/view.php', ['id' => $course->id]);
        $tree = new \global_navigation($PAGE);
        \report_learnpaths_extend_navigation_course($tree, $course, $context);
        $user = $generator->create_user();
        $tree = new \core_user\output\myprofile\tree();
        $this->assertTrue(report_learnpaths_myprofile_navigation($tree, $user, true, $course));
        $teacher = $generator->create_user();
        $role = $DB->get_record('role', ['shortname' => 'editingteacher']);
        $generator->enrol_user($teacher->id, $course->id, $role->shortname);
        $tree = new \core_user\output\myprofile\tree();
        $this->assertTrue(report_learnpaths_myprofile_navigation($tree, $teacher, true, $course));
        $this->setUser($teacher);
        $this->assertFalse(report_learnpaths_myprofile_navigation($tree, $user, false, $course));
        $this->setAdminUser();
        $this->assertFalse(report_learnpaths_myprofile_navigation($tree, $teacher, false, $course));
        $this->setGuestUser();
        $this->assertFalse(report_learnpaths_myprofile_navigation($tree, $USER, true, $course));
    }

    /**
     * Test the report viewed event.
     * @covers report_learnpaths\event\report_viewed
     */
    public function test_report_viewed() {
        $courseid = $this->getDataGenerator()->create_course()->id;
        $context = context_course::instance($courseid);
        require_capability('report/learnpaths:view', $context);
        $event = \report_learnpaths\event\report_viewed::create(['context' => $context]);
        $this->assertEquals('Learning path report viewed', $event->get_name());
        $this->assertContains('The user with id ', $event->get_description());
        $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);
        $this->assertInstanceOf('\report_learnpaths\event\report_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $url = new moodle_url('/report/learnpaths/index.php', ['courseid' => $courseid]);
        $this->assertEquals($url, $event->get_url());
        $this->assertEventContextNotUsed($event);

        $userid = $this->getDataGenerator()->create_user()->id;
        $context = context_user::instance($userid);
        require_capability('report/learnpaths:view', $context);
        $event = \report_learnpaths\event\report_viewed::create(['context' => $context]);
        $this->assertEquals('Learning path report viewed', $event->get_name());
        $this->assertContains('The user with id ', $event->get_description());
        $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);
        $this->assertInstanceOf('\report_learnpaths\event\report_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $url = new moodle_url('/report/learnpaths/index.php', ['userid' => $userid]);
        $this->assertEquals($url, $event->get_url());
        $this->assertEventContextNotUsed($event);

        $context = context_system::instance();
        require_capability('report/learnpaths:view', $context);
        $event = \report_learnpaths\event\report_viewed::create(['context' => $context]);
        $this->assertEquals('Learning path report viewed', $event->get_name());
        $this->assertContains('The user with id ', $event->get_description());
        $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);
        $this->assertInstanceOf('\report_learnpaths\event\report_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $url = new moodle_url('/report/learnpaths/index.php');
        $this->assertEquals($url, $event->get_url());
        $this->assertEventContextNotUsed($event);
    }
}