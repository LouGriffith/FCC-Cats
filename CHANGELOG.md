# Changelog — FCC Cats

All notable changes to this plugin are documented here.
Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

## [1.3.0] — 2026-03-10

### Changed
- Admin list columns simplified to show only: Status, Date Added, Best Trait
- Removed Photo, Age, and Sex columns from admin list view

---

## [1.2.0] — 2026-03-10

### Added
- Emoji support for Cat Traits — set an emoji on the Traits taxonomy edit screen or inline when adding a new trait from the cat edit screen
- Emoji column added to the Traits admin list table
- Emoji displayed alongside trait name in the admin list view and frontend shortcodes

---

## [1.1.0] — 2026-03-10

### Added
- `fcc_cat_trait` taxonomy (tag-style, single-select) to replace the hardcoded Best Trait select field
- Custom radio-button meta box on the cat edit screen enforcing single trait selection
- Ability to add new traits directly from the cat edit screen without navigating away
- Traits managed via **Cats → Traits** in the admin sidebar
- GitHub auto-updater (`class-github-updater.php`) — updates delivered via GitHub Releases through the WordPress admin

### Changed
- Removed `custom-fields` from post type supports — hides the Custom Fields meta box in the editor
- Best Trait column in admin list now reads from the `fcc_cat_trait` taxonomy

### Removed
- Hardcoded `_fcc_best_trait` meta field and select dropdown replaced by taxonomy

---

## [1.0.0] — 2026-02-16

### Added
- Initial release
- Custom post type `fcc_cat` with dashicons-pets icon
- Cat fields: name, photo, age, sex, arrived date
- Adoption toggle with conditional fields: adoption date, adopter name, adoption photo, success story
- Admin list columns: Photo, Status (Adopted/Adoptable), Age, Sex, Best Trait, Date Arrived
- Sortable columns for Status and Date Arrived
- Shortcodes: `[fcc_cats]`, `[fcc_success_stories]`
- Frontend CSS for cat card grid and success story grid
- Responsive layout (2-column on tablet, 1-column on mobile)
