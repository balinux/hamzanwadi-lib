<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <!-- Flash Messages -->
    @if (session()->has('success_single'))
        <x-ts-alert title="✅ Sukses!" :text="session('success_single')" color="green" icon="check-circle" dismissible />
    @endif

    @if (session()->has('success_multiple'))
        <x-ts-alert title="✅ Sukses!" :text="session('success_multiple')" color="green" icon="check-circle" dismissible />
    @endif

    @if (session()->has('success_document'))
        <x-ts-alert title="✅ Sukses!" :text="session('success_document')" color="green" icon="check-circle" dismissible />
    @endif

    @if (session()->has('error'))
        <x-ts-alert title="❌ Error!" :text="session('error')" color="red" icon="exclamation-circle" dismissible />
    @endif

    @if ($errors->any())
        <div class="space-y-2">
            @foreach ($errors->all() as $error)
                <x-ts-alert title="⚠️ Validasi" :text="$error" color="red" icon="exclamation-triangle" dismissible />
            @endforeach
        </div>
    @endif
    
    <!-- Form Upload File -->
    <x-ts-card class="!p-6">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span>📤</span> Form Upload File
            </h2>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Unggah file dengan berbagai opsi menggunakan TALLstackUI</p>
        </div>
        
        <form wire:submit.prevent="upload" class="space-y-6">
            <!-- Single File Upload with Section -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-300 rounded-lg p-2">🖼️</span>
                        Upload Gambar / PDF
                    </h3>
                    <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-1 rounded-full">
                        Max 10MB
                    </span>
                </div>
                
                <x-ts-upload 
                    label="Single File" 
                    hint="Upload satu file gambar atau PDF"
                    tip="Klik atau drag & drop file ke area upload"
                    wire:model="single_file"
                    accept="image/*,.pdf"
                    id="single-upload"
                />
                
                @error('single_file')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                
                @if ($single_file)
                    <div class="mt-3 flex items-center justify-between rounded-lg bg-green-50 dark:bg-green-900/20 p-3 border border-green-200 dark:border-green-800">
                        <div class="flex items-center gap-3">
                            @if(str_starts_with($single_file->getMimeType(), 'image/'))
                                <img src="{{ $single_file->temporaryUrl() }}" alt="preview" class="h-12 w-12 object-cover rounded-lg">
                            @else
                                <div class="h-12 w-12 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            @endif
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white text-sm">{{ $single_file->getClientOriginalName() }}</p>
                            </div>
                        </div>
                        <button type="button" wire:click="$set('single_file', null)" class="text-sm text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-medium flex items-center gap-1">
                            <span>✕</span> Hapus
                        </button>
                    </div>
                @endif
            </div>
            
            <!-- Multiple Files Upload with Section -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-300 rounded-lg p-2">🖼️📸</span>
                        Upload Galeri
                    </h3>
                    <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-1 rounded-full">
                        {{ count($multiple_files) }} file dipilih
                    </span>
                </div>
                
                <x-ts-upload 
                    label="Multiple Images" 
                    hint="Upload beberapa gambar untuk galeri"
                    tip="Anda bisa memilih hingga 10 file sekaligus"
                    wire:model.live="multiple_files"
                    accept="image/*"
                    id="multi-upload"
                />
                
                @if (!empty($multiple_files))
                    <div class="mt-3">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            @foreach($multiple_files as $index => $file)
                                <div class="relative group">
                                    <img src="{{ $file->temporaryUrl() }}" alt="preview" class="w-full h-24 object-cover rounded-lg border-2 border-transparent group-hover:border-indigo-500 transition-all">
                                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                                        <button type="button" wire:click="removeMultipleFile({{ $index }})" class="text-white font-bold text-2xl hover:scale-110 transition-transform">
                                            ✕
                                        </button>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500 truncate dark:text-gray-400">{{ $file->getClientOriginalName() }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            
            <!-- Document Upload with Section -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 rounded-lg p-2">📄</span>
                        Upload Dokumen
                    </h3>
                    <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-1 rounded-full">
                        Max 20MB
                    </span>
                </div>
                
                <x-ts-upload 
                    label="Document File" 
                    hint="Upload dokumen format PDF atau Office"
                    tip="Format yang didukung: PDF, DOC, DOCX, XLS, XLSX"
                    wire:model="document"
                    accept=".pdf,.doc,.docx,.xls,.xlsx"
                    id="document-upload"
                />
                
                @error('document')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                
                @if ($document)
                    <div class="mt-3 flex items-center justify-between rounded-lg bg-blue-50 dark:bg-blue-900/20 p-4 border border-blue-200 dark:border-blue-800">
                        <div class="flex items-center gap-3">
                            <div class="h-12 w-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white text-sm">{{ $document->getClientOriginalName() }}</p>
                            </div>
                        </div>
                        <button type="button" wire:click="$set('document', null)" class="text-sm text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-medium flex items-center gap-1">
                            <span>✕</span> Hapus
                        </button>
                    </div>
                @endif
            </div>
            
            <!-- Submit Button with Loading State -->
            <x-ts-button 
                type="submit" 
                label="Upload Semua File" 
                icon="cloud-arrow-up"
                color="indigo"
                size="lg"
                class="w-full"
            >
                <span class="mr-2">🚀</span> Upload File
            </x-ts-button>
                
            <div class="text-center text-sm text-gray-500 dark:text-gray-400">
                <p>Tekan tombol di atas untuk mengunggah semua file</p>
            </div>
        </form>
    </x-ts-card>

    <!-- Info Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Tips Card -->
        <x-ts-card class="!p-5 hover:shadow-lg transition-shadow">
            <div class="flex items-start gap-3">
                <div class="bg-amber-100 dark:bg-amber-900 text-amber-600 dark:text-amber-400 rounded-lg p-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Tips Upload</h3>
                    <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1 mt-2">
                        <li class="flex items-center gap-1"><span class="text-green-500">✓</span> Ukuran max 10MB untuk gambar</li>
                        <li class="flex items-center gap-1"><span class="text-green-500">✓</span> Ukuran max 20MB untuk dokumen</li>
                        <li class="flex items-center gap-1"><span class="text-green-500">✓</span> Format: JPG, PNG, PDF, DOC, DOCX</li>
                    </ul>
                </div>
            </div>
        </x-ts-card>
        
        <!-- Features Card -->
        <x-ts-card class="!p-5 hover:shadow-lg transition-shadow">
            <div class="flex items-start gap-3">
                <div class="bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400 rounded-lg p-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Fitur Utama</h3>
                    <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1 mt-2">
                        <li class="flex items-center gap-1"><span>🖱️</span> Drag & Drop support</li>
                        <li class="flex items-center gap-1"><span>👁️</span> Real-time preview gambar</li>
                        <li class="flex items-center gap-1"><span>📦</span> Multiple file upload</li>
                    </ul>
                </div>
            </div>
        </x-ts-card>
        
        <!-- Tech Stack Card -->
        <x-ts-card class="!p-5 hover:shadow-lg transition-shadow">
            <div class="flex items-start gap-3">
                <div class="bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-400 rounded-lg p-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Tech Stack</h3>
                    <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1 mt-2">
                        <li class="flex items-center gap-1"><span>⚡</span> TALLstackUI Components</li>
                        <li class="flex items-center gap-1"><span>🔌</span> Livewire Power</li>
                        <li class="flex items-center gap-1"><span>🎨</span> Tailwind CSS</li>
                    </ul>
                </div>
            </div>
        </x-ts-card>
    </div>
</div>