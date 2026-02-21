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
 * Pathway format - course rendering entry point.
 *
 * Included by /course/view.php. Variables $course, $PAGE, $displaysection
 * are already available from the including script.
 *
 * When a learner visits the course without a specific section selected,
 * this script auto-redirects them to the first section with incomplete
 * activities (or section 1 if completion is not enabled / all complete).
 * Teachers/editors see the overview landing page instead.
 *
 * @package   format_pathway
 * @copyright 2025 Your Company
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use format_pathway\local\completion_helper;

// Retrieve course format option fields and add them to the $course object.
$format = core_courseformat\base::instance($course);
$course = $format->get_course();
$context = context_course::instance($course->id);

// Make sure section 0 is created.
course_create_sections_if_missing($course, 0);

// Auto-redirect logic: when no section is specified in the URL and the user
// is a learner (not editing), redirect to the first incomplete section.
if (empty($displaysection) && !$PAGE->user_is_editing()) {
    if (!has_capability('moodle/course:update', $context)) {
        $targetsection = completion_helper::get_learner_target_section($course);
        if ($targetsection !== null) {
            $url = $format->get_view_url($targetsection);
            if ($url) {
                redirect($url);
            }
        }
    }
}

// Setup the format base instance for the current section.
if (!empty($displaysection)) {
    $format->set_sectionnum($displaysection);
}

// Load the sidebar AMD module.
// Retrieve the user's saved sidebar collapsed preference.
$sidebarcollapsed = get_user_preferences('format_pathway_sidebar_collapsed', '0');
$PAGE->requires->js_call_amd(
    'format_pathway/sidebar',
    'init',
    [(bool)(int)$sidebarcollapsed]
);

// Render the course content via the output class and renderer.
$renderer = $format->get_renderer($PAGE);
$outputclass = $format->get_output_classname('content');
$widget = new $outputclass($format);
echo $renderer->render($widget);
