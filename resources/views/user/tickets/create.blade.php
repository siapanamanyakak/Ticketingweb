<x-layout.app title="Buat Tiket" pageTitle="Buat Tiket Baru">

    <div class="page-header">
        <div style="display:flex; align-items:center; gap:12px;">
            <a href="{{ route('user.tickets.index') }}" class="btn btn-secondary btn-sm">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali
            </a>
            <div>
                <h1 class="page-title">Buat Tiket Baru</h1>
                <p class="page-subtitle">Laporkan permasalahan IT kamu di sini</p>
            </div>
        </div>
    </div>

    <div style="max-width: 720px;">
        <div class="card">
            <div class="card-header">
                <span class="card-title">Formulir Laporan Masalah</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('user.tickets.store') }}"
                      enctype="multipart/form-data">
                    @csrf

                    {{-- Judul --}}
                    <div class="form-group">
                        <label class="form-label required">Judul Masalah</label>
                        <input type="text" name="title" class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}"
                               placeholder="Contoh: Komputer tidak bisa menyala"
                               value="{{ old('title') }}">
                        @error('title')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Deskripsi --}}
                    <div class="form-group">
                        <label class="form-label required">Deskripsi Masalah</label>
                        <textarea name="description" rows="5"
                                  class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}"
                                  placeholder="Jelaskan masalah kamu secara detail. Sistem akan otomatis menentukan kategori dan prioritas berdasarkan deskripsi kamu.">{{ old('description') }}</textarea>
                        @error('description')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                        <span class="form-hint">
                            💡 Semakin detail deskripsi kamu, semakin akurat sistem menentukan prioritas penanganan.
                        </span>
                    </div>

                    {{-- Attachment --}}
                    <div class="form-group">
                        <label class="form-label">Lampiran (Opsional)</label>
                        <input type="file" name="attachment"
                               class="form-control {{ $errors->has('attachment') ? 'is-invalid' : '' }}"
                               accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                        @error('attachment')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                        <span class="form-hint">Format: JPG, PNG, PDF, DOC. Maksimal 2MB.</span>
                    </div>

                    {{-- Info Box --}}
                    <div class="alert alert-info" style="margin-top: 8px;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Kategori dan prioritas tiket akan ditentukan otomatis oleh sistem berdasarkan deskripsi yang kamu masukkan.</span>
                    </div>

                    {{-- Submit --}}
                    <div style="display:flex; justify-content:flex-end; gap:10px; margin-top: 8px;">
                        <a href="{{ route('user.tickets.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Kirim Tiket
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-layout.app>
