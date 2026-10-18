<?php
/**
 * Downloads storage inventory (read-only).
 *
 * Reports candidate orphaned files; never deletes, renames, or writes files.
 * Treat all candidates as unverified until reviewed by an administrator.
 */

function DLM_storageInventory()
{
    global $_TABLES, $_DLM_CONF;

    $groups = array(
        'files' => array('dir' => $_DLM_CONF['path_filestore'], 'references' => array()),
        'snaps' => array('dir' => $_DLM_CONF['path_snapstore'], 'references' => array()),
        'tn' => array('dir' => $_DLM_CONF['path_tnstore'], 'references' => array())
    );

    $published = DB_query("SELECT url, logourl, secret_id FROM {$_TABLES['downloads']}");
    while ($row = DB_fetchArray($published)) {
        if (!empty($row['url'])) {
            $name = DLM_createSafeFileName($row['url'], $row['secret_id']);
            $groups['files']['references'][$name] = true;
        }
        if (!empty($row['logourl'])) {
            $name = DLM_createSafeFileName($row['logourl']);
            $groups['snaps']['references'][$name] = true;
            $groups['tn']['references'][basename(DLM_changeFileExt(
                $name, $_DLM_CONF['tnimage_format']
            ))] = true;
        }
    }

    $pending = DB_query("SELECT url, logourl, secret_id, date FROM {$_TABLES['downloadsubmission']}");
    while ($row = DB_fetchArray($pending)) {
        foreach (array('files' => 'url', 'snaps' => 'logourl') as $group => $field) {
            if (empty($row[$field])) {
                continue;
            }
            $dir = rtrim($groups[$group]['dir'], "/\\") . DIRECTORY_SEPARATOR;
            $name = DLM_createPendingFileName((int) $row['date'], $row[$field], $row['secret_id']);
            // Historical pending filenames are resolved by the same helper as approval.
            // Count only the actual resolved file, or the expected current filename
            // if no file exists. Otherwise old submissions generate false missing counts.
            $found = DLM_findPendingFile($groups[$group]['dir'], (int) $row['date'],
                                         $row[$field], $row['secret_id']);
            $groups[$group]['references'][is_file($found) ? basename($found) : $name] = true;
        }
    }

    $output = array();
    foreach ($groups as $type => $group) {
        $stat = array('total' => 0, 'referenced' => 0, 'candidates' => 0,
                      'examples' => array(), 'missing' => 0, 'missing_examples' => array(), 'error' => '');
        $dir = $group['dir'];
        if (!is_dir($dir) || !is_readable($dir) || ($handle = @opendir($dir)) === false) {
            $stat['error'] = 'Storage directory unavailable or unreadable';
            $output[$type] = $stat;
            continue;
        }
        // Count referenced filenames not found on disk separately from orphan candidates.
        foreach ($group['references'] as $referenced_name => $unused) {
            if (!is_file(rtrim($dir, "/\\") . DIRECTORY_SEPARATOR . $referenced_name)) {
                $stat['missing']++;
                if (count($stat['missing_examples']) < 100) {
                    $stat['missing_examples'][] = $referenced_name;
                }
            }
        }
        while (($name = readdir($handle)) !== false) {
            if ($name === '.' || $name === '..' || !is_file(rtrim($dir, "/\\") . DIRECTORY_SEPARATOR . $name)) {
                continue;
            }
            $stat['total']++;
            if (isset($group['references'][$name])) {
                $stat['referenced']++;
            } else {
                $stat['candidates']++;
                if (count($stat['examples']) < 100) {
                    $stat['examples'][] = $name;
                }
            }
        }
        closedir($handle);
        sort($stat['examples'], SORT_STRING);
        sort($stat['missing_examples'], SORT_STRING);
        $output[$type] = $stat;
    }
    return $output;
}
