@php
/**
 * TALLstackUI Upload Form Demo
 * 
 * Komponen ini menampilkan berbagai variasi form upload file dari TALLstackUI
 * dengan fitur lengkap termasuk:
 * - Single file upload
 * - Multiple files upload
 * - File dengan validasi khusus
 * - Preview gambar
 * - Progress indicator
 */
@endphp

<!-- Single File Upload Example -->
<div class="space-y-2">
    @if($label)
        <x-ts-label :label="$label" />
    @endif
    
    <x-ts-upload
        :label="$label"
        :hint="$hint"
        :tip="$tip"
        :name="$name"
        wire:model="$wireModel"
        :accept="$accept ?? '*'"
        :multiple="$multiple ?? false"
        :id="$id ?? $name"
        {{ $attributes }}
    />
    
    <!-- Preview Section for Images -->
    @if($preview && $this->{$wireModel})
        @if(is_array($this->{$wireModel}))
            <div class="mt-3 grid grid-cols-3 gap-2">
                @foreach($this->{$wireModel} as $file)
                    <div class="relative group">
                        <img src="{{ $file->temporaryUrl() }}" class="w-full h-24 object-cover rounded-lg border border-gray-200">
                        <button 
                            onclick="removeFile('{{$wireModel}}', '{{$file->getClientOriginalName()}}')"
                            class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                        >
                            ×
                        </button>
                    </div>
                @endforeach
            </div>
        @else
            <div class="mt-3 relative inline-block">
                <img src="{{ $this->{$wireModel}->temporaryUrl() }}" class="max-w-xs h-48 object-cover rounded-lg border border-gray-200">
            </div>
        @endif
    @endif
    
    <!-- Error Message -->
    @error($wireModel)
        <x-ts-alert title="Error" :text="$message" color="red" />
    @enderror
</div>