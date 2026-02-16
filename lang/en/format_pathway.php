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
 * Language strings for the format_pathway plugin.
 *
 * @package   format_pathway
 * @copyright 2025 Your Company
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Pathway format';
$string['plugin_description'] = 'A modern course format that displays sections one at a time with a progress-tracking sidebar. Designed for structured learning paths.';

// Section strings.
$string['addsections'] = 'Add section';
$string['currentsection'] = 'This section';
$string['deletesection'] = 'Delete section';
$string['editsection'] = 'Edit section';
$string['editsectionname'] = 'Edit section name';
$string['hidefromothers'] = 'Hide section';
$string['newsectionname'] = 'New name for section {$a}';
$string['sectionname'] = 'Section';
$string['section0name'] = 'Overview';
$string['showfromothers'] = 'Show section';
$string['numbersections'] = 'Number of sections';

// Course layout.
$string['courselayout'] = 'Course layout';
$string['courselayout_help'] = 'Choose how sections are displayed. "One section per page" shows a focused view with sidebar navigation (recommended). "All sections on one page" displays everything on a single scrollable page with the sidebar.';
$string['onesectionperpage'] = 'Show one section per page (recommended)';
$string['allsectionsonepage'] = 'Show all sections on one page';

// Sidebar & navigation.
$string['sidebarposition'] = 'Sidebar position';
$string['sidebarposition_help'] = 'Choose whether the progress sidebar appears on the left or right side of the course content.';
$string['sidebarleft'] = 'Left';
$string['sidebarright'] = 'Right';
$string['showprogress'] = 'Show progress tracking';
$string['showprogress_help'] = 'When enabled, the sidebar displays completion progress for each section and overall course progress.';
$string['showimages'] = 'Show section images';
$string['showimages_help'] = 'When enabled, uploaded section header images are displayed as banners at the top of each section and as thumbnails in the sidebar. Images can still be uploaded per section regardless of this setting.';
$string['showsection0'] = 'Show general section in sidebar';
$string['showsection0_help'] = 'When enabled, the general section (section 0) appears in the sidebar navigation and is only displayed in the content area when navigated to. When disabled, the general section is always shown above the course content.';
$string['showsection0'] = 'Include overview in sidebar';
$string['showsection0_help'] = 'When enabled, the overview/introduction section (Section 0) appears as a pinned link at the top of the sidebar and is only shown in the main content area when navigated to. When disabled, it is always displayed above the course content.';
$string['courseoutline'] = 'Course Outline';
$string['overallprogress'] = 'Overall Progress';
$string['sectionprogress'] = '{$a->completed} of {$a->total}';
$string['complete'] = 'Complete';
$string['inprogress'] = 'In Progress';
$string['notstarted'] = 'Not Started';

// Navigation.
$string['previoussection'] = 'Previous';
$string['nextsection'] = 'Continue';
$string['continuetosection'] = 'Continue to {$a}';
$string['backtosection'] = 'Back to {$a}';
$string['collapsesidebar'] = 'Collapse sidebar';
$string['expandsidebar'] = 'Expand sidebar';

// Section images.
$string['sectionimage'] = 'Section header image';
$string['sectionimage_help'] = 'Upload an image to display as a banner at the top of this section and as a thumbnail in the sidebar. Recommended size: 1200x300px. Supported formats: JPG, PNG, GIF, WebP.';

// Privacy.
$string['privacy:metadata'] = 'The Pathway format plugin does not store any personal data.';
