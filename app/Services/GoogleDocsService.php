<?php
namespace App\Services;

use Google_Client;
use Google_Service_Docs;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Illuminate\Support\Facades\Log;
use Exception;

class GoogleDocsService
{
    protected $client;
    protected $docsService;

    public function __construct()
    {
        try {
            Log::info("Inisialisasi GoogleDocsService...");
            $this->client = new Google_Client();
            $this->client->setAuthConfig(storage_path('app/google/credentials.json'));
            $this->client->addScope([
                Google_Service_Docs::DOCUMENTS,
                Google_Service_Drive::DRIVE
            ]);
            $this->docsService = new Google_Service_Docs($this->client);
            Log::info("GoogleDocsService berhasil diinisialisasi.");
        } catch (Exception $e) {
            Log::error("Gagal menginisialisasi GoogleDocsService: " . $e->getMessage());
            throw new Exception("Terjadi kesalahan saat inisialisasi Google Docs Service.");
        }
    }

    public function convertDocxToPdf($docxPath)
    {
        try {
            Log::info("Memulai konversi DOCX ke PDF untuk file: " . $docxPath);

            // 1. Upload File ke Google Docs (Sementara)
            $driveService = new Google_Service_Drive($this->client);
            $fileMetadata = new Google_Service_Drive_DriveFile([
                'name' => 'Temp Document',
                'mimeType' => 'application/vnd.google-apps.document'
            ]);

            $content = file_get_contents($docxPath);
            $file = $driveService->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'uploadType' => 'multipart'
            ]);

            $docId = $file->id;
            Log::info("File berhasil diunggah ke Google Docs dengan ID: " . $docId);

            // 2. Konversi ke PDF
            $response = $driveService->files->export($docId, 'application/pdf', ['alt' => 'media']);
            $pdfPath = storage_path('app/temp/' . uniqid() . '.pdf');
            file_put_contents($pdfPath, $response->getBody());

            Log::info("Konversi selesai. File PDF disimpan di: " . $pdfPath);

            // 3. Hapus file sementara dari Google Drive
            $driveService->files->delete($docId);
            Log::info("File sementara di Google Drive telah dihapus.");

            return $pdfPath;
        } catch (Exception $e) {
            Log::error("Kesalahan saat konversi DOCX ke PDF: " . $e->getMessage());
            throw new Exception("Terjadi kesalahan saat mengonversi file ke PDF.");
        }
    }
}
