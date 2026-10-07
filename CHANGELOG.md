# Changelog

## 1.3.0 — in development

### Compatibility

- Minimum Geeklog version is 2.1.2.
- Maintained PHP baseline is PHP 5.6 through PHP 8.1.
- Added PHP 8.1 linting in GitHub Actions.

### Submission and approval

- Fixed undefined category-permission properties in submission editing/preview.
- Load category ACLs before checking access to a pending submission.
- Unified filesystem finalization for admin and Geeklog moderation approval paths.
- Roll back failed moderation approvals to the pending queue.
- Added durable Pending / Published / Rejected submission status history.
- Added a user-menu “My Downloads” submission-history page.
- Added configurable new-submission notification email.
- Kept submitter approval notification as a separate option.

### Uploads and file integrity

- Create/validate configured storage directories before upload.
- Made published-file replacement failure-safe.
- Added admin diagnostics for missing published files.
- Added missing/unwritable storage warning.
- Validate uploaded snapshot/category images as actual JPEG/PNG/GIF content.
- Restrict image chooser hints to image MIME types.
- Missing physical downloads now return a 404 instead of an empty response.

### SEO and public pages

- Added Meta Description and Meta Keywords to downloads, submissions and categories.
- Added public metadata output for category and download pages.
- Added real 404 handling for invalid download IDs, categories and out-of-range pages.
- Added permission-aware public Downloads search with category scope.
- Added image lightbox on listing/detail thumbnails.
- Improved Project Name suggestions and title-derived default.
- Added Tags field guidance and required-field indicators.

### Interoperability

- Hardened `plugin_getiteminfo_downloads()` to expose only public/released items.
- Added collection options for `since`, `limit` and `order`.
- Added deterministic `plugin_idtourl_downloads()` fallback.
- Retained `PLG_itemSaved()` / `PLG_itemDeleted()` lifecycle integration.
- Improved XMLSitemap / IndexNow compatibility.

### Administration

- Search now covers title, ID, project and version.
- Added publication/listing status column.
- Added file-health column.
- Added pending-submission count/link.
- Added native Geeklog configuration tooltips for important settings.

### Security and stabilization

- Restored CSRF protection to public download rating.
- Ratings and download-history pages now enforce public item visibility.
- Fixed zero-vote rating initialization.
- Fixed feed update `$limit` variable handling.
- Removed obsolete duplicated approval file-move helpers.
