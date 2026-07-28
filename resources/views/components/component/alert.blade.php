@session('alert')
    <div class="notice-info mb-6" role="status">
        <svg viewBox="0 0 24 24" class="mt-0.5 size-4 shrink-0 fill-current" aria-hidden="true">
            <path d="M9.5 3h5v6.5H21v5h-6.5V21h-5v-6.5H3v-5h6.5V3Z" />
        </svg>
        <span>{{ session('alert') }}</span>
    </div>
@endsession
