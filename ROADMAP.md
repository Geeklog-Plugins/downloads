## Current 1.3.0 implementation status

Implemented in the development branch:

- storage directory validation/creation before uploads;
- failure-safe replacement of existing download files;
- fixed submission ACL initialization for `editsubmission`;
- shared file finalization for admin and Geeklog moderation approvals;
- rollback to pending state when moderation file finalization fails;
- durable submission status history: Pending / Published / Rejected;
- configurable moderator email notification for new submissions;
- `My Downloads` user-menu page ordered by submission date;
- download/category Meta Description and Meta Keywords;
- public SEO metadata and real 404 handling;
- image-only upload hints plus server-side JPEG/PNG/GIF validation;
- required-field indicators;
- Project Name suggestions and title-derived default;
- Tags field guidance;
- image lightbox on listings and detail pages;
- permission-aware public search with category scope;
- improved admin search, status visibility and pending-submission navigation;
- admin file-health and storage diagnostics;
- hardened Item Info for released/public content;
- deterministic ID-to-URL fallback for IndexNow/XMLSitemap lifecycle consumers;
- public rating CSRF protection and visibility checks;
- download history visibility checks;
- missing-file 404 handling in the delivery endpoint;
- configuration tooltips and refreshed README/INSTALL/changelog;
- versioned CSS/JavaScript asset URLs using the 1.3.0 release version;
- SEO fields positioned below Detail in the download editor;
- download/category editors organized into logical sections;
- admin summary and compact status indicators;
- category file counts and enabled/disabled state in administration;
- category/status/file-health admin filters;
- targeted category status toggles with CSRF and Root checks;
- CSRF hardening for submission approval/rejection and category mutations;
- HTML/plaintext notification templates;
- collision-safe pending upload names with legacy compatibility;
- filesystem rollback when database persistence fails;
- PHP 5.6 / 8.1 / 8.3 lint matrix and clean distribution checks.

The remaining sections below are the acceptance roadmap for the release.

## Release readiness

### Validated on a real installation

- [x] Fresh installation
- [x] Uninstall
- [x] Plugin activation / deactivation
- [x] Upgrade from the previous Downloads version

### Functional validation still required before PR

- [x] Add / edit / replace / delete a download
- [x] Add / edit / enable / disable a category
- [x] Public search with matches and with no results
- [x] Download image and lightbox
- [ ] Comments and rating
- [ ] User submission creates Pending state
- [ ] Approval through Geeklog moderation creates Published state
- [ ] Approval through Downloads administration
- [ ] Rejection creates Rejected state
- [ ] New-submission email notification
- [ ] Submitter approval email notification
- [ ] Missing physical file produces admin diagnostic and public 404
- [ ] XMLSitemap / IndexNow integration
- [ ] Oversized upload produces a controlled user-facing error

### Deferred from the 1.3.0 release candidate

- Multisite-safe automatic migration of persistent storage outside `public_html`.
- Default download image enhancement.
- Legacy File Manager conversion cleanup unless a reproducible 1.3.0 regression is found.

