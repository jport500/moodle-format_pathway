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
 * Output class for the Pathway format content.
 *
 * This overrides the core content output to wrap everything in our
 * custom sidebar + content layout.
 *
 * @package   format_pathway
 * @copyright 2025 Your Company
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_pathway\output\courseformat;

use core_courseformat\output\local\content as content_base;
use completion_info;
use format_pathway\local\image_helper;
use renderer_base;
use stdClass;

/**
 * Pathway content output class.
 *
 * Extends the core content output to inject sidebar navigation data,
 * per-section and overall completion progress, and prev/next navigation
 * into the template context.
 *
 * @package   format_pathway
 * @copyright 2025 Your Company
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content extends content_base {

    /**
     * Returns the template name for this output.
     *
     * @param renderer_base $renderer
     * @return string
     */
    public function get_template_name(renderer_base $renderer): string {
        return 'format_pathway/local/content';
    }

    /**
     * Export data for the mustache template.
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output): stdClass {
        $data = parent::export_for_template($output);
        $format = $this->format;
        $course = $format->get_course();
        $modinfo = get_fast_modinfo($course);
        $completioninfo = new completion_info($course);
        $sections = $modinfo->get_section_info_all();

        $formatoptions = $format->get_format_options();
        $sidebarposition = $formatoptions['pathwaysidebar'] ?? 'left';
        $showprogress = !empty($formatoptions['pathwayshowprogress']);
        $showimages = !empty($formatoptions['pathwayshowimages']);
        $showsection0insidebar = !empty($formatoptions['pathwayshowsection0']);
        $displaysection = $format->get_sectionnum() ?? 0;

        // Fetch section images only if the toggle is enabled.
        $sectionimages = $showimages ? image_helper::get_all_section_images($course->id) : [];

        // Build sidebar data with per-section completion stats.
        [$sidebardata, $overallcomplete, $overalltotal, $totalsections] =
            $this->build_sidebar_data(
                $format, $modinfo, $completioninfo, $sections,
                $displaysection, $sectionimages, $showsection0insidebar
            );

        $overallpct = ($overalltotal > 0) ? round(($overallcomplete / $overalltotal) * 100) : 0;

        // Build prev/next navigation.
        [$prevsection, $nextsection] = $this->build_section_navigation(
            $format, $sections, $displaysection
        );

        // Get the current section's image for the content area banner.
        $currentsectionimage = null;
        if ($showimages && $displaysection > 0 && isset($sections[$displaysection])) {
            $sectionid = $sections[$displaysection]->id;
            $currentsectionimage = $sectionimages[$sectionid] ?? null;
        }

        $data->pathway = [
            'coursename' => format_string($course->fullname),
            'courseshortname' => format_string($course->shortname),
            'sidebarsections' => $sidebardata,
            'sidebarposition' => $sidebarposition,
            'sidebarleft' => ($sidebarposition === 'left'),
            'sidebarright' => ($sidebarposition === 'right'),
            'showprogress' => $showprogress,
            'overallpct' => $overallpct,
            'overallcomplete' => $overallcomplete,
            'overalltotal' => $overalltotal,
            'totalsections' => $totalsections,
            'currentsectionnum' => $displaysection,
            'hassections' => ($totalsections > 0),
            'hasprevsection' => !empty($prevsection),
            'prevsection' => $prevsection,
            'hasnextsection' => !empty($nextsection),
            'nextsection' => $nextsection,
            'completionenabled' => $completioninfo->is_enabled(),
            'currentsectionimage' => $currentsectionimage,
            'hascurrentsectionimage' => !empty($currentsectionimage),
            'section0insidebar' => $showsection0insidebar,
            'showinitialabove' => !$showsection0insidebar,
        ];

        return $data;
    }

    /**
     * Build sidebar section data with completion progress.
     *
     * @param \core_courseformat\base $format      The course format instance.
     * @param \course_modinfo         $modinfo     Fast modinfo for the course.
     * @param completion_info         $completioninfo Completion info for the course.
     * @param array                   $sections    Array of section_info objects.
     * @param int                     $displaysection Currently displayed section number.
     * @param array                   $sectionimages Associative array of sectionid => image URL.
     * @param bool                    $includesection0 Whether to include section 0 in the sidebar.
     * @return array [sidebardata[], overallcomplete, overalltotal, totalsections]
     */
    protected function build_sidebar_data(
        \core_courseformat\base $format,
        \course_modinfo $modinfo,
        completion_info $completioninfo,
        array $sections,
        int $displaysection,
        array $sectionimages = [],
        bool $includesection0 = false
    ): array {
        $sidebardata = [];
        $overalltotal = 0;
        $overallcomplete = 0;
        $totalsections = 0;

        foreach ($sections as $section) {
            if (!$section->uservisible) {
                continue;
            }
            if ($section->section == 0 && !$includesection0) {
                continue;
            }

            // Section 0 is included in the sidebar but not in the numbered section count.
            if ($section->section > 0) {
                $totalsections++;
            }

            [$sectioncomplete, $sectiontotal] = $this->calculate_section_completion(
                $modinfo, $completioninfo, $section
            );
            $overalltotal += $sectiontotal;
            $overallcomplete += $sectioncomplete;

            $progresspct = ($sectiontotal > 0) ? round(($sectioncomplete / $sectiontotal) * 100) : 0;
            $iscomplete = ($sectiontotal > 0 && $sectioncomplete === $sectiontotal);
            $isinprogress = ($sectioncomplete > 0 && !$iscomplete);

            $sectionurl = $format->get_view_url($section);
            $imageurl = $sectionimages[$section->id] ?? null;

            $sidebardata[] = [
                'num' => $section->section,
                'name' => $format->get_section_name($section),
                'url' => $sectionurl ? $sectionurl->out(false) : '#',
                'iscurrent' => ($displaysection == $section->section),
                'iscomplete' => $iscomplete,
                'isinprogress' => $isinprogress,
                'progresspct' => $progresspct,
                'completedcount' => $sectioncomplete,
                'totalcount' => $sectiontotal,
                'hastrackeditems' => ($sectiontotal > 0),
                'imageurl' => $imageurl,
                'hasimage' => !empty($imageurl),
                'issection0' => ($section->section == 0),
            ];
        }

        return [$sidebardata, $overallcomplete, $overalltotal, $totalsections];
    }

    /**
     * Calculate the number of completed and total tracked activities in a section.
     *
     * @param \course_modinfo $modinfo        Fast modinfo for the course.
     * @param completion_info $completioninfo Completion info for the course.
     * @param \section_info   $section        The section to calculate for.
     * @return array [completed_count, total_count]
     */
    protected function calculate_section_completion(
        \course_modinfo $modinfo,
        completion_info $completioninfo,
        \section_info $section
    ): array {
        $total = 0;
        $complete = 0;

        if (!$completioninfo->is_enabled() || empty($modinfo->sections[$section->section])) {
            return [$complete, $total];
        }

        foreach ($modinfo->sections[$section->section] as $cmid) {
            $cm = $modinfo->cms[$cmid];
            if (!$cm->uservisible) {
                continue;
            }
            if ($completioninfo->is_enabled($cm) == COMPLETION_TRACKING_NONE) {
                continue;
            }

            $total++;
            $data = $completioninfo->get_data($cm);
            if ($data->completionstate == COMPLETION_COMPLETE
                    || $data->completionstate == COMPLETION_COMPLETE_PASS) {
                $complete++;
            }
        }

        return [$complete, $total];
    }

    /**
     * Build previous/next section navigation data.
     *
     * @param \core_courseformat\base $format         The course format instance.
     * @param array                   $sections       Array of section_info objects.
     * @param int                     $displaysection Currently displayed section number.
     * @return array [prevsection|null, nextsection|null] Each is an associative array
     *               with 'name' and 'url' keys, or null if no prev/next exists.
     */
    protected function build_section_navigation(
        \core_courseformat\base $format,
        array $sections,
        int $displaysection
    ): array {
        $prevsection = null;
        $nextsection = null;

        if ($displaysection <= 0) {
            return [$prevsection, $nextsection];
        }

        // Previous section: scan backwards from current.
        for ($i = $displaysection - 1; $i >= 1; $i--) {
            if (isset($sections[$i]) && $sections[$i]->uservisible) {
                $url = $format->get_view_url($sections[$i]);
                if ($url) {
                    $prevsection = [
                        'name' => $format->get_section_name($sections[$i]),
                        'url' => $url->out(false),
                    ];
                }
                break;
            }
        }

        // Next section: scan forwards from current.
        $sectioncount = count($sections);
        for ($i = $displaysection + 1; $i < $sectioncount; $i++) {
            if (isset($sections[$i]) && $sections[$i]->uservisible) {
                $url = $format->get_view_url($sections[$i]);
                if ($url) {
                    $nextsection = [
                        'name' => $format->get_section_name($sections[$i]),
                        'url' => $url->out(false),
                    ];
                }
                break;
            }
        }

        return [$prevsection, $nextsection];
    }
}
