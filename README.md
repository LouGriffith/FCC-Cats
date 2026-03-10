# FCC Cats

**Author:** Lou Griffith — [lougriffith.com](https://lougriffith.com)
**Version:** 1.3.0
**Requires:** WordPress 6.0+, PHP 8.0+

Manage the adoptable cats at Fancy Cat Cafe. Add and update each cat's profile — including photo, age, sex, arrival date, and best trait — and mark them as adopted with a success story from their new family. Display cats and adoption stories anywhere on your site using shortcodes.

---

## Features

- Custom post type `fcc_cat` for managing individual cat profiles
- Fields: name, photo, age, sex, arrival date
- **Cat Traits** taxonomy — tag-style, single-select with optional emoji (e.g. 🎾 Very Playful)
- Adoption toggle that reveals: adoption date, adopter name, adoption photo, and success story
- Admin list view showing Status, Date Added, and Best Trait columns — sortable
- Frontend shortcodes for displaying adoptable cats and success stories
- GitHub-powered auto-updates via WordPress admin

---

## Shortcodes

### `[fcc_cats]`
Displays a grid of cats.

| Attribute | Options | Default | Description |
|---|---|---|---|
| `status` | `adoptable`, `adopted`, `all` | `adoptable` | Which cats to show |
| `columns` | `2`, `3`, `4` | `3` | Grid columns |
| `limit` | any number | `-1` (all) | Max number of cats to show |

**Examples:**
```
[fcc_cats]
[fcc_cats status="adoptable" columns="4"]
[fcc_cats status="adopted" limit="6"]
[fcc_cats status="all" columns="2"]
```

### `[fcc_success_stories]`
Displays a grid of adopted cats with their success stories.

| Attribute | Options | Default | Description |
|---|---|---|---|
| `columns` | `2`, `3` | `2` | Grid columns |
| `limit` | any number | `-1` (all) | Max stories to show |

**Examples:**
```
[fcc_success_stories]
[fcc_success_stories limit="4" columns="2"]
```

---

## Admin Usage

### Adding a Cat
1. Go to **Cats → Add New Cat**
2. Enter the cat's name in the title field
3. Upload a photo using the **Cat Photo** sidebar panel
4. Fill in Age, Sex, and Arrived date in the **Cat Details** panel
5. Select a trait from the **Best Trait** sidebar panel (or add a new one)
6. Publish

### Marking as Adopted
1. Open the cat's edit screen
2. Check **"This cat has been adopted"** in the Cat Details panel
3. Fill in the adoption date, adopter name, upload an adoption photo, and write their success story
4. Update the post

### Managing Traits
Go to **Cats → Traits** to add, edit, or remove traits. Each trait can have an emoji set from the edit screen.

---

## GitHub Updates

This plugin updates directly through the WordPress admin via GitHub Releases.

**To release an update:**
1. Bump the version in `fcc-cats.php`
2. Push changes to GitHub
3. Create a new Release tagged `v1.x.x` with the plugin `.zip` attached
4. WordPress will detect the update within 12 hours

See `GITHUB-SETUP.md` for full instructions.

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md)
