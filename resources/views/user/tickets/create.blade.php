<x-layout.app title="Create Ticket" pageTitle="Create New Ticket">

    <div class="page-header">
        <div style="display:flex; align-items:center; gap:12px;">
            <a href="{{ route('user.tickets.index') }}" class="btn btn-secondary btn-sm">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back
            </a>
            <div>
                <h1 class="page-title">Create New Ticket</h1>
                <p class="page-subtitle">Report your IT issues here</p>
            </div>
        </div>
    </div>

    <div style="max-width: 720px;">
        <div class="card">
            <div class="card-header">
                <span class="card-title">Report Issue Form</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('user.tickets.store') }}"
                      enctype="multipart/form-data">
                    @csrf

                    {{-- Title --}}
                    <div class="form-group">
                        <label class="form-label required">Issue Title</label>
                        <input type="text" name="title" class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}"
                               placeholder="Example: Computer won't turn on"
                               value="{{ old('title') }}">
                        @error('title')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div class="form-group">
                        <label class="form-label required">Issue Description</label>
                        <textarea name="description" rows="5"
                                  class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}"
                                  placeholder="Explain your issue in detail. The system will automatically assign a category and priority based on your description.">{{ old('description') }}</textarea>
                        @error('description')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                        <span class="form-hint">
                            💡 The more detailed your description, the more accurate the system will be in determining the handling priority.
                        </span>
                    </div>

                    {{-- Attachment --}}
                    <div class="form-group">
                        <label class="form-label">Attachment (Optional)</label>
                        <input type="file" name="attachment"
                               class="form-control {{ $errors->has('attachment') ? 'is-invalid' : '' }}"
                               accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                        @error('attachment')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                        <span class="form-hint">Format: JPG, PNG, PDF, DOC. Maximum 2MB.</span>
                    </div>

                    {{-- Info Box --}}
                    <div class="alert alert-info" style="margin-top: 8px;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Category and priority will be automatically assigned by the system based on the description you provide.</span>
                    </div>

                    {{-- Submit --}}
                    <div style="display:flex; justify-content:flex-end; gap:10px; margin-top: 8px;">
                        <a href="{{ route('user.tickets.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Create Ticket
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-layout.app>
