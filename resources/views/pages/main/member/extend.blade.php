@extends('layouts.app')
@section('title', 'Perpanjang Membership')
@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
            <!-- Judul -->
            <div>
                <h1 class="text-2xl font-bold text-foreground">Perpanjang Membership</h1>
            </div>

            <!-- Tombol Kembali -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('member.index') }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-border bg-background text-foreground rounded-lg hover:bg-muted/50 transition-colors">
                    <x-ui.icon name="chevron-left" class="w-4 h-4 mr-2" />
                    <span>Kembali</span>
                </a>
            </div>
        </div>



        <form action="{{ route('member.extend.store') }}" method="POST" class="space-y-6" id="extendForm">
            @csrf

            <div class="bg-card p-6 rounded-lg shadow-sm border border-border">
                <h2 class="text-lg font-semibold text-card-foreground mb-4">Pilih Member</h2>

                <div class="space-y-4">
                    <div>
                        <div class="relative">
                            <x-ui.icon name="search"
                                class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                            <input type="text" id="member_search" name="member_search"
                                class="w-full pl-10 pr-4 py-2 bg-input border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                                placeholder="Ketik nama atau ID member" autocomplete="off">
                            <input type="hidden" id="member_id" name="member_id" value="{{ old('member_id') }}">
                        </div>

                        <!-- Member Search Results -->
                        <div id="member_results"
                            class="hidden mt-2 bg-background border border-border rounded-lg shadow-lg max-h-60 overflow-y-auto">
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
                                <button type="button" id="clear_selection"
                                    class="text-muted-foreground hover:text-destructive">
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
                            <option value="1" {{ old('membership_duration') == '1' ? 'selected' : '' }}>1 Bulan - Rp
                                150.000</option>
                            <option value="3" {{ old('membership_duration') == '3' ? 'selected' : '' }}>3 Bulan - Rp
                                400.000</option>
                        </select>
                    </div>

                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-card-foreground mb-2">
                            Metode Pembayaran <span class="text-destructive">*</span>
                        </label>
                        <select id="payment_method" name="payment_method" required
                            class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent">
                            <option value="">Pilih metode pembayaran</option>
                            <option value="CASH" {{ old('payment_method') == 'CASH' ? 'selected' : '' }}>Tunai (CASH)
                            </option>
                            <option value="TRANSFER" {{ old('payment_method') == 'TRANSFER' ? 'selected' : '' }}>Transfer
                                Bank</option>
                            <option value="EWALLET" {{ old('payment_method') == 'EWALLET' ? 'selected' : '' }}>E-Wallet
                            </option>
                        </select>
                    </div>
                </div>

                <div class="mt-6">
                    <label for="payment_notes" class="block text-sm font-medium text-card-foreground mb-2">Catatan
                        Pembayaran</label>
                    <textarea id="payment_notes" name="payment_notes" rows="3"
                        class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                        placeholder="Catatan tambahan untuk pembayaran (opsional)">{{ old('payment_notes') }}</textarea>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-2 sm:space-x-4 mt-4">
                <a href="{{ route('member.index') }}"
                    class="mb-2 w-full sm:w-auto inline-flex items-center justify-center px-6 py-2 border border-border bg-background text-foreground rounded-lg hover:bg-muted/50 transition-colors">
                    Batal
                </a>
                <button type="submit" id="submit_btn" disabled
                    class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2 rounded-lg bubblegum-button-primary text-chart-2-foreground transition-colors">
                    <x-ui.icon name="calendar-plus" class="w-4 h-4 mr-2 inline" />
                    Perpanjang
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                console.log('DOM loaded, initializing member search...');

                const memberSearch = document.getElementById('member_search');
                const memberResults = document.getElementById('member_results');
                const memberList = document.getElementById('member_list');
                const selectedMember = document.getElementById('selected_member');
                const selectedMemberName = document.getElementById('selected_member_name');
                const selectedMemberInfo = document.getElementById('selected_member_info');
                const memberIdInput = document.getElementById('member_id');
                const clearSelection = document.getElementById('clear_selection');
                const submitBtn = document.getElementById('submit_btn');

                // Debug: Check if all elements are found
                console.log('Elements found:', {
                    memberSearch: !!memberSearch,
                    memberResults: !!memberResults,
                    memberList: !!memberList,
                    selectedMember: !!selectedMember,
                    selectedMemberName: !!selectedMemberName,
                    selectedMemberInfo: !!selectedMemberInfo,
                    memberIdInput: !!memberIdInput,
                    clearSelection: !!clearSelection,
                    submitBtn: !!submitBtn
                });

                if (!memberSearch || !memberResults || !memberList) {
                    console.error('Required DOM elements not found!');
                    return;
                }

                let searchTimeout;

                // Search members
                memberSearch.addEventListener('input', function() {
                    console.log('Search input event triggered, value:', this.value);
                    const query = this.value.trim();

                    clearTimeout(searchTimeout);

                    if (query.length < 2) {
                        memberResults.classList.add('hidden');
                        return;
                    }

                    searchTimeout = setTimeout(() => {
                        // Show loading state
                        memberList.innerHTML =
                            '<div class="p-3 text-center text-muted-foreground">Mencari...</div>';
                        memberResults.classList.remove('hidden');

                        const searchUrl =
                            `{{ route('member.search') }}?q=${encodeURIComponent(query)}`;
                        console.log('Making fetch request to:', searchUrl);

                        fetch(searchUrl, {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(response => {
                                console.log('Response status:', response.status);
                                console.log('Response headers:', response.headers);
                                if (!response.ok) {
                                    throw new Error(`HTTP error! status: ${response.status}`);
                                }
                                return response.json();
                            })
                            .then(data => {
                                console.log('Search results received:', data);
                                console.log('Results count:', data ? data.length : 0);
                                displaySearchResults(data);
                            })
                            .catch(error => {
                                console.error('Error searching members:', error);
                                memberList.innerHTML =
                                    '<div class="p-3 text-center text-red-500">Error: Gagal mencari member</div>';
                                memberResults.classList.remove('hidden');
                            });
                    }, 300);
                });

                function displaySearchResults(members) {
                    console.log('Displaying search results:', members);

                    if (!members || members.length === 0) {
                        console.log('No members found, showing empty state');
                        memberList.innerHTML =
                            '<div class="p-3 text-center text-muted-foreground">Tidak ada member ditemukan</div>';
                    } else {
                        console.log('Rendering', members.length, 'members');
                        memberList.innerHTML = members.map(member => `
                            <div class="p-3 hover:bg-muted/50 cursor-pointer member-option flex items-center justify-between"
                                data-member-id="${member.id}"
                                data-member-name="${member.name}"
                                data-member-code="${member.member_code}"
                                data-member-status="${member.status}">
                                
                                <!-- Kiri: Informasi member -->
                                <div class="flex flex-col">
                                    <div class="font-medium text-card-foreground">${member.name}</div>
                                    <div class="text-sm text-muted-foreground">${member.member_code}</div>
                                </div>

                                <!-- Kanan: Status badge -->
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    ${member.status === 'ACTIVE'
                                        ? 'bg-chart-2/20 text-chart-2'
                                        : 'bg-destructive/20 text-destructive'}">
                                    ${member.status === 'ACTIVE' ? 'Aktif' : 'Tidak Aktif'}
                                </span>
                            </div>
                    `).join('');
                    }

                    console.log('Showing results container');
                    memberResults.classList.remove('hidden');
                }

                // Handle member selection
                memberList.addEventListener('click', function(e) {
                    console.log('Member list clicked, target:', e.target);
                    const memberOption = e.target.closest('.member-option');
                    if (memberOption) {
                        console.log('Member option clicked:', memberOption);
                        const memberId = memberOption.dataset.memberId;
                        const memberName = memberOption.dataset.memberName;
                        const memberCode = memberOption.dataset.memberCode;
                        const memberStatus = memberOption.dataset.memberStatus;

                        console.log('Selected member data:', {
                            memberId,
                            memberName,
                            memberCode,
                            memberStatus
                        });
                        selectMember(memberId, memberName, memberCode, memberStatus);
                    } else {
                        console.log('No member option found in click target');
                    }
                });

                function selectMember(id, name, code, exp_date) {
                    console.log('Selecting member:', {
                        id,
                        name,
                        code,
                        exp_date
                    });

                    memberIdInput.value = id;
                    selectedMemberName.textContent = name;
                    selectedMemberInfo.textContent = `${code} • ${exp_date}`;

                    console.log('Member ID input value set to:', memberIdInput.value);
                    console.log('Selected member name set to:', selectedMemberName.textContent);
                    console.log('Selected member info set to:', selectedMemberInfo.textContent);

                    selectedMember.classList.remove('hidden');
                    memberResults.classList.add('hidden');
                    memberSearch.value = '';

                    console.log('Member selection completed, updating submit button');
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

                    console.log('Submit button state check:', {
                        hasMember,
                        hasDuration,
                        hasPaymentMethod,
                        memberId: memberIdInput.value,
                        duration: document.getElementById('membership_duration').value,
                        paymentMethod: document.getElementById('payment_method').value
                    });

                    const shouldEnable = hasMember && hasDuration && hasPaymentMethod;
                    submitBtn.disabled = !shouldEnable;

                    console.log('Submit button enabled:', shouldEnable);
                }

                // Listen for form field changes
                const durationSelect = document.getElementById('membership_duration');
                const paymentSelect = document.getElementById('payment_method');

                if (durationSelect) {
                    durationSelect.addEventListener('change', function() {
                        console.log('Membership duration changed to:', this.value);
                        updateSubmitButton();
                    });
                }

                if (paymentSelect) {
                    paymentSelect.addEventListener('change', function() {
                        console.log('Payment method changed to:', this.value);
                        updateSubmitButton();
                    });
                }

                // Hide results when clicking outside
                document.addEventListener('click', function(e) {
                    if (!memberSearch.contains(e.target) && !memberResults.contains(e.target)) {
                        console.log('Clicking outside search area, hiding results');
                        memberResults.classList.add('hidden');
                    }
                });

                console.log('All event listeners attached successfully');
            });
        </script>
    @endpush
@endsection
