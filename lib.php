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
 * Main class for the Pathway course format.
 *
 * The Pathway format displays course sections one at a time with a custom
 * progress-tracking sidebar, replacing Moodle's default course index.
 * Designed for linear, structured learning paths common in SMB/nonprofit training.
 *
 * @package   format_pathway
 * @copyright 2025 Your Company
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Pathway course format class.
 *
 * @package   format_pathway
 * @copyright 2025 Your Company
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_pathway extends core_courseformat\base {

    /**
     * This format uses sections.
     *
     * @return bool
     */
    public function uses_sections(): bool {
        return true;
    }

    /**
     * Disable the default Moodle course index drawer.
     * We replace it with our own custom sidebar progress tracker.
     *
     * @return bool
     */
    public function uses_course_index(): bool {
        return false;
    }

    /**
     * Disable indentation - we use a cleaner card-based layout.
     *
     * @return bool
     */
    public function uses_indentation(): bool {
        return false;
    }

    /**
     * Get the course display mode.
     *
     * Defaults to COURSE_DISPLAY_MULTIPAGE (one section per page) for the
     * focused pathway experience, but allows teachers to switch to
     * COURSE_DISPLAY_SINGLEPAGE (all sections on one page) if preferred.
     *
     * @return int COURSE_DISPLAY_MULTIPAGE or COURSE_DISPLAY_SINGLEPAGE
     */
    public function get_course_display(): int {
        $course = $this->get_course();
        if (isset($course->coursedisplay)) {
            return (int)$course->coursedisplay;
        }
        return COURSE_DISPLAY_MULTIPAGE;
    }

    /**
     * Support AJAX for drag-and-drop editing.
     *
     * @return stdClass
     */
    public function supports_ajax(): stdClass {
        $ajaxsupport = new stdClass();
        $ajaxsupport->capable = true;
        return $ajaxsupport;
    }

    /**
     * Support reactive components for the course editor.
     *
     * @return bool
     */
    public function supports_components(): bool {
        return true;
    }

    /**
     * Allow section deletion.
     *
     * @param int|stdClass|section_info $section
     * @return bool
     */
    public function can_delete_section($section): bool {
        return true;
    }

    /**
     * Support news forum.
     *
     * @return bool
     */
    public function supports_news(): bool {
        return true;
    }

    /**
     * Return the display name of the given section.
     *
     * @param int|stdClass $section Section object from database or just field section.section
     * @return string Display name
     */
    public function get_section_name($section): string {
        $section = $this->get_section($section);
        if ((string)$section->name !== '') {
            return format_string(
                $section->name,
                true,
                ['context' => context_course::instance($this->courseid)]
            );
        } else {
            return $this->get_default_section_name($section);
        }
    }

    /**
     * Returns the default section name.
     *
     * @param stdClass $section Section object from database
     * @return string The default section name
     */
    public function get_default_section_name($section): string {
        if ($section->section == 0) {
            return get_string('section0name', 'format_pathway');
        } else {
            return get_string('sectionname', 'format_pathway') . ' ' . $section->section;
        }
    }

    /**
     * The URL to use for the specified course (with section).
     *
     * We always show one section at a time via the 'section' parameter.
     *
     * @param int|stdClass $section Section object from database or just field course_sections.section
     * @param array $options options for view URL
     * @return null|moodle_url
     */
    public function get_view_url($section, $options = []) {
        global $CFG;
        $course = $this->get_course();
        $url = new moodle_url('/course/view.php', ['id' => $course->id]);

        $sr = null;
        if (array_key_exists('sr', $options)) {
            $sr = $options['sr'];
        }

        if (is_object($section)) {
            $sectionno = $section->section;
        } else {
            $sectionno = $section;
        }

        if ($sectionno !== null) {
            if ($sr !== null) {
                if ($sr) {
                    $usercoursedisplay = COURSE_DISPLAY_MULTIPAGE;
                    $sectionno = $sr;
                } else {
                    $usercoursedisplay = COURSE_DISPLAY_SINGLEPAGE;
                }
            } else {
                $usercoursedisplay = COURSE_DISPLAY_MULTIPAGE;
            }

            if ($sectionno != 0 && $usercoursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                $url->param('section', $sectionno);
            } else {
                if (empty($CFG->linkcoursesections) && !empty($options['navigation'])) {
                    return null;
                }
                $url->set_anchor('section-' . $sectionno);
            }
        }
        return $url;
    }

    /**
     * Definitions of the additional options that this course format uses for course.
     *
     * @param bool $foreditform
     * @return array Array of options
     */
    public function course_format_options($foreditform = false): array {
        static $courseformatoptions = false;

        if ($courseformatoptions === false) {
            $cfgcoursedisplay = get_config('format_pathway', 'coursedisplay');
            $cfgsidebar = get_config('format_pathway', 'pathwaysidebar');
            $cfgshowprogress = get_config('format_pathway', 'pathwayshowprogress');
            $cfgshowimages = get_config('format_pathway', 'pathwayshowimages');
            $cfgshowsection0 = get_config('format_pathway', 'pathwayshowsection0');

            $courseformatoptions = [
                'coursedisplay' => [
                    'default' => ($cfgcoursedisplay !== false) ? (int)$cfgcoursedisplay : COURSE_DISPLAY_MULTIPAGE,
                    'type' => PARAM_INT,
                ],
                'pathwaysidebar' => [
                    'default' => ($cfgsidebar !== false && $cfgsidebar !== '') ? $cfgsidebar : 'left',
                    'type' => PARAM_ALPHA,
                ],
                'pathwayshowprogress' => [
                    'default' => ($cfgshowprogress !== false) ? (int)$cfgshowprogress : 1,
                    'type' => PARAM_INT,
                ],
                'pathwayshowimages' => [
                    'default' => ($cfgshowimages !== false) ? (int)$cfgshowimages : 1,
                    'type' => PARAM_INT,
                ],
                'pathwayshowsection0' => [
                    'default' => ($cfgshowsection0 !== false) ? (int)$cfgshowsection0 : 0,
                    'type' => PARAM_INT,
                ],
            ];
        }

        if ($foreditform && !isset($courseformatoptions['coursedisplay']['label'])) {
            $courseformatoptionsedit = [
                'coursedisplay' => [
                    'label' => new lang_string('courselayout', 'format_pathway'),
                    'help' => 'courselayout',
                    'help_component' => 'format_pathway',
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            COURSE_DISPLAY_MULTIPAGE => new lang_string('onesectionperpage', 'format_pathway'),
                            COURSE_DISPLAY_SINGLEPAGE => new lang_string('allsectionsonepage', 'format_pathway'),
                        ],
                    ],
                ],
                'pathwaysidebar' => [
                    'label' => new lang_string('sidebarposition', 'format_pathway'),
                    'help' => 'sidebarposition',
                    'help_component' => 'format_pathway',
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            'left' => new lang_string('sidebarleft', 'format_pathway'),
                            'right' => new lang_string('sidebarright', 'format_pathway'),
                        ],
                    ],
                ],
                'pathwayshowprogress' => [
                    'label' => new lang_string('showprogress', 'format_pathway'),
                    'help' => 'showprogress',
                    'help_component' => 'format_pathway',
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            1 => new lang_string('yes'),
                            0 => new lang_string('no'),
                        ],
                    ],
                ],
                'pathwayshowimages' => [
                    'label' => new lang_string('showimages', 'format_pathway'),
                    'help' => 'showimages',
                    'help_component' => 'format_pathway',
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            1 => new lang_string('yes'),
                            0 => new lang_string('no'),
                        ],
                    ],
                ],
                'pathwayshowsection0' => [
                    'label' => new lang_string('showsection0', 'format_pathway'),
                    'help' => 'showsection0',
                    'help_component' => 'format_pathway',
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            1 => new lang_string('yes'),
                            0 => new lang_string('no'),
                        ],
                    ],
                ],
            ];
            $courseformatoptions = array_merge_recursive($courseformatoptions, $courseformatoptionsedit);
        }
        return $courseformatoptions;
    }

    /**
     * Definitions of the additional options that this course format uses for sections.
     *
     * Adds a section image file manager to the section edit form.
     * The actual file is stored via Moodle's file API in the
     * 'format_pathway' / 'sectionimage' file area.
     *
     * @param bool $foreditform Whether this is for the edit form.
     * @return array Array of section format options.
     */
    public function section_format_options($foreditform = false): array {
        static $sectionformatoptions = false;

        if ($sectionformatoptions === false) {
            $sectionformatoptions = [
                'sectionimage' => [
                    'default' => 0,
                    'type' => PARAM_INT,
                ],
            ];
        }

        if ($foreditform && !isset($sectionformatoptions['sectionimage']['label'])) {
            $sectionformatoptionsedit = [
                'sectionimage' => [
                    'label' => new lang_string('sectionimage', 'format_pathway'),
                    'help' => 'sectionimage',
                    'help_component' => 'format_pathway',
                    'element_type' => 'filemanager',
                    'element_attributes' => [
                        null,
                        [
                            'subdirs' => 0,
                            'maxfiles' => 1,
                            'accepted_types' => ['.jpg', '.png', '.gif', '.webp'],
                            'return_types' => FILE_INTERNAL,
                        ],
                    ],
                ],
            ];
            $sectionformatoptions = array_merge_recursive($sectionformatoptions, $sectionformatoptionsedit);
        }

        return $sectionformatoptions;
    }

    /**
     * Add a file picker to the section edit form for header images.
     *
     * This is intentionally empty — the file manager is added via
     * section_format_options() above. We override this to avoid
     * the parent adding duplicate elements.
     *
     * @param \MoodleQuickForm $mform The section edit form.
     * @param bool $forsection Whether this is for a section (true) or course (false).
     * @return array Array of added element names.
     */
    public function create_edit_form_elements(&$mform, $forsection = false): array {
        return parent::create_edit_form_elements($mform, $forsection);
    }

    /**
     * Validate and process format options, including file uploads.
     *
     * This is called with the raw form data before it's filtered and stored.
     * We intercept the sectionimage draft area ID here to save the uploaded
     * file to permanent storage, since update_section_format_options() does
     * not receive the raw form values in Moodle 5.x.
     *
     * @param array $rawdata Raw data from the form submission.
     * @param int|null $sectionid The section ID (null for course-level options).
     * @return array Validated data to be stored.
     */
    public function validate_format_options(array $rawdata, ?int $sectionid = null): array {
        if ($sectionid && !empty($rawdata['sectionimage']) && is_numeric($rawdata['sectionimage'])) {
            global $DB;
            $section = $DB->get_record('course_sections', ['id' => $sectionid], '*', MUST_EXIST);
            $context = \context_course::instance($section->course);

            file_save_draft_area_files(
                (int)$rawdata['sectionimage'],
                $context->id,
                'format_pathway',
                'sectionimage',
                $sectionid,
                [
                    'subdirs' => 0,
                    'maxfiles' => 1,
                    'accepted_types' => ['.jpg', '.png', '.gif', '.webp'],
                ]
            );

            // Check if we still have a file after saving (user may have deleted it).
            $fs = get_file_storage();
            $files = $fs->get_area_files($context->id, 'format_pathway', 'sectionimage', $sectionid, 'id', false);
            $rawdata['sectionimage'] = !empty($files) ? 1 : 0;
        }

        return parent::validate_format_options($rawdata, $sectionid);
    }

    /**
     * Prepare the section edit form for display.
     *
     * Pre-populates the file manager with any existing section image.
     *
     * @param \stdClass $data The form data being prepared.
     * @param bool $forsection Whether preparing for section edit.
     * @return \stdClass The modified form data.
     */
    public function section_edit_form_data($data, $forsection = true) {
        if ($forsection && !empty($data->id)) {
            $context = \context_course::instance($data->course);

            $draftitemid = file_get_submitted_draft_itemid('sectionimage');
            file_prepare_draft_area(
                $draftitemid,
                $context->id,
                'format_pathway',
                'sectionimage',
                $data->id,
                [
                    'subdirs' => 0,
                    'maxfiles' => 1,
                    'accepted_types' => ['.jpg', '.png', '.gif', '.webp'],
                ]
            );
            $data->sectionimage = $draftitemid;
        }

        return $data;
    }

    /**
     * Returns the list of blocks to be automatically added for the newly created course.
     *
     * @return array of default blocks
     */
    public function get_default_blocks(): array {
        return [
            BLOCK_POS_LEFT => [],
            BLOCK_POS_RIGHT => [],
        ];
    }

    /**
     * Whether this format allows to delete sections.
     *
     * @param int|stdClass|section_info $section
     * @return bool
     */
    public function can_delete_section_info($section): bool {
        return true;
    }

    /**
     * Prepares the templateable data for section name editing.
     *
     * @param section_info|stdClass $section
     * @param bool $linkifneeded
     * @param bool $editable
     * @param null|lang_string|string $edithint
     * @param null|lang_string|string $editlabel
     * @return \core\output\inplace_editable
     */
    public function inplace_editable_render_section_name(
        $section,
        $linkifneeded = true,
        $editable = null,
        $edithint = null,
        $editlabel = null
    ) {
        if (empty($edithint)) {
            $edithint = new lang_string('editsectionname', 'format_pathway');
        }
        if (empty($editlabel)) {
            $title = get_section_name($section->course, $section);
            $editlabel = new lang_string('newsectionname', 'format_pathway', $title);
        }
        return parent::inplace_editable_render_section_name(
            $section,
            $linkifneeded,
            $editable,
            $edithint,
            $editlabel
        );
    }
}

