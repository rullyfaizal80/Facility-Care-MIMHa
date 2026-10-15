<?php
namespace App\Libraries;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;

class GoogleDriveService
{
    private $client;
    private $service;
    
    // Ganti dengan ID terbaru Anda: '1cFds4YLreag8idXFGWFAk2rvQilSak7a'
    private $rootFolderId = '1cFds4YLreag8idXFGWFAk2rvQilSak7a'; 

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setAuthConfig(WRITEPATH . 'credentials.json'); 
        $this->client->addScope(Drive::DRIVE);
        $this->service = new Drive($this->client);
    }

    public function uploadImage($filePath, $fileName, $mimeType)
    {
        $tahunIni = date('Y'); 

        // 1. Cari atau Buat Folder Tahun
        $tahunFolderId = $this->getOrCreateFolder($tahunIni, $this->rootFolderId);

        // 2. Siapkan Metadata
        $fileMetadata = new DriveFile([
            'name'    => $fileName,
            'parents' => [$tahunFolderId] 
        ]);

        $content = file_get_contents($filePath);
        
        // TAMBAHAN: supportsAllDrives = true (Wajib untuk Google Workspace / Edu)
        $file = $this->service->files->create($fileMetadata, [
            'data'              => $content,
            'mimeType'          => $mimeType,
            'uploadType'        => 'multipart',
            'fields'            => 'id',
            'supportsAllDrives' => true 
        ]);

        // 4. Permission Public
        $permission = new Permission([
            'type' => 'anyone',
            'role' => 'reader'
        ]);
        
        // TAMBAHAN: supportsAllDrives = true
        $this->service->permissions->create($file->id, $permission, [
            'supportsAllDrives' => true
        ]);

        return $file->id;
    }

    private function getOrCreateFolder($folderName, $parentId)
    {
        $query = "mimeType='application/vnd.google-apps.folder' and name='$folderName' and trashed=false and '$parentId' in parents";

        // TAMBAHAN: Parameter untuk membaca Shared Drives (Drive Bersama)
        $results = $this->service->files->listFiles([
            'q'                         => $query,
            'spaces'                    => 'drive',
            'fields'                    => 'files(id, name)',
            'includeItemsFromAllDrives' => true,
            'supportsAllDrives'         => true
        ]);

        if (count($results->getFiles()) > 0) {
            return $results->getFiles()[0]->getId();
        }

        $folderMetadata = new DriveFile([
            'name'     => $folderName,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents'  => [$parentId]
        ]);
        
        // TAMBAHAN: supportsAllDrives = true saat membuat folder baru
        $folder = $this->service->files->create($folderMetadata, [
            'fields' => 'id',
            'supportsAllDrives' => true
        ]);
        
        return $folder->id;
    }

    public function getMedia($fileId)
    {
        // Parameter 'alt' => 'media' akan memerintahkan Google mengirimkan data asli gambarnya (raw bytes)
        $response = $this->service->files->get($fileId, [
            'alt'               => 'media',
            'supportsAllDrives' => true // Sangat penting agar bisa membaca file di Drive Bersama!
        ]);
        
        return $response->getBody()->getContents();
    }
}