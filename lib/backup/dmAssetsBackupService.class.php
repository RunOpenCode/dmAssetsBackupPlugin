<?php

class dmAssetsBackupService extends dmConfigurable
{

    protected
        $user,
        $filesystem,
        $adapterClass,
        $adapter;

    public function __construct($user, dmFilesystem $filesystem, $adapter, array $options)
    {
        $this->user = $user;
        $this->filesystem = $filesystem;
        $this->adapterClass = $adapter;

        $this->initialize($options);
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
    
    protected function initialize(array $options)
    {
        $this->configure($options);
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
        $backups = $this->fetchBackups();        
        if (count($backups) == 0) {
            return;
        }
        
        $sorted = array();
        
        for ($i = 0; $i < count($backups); $i++) {
             $sorted[$backups[$i]] = filemtime($this->getFullPath($backups[$i]));
        }
        
        arsort($sorted);        
        
        $counter = 0;
        $delete = array();
        $sizeCount = 0;
        $config = sfConfig::get('dm_dmAssetsBackupPlugin_rotations');
        $maxSize = $config['max_backup_size'];
        $units = substr($config['max_backup_size'], -2);
        if (substr($units, -1) == 'B') {
            $units = substr($units, 0, 1);
            $maxSize = (int) substr($maxSize, 0, strlen($maxSize)-2);
        } else {
            $units = substr($units, -1);
            $maxSize = (int) substr($maxSize, 0, strlen($maxSize)-1);
        }
        
        foreach ($sorted as $file => $mktime) {
            $counter++;
            $sizeCount += filesize($file);  
            switch ($units) {
                case 'M': // MegaBytes
                    if (round(($sizeCount / 1048576), 0) > $config['max_backup_size']) {
                        $delete[] = $file;
                        continue;
                    }
                    break;
                case 'G': // GigaBytes
                    if (round(($sizeCount / 1073741824), 0) > $config['max_backup_size']) {
                        $delete[] = $file;
                        continue;
                    }
                    break;
            }
            if ($counter > $config['max_backup_files']) {
                $delete[] = $file;
                continue;
            }
        }   
        foreach ($delete as $file) {
            $this->deleteBackup($file);
        }
    }
}