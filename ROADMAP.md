# Downloads 1.3.0 Roadmap

## Release goal

Downloads 1.3.0 is a stabilization and modernization release for the Geeklog Downloads plugin.

The release targets:

- Geeklog 2.1.2 through 2.2.2;
- PHP 5.6 through PHP 8.1 as the maintained compatibility baseline, with PHP 8.3 validation where practical;
- safe upgrades from existing Downloads installations;
- preservation of existing user data and download files;
- compliance with the Geeklog Development & Modernization Memorandum;
- an installable distribution archive named `downloads_1.3.0_2.1.2.zip`.

The release should favor structural fixes over local patches. Submission, approval, storage, permissions and public rendering must each have one clear lifecycle and source of truth.

## P0 — Submission and approval reliability

### Fix submission editor permissions and preview

- Fix the `DLDownload::$_cat_owner_id` / undefined category permission warning reported in issue #25.
- Load the submission and its category permissions before checking access.
- Use category ACLs as the authoritative permission source for a submitted download.
- Ensure preview, edit, approve and delete operations use the same permission rules.
- Avoid adding placeholder properties merely to silence PHP warnings.

### Unify approval workflows

- Remove the duplicated business logic between:
  - `DLDownload::approve()`;
  - `DLM_approveNewDownload()`;
  - moderation approval hooks.
- Introduce one canonical approval workflow used by both the Downloads administration UI and Geeklog moderation.
- Verify the pending file exists before any database state is finalized.
- Move/rename the pending file to its final secret-id filename deterministically.
- Move snapshots and generate thumbnails through the same canonical workflow.
- Keep failed submissions recoverable.
- Never leave a published database record pointing to a missing file.
- Resolve issue #3.

### Make file replacement failure-safe

- Never delete an existing published file before a replacement upload has been fully validated.
- Upload/prepare the replacement first, then switch to it only after success.
- Preserve the previous file when replacement fails.
- Improve explicit logging for source path, destination path and move/copy failures without exposing sensitive filesystem information publicly.

### Upload errors

- Review all PHP upload error states.
- Keep the existing handling for `UPLOAD_ERR_INI_SIZE`, `UPLOAD_ERR_FORM_SIZE`, `UPLOAD_ERR_NO_FILE` and other failures.
- Return a useful administrator/user-facing error instead of silently returning to the list.
- Clean up partial filesystem/database state.
- Retest issue #8.

## P0 — Security and persistent storage

### Storage architecture

- Review the legacy default storage below `public_html/downloads_data/`.
- Prefer site-specific persistent storage derived from `$_CONF['path_data']` for files that do not require direct public access.
- Separate pending submissions from published files.
- Ensure multisite installations do not unintentionally share uploaded files.
- Provide a non-destructive, repeatable migration path for existing installations.
- Keep legacy files in place until migration success has been verified.

### Upload safety

- Do not trust uploaded filenames as filesystem paths.
- Normalize storage filenames consistently.
- Review executable/script upload risks.
- Serve downloads through a controlled endpoint where practical so permissions, content type, logging and filename handling can be enforced.
- Validate images before thumbnail generation.
- Remove avoidable error suppression from critical file operations.

### State-changing actions

- Audit CSRF protection for create, edit, approve, delete, vote/rating and moderation actions.
- Recheck ACLs server-side even when UI controls are hidden.

## P0 — Compatibility and release lifecycle

- Set release version to 1.3.0 only when the upgrade path exists.
- Keep the minimum Geeklog version at 2.1.2.
- Add a sequential `1.2.3.1 -> 1.3.0` upgrade stage.
- Keep `plugin.json`, installer metadata and documentation consistent.
- Add the PHP minimum to `plugin.json` once verified.
- Test clean install, enable, disable, re-enable, uninstall and reinstall.
- Test upgrade from the latest published 1.2.x state.
- Verify failed-install rollback through the complete auto-uninstall contract.
- Lint all shipped `.php` and `.inc` files before packaging.
- Test on Geeklog 2.1.2 and 2.2.2.
- Validate PHP 5.6 and PHP 8.1; additionally test PHP 8.3 where practical.

