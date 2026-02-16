# Pathway Course Format for Moodle

A modern course format plugin that replaces Moodle's default "scroll of death" with a focused, one-section-at-a-time layout featuring a custom progress-tracking sidebar.

## Design Philosophy

The Pathway format treats each course as a **linear learning journey** rather than a document to scroll through. It's optimized for structured training programs common in SMBs and nonprofits: onboarding, compliance, certifications, and professional development.

### Key Design Decisions

- **No default course index** — Moodle's built-in course index drawer is disabled (`uses_course_index()` returns `false`). Our custom sidebar replaces it entirely, reclaiming ~300px of screen width and providing a more purposeful navigation experience.

- **One section at a time** — Uses `COURSE_DISPLAY_MULTIPAGE` by default. Each section gets its own focused view, reducing cognitive load and reinforcing a sense of progression.

- **Progress-first sidebar** — The custom sidebar shows section numbers, completion status (via Moodle's completion API), and per-section progress bars. The current section is highlighted. Completed sections show green checkmarks.

- **Previous/Next navigation** — Clear navigation buttons at the bottom of each section maintain learning momentum without requiring sidebar interaction.

- **Collapsible sidebar** — A toggle button collapses/expands the sidebar for distraction-free reading. On mobile, the sidebar stacks above the content.

## Installation

### Moodle 5.1+ (with `/public` directory)

1. Copy the `pathway` folder to `public/course/format/` in your Moodle installation:
   ```
   public/course/format/pathway/
   ```
2. Log in as admin and go to **Site Administration → Notifications** to trigger the plugin installation.
3. The format will now be available when creating or editing a course under **Course Format → Pathway format**.

### Moodle 5.0 (pre-`/public` structure)

1. Copy the `pathway` folder to `course/format/` in your Moodle installation:
   ```
   course/format/pathway/
   ```
2. Follow the same notification step above.

## File Structure

```
format_pathway/
├── classes/
│   └── output/
│       ├── courseformat/
│       │   ├── content.php          # Main content output (adds sidebar data)
│       │   └── content/
│       │       └── section.php      # Section output override
│       └── renderer.php             # Format renderer
├── db/
│   └── access.php                   # Capabilities (empty for now)
├── lang/
│   └── en/
│       └── format_pathway.php       # English language strings
├── styles/
│   └── pathway.css                  # Development CSS (identical to styles.css)
├── templates/
│   └── local/
│       ├── content.mustache         # Main layout template (sidebar + content)
│       └── content/
│           └── section.mustache     # Section template (delegates to core)
├── format.php                       # Rendering entry point
├── lib.php                          # Format class (extends core_courseformat\base)
├── styles.css                       # Auto-loaded by Moodle CSS cache
├── version.php                      # Plugin metadata
└── README.md                        # This file
```

## Architecture Notes

### How the Sidebar Works

The sidebar is rendered entirely by the Mustache template (`templates/local/content.mustache`). The data it needs — section names, URLs, completion percentages — is computed in `classes/output/courseformat/content.php` by overriding `export_for_template()`. This approach:

- Uses Moodle's `completion_info` API for accurate progress tracking
- Respects section visibility (hidden sections are excluded for students)
- Computes overall course progress from individual activity completions
- Generates previous/next navigation URLs

### Course Format Options

The format adds two custom options to the course settings form:

| Option | Values | Default | Description |
|--------|--------|---------|-------------|
| `pathwaysidebar` | `left`, `right` | `left` | Sidebar position |
| `pathwayshowprogress` | `0`, `1` | `1` | Show/hide progress bars |

### Theme Integration

All colors use CSS custom properties (prefixed `--pathway-*`) defined in `:root`. Any Moodle theme can override these for brand consistency:

```css
:root {
    --pathway-primary: #your-brand-color;
    --pathway-success: #your-success-color;
    --pathway-sidebar-bg: #your-sidebar-bg;
}
```

Dark mode is supported via `[data-bs-theme="dark"]` selectors (Moodle 4.3+).

### What This Format Does NOT Do

- **Does not modify the block drawer** — The right-side block drawer remains functional for teachers/admins.
- **Does not alter the site navbar** — Navigation above the course content area is untouched.
- **Does not break editing mode** — Drag-and-drop, activity editing, and section management all work normally via Moodle's reactive components (`supports_components()` returns `true`).

## Customization & Extension

### Adding Activity Cards

The CSS includes styling to make Moodle's default activity list items appear as hover-responsive cards. To further customize activity appearance, override the core activity output classes in `classes/output/courseformat/content/cm/`.

### Adding Section Images

To add section header images (like the Tiles format), you would:
1. Add a `sectionimage` file area in `lib.php`
2. Override the section output class to include image URLs in template data
3. Update the section template to render the image

### Multi-Tenant Considerations

Since your platform uses multi-tenancy, the CSS custom properties approach means each tenant's theme can define its own brand colors without plugin modification.

## Compatibility

- **Moodle**: 5.0+ (built and tested for 5.0 and 5.1)
- **PHP**: 8.2+ (as required by Moodle 5.0)
- **Themes**: Boost and Boost-based themes (Classic theme may require additional CSS)
- **Directory structure**: Supports both the pre-5.1 flat layout and the 5.1+ `/public` directory layout

## License

GNU GPL v3 or later — https://www.gnu.org/copyleft/gpl.html
