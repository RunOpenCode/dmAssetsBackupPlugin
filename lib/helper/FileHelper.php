<?php

if (!function_exists('format_file_size_from_bytes')) {
    /**
     * Formats the file size from bytes to bytes, KBs, MBs or GBs and adds the label
     *
     * @param integer $sizeInBytes The size of file in bytes
     * @param integer $decimalPlaces The number of decimal places when rounding number, default is 2.
     * @param boolean $usePlural Should use plural for more than 1 unit? Default is false.
     * @param array $labels Additional, used labels for byte(s), KB(s), MB(s) and GB(s) can be set
     * @return string The formated size, per example '5 MB'
     */
    function format_file_size_from_bytes($sizeInBytes, $decimalPlaces = 2, $usePlural = false, array $labels = array(
            'byte' => 'byte',
            'bytes' => 'bytes',
            'KB' => 'KB',
            'KBs' => 'KBs',
            'MB' => 'MB',
            'MBs' => 'MBs',
            'GB' => 'GB',
            'GBs' => 'GBs'
        )) {
        if ($sizeInBytes < 1024) { // size of KB
            if ($sizeInBytes == 1) {
                return '1 ' . $labels['byte'];
            } else {
                return $sizeInBytes . ' ' . $labels['bytes']; // Always use plural for bytes
            }

        } elseif ($sizeInBytes < 1048576) { // size of MB
            if ($sizeInBytes == 1024) {
                return '1 ' . $labels['KB'];
            } else {
                return (round(( $sizeInBytes / 1024), $decimalPlaces) + 0) . ' ' . (($usePlural) ? $labels['KBs'] : $labels['KB']);
            }
        } elseif ($sizeInBytes < 1073741824) { // size of GB
            if ($sizeInBytes == 1048576) {
                return '1 ' . $labels['MB'];
            } else {
                return (round(($sizeInBytes / 1048576), $decimalPlaces)  + 0) . ' ' . (($usePlural) ? $labels['MBs'] : $labels['MB']);
            }
        } else { // Is there any need for TB for the web applications?
            if ($sizeInBytes == 1073741824) {
                return '1 ' . $labels['GB'];
            } else {
                return (round(($sizeInBytes / 1073741824), $decimalPlaces) + 0) . ' ' . (($usePlural) ? $labels['GBs'] : $labels['GB']);
            }
        }
    }

}

if (!function_exists('format_posix_file_permissions_to_human')) {
    /**
     * Format posix file permissions to human readable permissions, see: http://php.net/manual/en/function.fileperms.php
     *
     * @param int $perms Gained via fileperms PHP function
     * @return string The human readable permissions
     */
    function format_posix_file_permissions_to_human($perms) {
        if (($perms & 0xC000) == 0xC000) {
            $info = 's';
        } elseif (($perms & 0xA000) == 0xA000) {
            $info = 'l';
        } elseif (($perms & 0x8000) == 0x8000) {
            $info = '-';
        } elseif (($perms & 0x6000) == 0x6000) {
            $info = 'b';
        } elseif (($perms & 0x4000) == 0x4000) {
            $info = 'd';
        } elseif (($perms & 0x2000) == 0x2000) {
            $info = 'c';
        } elseif (($perms & 0x1000) == 0x1000) {
            $info = 'p';
        } else {
            $info = 'u';
        }

        $info .= (($perms & 0x0100) ? 'r' : '-');
        $info .= (($perms & 0x0080) ? 'w' : '-');
        $info .= (($perms & 0x0040) ?
            (($perms & 0x0800) ? 's' : 'x' ) :
            (($perms & 0x0800) ? 'S' : '-'));

        $info .= (($perms & 0x0020) ? 'r' : '-');
        $info .= (($perms & 0x0010) ? 'w' : '-');
        $info .= (($perms & 0x0008) ?
            (($perms & 0x0400) ? 's' : 'x' ) :
            (($perms & 0x0400) ? 'S' : '-'));

        $info .= (($perms & 0x0004) ? 'r' : '-');
        $info .= (($perms & 0x0002) ? 'w' : '-');
        $info .= (($perms & 0x0001) ?
            (($perms & 0x0200) ? 't' : 'x' ) :
            (($perms & 0x0200) ? 'T' : '-'));

        return $info;
    }

}

if (!function_exists('get_posix_file_owner_info_by_id')) {
    /**
     * Returns required info regarding owner, see http://www.php.net/manual/en/function.posix-getpwuid.php
     *
     * @param integer $uid UID of owner
     * @param string $field Field to fetch, default is name
     * @return string
     */
    function get_posix_file_owner_info_by_id($uid, $field = 'name') {
        if (function_exists('posix_getpwuid')) {
            $tmp = false;
            $tmp = @posix_getpwuid($uid);
            return ($tmp) ? $tmp[$field] : $uid;
        } else
            return $uid;
    }

}

if (!function_exists('get_posix_file_group_info_by_id')) {
    /**
     * Returns required info regarding group, see http://www.php.net/manual/en/function.posix-getgrgid.php
     *
     * @param integer $gid GID of group
     * @param string $field Field to fetch, default is name
     * @return string
     */
    function get_posix_file_group_info_by_id($gid, $field = 'name') {
        if (function_exists('posix_getgrgid')) {
            $tmp = false;
            $tmp = @posix_getgrgid($gid);
            return ($tmp) ? $tmp[$field] : $gid;
        } else
            return $gid;
    }

}

if (!function_exists('get_file_properties')) {
    /**
     * Fetches various info for file based on path for used display
     *
     * @param string $pathToFile
     * @return array the OS properties of the file:
     *          - dirname
     *          - server_path
     *          - root_path
     *          - public_path
     *          - filename
     *          - basename
     *          - extension
     *          - mime
     *          - accessed
     *          - modified
     *          - created
     *          - size
     *          - group
     *          - owner
     *          - permissions
     */
    function get_file_properties($pathToFile) {

        $mimeTypeResolver = dmContext::getInstance()->getServiceContainer()->getService('mime_type_resolver');

        $stats = stat($pathToFile);
        $pathinfo = pathinfo($pathToFile);

        $result['dirname'] = $pathinfo['dirname'];
        $result['server_path'] = $pathToFile;
        $result['root_path'] = str_replace(sfConfig::get('sf_root_dir'), '', $pathToFile);
        $result['public_path'] = (str_replace(sfConfig::get('sf_web_dir'), '', $pathToFile) == $pathToFile) ? 'N/A' : str_replace(sfConfig::get('sf_web_dir'), '', $pathToFile);
        $result['filename'] = $pathinfo['filename'];
        $result['basename'] = $pathinfo['basename'];
        $result['extension'] = $pathinfo['extension'];
        $result['mime'] = $mimeTypeResolver->getByExtension($pathinfo['extension'], 'text/plain');
        $result['accessed'] = $stats['atime'];
        $result['modified'] = $stats['atime'];
        $result['created'] = $stats['atime'];
        $result['size'] = $stats['size'];
        $result['group'] = $stats['gid'];
        $result['owner'] = $stats['uid'];
        $result['permissions'] = $stats['mode'];
        clearstatcache();
        return $result;
    }
}