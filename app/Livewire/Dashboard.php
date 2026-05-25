<?php

namespace App\Livewire;

use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class Dashboard extends Component
{
    use WithFileUploads;

    // Single file upload - hanya gambar atau PDF
    #[Validate(['nullable', 'file', 'mimes:jpeg,png,jpg,gif,pdf', 'max:10240'])]
    public $single_file = null;

    // Multiple files upload - gambar saja
    #[Validate(['array', 'max:10'])] // Max 10 files
    public $multiple_files = [];

    // Document upload - PDF dan Office files
    #[Validate(['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:20480'])]
    public $document = null;

    /**
     * Handle the form submission and upload files
     */
    public function upload()
    {
        $this->validate();

        try {
            $uploadedFiles = [];

            // Upload single file (gambar/PDF)
            if ($this->single_file) {
                $filename = time() . '_' . $this->single_file->getClientOriginalName();
                $singlePath = $this->single_file->storeAs('uploads/single', $filename, 'public');
                $uploadedFiles[] = [
                    'type' => 'single',
                    'name' => $this->single_file->getClientOriginalName(),
                    'path' => $singlePath,
                    'size' => $this->formatFileSize($this->single_file->getSize())
                ];
                session()->flash('success_single', '✅ File berhasil diupload: ' . $this->single_file->getClientOriginalName());
            }

            // Upload multiple files (galeri)
            if (!empty($this->multiple_files)) {
                foreach ($this->multiple_files as $file) {
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('uploads/gallery', $filename, 'public');
                }
                $uploadedFiles[] = [
                    'type' => 'multiple',
                    'count' => count($this->multiple_files),
                    'paths' => $this->multiple_files
                ];
                session()->flash('success_multiple', '✅ ' . count($this->multiple_files) . ' file galeri berhasil diupload');
            }

            // Document upload
            if ($this->document) {
                $filename = time() . '_' . $this->document->getClientOriginalName();
                $docPath = $this->document->storeAs('uploads/documents', $filename, 'public');
                $uploadedFiles[] = [
                    'type' => 'document',
                    'name' => $this->document->getClientOriginalName(),
                    'path' => $docPath,
                    'size' => $this->formatFileSize($this->document->getSize())
                ];
                session()->flash('success_document', '✅ Dokumen berhasil diupload: ' . $this->document->getClientOriginalName());
            }

            // Log uploaded files info
            if (!empty($uploadedFiles)) {
                logger('Files uploaded successfully', $uploadedFiles);
            }

            // Reset form after successful upload
            $this->reset([
                'single_file',
                'multiple_files',
                'document'
            ]);

            // Dispatch event for client-side handling
            $this->dispatch('upload-complete');

        } catch (\Exception $e) {
            logger()->error('Upload failed: ' . $e->getMessage());
            session()->flash('error', '❌ Terjadi kesalahan saat upload file: ' . $e->getMessage());
        }
    }

    /**
     * Remove multiple file from array by index
     */
    public function removeMultipleFile($index)
    {
        if (isset($this->multiple_files[$index])) {
            unset($this->multiple_files[$index]);
            $this->multiple_files = array_values($this->multiple_files);
            
            // Recalculate validation
            if (count($this->multiple_files) === 0) {
                $this->multiple_files = [];
            }
        }
    }

    /**
     * Format file size for display
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }

    /**
     * Get uploaded files count
     */
    public function getUploadedCountProperty()
    {
        $count = 0;
        if ($this->single_file) $count++;
        if (!empty($this->multiple_files)) $count += count($this->multiple_files);
        if ($this->document) $count++;
        return $count;
    }

    /**
     * Check if any file is selected
     */
    public function getHasFilesProperty()
    {
        return $this->single_file || !empty($this->multiple_files) || $this->document;
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}