/**
 * Implements callback inplace_editable() allowing to edit values in-place.
 *
 * This is a callback function; Moodle core requires it as a global function
 * in lib.php for the inplace editable API.
 *
 * @param string $itemtype The item type (sectionname or sectionnamenl).
 * @param int    $itemid   The section id.
 * @param mixed  $newvalue The new section name value.
 * @return \core\output\inplace_editable
 */
function format_pathway_inplace_editable($itemtype, $itemid, $newvalue) {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/course/lib.php');

    if ($itemtype === 'sectionname' || $itemtype === 'sectionnamenl') {
        $section = $DB->get_record_sql(
            'SELECT s.* FROM {course_sections} s JOIN {course} c ON s.course = c.id WHERE s.id = :sectionid AND c.format = :format',
            ['sectionid' => $itemid, 'format' => 'pathway'],
            MUST_EXIST
        );
        return course_get_format($section->course)->inplace_editable_update_section_name($section, $itemtype, $newvalue);
    }
}

/**
 * Serves file from the format_pathway file areas.
 *
 * @param stdClass $course        Course object.
 * @param stdClass $cm            Course module object (unused, can be null).
 * @param context  $context       Context object.
 * @param string   $filearea      File area name.
 * @param array    $args          Extra arguments (itemid, filepath, filename).
 * @param bool     $forcedownload Whether to force download.
 * @param array    $options       Additional options.
 * @return bool    False if file not found.
 */
function format_pathway_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    if ($context->contextlevel != CONTEXT_COURSE) {
        return false;
    }

    if ($filearea !== 'sectionimage') {
        return false;
    }

    require_login($course, true);

    $itemid = array_shift($args);
    $filename = array_pop($args);
    $filepath = $args ? '/' . implode('/', $args) . '/' : '/';

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'format_pathway', $filearea, $itemid, $filepath, $filename);

    if (!$file || $file->is_directory()) {
        return false;
    }

    // Cache for 1 day in browser, 1 week in CDN.
    send_stored_file($file, 86400, 0, $forcedownload, $options);
}
