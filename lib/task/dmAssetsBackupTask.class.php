<?php

class dmAssetsBackupTask extends dmContextTask
{

    /**
     * @see sfTask
     */
    protected function configure()
    {
        parent::configure();

        $this->addOptions(array(
        ));

        $this->namespace = 'dm';
        $this->name = 'assets-backup';
        $this->briefDescription = 'Creates a backup of project assets';

        $this->detailedDescription = $this->briefDescription;
    }

    /**
     * @see sfTask
     */
    protected function execute($arguments = array(), $options = array())
    {        
        $user = $this->get('user');
        $user->addCredential('assets_backup');
        $user->setAuthenticated(true);
        
        $backupService = $this->get('assets_backup');        
        $this->logSection(
            'assets-backup', 
            sprintf(
                'About to backup directory: "%s" into "%s"...', 
                $backupService->getSourceDirectory(), 
                $backupService->getBackupDirectory()
            )
        );
        try {
            $this->logSection(
                'assets-backup', 
                sprintf('Backup successfuly created on path: "%s"', $backupService->createBackup())
            );            
        } catch (Exception $e) {
            $this->logBlock(
                sprintf('ERROR: Could not create assets backup.'),
                'ERROR'
            );
        }
    }
}