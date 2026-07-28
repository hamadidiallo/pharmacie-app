@session('alert')
    <div class="notice-info mb-4" role="status">
        <svg class="mt-px size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="9" />
            <path d="m8.5 12 2.5 2.5 4.5-5" />
        </svg>
        <span>{{ session('alert') }}</span>
    </div>
@endsession
