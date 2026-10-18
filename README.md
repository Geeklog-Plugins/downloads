# Geeklog Downloads plugin

The Downloads plugin provides a categorized file-download area for Geeklog.

## Compatibility

Downloads 1.3.2 targets:

- Geeklog 2.1.2 through 2.2.2
- PHP 5.6 through PHP 8.1 as the maintained baseline
- PHP 8.3 validation where practical

## Main features

- Categories and sub-categories
- User file submissions with moderation
- User-menu page showing each user's submissions and their status
- Moderator notification for new queued submissions
- Approval notification to submitters
- Download comments and ratings
- Project grouping
- Version, MD5, file size, snapshot and thumbnail metadata
- Public search by title, description, detail, project, version and tags
- Category-scoped search
- Meta Description and Meta Keywords for categories and downloads
- Public 404 handling for invalid items/categories/pages
- Permission-aware Geeklog Item Info interoperability
- IndexNow/XMLSitemap-compatible canonical URL resolution
- Image lightbox on public listing/detail thumbnails
- Administration search, publication status and file-health diagnostics
- Multi-language support
- Geeklog Configuration integration with native tooltips

## Submission workflow

Regular users can submit a file to the moderation queue. Downloads 1.3.0 keeps a durable status history with these states:

- Pending
- Published
- Rejected

The same file-finalization workflow is used when approval happens in the Downloads administration screen or through Geeklog moderation. If file finalization fails after Geeklog has copied a moderation row into the published table, the plugin rolls the item back to the pending queue instead of leaving a published database row pointing to a missing file.

## GitHub Release submissions (1.3.2)\n\nAuthenticated members and administrators may submit public GitHub Releases from `downloads/github-submit.php`. The page accepts direct release-tag URLs and release-list anchors, previews metadata and offers a ZIP asset. Submission creates a native pending Downloads moderation item and records provider identity separately; no ZIP is downloaded or published until approval. Duplicate assets are rejected. Moderators approve/reject via the ordinary queue; approval fetches and validates the selected archive with size and destination checks and attempts rollback on failure. Original release Markdown is retained for editing; a limited, HTML-escaped Markdown renderer formats GitHub descriptions and details publicly. This is not full GitHub Flavored Markdown support.\n\nOn Geeklog 2.2.2 / PHP 8.1, a complete GitHub preview, submission, moderation, archive fetch and publication path plus a failure/rollback path have been manually tested on g2. PHP 5.6/8.1/8.3 CI checks are lint and targeted unit tests; they do not prove end-to-end behavior on those platforms.\n\n## Storage

The configured file and image directories are checked before upload. Missing directories are created when possible and the administration list reports missing published files.

The legacy defaults still place Downloads storage below `$_CONF['path_html']/downloads_data/` and set snapshot/thumbnail URLs below `$_CONF['site_url']/downloads_data/`. These are **per-installation configuration values**, not automatically per-host or per-site isolation. Two installations using the same physical public directory or configured storage paths may share files. Check the effective paths and URLs for each site, especially when sharing one Geeklog code tree.\n\nFor a multisite deployment, configure separate physical directories for each site before importing content. Prefer a non-web-accessible location for ZIP archives when the existing controlled download handler supports it; do not assume that moving public snapshots/thumbnails outside the web root preserves their URLs. Audit URL generation, direct file access, permissions, and historical references before any move. Never change an existing installation's storage defaults in place without a reviewed migration, backups, file-count checks and rollback. The Root-only storage inventory is read-only and may help with reconciliation.

## Public search

A search form is available directly on `downloads/index.php`. Search respects:

- category permissions;
- active categories;
- released state;
- publication date;
- category/sub-category scope.

## SEO and interoperability

Downloads and categories can define:

- Meta Description
- Meta Keywords

Public detail/category pages emit those values where appropriate. Downloads also exposes permission-aware Item Info data and a deterministic ID-to-URL fallback for Geeklog lifecycle consumers such as XMLSitemap and IndexNow.

## Development status

The active development roadmap is in [ROADMAP.md](ROADMAP.md).

The installable development archive is generated as:

`dist/downloads_1.3.2_2.1.2.zip`

The packaging workflow rejects hidden dot-prefixed archive paths and verifies the required installable layout.

## Project

Geeklog homepage: https://www.geeklog.net

Downloads plugin: https://github.com/Geeklog-Plugins/downloads

Issues: https://github.com/Geeklog-Plugins/downloads/issues
