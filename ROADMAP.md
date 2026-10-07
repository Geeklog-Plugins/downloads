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
- PHP 8.1 lint workflow and clean distribution checks.

The remaining sections below are the acceptance roadmap for the release.