## P1 — Notifications and email

### Submission notifications

- Resolve issue #1.
- Add an explicit option to notify Downloads moderators when a new submission enters the queue.
- Keep submitter approval notification separate from moderator submission notification.
- Determine recipients from a clear moderation contract rather than assuming all Root users should receive the email.
- Include a direct moderation link when possible.

### Modern email templates

- Resolve issue #21.
- Replace legacy hand-built email bodies with the standard Geeklog templated email mechanism where supported across the target range.
- Support plaintext and HTML without duplicating notification business logic.
- Keep language strings translatable and provide safe English fallback.

## P1 — PHP 8 stabilization and code quality

Audit the complete plugin for PHP 8 warnings and runtime regressions, including:

- undefined variables;
- undefined array keys;
- undefined object properties;
- nullable values passed to string functions;
- deprecated behavior;
- invalid image/resource handling;
- direct optional request access;
- assumptions about missing configuration keys.

Known examples to verify include:

- initialize `$finalrating` when a download has no votes;
- correct the `$limit` / `$limits` mismatch in feed update checks;
- make optional `$_FILES` keys safe;
- ensure submission objects are fully initialized before permission checks.

Do not suppress warnings as a substitute for fixing their cause.

## P1 — Public SEO

Audit all public Downloads pages against the Memorandum SEO guidance.

### Download detail pages

- Ensure each released download has a unique and meaningful page title.
- Generate a useful meta description from the download description when appropriate.
- Expose canonical public URLs consistently.
- Ensure Item Info returns usable `title`, `url`, `description/excerpt`, dates and IDs.
- Preserve permissions and language filtering.
- Review Open Graph/social metadata integration through generic Geeklog/plugin contracts rather than provider-specific coupling.
- Ensure released/unreleased state is respected by crawlers and APIs.

### Categories and listings

- Provide meaningful titles/headings and descriptive context.
- Avoid duplicate/empty indexable pages.
- Review pagination canonicalization and metadata.
- Ensure invalid category IDs, file IDs and page numbers return an actual 404.
- Resolve issue #19.
- Retest sitemap/IndexNow behavior from issue #24 after Item Info and URLs are stabilized.

### Structured interoperability

- Keep `plugin_getiteminfo_downloads()` as the canonical content metadata contract.
- Review collection support for `'*'`, including permission/language-aware results.
- Add common collection options such as `since`, `limit` and `order` if useful and compatible with the Memorandum.
- Ensure `PLG_itemSaved()` and `PLG_itemDeleted()` fire only after successful state changes.
- Add `plugin_idtourl_downloads()` where appropriate and compatible.

## P1 — Public usability and search

### Downloads search on `downloads/index.php`

Add a lightweight search capability directly to the Downloads public interface.

Requirements:

- search released/visible downloads only;
- honor category permissions;
- honor active language filtering;
- search at least title, description, detail, project and version where useful;
- preserve a category filter when practical;
- support a clear empty-result state;
- keep queries safe through Geeklog database abstractions;
- use GET parameters so search result URLs are bookmarkable;
- avoid duplicating Geeklog's global search engine where the existing search API can be reused cleanly.

Possible UI:

- compact search field near the Downloads heading/navigation;
- optional category selector;
- clear/reset action;
- pagination for larger result sets.

### Public ergonomics

Review the current public templates on both Denim-style/legacy themes and Eclipse.

Improve only where the plugin benefits from it:

- responsive listings;
- clearer category navigation;
- readable download cards/rows;
- visible version/date/filesize metadata;
- accessible buttons and form labels;
- useful empty states;
- consistent download, details, history and rating actions;
- avoid theme-specific hard dependencies;
- default image/fallback presentation for issue #9 if it improves consistency.

## P1 — Administration usability

Review the Downloads administration organization.

Possible improvements:

