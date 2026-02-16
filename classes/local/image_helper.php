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

use context_course;
use moodle_url;

/**
 * Helper for resolving section header images.
 *
 * Looks up images stored in the 'format_pathway' / 'sectionimage' file area
 * and returns pluginfile URLs suitable for rendering in templates.
 *
 * @package   format_pathway
 * @copyright 2025 Your Company
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class image_helper {

    /**
     * Get the URL for a section's header image.
     *
     * @param int $courseid The course ID.
     * @param int $sectionid The section ID (database id, not section number).
     * @return string|null The image URL, or null if no image is set.
     */
    public static function get_section_image_url(int $courseid, int $sectionid): ?string {
        $context = context_course::instance($courseid);
        $fs = get_file_storage();

        $files = $fs->get_area_files(
            $context->id,
            'format_pathway',
            'sectionimage',
            $sectionid,
            'sortorder DESC, id ASC',
            false // Exclude directories.
        );

        if (empty($files)) {
            return null;
        }

        $file = reset($files);

        $url = moodle_url::make_pluginfile_url(
            $context->id,
            'format_pathway',
            'sectionimage',
            $sectionid,
            $file->get_filepath(),
            $file->get_filename(),
            false
        );

        return $url->out(false);
    }

    /**
     * Get image URLs for all sections in a course, keyed by section ID.
     *
     * This is more efficient than calling get_section_image_url() in a loop
     * because it fetches all files in a single query.
     *
     * @param int $courseid The course ID.
     * @return array Associative array of sectionid => image URL string.
     */
    public static function get_all_section_images(int $courseid): array {
        $context = context_course::instance($courseid);
        $fs = get_file_storage();

        $files = $fs->get_area_files(
            $context->id,
            'format_pathway',
            'sectionimage',
            false, // All itemids.
            'itemid, sortorder DESC, id ASC',
            false // Exclude directories.
        );

        $images = [];
        foreach ($files as $file) {
            $sectionid = $file->get_itemid();
            // Only take the first file per section (in case of duplicates).
            if (isset($images[$sectionid])) {
                continue;
            }

            $url = moodle_url::make_pluginfile_url(
                $context->id,
                'format_pathway',
                'sectionimage',
                $sectionid,
                $file->get_filepath(),
                $file->get_filename(),
                false
            );
            $images[$sectionid] = $url->out(false);
        }

        return $images;
    }
}
