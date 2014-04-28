dmAssetsBackupPlugin for Diem Extended
===============================

Author: [TheCelavi](http://www.runopencode.com/about/thecelavi)
Version: 1.0.1
Stability: Stable
Backward compatibility: Incompatible configuration with version 1.0.0
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
          max_backup_files:        3
          max_backup_size:         4294967296 # 4GB
        adapter:                   dmAssetsBackupAdapterTar
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
that `max_backup_size` accepts bytes as size units.

`adapter` which of installed adapters you would like to use for compression.

`adapters` are installed adapters for compressing the backup. Two are already provided,
TAR and ZIP. Each adapter uses some extension or creates file with some extension.


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

