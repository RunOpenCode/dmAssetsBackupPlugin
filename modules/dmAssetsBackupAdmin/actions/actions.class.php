<?php

class dmAssetsBackupAdminActions extends dmAdminBaseActions {

    protected $assetsBackupService;

    public function __construct($context, $moduleName, $actionName) {
        parent::__construct($context, $moduleName, $actionName);
        $this->assetsBackupService = $this->getService('assets_backup');
    }
    
    public function executeIndex(sfWebRequest $request)
    {
        $this->backups = $this->assetsBackupService->fetchBackups();
    }

    public function executeDownload(dmWebRequest $request)
    {
        if (
            $request->hasParameter('key') && 
            $this->assetsBackupService->isBackupExists($request->getParameter('key'))
            ) {
            return $this->download($this->assetsBackupService->getFullPath($request->getParameter('key')));
        } else {
            $this->forward404($this->getI18n()->__('The %file% is not valid assets backup file for download.', array('%file%'=>$request->getParameter('key'))));
        }
    }   

    public function executeDelete(dmWebRequest $request)
    {
        if (
            $request->hasParameter('key') && 
            $this->assetsBackupService->isBackupExists($request->getParameter('key'))
            ) {
            if ($this->assetsBackupService->deleteBackup($request->getParameter('key'))) {
                $this->getUser()->setFlash('notice', $this->getI18n()->__('The assets backup file "%file%" has been successfully deleted.', array('%file%' =>  $request->getParameter('key'))));
            } else {
                $this->getUser()->setFlash('error', $this->getI18n()->__('Something went wrong, assets backup file "%file%" is not de;eted.', array('%file%' =>  $request->getParameter('key'))));
            }
            $this->redirect('dmAssetsBackupAdmin/index');
        } else {
            $this->forward404($this->getI18n()->__('"%file%" is not valid assets backup file.', array('%file%'=>$request->getParameter('key'))));
        }
    }
    
    public function executeBackup(dmWebRequest $request) {
        if ($request->hasParameter('key') && $request->getParameter('key') == 'Backup') {
            try {
                $path = $this->assetsBackupService->createBackup();
                $this->getUser()->setFlash('notice', $this->getI18n()->__('New assets backup file is created.'));
                $this->redirect('dmAssetsBackupAdmin/index');
            } catch (dmException $e) {
                $this->getUser()->setFlash('error', $this->getI18n()->__('The assets backup could not be created.'));
                $this->redirect('dmAssetsBackupAdmin/index');
            }
        } else {
            $this->forward404($this->getI18n()->__('You can not call this action directly.'));
        }
    }
    
    public function executeDeleteAll(dmWebRequest $request)
    {
        if ($request->hasParameter('key') && $request->getParameter('key') == 'DeleteAll') {
            if ($this->assetsBackupService->deleteAll()) {
                $this->getUser()->setFlash('notice', $this->getI18n()->__('All assets backup files are successfully deleted.'));
            } else {
                $this->getUser()->setFlash('error', $this->getI18n()->__('Something went wrong, not all assets backup files are successfully deleted.'));
            }
            $this->redirect('dmAssetsBackupAdmin/index');
        } else {
            $this->forward404($this->getI18n()->__('You can not access this action directly.'));
        }       
    }
    
    public function executeBatchDelete(dmWebRequest $request)
    {
        if ($request->hasParameter('_batch_delete')) {
            $files = $request->getParameter('file');
            $totalFiles = 0;
            $successfullFiles = array();
            $errorFiles = array();
            foreach ($files as $file => $val) {
                $totalFiles++;
                if ($this->assetsBackupService->isBackupExists($file) && $this->assetsBackupService->deleteBackup($file)) {
                    $successfullFiles[] = $file;
                } else {
                    $errorFiles[] = $file;
                }
            }
            if (count($errorFiles)) {
                $this->getUser()->setFlash('error', $this->getI18n()->__('Not all selected assets backup files are successfully deleted. Successfull files are "%successfull_files%", error occured with these files "%error_files%".', array('%successfull_files%' => implode('", "', $successfullFiles), '%error_files%' => implode('", "', $errorFiles))));
            } else {                
                $this->getUser()->setFlash('notice', $this->getI18n()->__('Selected assets backup files "%files%" are successfully deleted.', array('%files%' => implode('", "', $successfullFiles))));
            }            
            $this->redirect('dmAssetsBackupAdmin/index');
        } else {
            $this->forward404($this->getI18n()->__('You can not access this action directly.'));
        }
    }
    
}