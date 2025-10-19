@extends('layouts.app')
@section('title', 'Check In Member')
@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
            <!-- Judul -->
            <div>
                <h1 class="text-2xl font-bold text-foreground">Check In Member</h1>
            </div>

            <!-- Tombol Kembali -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('attendance.index') }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-border bg-background text-foreground rounded-lg hover:bg-muted/50 transition-colors">
                    <x-ui.icon name="chevron-left" class="w-4 h-4 mr-2" />
                    <span>Kembali</span>
                </a>
            </div>
        </div>

        <form action="{{ route('attendance.checkin') }}" method="POST" class="space-y-6" id="checkInForm">
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
                                placeholder="Ketik nama atau ID member..." autocomplete="off">
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

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-2 sm:space-x-4 mt-4">
                <a href="{{ route('attendance.index') }}"
                    class="mb-2 w-full sm:w-auto inline-flex items-center justify-center px-6 py-2 border border-border bg-background text-foreground rounded-lg hover:bg-muted/50 transition-colors">
                    Batal
                </a>
                <button type="submit" id="submit_btn" disabled
                    class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2 rounded-lg bubblegum-button-primary text-chart-2-foreground transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <x-ui.icon name="login" class="w-4 h-4 mr-2 inline" />
                    Check In
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Debug toggle - set to false for production
                const DEBUG = true;
                
                function log(...args) {
                    if (DEBUG) console.log(...args);
                }

                function escapeHtml(str) {
                    // Handle null, undefined, or non-string values
                    if (str == null || typeof str !== 'string') {
                        return String(str || '');
                    }
                    
                    return str.replace(/[&<>'"]/g, tag => ({
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        "'": '&#39;',
                        '"': '&quot;'
                    }[tag]));
                }

                log('DOM loaded, initializing member search...');

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
                log('Elements found:', {
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
                let currentSearchQuery = '';
                let currentController = null;

                // Search members
                memberSearch.addEventListener('input', function() {
                    log('Search input event triggered, value:', this.value);
                    const query = this.value.trim();

                    clearTimeout(searchTimeout);

                    // Cancel previous request
                    if (currentController) {
                        currentController.abort();
                    }

                    if (query.length < 2) {
                        memberResults.classList.add('hidden');
                        currentSearchQuery = '';
                        return;
                    }

                    searchTimeout = setTimeout(() => {
                        // Check if query has changed since timeout was set
                        if (query !== this.value.trim()) {
                            log('Query changed during timeout, skipping...');
                            return;
                        }

                        currentSearchQuery = query;

                        // Show loading state
                        memberList.innerHTML =
                            '<div class="p-3 text-center text-muted-foreground">Mencari...</div>';
                        memberResults.classList.remove('hidden');

                        const searchUrl = `{{ route('member.search') }}?q=${encodeURIComponent(query)}&t=${Date.now()}`;
                        log('Making fetch request to:', searchUrl);

                        // Create new AbortController for this request
                        currentController = new AbortController();

                        fetch(searchUrl, {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                signal: currentController.signal
                            })
                            .then(response => {
                                log('Response status:', response.status);
                                if (!response.ok) {
                                    throw new Error(`HTTP error! status: ${response.status}`);
                                }
                                return response.json();
                            })
                            .then(data => {
                                // Check if this is still the current search query
                                if (query !== currentSearchQuery) {
                                    log('Search query changed, ignoring results');
                                    return;
                                }

                                log('Search results received:', data);
                                displaySearchResults(data);
                            })
                            .catch(error => {
                                // Don't show error if request was aborted
                                if (error.name === 'AbortError') {
                                    log('Request aborted');
                                    return;
                                }

                                // Only show error if this is still the current search
                                if (query === currentSearchQuery) {
                                    console.error('Error searching members:', error);
                                    memberList.innerHTML =
                                        '<div class="p-3 text-center text-red-500">Error: Gagal mencari member</div>';
                                    memberResults.classList.remove('hidden');
                                }
                            });
                    }, 500); // Debounce timeout
                });

                function displaySearchResults(members) {
                    log('Displaying search results:', members);

                    if (!members || members.length === 0) {
                        log('No members found, showing empty state');
                        memberList.innerHTML =
                            '<div class="p-3 text-center text-muted-foreground">Tidak ada member ditemukan</div>';
                    } else {
                        log('Rendering', members.length, 'members');
                        
                        // Optimized rendering with escaped HTML for security
                        memberList.innerHTML = members.map(member => {
                            // Determine if member is inactive
                            const isInactive = member.status !== 'ACTIVE';
                            const isDisabled = isInactive || member.has_checked_in_today;

                            // Escape HTML to prevent XSS
                            // Debug logging to check data types
                            log('Member data types:', {
                                id: typeof member.id,
                                name: typeof member.name,
                                member_code: typeof member.member_code,
                                exp_date_formatted: typeof member.exp_date_formatted,
                                exp_date: typeof member.exp_date
                            });
                            
                            const safeName = escapeHtml(member.name);
                            const safeCode = escapeHtml(member.member_code);
                            const safeExpDate = escapeHtml(member.exp_date_formatted || member.exp_date);

                            return `
                                <div class="p-3 hover:bg-muted/50 cursor-pointer member-option flex items-center justify-between ${isDisabled ? 'opacity-50' : ''}" 
                                     data-member-id="${escapeHtml(member.id)}" 
                                     data-member-name="${safeName}" 
                                     data-member-code="${safeCode}" 
                                     data-member-status="${escapeHtml(member.status)}"
                                     data-member-exp-date="${escapeHtml(member.exp_date)}"
                                     data-member-exp-date-formatted="${safeExpDate}"
                                     data-member-has-checked-in-today="${member.has_checked_in_today}"
                                     data-member-can-checkin="${member.can_checkin}"
                                     data-member-is-inactive="${isInactive}"
                                     data-member-is-disabled="${isDisabled}">
                                    
                                    <!-- Kiri: Informasi member -->
                                    <div class="flex flex-col">
                                        <div class="font-medium text-card-foreground">${safeName}</div>
                                        <div class="text-sm text-muted-foreground">${safeCode}</div>
                                        <div class="text-xs text-muted-foreground mt-1">Exp: ${safeExpDate}</div>
                                    </div>

                                    <!-- Kanan: Status badges -->
                                    <div class="flex flex-col gap-1 ml-2">
                                        ${getStatusBadge(member.status)}
                                        ${member.has_checked_in_today ? getCheckinBadge() : ''}
                                    </div>
                                </div>
                            `;
                        }).join('');
                    }

                    log('Showing results container');
                    memberResults.classList.remove('hidden');
                }

                function getStatusBadge(status) {
                    if (status === 'ACTIVE') {
                        return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">ACTIVE</span>';
                    } else {
                        return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">INACTIVE</span>';
                    }
                }

                function getCheckinBadge() {
                    return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">✓ Sudah Check-in</span>';
                }

                // Handle member selection
                memberList.addEventListener('click', function(e) {
                    log('Member list clicked, target:', e.target);
                    const memberOption = e.target.closest('.member-option');
                    if (memberOption) {
                        log('Member option clicked:', memberOption);
                        const memberId = memberOption.dataset.memberId;
                        const memberName = memberOption.dataset.memberName;
                        const memberCode = memberOption.dataset.memberCode;
                        const memberExpDate = memberOption.dataset.memberExpDateFormatted || memberOption
                            .dataset.memberExpDate;
                        const memberStatus = memberOption.dataset.memberStatus;
                        const isInactive = memberOption.dataset.memberIsInactive === 'true';
                        const hasCheckedInToday = memberOption.dataset.memberHasCheckedInToday === 'true';
                        const isDisabled = memberOption.dataset.memberIsDisabled === 'true';

                        log('Selected member data:', {
                            memberId,
                            memberName,
                            memberCode,
                            memberExpDate,
                            memberStatus,
                            isInactive,
                            hasCheckedInToday,
                            isDisabled
                        });

                        // Check if member can be selected
                        if (isDisabled) {
                            let message = '';
                            if (isInactive) {
                                message = 'Member tidak aktif. Tidak dapat melakukan check-in.';
                            } else if (hasCheckedInToday) {
                                message = 'Member sudah melakukan check-in hari ini.';
                            }

                            // Show popup message
                            showAlert(
                                message,
                                'Info Aja Bro ini',
                                'info'
                            );
                            return;
                        }

                        selectMember(memberId, memberName, memberCode, memberExpDate);
                    }
                });

                function selectMember(id, name, code, expDate) {
                    log('Selecting member:', {
                        id,
                        name,
                        code,
                        expDate
                    });

                    memberIdInput.value = id;
                    selectedMemberName.textContent = name;
                    selectedMemberInfo.textContent = `${code} • Exp: ${expDate}`;

                    log('Member ID input value set to:', memberIdInput.value);
                    log('Selected member name set to:', selectedMemberName.textContent);
                    log('Selected member info set to:', selectedMemberInfo.textContent);

                    selectedMember.classList.remove('hidden');
                    memberResults.classList.add('hidden');
                    memberSearch.value = '';

                    log('Member selection completed, updating submit button');
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

                    log('Submit button state check:', {
                        hasMember,
                        memberId: memberIdInput.value
                    });

                    // Enable submit button if member is selected
                    // The validation for inactive/expired/checked-in will be handled in the controller
                    submitBtn.disabled = !hasMember;

                    log('Submit button enabled:', hasMember);
                }

                // Hide results when clicking outside
                document.addEventListener('click', function(e) {
                    if (!memberSearch.contains(e.target) && !memberResults.contains(e.target)) {
                        log('Clicking outside search area, hiding results');
                        memberResults.classList.add('hidden');
                    }
                });

                log('All event listeners attached successfully');
            });
        </script>
    @endpush
@endsection
