<?php
/**
 * \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/
 */
// removetorrentfromhash djGrrr <3
function remove_torrent_peers($id)
{
    global $mc1;
    if (!is_int($id) || $id < 1) {
        return false;
    }
    $delete = 0;
    $seed_key = 'torrents::seeds:::' . $id;
    $leech_key = 'torrents::leechs:::' . $id;
    $comp_key = 'torrents::comps:::' . $id;
    $delete += $mc1->delete_value($seed_key);
    $delete += $mc1->delete_value($leech_key);
    $delete += $mc1->delete_value($comp_key);

    return (bool)$delete;
}

function remove_torrent($infohash)
{
    global $mc1;
    if (strlen($infohash) != 20 || !bin2hex($infohash)) {
        return false;
    }
    $key = 'torrent::hash:::' . md5($infohash);
    $torrent = $mc1->get_value($key);
    if ($torrent === false) {
        return false;
    }
    $mc1->delete_value($key);
    if (is_array($torrent)) {
        remove_torrent_peers($torrent['id']);
    }

    return true;
}
