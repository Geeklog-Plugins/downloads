# Downloads development roadmap

## Next maintenance cycle: 1.3.2

**Status (2026-10-18):** GitHub submission, approval, ZIP fetch, publication and tested failure/rollback path reported successful on g2 (Geeklog 2.2.2/PHP 8.1). PHP 5.6/8.1/8.3 CI green. PR preparation in progress; functional g2 acceptance passed. Multisite storage reorganization is documented as a future enhancement and is **not a 1.3.2 PR blocker**.

**Baseline:** Downloads 1.3.1, including successful manual tests of regular-user submission, Preview, approval through moderation and Downloads administration, email notifications, and identical shared snapshots on Geeklog 2.2.2. PHP 5.6/8.1/8.3 syntax CI passed; this does **not** constitute full functional verification of older Geeklog installations.

### Deferred after 1.3.2 — multisite image and archive directory convention (2026-10-18)

**Updated design decision (not implemented): use Geeklog's native `path_images` for filesystem placement, but never require `images_url`.** In the confirmed g2 multisite installation, `$_CONF['path_images']` is `/home/vmrp0021/g2.pascalburnet.com/images/g2/` and `$_CONF['images_url']` is `https://g2.pascalburnet.com/images/g2`.

- Public Downloads images: `rtrim($_CONF['path_images'], '/\\\\') . '/downloads/{snaps,category_snaps,tn}/'`.
- Corresponding URLs: retain separately configurable `snapstore_url`, `snapcat_url`, and `tnstore_url`; `$_CONF['images_url']` is an optional site customization, **not a Geeklog 2.2.2 core configuration guarantee**. Do not derive a public URL from `path_images` unless the web mapping is explicitly known.
- Do **not** append another site identifier when a given installation's configured `path_images` is already site-specific. Other installations may not use that layout; verify isolation before choosing new defaults.
- Download archives: prefer an explicitly configured per-site private path outside the document root, with controlled serving. If a shared public root is retained, use a stable validated per-site subdirectory such as `downloads_data/<site-id>/files/` and deny direct HTTP access.
- Do not infer a storage path from untrusted HTTP_HOST. Use the site's validated configuration and ensure private file directories are actually separate between sites.
- Existing installs keep configured values; new-install defaults and an opt-in migration require file/URL compatibility tests, backups, collision checks and rollback.
- Audit download delivery, thumbnail creation and cached URLs before changing the defaults. No silent relocation during a plugin update. The stock Geeklog code references `path_images`, but a search of the official core repository found no `images_url` key; site-specific use must remain optional.

### Deferred after 1.3.2 — multisite storage separation (2026-10-18)

- [x] Audit current defaults: `path_filestore`, `path_snapstore`, `path_snapcat` and `path_tnstore` are rooted in `$_CONF['path_html']/downloads_data/`; image URLs are based on `$_CONF['site_url']`. Values are installation settings, not automatically host-specific folders.
- [ ] Define a documented per-site storage identity (site configuration or explicit directory settings) that never mixes files between sites sharing a Geeklog tree; check multisite database/config mapping and file permissions.
- [ ] Evaluate private ZIP storage outside the document root while retaining controlled download access; review whether any code assumes direct URL access, filesystem paths or same-device renames.
- [ ] Keep public snapshot and thumbnail URL mappings working with site-specific directories; audit direct HTTP access/ACL implications separately.
- [ ] Build an **opt-in**, backup-first migration utility with dry-run, collision detection, counts/hashes and rollback guidance for existing installations. Never silently relocate live files on upgrade.
- [ ] Verify two independent Geeklog sites with distinct hostnames, configurations and storage roots; test submission, approval, download, snapshot rendering, cleanup and backup/restore on each.
- [x] Decision: defer storage isolation and migration to a later version; 1.3.2 preserves existing paths and documents the multisite caveat.

### Manual acceptance confirmed on g2 (2026-10-18)

