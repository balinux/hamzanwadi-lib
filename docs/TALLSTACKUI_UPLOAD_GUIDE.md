# TALLstackUI Upload File Implementation Guide

## Overview
Ini adalah implementasi form upload file menggunakan TALLstackUI yang terintegrasi dengan Livewire. Form ini mendukung berbagai jenis file dengan validasi dan preview.

## Fitur

### 1. **Single File Upload**
- Mendukung gambar (JPEG, PNG, JPG, GIF) dan PDF
- Maksimum 10MB
- Preview otomatis untuk gambar
- Icon khusus untuk file PDF

### 2. **Multiple Files Upload**
- Mendukung gambar untuk galeri
- Maksimum 10 file sekaligus
- Grid preview untuk semua file
- Hapus file individual dengan tombol

### 3. **Document Upload**
- Mendukung PDF dan Office files (DOC, DOCX, XLS, XLSX)
- Maksimum 20MB
- Menampilkan ukuran file

## Komponen yang Dibuat

### Files:
1. `app/Livewire/Dashboard.php` - Komponen Livewire
2. `resources/views/dashboard.blade.php` - View utama
3. `resources/views/livewire/dashboard.blade.php` - View komponen
4. `resources/views/livewire/partials/upload-demo.blade.php` - Component partial

## Penggunaan

### Basic Usage:
```blade
<livewire:dashboard />
```

### Custom Validasi Rules:
```php
// Di Dashboard.php
#[Validate(['required', 'file', 'mimes:png,jpg', 'max:2048'])]
public $single_file;
```

### Custom Upload Configuration:
```php
// Mengubah storage disk
$this->single_file->storeAs('uploads', $filename, 's3');
```

## Props untuk x-ts-upload

| Prop | Tipe | Deskripsi |
|------|------|-----------|
| `label` | string | Label untuk field upload |
| `hint` | string | Hint/help text di bawah label |
| `tip` | string | Tip text di dalam area upload |
| `name` | string | Name attribute untuk input |
| `wire:model` | string | Menghubungkan dengan Livewire property |
| `accept` | string | Format file yang diterima (contoh: `image/*,.pdf`) |
| `multiple` | boolean | Mengaktifkan multiple file selection |
| `id` | string | ID unik untuk elemen |

## Contoh Implementasi

### 1. Basic Upload:
```blade
<x-ts-upload 
    label="Upload File" 
    hint="Pilih file untuk diupload"
    wire:model="file"
    accept="image/*"
/>
```

### 2. Multiple Upload:
```blade
<x-ts-upload 
    label="Upload Gambar" 
    wire:model.live="files"
    accept="image/*"
    id="multi-upload"
/>
```

### 3. Document Upload:
```blade
<x-ts-upload 
    label="Dokumen" 
    hint="Upload PDF dan Office files"
    wire:model="document"
    accept=".pdf,.doc,.docx"
/>
```

## Validasi Laravel yang Digunakan

### Single File:
```php
'required'           // Wajib diisi (gunakan 'nullable' untuk opsional)
'file'              // Harus berupa file
'mimes:jpeg,png,pdf' // Format yang diizinkan
'max:10240'         // Maksimum 10MB (dalam KB)
```

### Multiple Files:
```php
'array'             // Harus berupa array
'max:10'            // Maksimum 10 file
```

## Storage Paths

File akan disimpan di:
- Single files: `storage/app/public/uploads/single/`
- Gallery files: `storage/app/public/uploads/gallery/`
- Documents: `storage/app/public/uploads/documents/`

Pastikan sudah menjalankan:
```bash
php artisan storage:link
```

## Styling dengan TALLstackUI

Form ini menggunakan:
- `x-ts-card` untuk container
- `x-ts-button` untuk tombol submit
- `x-ts-alert` untuk pesan error/success
- `x-ts-upload` untuk komponen upload
- Tailwind CSS untuk layout dan styling

## Dark Mode Support
Form otomatis mendukung dark mode dengan class:
- `dark:bg-` untuk backgrounds
- `dark:text-` untuk text
- `dark:border-` untuk borders

## Error Handling

### Upload Errors:
```php
catch (\Exception $e) {
    session()->flash('error', 'Upload gagal: ' . $e->getMessage());
}
```

### Validation Errors:
```blade
@error('file')
    <p class="text-red-600">{{ $message }}</p>
@enderror
```

## Tips & Best Practices

1. **Gunakan `wire:model.live` untuk preview real-time**
   ```blade
   wire:model.live="files"
   ```

2. **Reset form setelah upload**
   ```php
   $this->reset(['files']);
   ```

3. **Tampilkan ukuran file**
   ```php
   Number::format($file->getSize() / 1024, 2) . ' KB'
   ```

4. **Validasi di server dan client**
   - Server: Livewire validation
   - Client: `accept` attribute, file size check

## Troubleshooting

### File tidak terupload?
- Cek konfigurasi `config/filesystems.php`
- Pastikan direktory storage sudah writable
- Run `php artisan storage:link`

### Validasi gagal?
- Pastikan format file sesuai dengan `mimes` rule
- Cek ukuran file tidak melebihi `max` limit

### Preview tidak muncul?
- Pastikan menggunakan `temporaryUrl()`
- Verify Livewire WithFileUploads trait di-import

## License
Code ini dibuat untuk penggunaan bebas dalam proyek Laravel dengan TALLstackUI.

## Support
Untuk dokumentasi TALLstackUI lebih lanjut:
- https://tallstackui.com/docs
- https://livewire.laravel.com