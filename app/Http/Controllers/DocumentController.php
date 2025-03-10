<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\GoogleDocsService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;

class DocumentController extends Controller
{
    protected $googleDocsService;

    public function __construct(GoogleDocsService $googleDocsService)
    {
        $this->googleDocsService = $googleDocsService;
    }

    public function generateDocument(Request $request)
    {
        try {
            Log::info("Permintaan baru untuk generate PDF dengan data: ", $request->all());

            // Validasi input
            $request->validate([
                'asesi' => 'required|string',
                'asesor' => 'required|string',
            ]);
            App::setLocale('id');
            Carbon::setLocale('id');
            $asesi = $request->asesi;
            $asesor = $request->asesor;
            $tanggal = null; //Carbon::now()->translatedFormat('d F Y');
            Log::info("Memproses template Word untuk Asesi: $asesi, Asesor: $asesor, Tanggal: $tanggal");

            // 1. Load Template Word
            $templatePath = storage_path('app/template/template.docx');
            if (!file_exists($templatePath)) {
                Log::error("Template Word tidak ditemukan: " . $templatePath);
                return response()->json(['error' => 'Template tidak ditemukan'], 404);
            }

            $outputPath = storage_path('app/temp/document_filled_' . uniqid() . '.docx');

            $templateProcessor = new TemplateProcessor($templatePath);
            $templateProcessor->setValue('nama_asesi', !empty($asesi) ? $asesi : " ");
            $templateProcessor->setValue('nama_asesor', !empty($asesor) ? $asesor : " ");
            $templateProcessor->setValue('tanggal', !empty($tanggal) ? $tanggal : " ");
            $templateProcessor->saveAs($outputPath);

            Log::info("Dokumen Word berhasil diproses: " . $outputPath);

            // 2. Konversi ke PDF
            $pdfPath = $this->googleDocsService->convertDocxToPdf($outputPath);
            if (file_exists($outputPath)) {
                unlink($outputPath);
                Log::info("File Word sementara telah dihapus: " . $outputPath);
            }
            Log::info("Konversi selesai, mengembalikan file PDF ke pengguna.");

            // 3. Download PDF ke User
            return response()->download($pdfPath)->deleteFileAfterSend(true);
        } catch (Exception $e) {
            Log::error("Kesalahan dalam generateDocument: " . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan dalam proses pembuatan dokumen'], 500);
        }
    }
}
