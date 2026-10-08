# Geeklog Downloads plugin

The Downloads plugin provides a categorized file-download area for Geeklog.

## Compatibility

Downloads 1.3.0 targets:

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

## Storage

The configured file and image directories are checked before upload. Missing directories are created when possible and the administration list reports missing published files.

The legacy defaults still place Downloads storage below `public_html/downloads_data/`. Moving persistent storage outside the public web root remains a planned hardening step and should not be assumed to be complete in 1.3.0 development builds.

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

`dist/downloads_1.3.0_2.1.2.zip`

The packaging workflow rejects hidden dot-prefixed archive paths and verifies the required installable layout.

## Project

Geeklog homepage: https://www.geeklog.net

Downloads plugin: https://github.com/Geeklog-Plugins/downloads

Issues: https://github.com/Geeklog-Plugins/downloads/issues
