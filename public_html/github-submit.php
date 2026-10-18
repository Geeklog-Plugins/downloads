<?php
/**
 * Member-facing GitHub Release proposal preview.
 * Read-only first step. This page never creates a Downloads item or downloads a ZIP.
 */
require_once '../lib-common.php';

if (!in_array('downloads', $_PLUGINS)) {
    COM_redirect($_CONF['site_url'] . '/index.php');
}
if (COM_isAnonUser()) {
    COM_output(COM_createHTMLDocument(SEC_loginRequiredForm()));
    exit;
}

$escape = function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$pageUrl = $_CONF['site_url'] . '/downloads/github-submit.php';
$display = '<h2>Propose a GitHub Release</h2>'
         . '<p>Preview a public release before proposing it to the Downloads moderators. '
         . 'This page does not submit or publish an item, and no archive is downloaded.</p>'
         . '<form action="' . $escape($pageUrl) . '" method="post">'
         . '<label for="release_url">Public GitHub Release URL</label> '
         . '<input id="release_url" name="release_url" type="url" size="70" maxlength="500" required '
         . 'placeholder="https://github.com/owner/repository/releases/tag/v1.0.0">'
         . '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . $escape(SEC_createToken()) . '">'
         . '<button type="submit">Preview release</button></form>';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!SEC_checkToken()) {
        $display .= '<p>Security token expired. Reload this page and try again.</p>';
    } else {
        $url = isset($_POST['release_url']) && is_string($_POST['release_url'])
             ? trim($_POST['release_url']) : '';
        if (strlen($url) > 500) {
            $display .= '<p>The GitHub Release URL is too long.</p>';
        } else {
            require_once $_CONF['path'] . 'plugins/downloads/include/github_release.php';
            $error = '';
            $release = DLM_githubReleaseDetails($url, $error);
            if ($release === false) {
                $display .= '<p>' . $escape($error) . '</p>';
            } else {
                $action = isset($_POST['github_action']) && is_string($_POST['github_action'])
                        ? $_POST['github_action'] : 'preview';
                if ($action === 'propose') {
                    $assetId = isset($_POST['asset_id']) && is_scalar($_POST['asset_id'])
                             ? (string) $_POST['asset_id'] : '';
                    $title = isset($_POST['title']) && is_string($_POST['title'])
                           ? trim($_POST['title']) : '';
                    $description = isset($_POST['description']) && is_string($_POST['description'])
                                 ? trim($_POST['description']) : '';
                    $selected = null;
                    if (ctype_digit($assetId)) {
                        foreach ($release['assets'] as $asset) {
                            if ((string) $asset['id'] === $assetId) {
                                $selected = $asset;
                                break;
                            }
                        }
                    }
                    $uid = (int) $_USER['uid'];
                    $recent = DB_query("SELECT COUNT(*) AS count_proposals FROM {$_TABLES['downloadgithubproposals']} " .
                                       "WHERE owner_id=$uid AND created_at > " . (time() - 86400));
                    $recentRow = $recent ? DB_fetchArray($recent) : array('count_proposals' => 99);
                    if ($selected === null || $title === '' || strlen($title) > 100 ||
                        $description === '' || strlen($description) > 4000 ||
                        (int) $selected['size'] > 52428800 || (int) $recentRow['count_proposals'] >= 5) {
                        $display .= '<p>Invalid proposal or daily submission limit reached (5 per day).</p>';
                    } else {
                        $repository = DB_escapeString($release['repository']);
                        $releaseUrl = DB_escapeString($release['url']);
                        $tag = DB_escapeString($release['tag']);
                        $assetName = DB_escapeString($selected['name']);
                        $safeTitle = DB_escapeString(strip_tags($title));
                        $safeDescription = DB_escapeString(strip_tags($description));
                        $releaseId = (int) $release['id'];
                        $selectedId = (int) $selected['id'];
                        $now = time();
                        $secretId = bin2hex(openssl_random_pseudo_bytes(16));
                        // Native Downloads submission is the authoritative moderation item.
                        $lid = 'gh' . substr(hash('sha256', $repository . '/' . $releaseId . '/' . $selectedId), 0, 30);
                        $safeLid = DB_escapeString($lid);
                        $previous = DB_query("SELECT lid FROM {$_TABLES['downloadgithubproposals']} " .
                           "WHERE repository='$repository' AND release_id=$releaseId AND asset_id=$selectedId");
                        if ($previous && DB_numRows($previous) > 0) {
                            $display .= '<p>This release asset has already been submitted.</p>';
                        } else {
                            // This record is incomplete by design until approval transfers its ZIP.
                            $native = "INSERT INTO {$_TABLES['downloadsubmission']} " .
                                "(lid,cid,title,url,homepage,version,description,detail,date,createddate,owner_id,secret_id," .
                                "is_released,is_listing,postmode) VALUES " .
                                "('$safeLid','" . DB_escapeString(DLM_ROOTID) . "','$safeTitle','$assetName'," .
                                "'','" . DB_escapeString(substr($release['tag'], 0, 10)) . "'," .
                                "'$safeDescription',''," . $now . ",NOW(),$uid,'$secretId',0,0,'plaintext')";
                            $meta = "INSERT INTO {$_TABLES['downloadgithubproposals']} " .
                                "(lid,owner_id,repository,release_id,asset_id,release_url,tag,asset_name," .
                                "title,description,status,created_at,updated_at) VALUES " .
                                "('$safeLid',$uid,'$repository',$releaseId,$selectedId,'$releaseUrl','$tag'," .
                                "'$assetName','$safeTitle','$safeDescription','pending',$now,$now)";
                            if (DB_query($native, 1)) {
                                if (DB_query($meta, 1)) {
                                    require_once $_CONF['path'] . 'plugins/downloads/include/functions.php';
                                    DLM_recordSubmissionStatus($lid, 'pending');
                                    $display .= '<p>Submitted to the normal Geeklog moderation queue. No ZIP was downloaded.</p>';
                                } else {
                                    DB_delete($_TABLES['downloadsubmission'], 'lid', $safeLid);
                                    $display .= '<p>Could not save GitHub metadata. No submission was created.</p>';
                                }
                            } else {
                                $display .= '<p>Could not create native Downloads submission.</p>';
                            }
                        }
                    }
                }
                $display .= '<h3>' . $escape($release['name'] !== '' ? $release['name'] : $release['tag']) . '</h3>'
                         . '<p>Repository: ' . $escape($release['repository']) .
                           ' — Tag: ' . $escape($release['tag']) . '</p>'
                         . '<p>Published: ' . $escape($release['published_at']) . '</p>'
                         . '<h4>Release notes</h4><pre class="dlm-github-notes">'
                         . $escape(substr($release['notes'], 0, 8000)) . '</pre>'
                         . '<h4>Available ZIP assets</h4>';
                if (empty($release['assets'])) {
                    $display .= '<p>No ZIP archive is attached to this release.</p>';
                } else {
                    $display .= '<ul>';
                    foreach ($release['assets'] as $asset) {
                        $display .= '<li>' . $escape($asset['name']) . ' (' .
                            (int) $asset['size'] . ' bytes)</li>';
                    }
                    $display .= '</ul>';
                    if ($action !== 'propose') {
                        $defaultTitle = $release['name'] !== '' ? $release['name'] : $release['tag'];
                        $defaultDescription = substr($release['notes'], 0, 3500);
                        $display .= '<form method="post" action="' . $escape($pageUrl) . '">'
                            . '<input type="hidden" name="github_action" value="propose">'
                            . '<input type="hidden" name="release_url" value="' . $escape($url) . '">'
                            . '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . $escape(SEC_createToken()) . '">'
                            . '<label for="asset_id">ZIP archive</label> <select name="asset_id" id="asset_id" required>';
                        foreach ($release['assets'] as $asset) {
                            if ((int) $asset['size'] > 0 && (int) $asset['size'] <= 52428800) {
                                $display .= '<option value="' . (int) $asset['id'] . '">'
                                          . $escape($asset['name']) . '</option>';
                            }
                        }
                        $display .= '</select><p><label for="proposal_title">Title</label> '
                            . '<input id="proposal_title" name="title" required maxlength="100" value="'
                            . $escape(substr($defaultTitle, 0, 100)) . '"></p>'
                            . '<p><label for="proposal_description">Description</label></p>'
                            . '<textarea id="proposal_description" name="description" required rows="7" cols="70" maxlength="4000">'
                            . $escape($defaultDescription) . '</textarea>'
                            . '<p><button type="submit">Submit proposal for moderation</button></p></form>';
                    }
                }
            }
        }
    }
}
$display .= '<p><a href="' . $escape($_CONF['site_url'] . '/submit.php?type=downloads') .
            '">Standard Downloads submission</a></p>';
COM_output(COM_createHTMLDocument($display, array('pagetitle' => 'GitHub Release proposal')));
