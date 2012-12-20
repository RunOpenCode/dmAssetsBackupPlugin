dmAssetsBackupPlugin for Diem Extended
===============================

Author: [TheCelavi](http://www.runopencode.com/about/thecelavi)
Version: 1.0.0
Stability: Stable  
Date: December 9th, 2012  
Courtesy of [Run Open Code](http://www.runopencode.com)   
License: [Free for all](http://www.runopencode.com/terms-and-conditions/free-for-all)

dmAssetsBackupPlugin for Diem Extended is manager for assets backup, as well as 
for backuping assets. 

Configuration
-------------

In `dmAssetsBackupPlugin/config/dm/config.yml` are default configuration values.

	default:
      dmAssetsBackupPlugin:
        locations:
          backup_dir:              %SF_ROOT_DIR%/data/dm/backup/assets
          source_dir:              %SF_UPLOAD_DIR%
        rotations:
          max_backup_files:        10
          max_backup_size:         100M
        adapters:
          dmAssetsBackupAdapterTar:
            extensions:     [ tar, tar.gz, tar.bz2 ]
            use:            tar
          dmAssetsBackupAdapterZip:
            extensions:     [ zip ]
            use:            zip

`locations` configures source and destination directory, that is the directory
to backup and directory where that backup should go.

`rotations` defines the max backup files or max backup size. When one of these 
two is reached, the oldest backup gets deleted when new backup is created. Note
that `max_backup_size` accepts either `M` or `MB` and `G` or `GB` - megabytes and
gigabytes as size units. 

`adapters` are installed adapters for compressing the backup. Two are already provided,
TAR and ZIP. Each adapter uses some extension or creates file with some extension.

Which adapter will be used is configured in `dmAssetsBackupPlugin/config/dm/services.yml`


    parameters:

      assets_backup.class:       dmAssetsBackupService
      assets_backup.adapter:     dmAssetsBackupAdapterTar
      assets_backup.options:     []

    services:

      assets_backup:
        class:                %assets_backup.class%
        shared:               true
        arguments:            [ @user, @filesystem, %assets_backup.adapter%, %assets_backup.options% ]


Set parameter `assets_backup.adapter` for class that you would like to use.


Administration
---------------
Go to `System > Backup > Assets backup` and you will see the list of backup files. Each
file can be:

- Downloaded
- Deleted

Or you can create new backup.

In order to see list of backup files, to create or delete a backup and to download 
some or all of them, user must have a `assets_backup` or `backup` permission associated.


Task
----------------
Backups can be created via console, by using a task `dm:assets-backup`.

The task can be executed periodically, via cron - which is major advantage of this
plugin.

