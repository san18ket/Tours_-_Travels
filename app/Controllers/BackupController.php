<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\BackupService;
use Throwable;

class BackupController extends Controller
{
    private BackupService $backupService;

    public function __construct()
    {
        $this->backupService = new BackupService();
    }

    public function index(Request $request): Response
    {
        $backups = $this->backupService->listBackups();

        return $this->render('office/backups/index', [
            'backups' => $backups,
            'title'   => 'Encrypted Database Backups & Recovery',
        ], 'layouts/office');
    }

    public function create(Request $request): Response
    {
        try {
            $backup = $this->backupService->createBackup();
            Session::setFlash('success', "Database backup created: {$backup['filename']} (SHA256: " . substr($backup['checksum'], 0, 12) . "...)");
        } catch (Throwable $e) {
            Session::setFlash('danger', "Backup failed: " . $e->getMessage());
        }

        return $this->redirect(url('/office/backups'));
    }

    public function restore(Request $request): Response
    {
        $filename = $request->input('filename');
        if (empty($filename) || !str_starts_with($filename, 'backup_voyagego_') || !str_ends_with($filename, '.sql')) {
            Session::setFlash('danger', 'Invalid backup filename specified.');
            return $this->redirect(url('/office/backups'));
        }

        $backupDir = \App\Core\Config::get('paths.backups');
        $filepath = $backupDir . '/' . basename($filename);

        try {
            $result = $this->backupService->restoreBackup($filepath);
            Session::setFlash('success', "Database restored safely from backup: {$result['file']}");
        } catch (Throwable $e) {
            Session::setFlash('danger', "Restoration aborted: " . $e->getMessage());
        }

        return $this->redirect(url('/office/backups'));
    }
}
