<?php

class dmAssetsBackupAdapterZip extends dmAssetsBackupAdapter
{

    public function execute()
    {
        $destination = $this->generateDestination();
        
        if (chdir($this->getSourceDir())) {
            $command = sprintf('zip -r %s *', $destination);
        } else {
            $command = sprintf('zip -r %s %s', $destination, $this->getSourceDir());
        }
        
        $success = $this->filesystem->execute($command);
        return ($success) ? $destination : false;
    }

}
