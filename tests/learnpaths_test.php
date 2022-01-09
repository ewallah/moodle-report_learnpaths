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
namespace report_learnpaths;

defined('MOODLE_INTERNAL') || die();

use moodle_url;

/**
 * Class for report learnpaths.
 *
 * @package    report_learnpaths
 * @copyright  2020 Renaat Debleu <info@eWallah.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */
class learnpaths_test extends \advanced_testcase {

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
     * Test privacy.
     */
    public function test_privacy() {
        $privacy = new \report_learnpaths\privacy\provider();
        $this->assertEquals($privacy->get_reason(), 'privacy:metadata');
    }

    /**
     * Test renderer.
     */
    public function test_renderer() {
        global $PAGE;
        $generator = $this->getDataGenerator();
        $course1 = $generator->create_course();
        $context = \context_course::instance($course1->id);
        $renderable = new \report_learnpaths\output\main($context);
        $output = $PAGE->get_renderer('report_learnpaths');
        $this->assertStringContainsString('new vis.Network', $output->render_main($renderable));
    }

    /**
     * Test page_type_list.
     */
    public function test_page_type_list() {
        global $CFG;
        require_once($CFG->dirroot . '/report/learnpaths/lib.php');
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $context = \context_course::instance($course->id);
        $this->assertCount(5, report_learnpaths_page_type_list('report', \context_system::instance(), $context));
    }

    /**
     * Test renderable.
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
        $context = \context_system::instance();
        $renderable = new \report_learnpaths\output\main($context);
        $output = $PAGE->get_renderer('report_learnpaths');
        $this->assertStringContainsString('new vis.Network', $output->render($renderable));
    }

    /**
     * Test renderable.
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
        $context = \context_user::instance($user->id);
        $renderable = new \report_learnpaths\output\main($context);
        $output = $PAGE->get_renderer('report_learnpaths');
        $this->assertStringContainsString('new vis.Network', $output->render($renderable));
    }

    /**
     * Test renderable.
     */
    public function test_network3() {
        global $PAGE;
        $generator = $this->getDataGenerator();
        $course1 = $generator->create_course();
        $course2 = $generator->create_course();
        $plugin = enrol_get_plugin('coursecompleted');
        $plugin->add_instance($course1, ['customint1' => $course2->id]);
        $plugin->add_instance($course2, ['customint1' => $course1->id]);
        $context = \context_course::instance($course1->id);
        $renderable = new \report_learnpaths\output\main($context);
        $output = $PAGE->get_renderer('report_learnpaths');
        $this->assertStringContainsString('new vis.Network', $output->render($renderable));
    }

    /**
     * Tests the report navigation as an admin.
     */
    public function test_navigation() {
        global $CFG, $PAGE, $USER;
        require_once($CFG->dirroot . '/report/learnpaths/lib.php');
        $generator = $this->getDataGenerator();
        $category = $this->getDataGenerator()->create_category();
        $context = \context_coursecat::instance($category->id);
        $course = $generator->create_course();
        $PAGE->set_url('/course/view.php', ['id' => $course->id]);
        $tree = new \global_navigation($PAGE);
        \report_learnpaths_extend_navigation_category_settings($tree, $context);
        $context = \context_course::instance($course->id);
        \report_learnpaths_extend_navigation_course($tree, $course, $context);
        $user = $generator->create_user();
        $tree = new \core_user\output\myprofile\tree();
        $this->assertTrue(report_learnpaths_myprofile_navigation($tree, $user, true, $course));
        $tree = new \core_user\output\myprofile\tree();
        $this->setGuestUser();
        $this->assertFalse(report_learnpaths_myprofile_navigation($tree, $USER, true, $course));
    }

    /**
     * Test the report viewed event.
     */
    public function test_report_viewed() {
        $categoryid = $this->getDataGenerator()->create_category()->id;
        $context = \context_coursecat::instance($categoryid);
        require_capability('report/learnpaths:view', $context);
        $event = \report_learnpaths\event\report_viewed::create(['context' => $context]);
        $this->assertEquals('Learning path report viewed', $event->get_name());
        $this->assertStringContainsString('The user with id ', $event->get_description());
        $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);
        $this->assertInstanceOf('\report_learnpaths\event\report_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $url = new moodle_url('/report/learnpaths/index.php', ['categoryid' => $categoryid]);
        $this->assertEquals($url, $event->get_url());
        $this->assertEventContextNotUsed($event);

        $courseid = $this->getDataGenerator()->create_course()->id;
        $context = \context_course::instance($courseid);
        require_capability('report/learnpaths:view', $context);
        $event = \report_learnpaths\event\report_viewed::create(['context' => $context]);
        $this->assertEquals('Learning path report viewed', $event->get_name());
        $this->assertStringContainsString('The user with id ', $event->get_description());
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
        $context = \context_user::instance($userid);
        require_capability('report/learnpaths:view', $context);
        $event = \report_learnpaths\event\report_viewed::create(['context' => $context]);
        $this->assertEquals('Learning path report viewed', $event->get_name());
        $this->assertStringContainsString('The user with id ', $event->get_description());
        $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);
        $this->assertInstanceOf('\report_learnpaths\event\report_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $url = new moodle_url('/report/learnpaths/index.php', ['userid' => $userid]);
        $this->assertEquals($url, $event->get_url());
        $this->assertEventContextNotUsed($event);

        $context = \context_system::instance();
        require_capability('report/learnpaths:view', $context);
        $event = \report_learnpaths\event\report_viewed::create(['context' => $context]);
        $this->assertEquals('Learning path report viewed', $event->get_name());
        $this->assertStringContainsString('The user with id ', $event->get_description());
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
