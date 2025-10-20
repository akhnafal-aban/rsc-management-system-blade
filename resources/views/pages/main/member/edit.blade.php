@extends('layouts.app')
@section('title', 'Edit Member - ' . $member->name)
@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-foreground">Edit Member</h1>
                <p class="text-muted-foreground mt-1">{{ $member->name }} - {{ $member->member_code }}</p>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('member.show', $member) }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-border bg-background text-foreground rounded-lg hover:bg-muted/50 transition-colors">
                    <x-ui.icon name="chevron-left" class="w-4 h-4 mr-2" />
                    <span>Kembali</span>
                </a>
            </div>
        </div>



        <form action="{{ route('member.update', $member) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-card p-5 border border-border rounded-lg shadow-sm space-y-6">
                <!-- Grid Utama -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nama Lengkap -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-card-foreground mb-2">
                            Nama Lengkap <span class="text-destructive">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name', $member->name) }}"
                            required
                            class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground 
                            placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                            placeholder="Masukkan nama lengkap">
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-card-foreground mb-2">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $member->email) }}"
                            class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground 
                            placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                            placeholder="contoh@email.com">
                    </div>
                </div>

                <!-- Nomor Telepon -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-card-foreground mb-2">Nomor Telepon</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone', $member->phone) }}"
                        class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground 
                        placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                        placeholder="08123456789">
                </div>

                <!-- Tanggal Expired -->
                <div>
                    <label for="exp_date" class="block text-sm font-medium text-card-foreground mb-2">
                        Tanggal Expired <span class="text-destructive">*</span>
                    </label>
                    <input type="date" id="exp_date" name="exp_date" value="{{ old('exp_date', $member->exp_date?->format('Y-m-d')) }}"
                        required
                        class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground 
                        placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                        onchange="showExpDateWarning(this.value, '{{ $member->exp_date?->format('Y-m-d') }}')">
                    <p class="text-sm text-muted-foreground mt-1">
                        <x-ui.icon name="alert-triangle" class="w-4 h-4 inline mr-1" />
                        Perubahan tanggal expired akan mempengaruhi status keanggotaan member
                    </p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-2 sm:space-x-4 mt-4">
                <a href="{{ route('member.show', $member) }}"
                    class="mb-2 w-full sm:w-auto inline-flex items-center justify-center px-6 py-2 border border-border bg-background text-foreground rounded-lg hover:bg-muted/50 transition-colors">
                    Batal
                </a>
                <button type="submit"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2 rounded-lg bubblegum-button-primary text-chart-2-foreground transition-colors">
                    <x-ui.icon name="edit" class="w-4 h-4 mr-2" />
                    Update Member
                </button>
            </div>

        </form>
    </div>

    <!-- Modal Warning -->
    <div id="expDateWarningModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden p-4">
        <div class="bg-card border border-border rounded-lg w-full max-w-sm sm:max-w-md mx-auto max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="flex items-start gap-3 p-4 pb-3 border-b border-border">
                <x-ui.icon name="alert-triangle" class="w-5 h-5 text-yellow-500 mt-0.5 flex-shrink-0" />
                <h3 class="text-base sm:text-lg font-semibold text-card-foreground leading-tight">
                    Peringatan Perubahan Tanggal Expired
                </h3>
            </div>
            
            <!-- Content -->
            <div class="p-4 space-y-3">
                <div>
                    <p class="text-sm text-card-foreground mb-2">Mengubah tanggal expired dari:</p>
                    <div class="bg-muted p-2 rounded-md">
                        <p class="text-sm font-medium" id="oldExpDate"></p>
                    </div>
                </div>
                
                <div>
                    <p class="text-sm text-card-foreground mb-2">Menjadi:</p>
                    <div class="bg-muted p-2 rounded-md">
                        <p class="text-sm font-medium" id="newExpDate"></p>
                    </div>
                </div>
                
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-md p-3">
                    <p class="text-xs sm:text-sm text-yellow-800 dark:text-yellow-200 leading-relaxed">
                        <strong>Perhatian:</strong> Perubahan ini akan mempengaruhi status keanggotaan member.
                    </p>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-2 p-4 pt-3 border-t border-border">
                <button type="button" onclick="hideExpDateWarning()"
                    class="w-full sm:w-auto px-4 py-2.5 border border-border bg-background text-foreground rounded-lg hover:bg-muted/50 transition-colors text-sm font-medium">
                    Batal
                </button>
                <button type="button" onclick="confirmExpDateChange()"
                    class="w-full sm:w-auto px-4 py-2.5 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors text-sm font-medium">
                    Ya, Ubah Tanggal
                </button>
            </div>
        </div>
    </div>

    <script>
        let pendingExpDate = null;
        let originalExpDate = null;

        function showExpDateWarning(newDate, originalDate) {
            if (newDate === originalDate) {
                return; // No change, no warning needed
            }

            pendingExpDate = newDate;
            originalExpDate = originalDate;

            // Format dates for display
            const originalFormatted = originalDate ? new Date(originalDate).toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            }) : 'Belum diatur';
            
            const newFormatted = new Date(newDate).toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            });

            document.getElementById('oldExpDate').textContent = originalFormatted;
            document.getElementById('newExpDate').textContent = newFormatted;
            
            const modal = document.getElementById('expDateWarningModal');
            modal.classList.remove('hidden');
            
            // Prevent body scroll on mobile
            document.body.style.overflow = 'hidden';
            
            // Focus on first button for accessibility
            setTimeout(() => {
                modal.querySelector('button').focus();
            }, 100);
        }

        function hideExpDateWarning() {
            const modal = document.getElementById('expDateWarningModal');
            modal.classList.add('hidden');
            
            // Restore body scroll
            document.body.style.overflow = '';
            
            // Reset the date input to original value
            document.getElementById('exp_date').value = originalExpDate || '';
            pendingExpDate = null;
        }

        function confirmExpDateChange() {
            const modal = document.getElementById('expDateWarningModal');
            modal.classList.add('hidden');
            
            // Restore body scroll
            document.body.style.overflow = '';
            
            // The form will submit with the new date value
        }

        // Close modal when clicking outside
        document.getElementById('expDateWarningModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideExpDateWarning();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('expDateWarningModal');
                if (!modal.classList.contains('hidden')) {
                    hideExpDateWarning();
                }
            }
        });
    </script>
@endsection
