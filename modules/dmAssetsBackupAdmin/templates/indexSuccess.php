<?php

use_helper('File');
use_helper('Date');

$totalBackupSize = 0;

echo _open('div.dm-assets-backup-plugin');

    echo _open('form', array(
        'action' => _link('dmAssetsBackupAdmin/batchDelete')->getHref(),
        'method' => 'post'
    ));

        echo _open('table.dm-assets-backup-table.dm_data', 
            array('json' => array(
                'translation_url' => _link('dmPage/tableTranslation')->getHref()
            )));

            echo _open('thead');
                echo _tag('tr',
                        _tag('th', _tag('input.check-all', array('type'=>'checkbox'))).
                        _tag('th', __('File')).
                        _tag('th', __('Mime')). 
                        _tag('th', __('Created')).
                        _tag('th', __('Group')).
                        _tag('th', __('Owner')).
                        _tag('th', __('Permissions')).
                        _tag('th', __('Size')).
                        _tag('th', __('Actions'))
                    );
            echo _close('thead');

            echo _open('tbody');
                $count = 0;
                foreach ($backups as $backup) {

                    if (count($backups)) {
                        
                        $fileInfo = get_file_properties($backup);

                        $actionsHTML = _open('ul.sf_admin_td_actions').
                                _tag('li.sf_admin_action_download',
                                    _tag(
                                        'a.s16.s16_download.dm_download_link.sf_admin_action', 
                                        array(
                                            'title' => __('Download this backup file'), 
                                            'href' => _link('dmAssetsBackupAdmin/download')->param('key',$fileInfo['basename'])->getHref(),
                                            'target' => '_blank'
                                        ), 
                                        __('Download'))
                                    ).
                               _tag('li.sf_admin_action_clean', array('json'=>array('message'=>__('Are you shore that you want to delete this backup file?'))),
                                    _link('dmAssetsBackupAdmin/delete')
                                        ->set('a.s16.s16_delete.dm_delete_link.sf_admin_action')
                                        ->param('key', $fileInfo['basename'])
                                        ->title(__('Delete this backup file'))
                                        ->text(__('Delete'))).                               
                            _close('ul');
                        $count++;
                        echo _tag('tr'. (($count == count($backups)) ? '.last' : ''),
                            _tag('td', _tag('input.assets-backup-file', array('type'=>'checkbox', 'name'=>'file['.$fileInfo['basename'].']'))).
                            _tag('td', $fileInfo['basename']).
                            _tag('td', $fileInfo['mime']).  
                            _tag('td', format_date($fileInfo['created'], 'G', $sf_user->getCulture())).
                            _tag('td', get_posix_file_group_info_by_id($fileInfo['group'])).
                            _tag('td', get_posix_file_owner_info_by_id($fileInfo['owner'])).
                            _tag('td', format_posix_file_permissions_to_human($fileInfo['permissions'])).
                            _tag('td', format_file_size_from_bytes($fileInfo['size'])).
                            _tag('td', $actionsHTML)
                        );
                        $totalBackupSize += $fileInfo['size'];
                    }    
                }
            echo _close('tbody');
        echo _close('table');

        echo _open('div.dm_form_action_bar.dm_form_action_bar_bottom.clearfix');
            echo _open('ul.sf_admin_actions.clearfix');
                echo _tag('li', _tag('input.batch-delete-button', array('type'=>'submit', 'value'=>__('Batch delete selected'), 'name'=>'_batch_delete', 'json'=>array(
                    'message'=>__('Please select backup files for batch delete.')
                ))));
                echo _tag('li', _tag('input.delete-all-button', array('type'=>'button', 'value'=>__('Delete all backup files'), 'name'=>'_delete_all', 'json'=>array(
                    'message'=>__('Are you shore that you want to delete all backups?'),
                    'action' => _link('dmAssetsBackupAdmin/deleteAll')->param('key','DeleteAll')->getHref()
                ))));
            echo _close('ul');
            echo _tag('div.dm_help_wrap', array('style'=>'float:right; margin-top:2px;'), __('Total assets backup stored in: '). ' '. _tag('strong', format_file_size_from_bytes($totalBackupSize)) . ' ' .
                _tag('span', '&nbsp;&nbsp;').
                _tag('input.backup-button', array('type'=>'button', 'value'=>__('Backup assets now'), 'name'=>'_backup', 'json'=>array(
                    'action' => _link('dmAssetsBackupAdmin/backup')->param('key','Backup')->getHref()
                )))
                );
        echo _close('div');
    echo _close('div');


echo _open('div.help_box');
    echo _tag('strong', __('NOTE:'));
        echo ' ';
    echo _tag('span', __('Table row with bolder text marks latest backup.'));
        echo ' ';

    $rotations = sfConfig::get('dm_dmAssetsBackupPlugin_rotations');

    echo _tag('span', __('Current backup settings are: number of maximum backup files allowed is %max_backup_files%, backup storage size allowed is %max_backup_storage_size%.', array(
        '%max_backup_files%' => _tag('strong', ($rotations['max_backup_files']) ? $rotations['max_backup_files'] : __('unlimited')),
        '%max_backup_storage_size%' => _tag('strong', ($rotations['max_backup_size']) ? format_file_size_from_bytes($rotations['max_backup_size']) : __('unlimited'))
    )));

echo _close('div');

    echo _open('div.dm_box.big');
        echo _tag('h1.title', __('Setup a cron to backup assets periodically'));
        echo _open('div.dm_box_inner.documentation');
            echo _tag('p', __('Most UNIX and GNU/Linux systems allows for task planning through a mechanism known as cron. The cron checks a configuration file (a crontab) for commands to run at a certain time.'));
            echo _tag('p.mt10.mb10', __('Open /etc/crontab and add the line:'));
            echo _tag('code', _tag('pre', sprintf('@daily www-data /path/to/php %s/symfony dm:assets-backup', sfConfig::get('sf_root_dir'))));
            echo _tag('p.mt10', __('For more information on the crontab configuration file format, type man 5 crontab in a terminal.'));
        echo _close('div');
    echo _close('form');
echo _close('div');