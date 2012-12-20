<?php

abstract class dmAssetsBackupAdapter
{

    protected
        $filesystem,
        $extension;

    public function __construct(dmFilesystem $filesystem, $extension)
    {
        $this->filesystem = $filesystem;
        $this->extension = $extension;
    }

    abstract public function execute();

    protected function getBackupDir()
    {
        $conf = sfConfig::get('dm_dmAssetsBackupPlugin_locations');
        return $conf['backup_dir'];
    }
    
    protected function getSourceDir()
    {
        $conf = sfConfig::get('dm_dmAssetsBackupPlugin_locations');
        return $conf['source_dir'];
    }

    protected function generateDestination()
    {
        $conf = sfConfig::get('dm_dmAssetsBackupPlugin_locations');
        if (!file_exists($this->getBackupDir())) {
            $this->filesystem->mkdir($this->getBackupDir());
        }
        return dmOs::join($conf['backup_dir'], $this->getFileName());
    }

    protected function getFileName()
    {
        return date('Y-m-d-H-i-s-u') . '.' . $this->extension;
    }
}