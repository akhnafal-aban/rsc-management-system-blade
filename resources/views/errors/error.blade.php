<x-error-page 
    code="{{ $exception->getStatusCode() ?? 'Error' }}"
    title="Terjadi Kesalahan"
    description="Terjadi kesalahan yang tidak terduga. Silakan coba lagi atau hubungi administrator jika masalah berlanjut."
    icon="alert-circle"
/>

