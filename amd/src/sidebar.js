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
 * AMD module for the Pathway course format sidebar.
 *
 * Handles sidebar collapse/expand toggle, keyboard accessibility,
 * and persists the sidebar state via user preferences.
 *
 * @module     format_pathway/sidebar
 * @copyright  2025 Your Company
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {call as fetchMany} from 'core/ajax';
import log from 'core/log';

/** @type {Object} DOM element references, cached on init. */
let elements = {};

/** @type {boolean} Whether the sidebar is currently collapsed. */
let collapsed = false;

/**
 * CSS classes used by the sidebar.
 *
 * @type {Object}
 */
const SELECTORS = {
    WRAPPER: '#pathway-wrapper',
    SIDEBAR: '#pathway-sidebar',
    TOGGLE_BTN: '#pathway-sidebar-toggle',
    EXPAND_BTN: '#pathway-expand-btn',
};

const CSS = {
    COLLAPSED: 'pathway-sidebar-collapsed',
};

/**
 * Collapse the sidebar.
 */
const collapseSidebar = () => {
    collapsed = true;
    elements.wrapper.classList.add(CSS.COLLAPSED);
    elements.sidebar.setAttribute('aria-hidden', 'true');
    elements.expandBtn.style.display = '';
    elements.expandBtn.focus();
    saveSidebarPreference(true);
};

/**
 * Expand the sidebar.
 */
const expandSidebar = () => {
    collapsed = false;
    elements.wrapper.classList.remove(CSS.COLLAPSED);
    elements.sidebar.setAttribute('aria-hidden', 'false');
    elements.expandBtn.style.display = 'none';
    elements.toggleBtn.focus();
    saveSidebarPreference(false);
};

/**
 * Persist the sidebar collapsed state via the Moodle user preference API.
 *
 * @param {boolean} isCollapsed - Whether the sidebar should be stored as collapsed.
 */
const saveSidebarPreference = (isCollapsed) => {
    try {
        fetchMany([{
            methodname: 'core_user_update_user_preferences',
            args: {
                preferences: [{
                    type: 'format_pathway_sidebar_collapsed',
                    value: isCollapsed ? '1' : '0',
                }],
            },
        }]);
    } catch (err) {
        log.warn('format_pathway/sidebar: Could not save preference.', err);
    }
};

/**
 * Register event listeners on sidebar toggle buttons.
 */
const registerEventListeners = () => {
    elements.toggleBtn.addEventListener('click', (e) => {
        e.preventDefault();
        collapseSidebar();
    });

    elements.expandBtn.addEventListener('click', (e) => {
        e.preventDefault();
        expandSidebar();
    });

    // Keyboard support: Enter or Space toggles.
    elements.toggleBtn.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            collapseSidebar();
        }
    });

    elements.expandBtn.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            expandSidebar();
        }
    });
};

/** @type {number} Breakpoint below which sidebar auto-collapses (matches CSS). */
const MOBILE_BREAKPOINT = 768;

/**
 * Initialise the sidebar module.
 *
 * Called from PHP via $PAGE->requires->js_call_amd().
 *
 * @param {boolean} initiallyCollapsed - Whether the sidebar should start collapsed
 *                                        (from user preference).
 */
export const init = (initiallyCollapsed) => {
    const wrapper = document.querySelector(SELECTORS.WRAPPER);
    const sidebar = document.querySelector(SELECTORS.SIDEBAR);
    const toggleBtn = document.querySelector(SELECTORS.TOGGLE_BTN);
    const expandBtn = document.querySelector(SELECTORS.EXPAND_BTN);

    // Bail gracefully if elements aren't present (e.g. all-sections-one-page without sidebar).
    if (!wrapper || !sidebar || !toggleBtn || !expandBtn) {
        log.debug('format_pathway/sidebar: Required DOM elements not found. Sidebar JS not initialised.');
        return;
    }

    elements = {wrapper, sidebar, toggleBtn, expandBtn};

    registerEventListeners();

    // On mobile viewports, auto-collapse the sidebar so content is visible first.
    const isMobile = window.innerWidth < MOBILE_BREAKPOINT;
    if (initiallyCollapsed || isMobile) {
        collapseSidebar();
    }

    // Re-evaluate on resize (e.g. orientation change).
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            const nowMobile = window.innerWidth < MOBILE_BREAKPOINT;
            if (nowMobile && !collapsed) {
                collapseSidebar();
            }
        }, 250);
    });
};