- [x] User reports preview, GitHub submission, native moderation, approval and ZIP transfer/publication functional.
- [x] User reports failed approvals and rollback cleanup scenarios functional.
- [x] Public GitHub Markdown is retained in storage and rendered on descriptions and details (per user acceptance).
- [ ] Repeat sensitive acceptance on Geeklog/PHP combinations other than g2; do not equate lint with runtime validation.

### 1.3.2 proposed priorities

1. **P0 — Uploaded-file diagnostics and data integrity**
   - [x] Avoid undefined `$_FILES['newfile']['name']` access when PHP drops a request.
   - [x] Detect a discarded HTTP POST exceeding `post_max_size` and display upload-size error in Downloads admin and submission callbacks (static implementation; browser-level regression still pending).
   - [ ] Investigate upstream [#8](https://github.com/Geeklog-Plugins/downloads/issues/8): enforce `upload_max_filesize`, `post_max_size`, `UPLOAD_ERR_*`, empty `$_POST` and server-size errors; display actionable, localized messages; clean failed partial uploads without touching published files.
   - [ ] Audit real delete/replace paths for orphaned ZIPs and thumbnails; distinguish shared snapshots and retained historical files.
   - [x] Add a **read-only** Root-admin storage inventory showing references to published and pending files, image files and thumbnails, and up to 100 unreferenced candidates per directory (initial version; no deletion).
   - [x] Report missing references and up to 100 missing filenames per storage folder, with legacy pending filenames resolved without false missing counts.
   - [ ] Add duplicate-content detection and manual cross-checks of old upload formats.
   - [ ] Confirm [#3](https://github.com/Geeklog-Plugins/downloads/issues/3) is fixed for **legacy** submissions on geeklog.net before requesting closure. 1.3.1 addresses timezone-dependent names and shared snapshots; do not assume every old upload format has been tested.
2. **P1 — Authorization and moderation correctness**
   - [x] Tighten `plugin_commentenabled_downloads()` to require a published download and enabled category; retain SQL read-permission filtering (initial correction for #22; end-to-end permission tests still pending).
   - [ ] Re-audit [#22](https://github.com/Geeklog-Plugins/downloads/issues/22): `plugin_commentenabled_downloads()` must apply effective item **and category** visibility and avoid leaking comment availability to unauthorized readers. Add anonymous/member/owner tests.
   - [ ] Re-check approval failure feedback and Geeklog's global moderation “Approved N items” counter: core reports an attempted approval even when Downloads has rolled the file back. Document core/plugin integration options; avoid misleading success claims.
   - [ ] Regression-test [#25](https://github.com/Geeklog-Plugins/downloads/issues/25) Preview ACL fix (addressed in 1.3.0, issue still open), including new, existing and pending downloads.
3. **P2 — Metadata and presentation**
   - [ ] Re-test [#24](https://github.com/Geeklog-Plugins/downloads/issues/24) on current XMLSitemap/IndexNow with create/edit/delete; diagnose the origin of any empty URL events before closing.
   - [ ] Verify [#19](https://github.com/Geeklog-Plugins/downloads/issues/19) real 404 handling for invalid file/category IDs and pagination on public routes (implemented in 1.3.0, issue still open).
   - [ ] Verify [#21](https://github.com/Geeklog-Plugins/downloads/issues/21) HTML/plaintext template notifications (implemented in 1.3.0, issue still open).
   - [ ] Verify [#1](https://github.com/Geeklog-Plugins/downloads/issues/1) admin new-submission email option (implemented in 1.3.0, issue still open).
   - [x] Preserve blank-image fallback without lightbox when a snapshot or thumbnail is physically missing (initial #9 robustness fix).
   - [ ] Record design proposal for [#9](https://github.com/Geeklog-Plugins/downloads/issues/9) optional default image; keep existing public layout.
   - [ ] Triage [#15](https://github.com/Geeklog-Plugins/downloads/issues/15) File Manager migration leftovers as legacy housekeeping, separate from any deletion feature.

### Issue inventory (upstream, checked 2026-10-16)

Ten open issues on `Geeklog-Plugins/downloads`: **#25, #24, #22, #21, #19, #15, #9, #8, #3, #1**. “Open” is a GitHub state, **not proof a defect remains**. Issues #1, #19, #21, #25 and substantial parts of #3 have corresponding changes in 1.3.0/1.3.1; validate on current deployments, then propose closing with test evidence. Do not close upstream issues without confirmation from maintainers.

### P0 — GitHub Releases importer (moved from 1.4.0)

**Implemented; core live path confirmed on g2, with cross-version, isolation and edge-case tests still pending.** This is a deliberately bounded, manually triggered administrative workflow, not unattended synchronization.

- [x] Validate the public GitHub release URL (`https://github.com/OWNER/REPO/releases/tag/TAG`) before metadata API calls, with redirects disabled (implementation pending tests).
- [x] Add PHP 5.6-compatible GitHub Releases API metadata reader and Root-only, CSRF-protected read-only preview of title, tag, date, notes and ZIP assets (implementation pending CI and browser test). No archive transfer or publication yet.
- [x] Add CSRF-protected handoff from release preview to the existing editable Downloads creation form, prefilled with safe text metadata, URL and version (static implementation; browser test pending). No automatic archive transfer.\n- [x] Add ZIP asset selection; re-fetch GitHub release metadata and validate the selected asset ID against the live release before opening the draft (static implementation; browser test pending).\n- [x] Add a standalone bounded ZIP staging helper with TLS validation, byte caps, size/signature checks, refusal of unvalidated redirects and failed-transfer cleanup. **Not connected to the admin workflow yet; real GitHub ZIP URLs commonly redirect, so production transfer is intentionally blocked until safe redirect handling is implemented.**\n- [x] Add a first-pass manual redirect resolution restricted to GitHub's release-assets / objects CDN hosts, with TLS checks, a single redirect and fail-closed behavior (untested against live GitHub release assets).\n- [ ] Validate CDN redirect and HTTP HEAD behavior on real releases; improve compatibility as needed, without relaxing destination restrictions.\n- [x] Harden standalone staging to refuse existing destinations (`xb`) and verify GitHub-provided SHA-256 digest when available (static review only).\n- [x] Add separate, collision-safe server-fetched-archive storage helper with exclusive target creation, copy verification and failure cleanup. Never bypass `is_uploaded_file()` for normal browser uploads (static implementation, tests pending).\n- [x] Connect selected GitHub asset to Root-only Downloads creation: revalidate via GitHub API, transfer to private 0700 staging directory, store without overwrite, and use existing file/DB rollback code (implementation; not yet validated on CI or a real deployment).\n- [ ] Test successful transfer, failure rollback, multipart editor form preservation, HTTP redirects, archive size boundary, ZIP signature, archive digest and duplicate-import behavior. The form currently does not prevent repeated imports of the same release.\n- [ ] Add durable GitHub release IDs / duplicate detection and ensure an import is never publicly listed before administrator approval.\n- [ ] Confirm 5.6, 8.1 and 8.3 CI, build dist ZIP and run controlled live g2 acceptance before proposing a PR.\n- [x] Correct accidental literal newline escape sequences in the ZIP fetch helper; explicitly restrict cURL requests to HTTPS (static fixes, CI status pending).\n- [ ] Validate the HEAD redirect, ZIP integrity and error paths in controlled tests, then run a live test on g2 after secure integration and successful CI.\n- [ ] Confirm publication restrictions in functional tests.
- [ ] Download the selected release asset through a bounded, validated server-side transfer to **temporary** local storage. Validate scheme/host and redirects against SSRF, HTTP status, content size, extension, resulting bytes, collisions, and optional checksums; use the regular Downloads finalization flow after confirmation.
- [ ] Enforce Downloads administrative permission and Geeklog CSRF tokens on fetch, import and confirmation; sanitize imported Markdown/HTML to the site's permitted content subset.
- [ ] Preserve existing entries and files on import failures; clean only the new temporary files and provide actionable errors for missing assets, failed network requests, rate limits and invalid responses.
- [ ] Detect already-imported releases before confirmation (stable GitHub repository/release identifiers or a documented equivalent), and offer a reviewable update path instead of silently creating duplicates.
- [ ] Add tests for multiple assets, no ZIP assets, prereleases, oversized responses, redirects, network timeouts, duplicate releases and failure rollback; include PHP 5.6 / 8.1 / 8.3 compatibility checks.
- [ ] Verify the workflow on g2, review package manifest and documentation, and obtain maintainer review before any tag.

**Explicitly deferred to 1.4.0:** scheduled update checks, automatic publication, multi-provider imports, and a broader project/release history data-model migration. A stable project listing remains a design requirement for subsequent versions; 1.3.2 must not silently alter existing URLs or counters.

### P0 — Community GitHub Release proposals (Geeklog.net)

**Decision (2026-10-18): add a second, community-facing GitHub pathway to 1.3.2.** Preserve the Root-only administrative importer as a separate operation. Ordinary authenticated members may propose a public GitHub release, but may not use the privileged importer or publish a download.

#### Community workflow and ACL
- [x] Add a login-only, read-only public GitHub Release metadata preview linked from My Downloads (experimental initial screen; not a real submission yet).\n- [ ] Provide a **Submit from GitHub** action alongside normal Downloads submission; require login and Downloads' normal submit permissions/eligibility. Do not expose Root-only admin routes.
- [ ] Display a bounded, rate-limited public-release metadata preview (repository, release, tag, notes and selectable ZIP asset); allow the member to review/edit title, description, project, category and relevant metadata.
- [x] Implement separate `downloadgithubproposals` SQL table, install/upgrade registration, and member-owned pending proposals with stable release/asset IDs and UNIQUE release-asset constraint. The browser form re-fetches GitHub metadata before insertion; no ZIP is downloaded or published. Database upgrade and browser acceptance tests remain pending.
- [ ] Require a Geeklog CSRF token for every state-changing request. Apply input length limits, safe text/HTML handling, category permissions and per-user submission quotas.
- [ ] Do **not download the ZIP at member-submission time**. Preserve a reviewable pending proposal and notify authorized moderators through the existing moderation/notification conventions.
- [ ] Add a Root/moderator review path with approve/reject and actionable feedback. On approval, re-fetch the release and asset by immutable IDs, revalidate ownership and checksum where offered, apply existing bounded/private staging and transactional file finalization, then publish only after successful persistence.
- [ ] Failed validation, GitHub outages, changes/deletion of an asset, oversized ZIPs and failed storage writes must leave the proposal pending and **never** publish a broken Downloads entry.
- [x] Add schema-level uniqueness for GitHub proposal repository/release/asset and show proposals in My Downloads (pending only).\n- [ ] Extend duplicate protections across administrative imports and public proposals, record review history and implement moderator approval/rejection and resulting status updates.
- [ ] Ensure existing native file-upload submission, approval, rejection and historical submissions work unchanged.
- [ ] Provide localized labels, guidance, empty/error states and accessibility consistent with Memorandum administration/public UI instructions.
- [ ] Test with anonymous, ordinary member, Downloads editor, moderator and Root users, including spoofed POSTs, replayed CSRF, network failure and multiple assets.

**Non-goals for 1.3.2:** automatic polling or updates, trust based solely on a GitHub URL as proof of repository ownership, arbitrary provider URLs or unattended publication. Those remain part of 1.4.0 planning.

### Architecture correction — reuse native Geeklog Downloads moderation (2026-10-18)

- [x] Member GitHub proposals are now inserted into `downloadsubmission` and appear in native Geeklog moderation and the existing My Downloads submission history. The `downloadgithubproposals` table is retained **only as a provider-metadata sidecar**, keyed by the native `lid`, for immutable release/asset identity and processing status.
- [x] Native Geeklog approval and Downloads admin approval now both attempt bounded GitHub archive transfer at moderation time using the existing private staging and normal pending-file finalization. Failed transfers must not result in a published item; core approval rollback is implemented but **needs live verification**.
- [x] Rejection updates GitHub provider metadata; successful approval updates status, size and checksum.
- [x] Removed the duplicate My Downloads proposals list in favor of the ordinary submission history.
- [x] Correct GitHub-specific approval visibility in Core moderation and Downloads admin approval; ensure failed Core finalization removes newly staged pending ZIP to permit a clean retry (code paths only, functional tests pending).\n- [x] Repair legacy upgrade chain so installations upgrading via 1.2.3.1/1.3.0 continue to the 1.3.1 -> 1.3.2 schema change (static fix).\n- [x] Refuse ZIP assets with missing or excessive size before creating staging files in moderation (static fix).\n- [x] Check that Core approval rollback never deletes the sole remaining submission row if copying back to the pending table fails; leave the public row hidden and log a critical error for recovery (static fix).\n- [ ] Confirm MySQL migration and uninstall safety, idempotency and failed-transfer recovery on g2 before installing.
- [x] Handle partial disk writes within the bounded cURL ZIP streaming callback; only acknowledge a complete chunk (static correction, functional tests pending).
- [x] Bound GitHub Release asset HEAD response headers to 16 KiB and accept only explicit CDN redirect statuses 302/303/307/308 (static correction; live GitHub asset behavior still unverified).
- [x] Support the documented direct HTTP 200 release asset response alongside validated CDN redirects; both use bounded streaming and ZIP signature verification. Actual GitHub behavior still needs an integration test.\n- [x] Clean newly fetched temporary pending ZIP on failed Downloads-admin approval to allow retry without a stale-file collision (static correction).\n- [ ] Test GitHub release CDN redirects, successful ZIP download, approval/rejection from both moderation screens, user-owned history, no-file submissions, duplicates and missing metadata; verify that failed imports stay pending.
- [ ] Revisit whether existing abandoned `downloadgithubproposals` rows from intermediate unreleased builds need one-off migration; do not drop user records silently.
- [ ] Localize form labels and make category/project editing consistent with the native submission editor.
- [ ] Do not release until browser acceptance and integration tests confirm the full transaction.

### Release acceptance for 1.3.2

- [ ] Reproduce and document each targeted bug before making a behavioral change.
- [ ] Test create/edit/replacement/deletion, admin and regular-user moderation, identical/different snapshot name collisions, rollback and old pending uploads.
- [ ] Test files with spaces, punctuation and non-ASCII names; verify broken and unavailable snapshots are handled gracefully.
- [ ] Test errors for empty upload, PHP size limit (including `post_max_size` exceeded with empty `$_POST`), permissions and disk write failures.
- [ ] Validate with Geeklog 2.2.2 and PHP 8.1, run PHP 5.6/8.1/8.3 syntax checks, and explicitly note untested historical installations.
- [ ] Produce release ZIP via Actions and inspect manifest; keep version/package metadata aligned before PR.
- [ ] Obtain maintainer review prior to release.

### Download statistics by period (planned for 1.4.0)

**Goal:** supplement the existing lifetime `downloads.hits` counter with aggregated, date-based reporting; do not delay the 1.3.2 PR.

- [ ] Add a daily aggregate table keyed by download ID and date (site-local reporting timezone), with a unique key and indexed date for efficient reporting; store counts only, without visitor IPs or individual event histories.
- [ ] Update the existing successful download counter path to increment the daily aggregate reliably, keeping `hits` and its historical meaning unchanged. Confirm concurrent downloads cannot lose increments.
- [ ] Provide admin dashboards for today, last 7/30 days, custom date range, month/year trends, top downloads and category-level totals.
- [ ] Support comparison with the previous equivalent period and explicit CSV export when appropriate.
- [ ] Apply Downloads authorization rules to administrative reports and keep aggregate collection economical on busy sites.
- [ ] Define retention, deletion/uninstall behavior and handling of removed downloads; document multisite database separation.
- [ ] Test date boundaries/timezones, high concurrency, upgrades, rollback and PHP/Geeklog compatibility.
- [ ] Explain clearly that period statistics begin **only when tracking is enabled**; historical per-period counts cannot be reconstructed from existing lifetime `hits` totals.

## Planned feature cycle: 1.4.0 — release synchronization and project history

The manual GitHub Releases importer is now a **1.3.2 release requirement** (see P0 above). Version 1.4.0 is reserved for opt-in review queues for newer releases, durable project-to-release history, and possible additional release providers. No unattended publication.

### Deliberate non-goals

No automatic filesystem deletion, no unattended external release publication, and no automatic replacement of existing downloads. The 1.3.2 release should remain a focused corrective release.

---

## Historical 1.3.0 implementation status

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
- XMLSitemap collection now includes the Downloads landing page, public categories and public downloads;
- richer Preview rendering with title/category/file/version/size/project/homepage/image/content;
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

Planned GitHub release tag: `v1.3.0` (create only after final acceptance).

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
- [x] Comments and rating
- [ ] User submission creates Pending state
- [ ] Approval through Geeklog moderation creates Published state
- [ ] Approval through Downloads administration
- [ ] Rejection creates Rejected state
- [ ] New-submission email notification
- [ ] Submitter approval email notification
- [ ] Missing physical file produces admin diagnostic and public 404
- [x] IndexNow integration
- [x] XMLSitemap downloads
- [x] XMLSitemap categories (implementation added; real regeneration test required)
- [x] Generic public 404 handling
- [x] Rich editor Preview reflects the intended public content
- [ ] Oversized upload produces a controlled user-facing error

### Public UI direction

- Public pages should keep their existing visual identity and charm.
- Prefer spacing, responsive, accessibility and small visual refinements over a wholesale redesign.
- Administration forms can be modernized more substantially because clarity and maintenance take priority there.

### Deferred from the 1.3.0 release candidate

- Multisite-safe automatic migration of persistent storage outside `public_html`.
- Default download image enhancement.
- Legacy File Manager conversion cleanup unless a reproducible 1.3.0 regression is found.



### Interoperability alignment

Downloads 1.3.0 now targets the Memorandum content interoperability contract:

- [x] Item Info for legacy download IDs
- [x] Stable root identity: `root`
- [x] Stable category identities: `category:<cid>`
- [x] Normalized subtype / is-container / parent-id fields
- [x] Normalized hits / image / category metadata for downloads
- [x] Collection filtering by subtype, including `all`
- [x] Native `plugin_collectSitemapItems_downloads()`
- [x] Correct Geeklog 2.2.2 `plugin_idToURL_downloads($sub_type, $item_id)` signature
- [x] Sitemap URL de-duplication
- [x] Category lifecycle events
- [x] Download lifecycle events on category cascade delete
- [x] `PLG_itemDisplay()` extension points on full download/root/category views
- [x] Metadata cooperation through `PLG_getMetaTags()`
- [x] Language-aware category collections

A full XMLSitemap regeneration is required after installing this build to remove duplicate rows created by the previous incorrect `idToURL` signature.


### XMLSitemap interoperability note

- XMLSitemap duplicate output was traced to duplicated `types` entries in XMLSitemap configuration, not to duplicate URLs returned by Downloads.
- Downloads itself de-duplicates its collection output.
- A separate core XMLSitemap fix is maintained in `hostellerie/geeklog-2.2.2:xmlsitemap-deduplicate-types`.
- After deploying that core fix, run a full sitemap regeneration once to remove previously persisted duplicate rows.


### Plugin configuration interoperability

- [x] Runtime configuration loading no longer leaks generic temporary globals.
- [x] Generic `ROOTID` constant replaced by `DLM_ROOTID`.
- [x] Multilingual SQL helper accepts the category table alias.
- [x] XMLSitemap/Item Info queries using alias `c` explicitly request `DLM_helper_getLangSQL('c')`.
- [x] Validate saving the configuration of another active plugin while Downloads is enabled.
