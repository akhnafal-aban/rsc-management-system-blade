@extends('layouts.app')
@section('title', 'Perpanjang Membership')
@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-foreground">Perpanjang Membership</h1>
                <p class="text-muted-foreground mt-1">Pilih member dan durasi perpanjangan membership</p>
            </div>
            <a href="{{ route('member.index') }}" 
                class="inline-flex items-center px-4 py-2 border border-border bg-background text-foreground rounded-lg hover:bg-muted/50 transition-colors">
                <x-ui.icon name="chevron-left" class="w-4 h-4 mr-2" />
                <span>Kembali</span>
            </a>
        </div>

        @if ($errors->any())
            <div class="bg-destructive/10 border border-destructive/20 rounded-lg p-4">
                <div class="flex">
                    <x-ui.icon name="alert-circle" class="w-5 h-5 text-destructive mr-3 mt-0.5" />
                    <div>
                        <h3 class="text-sm font-medium text-destructive">Terdapat kesalahan:</h3>
                        <ul class="mt-2 text-sm text-destructive list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('member.extend.store') }}" method="POST" class="space-y-6" id="extendForm">
            @csrf
            
            <div class="bg-card p-6 rounded-lg shadow-sm border border-border">
                <h2 class="text-lg font-semibold text-card-foreground mb-4">Pilih Member</h2>
                
                <div class="space-y-4">
                    <div>
                        <label for="member_search" class="block text-sm font-medium text-card-foreground mb-2">
                            Cari Member <span class="text-destructive">*</span>
                        </label>
                        <div class="relative">
                            <x-ui.icon name="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                            <input type="text" id="member_search" name="member_search" 
                                class="w-full pl-10 pr-4 py-2 bg-input border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                                placeholder="Ketik nama, ID member, atau email..."
                                autocomplete="off">
                            <input type="hidden" id="member_id" name="member_id" value="{{ old('member_id') }}">
                        </div>
                        
                        <!-- Member Search Results -->
                        <div id="member_results" class="hidden mt-2 bg-background border border-border rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            <div id="member_list" class="divide-y divide-border">
                                <!-- Results will be populated here -->
                            </div>
                        </div>
                        
                        <!-- Selected Member Display -->
                        <div id="selected_member" class="hidden mt-3 p-3 bg-muted/30 border border-border rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-card-foreground" id="selected_member_name"></div>
                                    <div class="text-sm text-muted-foreground" id="selected_member_info"></div>
                                </div>
                                <button type="button" id="clear_selection" class="text-muted-foreground hover:text-destructive">
                                    <x-ui.icon name="x" class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-card p-6 rounded-lg shadow-sm border border-border">
                <h2 class="text-lg font-semibold text-card-foreground mb-4">Detail Perpanjangan</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="membership_duration" class="block text-sm font-medium text-card-foreground mb-2">
                            Durasi Perpanjangan <span class="text-destructive">*</span>
                        </label>
                        <select id="membership_duration" name="membership_duration" required
                            class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent">
                            <option value="">Pilih durasi perpanjangan</option>
                            <option value="1" {{ old('membership_duration') == '1' ? 'selected' : '' }}>1 Bulan - Rp 150.000</option>
                            <option value="3" {{ old('membership_duration') == '3' ? 'selected' : '' }}>3 Bulan - Rp 400.000</option>
                            <option value="6" {{ old('membership_duration') == '6' ? 'selected' : '' }}>6 Bulan - Rp 750.000</option>
                            <option value="12" {{ old('membership_duration') == '12' ? 'selected' : '' }}>12 Bulan - Rp 1.400.000</option>
                        </select>
                    </div>

                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-card-foreground mb-2">
                            Metode Pembayaran <span class="text-destructive">*</span>
                        </label>
                        <select id="payment_method" name="payment_method" required
                            class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent">
                            <option value="">Pilih metode pembayaran</option>
                            <option value="CASH" {{ old('payment_method') == 'CASH' ? 'selected' : '' }}>Tunai (CASH)</option>
                            <option value="TRANSFER" {{ old('payment_method') == 'TRANSFER' ? 'selected' : '' }}>Transfer Bank</option>
                            <option value="EWALLET" {{ old('payment_method') == 'EWALLET' ? 'selected' : '' }}>E-Wallet</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6">
                    <label for="payment_notes" class="block text-sm font-medium text-card-foreground mb-2">Catatan Pembayaran</label>
                    <textarea id="payment_notes" name="payment_notes" rows="3"
                        class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                        placeholder="Catatan tambahan untuk pembayaran (opsional)">{{ old('payment_notes') }}</textarea>
                </div>
            </div>
            
            <div class="flex items-center justify-end space-x-4">
                <a href="{{ route('member.index') }}" 
                    class="px-6 py-2 border border-border bg-background text-foreground rounded-lg hover:bg-muted/50 transition-colors">
                    Batal
                </a>
                <button type="submit" id="submit_btn" disabled
                    class="px-6 py-2 rounded-lg bubblegum-button-primary text-chart-2-foreground transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <x-ui.icon name="calendar-plus" class="w-4 h-4 mr-2 inline" />
                    Perpanjang Membership
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const memberSearch = document.getElementById('member_search');
            const memberResults = document.getElementById('member_results');
            const memberList = document.getElementById('member_list');
            const selectedMember = document.getElementById('selected_member');
            const selectedMemberName = document.getElementById('selected_member_name');
            const selectedMemberInfo = document.getElementById('selected_member_info');
            const memberIdInput = document.getElementById('member_id');
            const clearSelection = document.getElementById('clear_selection');
            const submitBtn = document.getElementById('submit_btn');
            
            let searchTimeout;

            // Search members
            memberSearch.addEventListener('input', function() {
                const query = this.value.trim();
                
                clearTimeout(searchTimeout);
                
                if (query.length < 2) {
                    memberResults.classList.add('hidden');
                    return;
                }

                searchTimeout = setTimeout(() => {
                    fetch(`{{ route('member.search') }}?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            displaySearchResults(data);
                        })
                        .catch(error => {
                            console.error('Error searching members:', error);
                        });
                }, 300);
            });

            function displaySearchResults(members) {
                if (members.length === 0) {
                    memberList.innerHTML = '<div class="p-3 text-center text-muted-foreground">Tidak ada member ditemukan</div>';
                } else {
                    memberList.innerHTML = members.map(member => `
                        <div class="p-3 hover:bg-muted/50 cursor-pointer member-option" data-member-id="${member.id}" data-member-name="${member.name}" data-member-code="${member.member_code}" data-member-email="${member.email}">
                            <div class="font-medium text-card-foreground">${member.name}</div>
                            <div class="text-sm text-muted-foreground">${member.member_code} • ${member.email}</div>
                        </div>
                    `).join('');
                }
                
                memberResults.classList.remove('hidden');
            }

            // Handle member selection
            memberList.addEventListener('click', function(e) {
                const memberOption = e.target.closest('.member-option');
                if (memberOption) {
                    const memberId = memberOption.dataset.memberId;
                    const memberName = memberOption.dataset.memberName;
                    const memberCode = memberOption.dataset.memberCode;
                    const memberEmail = memberOption.dataset.memberEmail;
                    
                    selectMember(memberId, memberName, memberCode, memberEmail);
                }
            });

            function selectMember(id, name, code, email) {
                memberIdInput.value = id;
                selectedMemberName.textContent = name;
                selectedMemberInfo.textContent = `${code} • ${email}`;
                
                selectedMember.classList.remove('hidden');
                memberResults.classList.add('hidden');
                memberSearch.value = '';
                
                updateSubmitButton();
            }

            // Clear selection
            clearSelection.addEventListener('click', function() {
                memberIdInput.value = '';
                selectedMember.classList.add('hidden');
                updateSubmitButton();
            });

            // Update submit button state
            function updateSubmitButton() {
                const hasMember = memberIdInput.value !== '';
                const hasDuration = document.getElementById('membership_duration').value !== '';
                const hasPaymentMethod = document.getElementById('payment_method').value !== '';
                
                submitBtn.disabled = !(hasMember && hasDuration && hasPaymentMethod);
            }

            // Listen for form field changes
            document.getElementById('membership_duration').addEventListener('change', updateSubmitButton);
            document.getElementById('payment_method').addEventListener('change', updateSubmitButton);

            // Hide results when clicking outside
            document.addEventListener('click', function(e) {
                if (!memberSearch.contains(e.target) && !memberResults.contains(e.target)) {
                    memberResults.classList.add('hidden');
                }
            });
        });
    </script>
    @endpush
@endsection