- clearer navigation between Downloads, Categories, Submissions and Configuration;
- pending submission count visible where useful;
- consistent list actions;
- compact filters/search for large download catalogs;
- category filtering;
- sortable useful columns;
- clearer published/unreleased/listing status;
- direct public-page link from each item;
- explicit file-health indication when a database record references a missing file;
- warnings for unwritable storage paths;
- diagnostics for pending orphan files.

Keep admin presentation in templates/CSS where practical and avoid page-specific inline styling.

## P2 — Configuration modernization

- Keep labels concise.
- Add native Geeklog configuration tooltips only for settings that need explanation.
- Clarify the distinction between:
  - upload permission;
  - submission queue behavior;
  - moderator notifications;
  - submitter approval notifications;
  - storage paths;
  - file permissions;
  - thumbnail behavior.
- Ensure all configuration keys used during bootstrap exist safely on upgraded sites.
- Keep configuration migration separate from fresh-install defaults.
- Avoid breaking shared-file/multisite deployments while one site is still on previous persisted configuration.

## P2 — Media and thumbnails

- Retest thumbnail generation for JPEG, PNG and GIF.
- Correct legacy extension/format edge cases.
- Handle invalid/corrupt images without fatal errors.
- Preserve original image quality where possible.
- Review default/fallback image support from issue #9.
- Ensure thumbnail paths remain consistent if persistent storage is migrated.

## P2 — Feeds, comments, ratings and history

- Retest comment permissions from issue #22 and close only after regression tests pass.
- Verify feed queries honor permissions, language, release state and limits.
- Fix feed update checks and undefined variables.
- Verify rating calculations with zero votes.
- Review download history/privacy settings and access checks.
- Ensure these secondary features do not create warnings under PHP 8.

## P3 — Legacy cleanup

- Review File Manager conversion code separately from normal runtime.
- Do not risk current installations merely to solve obsolete migration scenarios.
- Document issue #15 as legacy unless a safe, bounded cleanup tool is justified.
- Remove duplicated helpers only after the canonical replacement is proven.
- Keep compatibility code narrow and documented.

## Documentation

Update at least:

- `README.md`;
- `INSTALL.txt`;
- release notes/changelog for 1.3.0;
- configuration/help documentation;
- compatibility requirements;
- upgrade/storage migration notes;
- contributor/developer notes where architecture changes need explanation.

Document the distinction between published storage and pending submission storage.

## Distribution and CI

The release archive must be produced as:

`dist/downloads_1.3.0_2.1.2.zip`

Packaging rules:

- archive must contain the plugin files in installable Geeklog layout;
- exclude `dist/` itself from the archive;
- exclude VCS metadata;
- exclude development-only tooling that is not required by the installed plugin;
- exclude every file or directory whose basename starts with `.`;
- reject the build if any archived path component begins with `.`;
- lint shipped PHP/INC files before packaging;
- verify required templates, CSS, JavaScript, language and image assets exist;
- produce deterministic/reproducible packaging where practical.

## Release acceptance checklist

1. Clean install succeeds on Geeklog 2.1.2.
2. Clean install succeeds on Geeklog 2.2.2.
3. Upgrade from 1.2.3.1 succeeds without data loss.
4. Regular user submission succeeds.
5. Moderator receives the configured notification.
6. Submission preview/edit works without PHP warnings.
7. Approval from Downloads Admin succeeds.
8. Approval from Geeklog moderation succeeds through the same canonical workflow.
9. Published file exists and downloads correctly after approval.
10. Failed approval does not create a broken published record.
11. Existing-file replacement failure preserves the previous file.
12. Public category/detail/search pages honor permissions and language.
13. Invalid public IDs/pages produce HTTP 404.
14. Comment/rating/feed/history paths run without PHP warnings.
15. No critical PHP warnings under PHP 8.1/8.3 validation.
16. Storage remains isolated between multisite contexts.
17. The installable ZIP contains no path whose basename starts with `.`.
18. The final ZIP installs without manual file rearrangement.
