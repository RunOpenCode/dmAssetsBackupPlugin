<?php

class dmAssetsBackupAdapterTar extends dmAssetsBackupAdapter
{

    public function execute()
    {
        $destination = $this->generateDestination();
        
        if (chdir($this->getSourceDir())) {
            $command = sprintf('tar -cf %s *', $destination);
        } else {
            $command = sprintf('tar -cf %s %s', $destination, $this->getSourceDir());
        }
        
        $success = $this->filesystem->execute($command);
        return ($success) ? $destination : false;
    }

}
