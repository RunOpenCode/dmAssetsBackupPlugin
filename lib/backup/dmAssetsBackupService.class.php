<?php

class dmAssetsBackupService extends dmConfigurable
{

    protected
        $user,
        $filesystem,
        $adapterClass,
        $adapter;

    public function __construct($user, dmFilesystem $filesystem)
    {
        $this->user = $user;
        $this->filesystem = $filesystem;
        $this->adapterClass = sfConfig::get('dm_dmAssetsBackupPlugin_adapter');
    }
    
    public function getSourceDirectory()
    {
        $locations = sfConfig::get('dm_dmAssetsBackupPlugin_locations');
        return $locations['source_dir'];
    }

    public function getBackupDirectory()
    {
        $locations = sfConfig::get('dm_dmAssetsBackupPlugin_locations');
        return $locations['backup_dir'];
    }
    
    /*********************************
     * IO operations
     *********************************/
    public function fetchBackups()
    {
        $this->checkCredentials();
        $finder = sfFinder::type('file')->ignore_version_control()->maxdepth(0)->sort_by_name();
        foreach ($adapters = sfConfig::get('dm_dmAssetsBackupPlugin_adapters') as $adapter) {
            foreach ($adapter['extensions'] as $extension) {
                $finder->name('*.' . $extension);
            }
        }            
        return $finder->in($this->getBackupDirectory());
    }

    public function deleteBackup($fileName) 
    {
        $this->checkCredentials();
        return $this->filesystem->unlink($this->getFullPath($fileName));
    }

    public function deleteAll()
    {
        $this->checkCredentials();
        $signal = true;
        foreach ($backups = $this->fetchBackups() as $backup) {
            $signal = $signal && $this->deleteBackup($backup);
        }
        return $signal;
    }
    
    public function createBackup()
    {
        $this->checkCredentials();
        $eventLog = dmContext::getInstance()->getServiceContainer()->getService('event_log');
        if ($path = $this->getAdapter()->execute()) {            
            $eventLog->log(array(
                'server' => $_SERVER,
                'action' => 'assets',
                'type' => 'Assets',
                'subject' => 'Backup created'
            ));
            try {
                $this->rotate();
            } catch (dmException $e) {
                throw new dmException('Assets backup created, but there is a problem with rotatiton of backups.');
            }
            return $path;            
        } else {
            $eventLog->log(array(
                'server' => $_SERVER,
                'action' => 'error',
                'type' => 'Assets',
                'subject' => 'Backup error'
            ));
            throw new dmException('Could not create new assets backup');
        }
    }

    public function getFullPath($fileName)
    {        
        $this->checkCredentials();
        $origin = $fileName;
        if (!file_exists($fileName)){
            $fileName = dmOs::join($this->getBackupDirectory(), $fileName);
        }
        if (file_exists($fileName)) {
            return $fileName;
        } else {
            throw new dmException(sprintf('Backup file "%" does not exists.', $origin));
        }
    }

    public function isBackupExists($fileName)
    {
        $this->checkCredentials();
        if (!file_exists($fileName)){
            $fileName = dmOs::join($this->getBackupDirectory(), $fileName);
        }
        if (file_exists($fileName)) {
            return true;
        } else {
            return false;
        }
    }
    /*********************************
     * END IO operations
     *********************************/
    
    protected function checkCredentials() {
        if ($this->user->can('backup') || $this->user->can('assets_backup')) {
            return;
        } else {            
            throw new dmException('You do not have permissions to manage assets backups.');
        }
    }
    
    protected function getAdapter()
    {
        if ($this->adapter) {
            return $this->adapter;
        }
        $adapters = sfConfig::get('dm_dmAssetsBackupPlugin_adapters');
        if (!isset($adapters[$this->adapterClass])) {
            throw new dmException(sprintf('Adapter "%s" is not supported. Available adapters are: %s', $this->adapterClass, implode(', ', array_keys($adapters))));
        }
        try {
            $this->adapter = new $this->adapterClass($this->filesystem, $adapters[$this->adapterClass]['use']);
            return $this->adapter;
        } catch (Exception $e) {
            throw new dmException(sprintf('FATAL ERROR: adapter "%s" could not be initialized.', $this->adapterClass));
        }        
    }
    
    protected function rotate() {
        $config = sfConfig::get('dm_dmAssetsBackupPlugin_rotations');

        if ($config['max_backup_size'] ||  $config['max_backup_files']) {
            $backups = $this->fetchBackups();

            if (count($backups) == 0) {
                return;
            }

            $sorted = array();

            for ($i = 0; $i < count($backups); $i++) {
                $sorted[$backups[$i]] = filemtime($this->getFullPath($backups[$i]));
            }

            arsort($sorted);

            $delete = array();
            $sizeCount = 0;
            $counter = 0;

            foreach ($sorted as $file => $mktime) {
                $counter++;
                $sizeCount += filesize($file);

                if ($counter == 1) continue; // At least one backup file is required to be present on system...

                if (!is_null($config['max_backup_size']) && $sizeCount > $config['max_backup_size']) {
                    $delete[] = $file;
                    continue;
                }

                if (!is_null($config['max_backup_files']) && $counter > $config['max_backup_files']) {
                    $delete[] = $file;
                    continue;
                }
            }

            foreach ($delete as $file) {
                @unlink($file);
            }
        }
    }
}