# Changelog

## 1.3.0 — in development

### Compatibility

- Minimum Geeklog version is 2.1.2.
- Maintained PHP baseline is PHP 5.6 through PHP 8.1.
- Added CI linting on PHP 5.6, PHP 8.1 and PHP 8.3.

### Submission and approval

- Fixed undefined category-permission properties in submission editing/preview.
- Load category ACLs before checking access to a pending submission.
- Unified filesystem finalization for admin and Geeklog moderation approval paths.
- Roll back failed moderation approvals to the pending queue.
- Added durable Pending / Published / Rejected submission status history.
- Added a user-menu “My Downloads” submission-history page.
- Added configurable new-submission notification email.
- Kept submitter approval notification as a separate option.
- Added matching HTML/plaintext Geeklog email templates for submission and approval notifications.

### Uploads and file integrity

- Create/validate configured storage directories before upload.
- Use collision-safe pending filenames while retaining legacy pending-file compatibility.
- Treat an existing upload destination as an error instead of a false success.
- Roll back uploaded/finalized files when database persistence fails.
- Made published-file replacement failure-safe and keep the old file until the UPDATE succeeds.
- Added admin diagnostics for missing published files.
- Added missing/unwritable storage warning.
- Validate uploaded snapshot/category images as actual JPEG/PNG/GIF content.
- Restrict image chooser hints to image MIME types.
- Missing physical downloads now return a 404 instead of an empty response.

### SEO and public pages

- Added the Downloads landing page and public categories to XMLSitemap through Geeklog's native sitemap collection API.

- Added Meta Description and Meta Keywords to downloads, submissions and categories.
- Added public metadata output for category and download pages.
- Added real 404 handling for invalid download IDs, categories and out-of-range pages.
- Added permission-aware public Downloads search with category scope.
- Added image lightbox on listing/detail thumbnails.
- Improved Project Name suggestions and title-derived default.
- Added Tags field guidance and required-field indicators.

### Interoperability

- Corrected the Geeklog 2.2.2 `plugin_idToURL_downloads($sub_type, $item_id)` signature; the previous one-argument signature could prevent XMLSitemap incremental updates from removing old entries and cause duplicate URLs.
- Added stable root/category identities and normalized multi-resource Item Info metadata.
- Added sitemap URL de-duplication and language-aware category discovery.
- Added category lifecycle events and public `PLG_itemDisplay()` extension points.
- Routed page metadata through Geeklog's `PLG_getMetaTags()` cooperation layer when enabled.

- Hardened `plugin_getiteminfo_downloads()` to expose only public/released items.
- Added collection options for `since`, `limit` and `order`.
- Added deterministic `plugin_idtourl_downloads()` fallback.
- Retained `PLG_itemSaved()` / `PLG_itemDeleted()` lifecycle integration.
- Improved XMLSitemap / IndexNow compatibility.

### Administration

- Final form alignment now uses the same grid for fields, Access Rights and action buttons.

- Fixed Access Rights alignment by restructuring permission help inside the permissions field.
- Moved the required-field note out of the Category Image block.
- Improved category image upload/preview alignment and responsive admin form sizing.
- Aligned form action buttons with the same field grid used by the editors.
- Moved Access Rights inside the same administration layout container to remove horizontal drift.

- Expanded Preview from Description/Detail only to a representative download card with title, category, file metadata, project, homepage, image and content.
- Refined the administration form layout with responsive two-column field alignment and cleaner section spacing.

- Moved Meta Description and Meta Keywords directly below Detail in the download editor.
- Organized the download and category editors into logical General / Content & SEO / Media / Publication sections.
- Added an administration summary for total, published, unreleased, hidden, missing-file and pending-submission counts.
- Added compact visual status indicators for publication and file health.
- Added file counts and enabled/disabled status to the category administration list.
- Added category, publication-status and file-health filters to the download administration list.
- Replaced the legacy bulk category enable/disable POST behavior with a targeted per-category toggle.
- Added CSRF/Root enforcement to category moves/toggles and CSRF checks to submission approval/rejection and category creation.
- Hardened category-image preview against invalid image dimensions.
- Versioned plugin-owned CSS and JavaScript URLs with the 1.3.0 release version to prevent stale browser caches.

- Search now covers title, ID, project and version.
- Added publication/listing status column.
- Added file-health column.
- Added pending-submission count/link.
- Added native Geeklog configuration tooltips for important settings.

### Security and stabilization

- Made the multilingual SQL helper alias-aware so interoperability queries using category alias `c` no longer generate invalid `b.cid` references during XMLSitemap-triggered plugin configuration saves.
- Removed generic global `ROOTID` usage in favor of `DLM_ROOTID`.
- Encapsulated Downloads runtime configuration loading to avoid leaking generic temporary variables into the global scope.

- Multisite-safe storage migration remains deferred until functional stabilization is validated; intermediate 1.3.0 builds do not move existing storage.

- Qualified joined SEO columns in the download editor query to avoid MySQL ambiguous-column errors after adding category SEO metadata.

- Restored CSRF protection to public download rating.
- Ratings and download-history pages now enforce public item visibility.
- Fixed zero-vote rating initialization.
- Fixed feed update `$limit` variable handling.
- Removed obsolete duplicated approval file-move helpers.
- Hardened thumbnail generation for PHP 8/GdImage and invalid image sources.
- Fixed missing globals and empty-result handling in Geeklog callbacks.
- Load authoritative download/category ACLs before deletion.
