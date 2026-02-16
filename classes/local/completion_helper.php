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

namespace format_pathway\local;

use completion_info;
use course_modinfo;

/**
 * Helper for completion-based navigation logic.
 *
 * Provides reusable, testable methods for determining which section
 * a learner should be directed to based on activity completion state.
 *
 * @package   format_pathway
 * @copyright 2025 Your Company
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class completion_helper {

    /**
     * Find the first section with incomplete tracked activities.
     *
     * Iterates visible sections (skipping section 0) and returns the section
     * number of the first one containing at least one incomplete tracked
     * activity. Returns null if all sections are complete or if completion
     * tracking is disabled.
     *
     * @param \stdClass       $course  The course record.
     * @param course_modinfo  $modinfo The fast modinfo for the course.
     * @param completion_info $completioninfo Completion info for the course.
     * @return int|null Section number, or null if none found.
     */
    public static function get_first_incomplete_section(
        \stdClass $course,
        course_modinfo $modinfo,
        completion_info $completioninfo
    ): ?int {
        if (!$completioninfo->is_enabled()) {
            return null;
        }

        $sections = $modinfo->get_section_info_all();

        foreach ($sections as $section) {
            if ($section->section == 0 || !$section->uservisible) {
                continue;
            }

            if (empty($modinfo->sections[$section->section])) {
                continue;
            }

            $hasactivities = false;
            $sectioncomplete = true;

            foreach ($modinfo->sections[$section->section] as $cmid) {
                $cm = $modinfo->cms[$cmid];
                if (!$cm->uservisible) {
                    continue;
                }
                if ($completioninfo->is_enabled($cm) == COMPLETION_TRACKING_NONE) {
                    continue;
                }

                $hasactivities = true;
                $data = $completioninfo->get_data($cm);
                if ($data->completionstate != COMPLETION_COMPLETE
                        && $data->completionstate != COMPLETION_COMPLETE_PASS) {
                    $sectioncomplete = false;
                    break;
                }
            }

            if ($hasactivities && !$sectioncomplete) {
                return (int)$section->section;
            }
        }

        return null;
    }

    /**
     * Get the first visible section number (excluding section 0).
     *
     * @param course_modinfo $modinfo The fast modinfo for the course.
     * @return int|null Section number, or null if no visible sections.
     */
    public static function get_first_visible_section(course_modinfo $modinfo): ?int {
        foreach ($modinfo->get_section_info_all() as $section) {
            if ($section->section > 0 && $section->uservisible) {
                return (int)$section->section;
            }
        }
        return null;
    }

    /**
     * Determine the target section for a learner landing on the course.
     *
     * Returns the first incomplete section if completion is enabled,
     * otherwise falls back to the first visible section.
     *
     * @param \stdClass $course The course record.
     * @return int|null Section number to redirect to.
     */
    public static function get_learner_target_section(\stdClass $course): ?int {
        $modinfo = get_fast_modinfo($course);
        $completioninfo = new completion_info($course);

        $target = self::get_first_incomplete_section($course, $modinfo, $completioninfo);
        if ($target !== null) {
            return $target;
        }

        return self::get_first_visible_section($modinfo);
    }
}
