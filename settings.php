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
 * Site-wide default settings for the Pathway course format.
 *
 * These defaults are used when creating new courses with the Pathway format.
 * Teachers can override any of these settings in their individual course settings.
 *
 * @package   format_pathway
 * @copyright 2025 Your Company
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    // Sidebar position default.
    $settings->add(new admin_setting_configselect(
        'format_pathway/pathwaysidebar',
        new lang_string('sidebarposition', 'format_pathway'),
        new lang_string('sidebarposition_help', 'format_pathway'),
        'left',
        [
            'left' => new lang_string('sidebarleft', 'format_pathway'),
            'right' => new lang_string('sidebarright', 'format_pathway'),
        ]
    ));

    // Show progress tracking default.
    $settings->add(new admin_setting_configselect(
        'format_pathway/pathwayshowprogress',
        new lang_string('showprogress', 'format_pathway'),
        new lang_string('showprogress_help', 'format_pathway'),
        1,
        [
            1 => new lang_string('yes'),
            0 => new lang_string('no'),
        ]
    ));

    // Show section images default.
    $settings->add(new admin_setting_configselect(
        'format_pathway/pathwayshowimages',
        new lang_string('showimages', 'format_pathway'),
        new lang_string('showimages_help', 'format_pathway'),
        1,
        [
            1 => new lang_string('yes'),
            0 => new lang_string('no'),
        ]
    ));

    // Show section 0 in sidebar default.
    $settings->add(new admin_setting_configselect(
        'format_pathway/pathwayshowsection0',
        new lang_string('showsection0', 'format_pathway'),
        new lang_string('showsection0_help', 'format_pathway'),
        0,
        [
            1 => new lang_string('yes'),
            0 => new lang_string('no'),
        ]
    ));

    // Include overview (section 0) in sidebar default.
    $settings->add(new admin_setting_configselect(
        'format_pathway/pathwayshowsection0',
        new lang_string('showsection0', 'format_pathway'),
        new lang_string('showsection0_help', 'format_pathway'),
        0,
        [
            1 => new lang_string('yes'),
            0 => new lang_string('no'),
        ]
    ));
}
