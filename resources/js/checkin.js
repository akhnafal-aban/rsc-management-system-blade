const CHUNK_SIZE = 15;

function getCsrfToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute('content') : '';
}

function formatStatusBadge(member) {
    const baseClass = 'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide';

    if (member.status === 'ACTIVE') {
        return `<span class="${baseClass} bg-green-100 text-green-700">Aktif</span>`;
    }

    if (member.status === 'EXPIRED') {
        return `<span class="${baseClass} bg-orange-100 text-orange-700">Expired</span>`;
    }

    return `<span class="${baseClass} bg-red-100 text-red-700">Tidak Aktif</span>`;
}

function createMemberRow(member, onSelect) {
    const row = document.createElement('div');
    row.className = 'flex items-start justify-between px-4 py-3 gap-4 hover:bg-muted/40 transition-colors';

    const infoWrapper = document.createElement('div');
    infoWrapper.className = 'flex flex-col gap-1';

    const name = document.createElement('p');
    name.className = 'text-sm font-semibold text-card-foreground';
    name.textContent = member.name;

    const meta = document.createElement('div');
    meta.className = 'flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted-foreground';
    meta.innerHTML = `
        <span>ID: <span class="font-medium">${member.member_code}</span></span>
        <span>Exp: <span class="font-medium">${member.exp_date_formatted}</span></span>
    `;

    const badgeContainer = document.createElement('div');
    badgeContainer.className = 'flex flex-wrap items-center gap-2';
    badgeContainer.innerHTML = formatStatusBadge(member);

    if (member.has_checked_in_today) {
        badgeContainer.innerHTML += '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide bg-blue-100 text-blue-700">Sudah Check-in</span>';
    }

    infoWrapper.appendChild(name);
    infoWrapper.appendChild(meta);
    infoWrapper.appendChild(badgeContainer);

    const actionWrapper = document.createElement('div');
    actionWrapper.className = 'flex flex-col items-end gap-2 whitespace-nowrap';

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'inline-flex items-center px-3 py-1.5 border border-border rounded-md text-xs font-medium transition-colors gap-2';

    if (member.can_checkin) {
        button.classList.add('bg-chart-2', 'text-chart-2-foreground', 'hover:bg-chart-2/90');
        button.innerHTML = '<span class="text-sm leading-none">+</span><span>Tambah ke Batch</span>';
        button.addEventListener('click', () => onSelect(member));
    } else {
        button.classList.add('bg-muted/40', 'text-muted-foreground', 'cursor-not-allowed');
        button.disabled = true;

        const reason = member.status !== 'ACTIVE'
            ? 'Status tidak aktif'
            : 'Sudah check-in hari ini';
        button.textContent = reason;
    }

    actionWrapper.appendChild(button);

    row.appendChild(infoWrapper);
    row.appendChild(actionWrapper);

    return row;
}

function createSelectedCard(member, onRemove) {
    const card = document.createElement('div');
    card.className = 'border border-border rounded-lg px-4 py-3 bg-background flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3';

    const info = document.createElement('div');
    info.className = 'flex flex-col';
    info.innerHTML = `
        <span class="text-sm font-semibold text-card-foreground">${member.name}</span>
        <span class="text-xs text-muted-foreground">${member.member_code} • Exp: ${member.exp_date_formatted}</span>
    `;

    const actions = document.createElement('div');
    actions.className = 'flex items-center gap-2';

    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'inline-flex items-center px-3 py-1.5 border border-border rounded-md text-xs font-medium text-destructive hover:bg-destructive/10 transition-colors gap-1';
    removeBtn.innerHTML = '<span aria-hidden="true">&times;</span><span>Hapus</span>';
    removeBtn.addEventListener('click', () => onRemove(member.id));

    actions.appendChild(removeBtn);

    card.appendChild(info);
    card.appendChild(actions);

    return card;
}

function updateCounter(counterElement, count) {
    counterElement.textContent = `${count} dipilih`;
}

function showToast(type, message) {
    if (typeof window.showAlert === 'function') {
        const title = type === 'success' ? 'Berhasil' : (type === 'error' ? 'Kesalahan' : 'Informasi');
        window.showAlert(message, title, type);
    } else {
        // Fallback minimal
        alert(message);
    }
}

function initCheckInApp() {
    const root = document.getElementById('checkInApp');
    if (!root) {
        return;
    }

    const searchUrl = root.dataset.searchUrl;
    const batchUrl = root.dataset.batchUrl;

    const csrfToken = getCsrfToken();

    const searchInput = document.getElementById('member_search_input');
    const searchButton = document.getElementById('member_search_button');
    const clearButton = document.getElementById('member_search_clear');
    const metaElement = document.getElementById('member_search_meta');
    const resultsWrapper = document.getElementById('member_results_wrapper');
    const resultsContainer = document.getElementById('member_results_container');
    const resultsSentinel = document.getElementById('member_results_sentinel');
    const resultsCount = document.getElementById('member_results_count');
    const resultsEmpty = document.getElementById('member_results_empty');

    const selectedPlaceholder = document.getElementById('selected_members_placeholder');
    const selectedList = document.getElementById('selected_members_list');
    const selectedCounter = document.getElementById('selected_counter');
    const clearSelectedButton = document.getElementById('selected_clear_all');
    const batchCheckInButton = document.getElementById('batch_checkin_button');

    if (!searchInput || !searchButton || !clearButton || !resultsWrapper || !resultsContainer || !resultsSentinel || !selectedList || !selectedPlaceholder || !clearSelectedButton || !batchCheckInButton) {
        return;
    }

    let searchController = null;
    let searchResults = [];
    let renderedCount = 0;
    let observer = null;

    const selectedMembers = new Map();

    function resetResults() {
        searchResults = [];
        renderedCount = 0;
        resultsContainer.innerHTML = '';
        if (resultsEmpty) {
            resultsEmpty.classList.add('hidden');
        }
        resultsWrapper.classList.add('hidden');
        if (metaElement) {
            metaElement.classList.add('hidden');
        }
        if (resultsCount) {
            resultsCount.textContent = '';
        }
    }

    function renderSelected() {
        selectedList.innerHTML = '';

        if (selectedMembers.size === 0) {
            selectedPlaceholder.classList.remove('hidden');
            selectedList.classList.add('hidden');
            clearSelectedButton.disabled = true;
            batchCheckInButton.disabled = true;
        } else {
            selectedPlaceholder.classList.add('hidden');
            selectedList.classList.remove('hidden');
            clearSelectedButton.disabled = false;
            batchCheckInButton.disabled = false;

            selectedMembers.forEach((member) => {
                const card = createSelectedCard(member, handleRemoveSelected);
                selectedList.appendChild(card);
            });
        }

        updateCounter(selectedCounter, selectedMembers.size);
    }

    function handleRemoveSelected(memberId) {
        selectedMembers.delete(String(memberId));
        renderSelected();
    }

    function handleAddSelected(member) {
        if (!member.can_checkin) {
            const reason = member.status !== 'ACTIVE' ? 'Status member tidak aktif' : 'Member sudah check-in hari ini';
            showToast('info', reason);
            return;
        }

        const key = String(member.id);

        if (!selectedMembers.has(key)) {
            selectedMembers.set(key, member);
            renderSelected();
        }
    }

    function updateMeta(total) {
        if (!metaElement) {
            return;
        }

        if (total === 0) {
            metaElement.classList.add('hidden');
        } else {
            metaElement.textContent = `Menemukan ${total} member. Scroll untuk memuat hasil lainnya.`;
            metaElement.classList.remove('hidden');
        }
    }

    function renderNextChunk() {
        if (renderedCount >= searchResults.length) {
            return;
        }

        const fragment = document.createDocumentFragment();
        const end = Math.min(renderedCount + CHUNK_SIZE, searchResults.length);

        for (let index = renderedCount; index < end; index += 1) {
            const member = searchResults[index];
            const row = createMemberRow(member, handleAddSelected);
            fragment.appendChild(row);
        }

        renderedCount = end;
        resultsContainer.appendChild(fragment);

        if (resultsCount) {
            resultsCount.textContent = `Menampilkan ${renderedCount} dari ${searchResults.length}`;
        }
    }

    function initObserver() {
        if (observer) {
            observer.disconnect();
        }

        observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    renderNextChunk();
                }
            });
        });

        observer.observe(resultsSentinel);
    }

    function setLoadingState(isLoading) {
        searchButton.disabled = isLoading;
        clearButton.disabled = isLoading;
        searchButton.classList.toggle('opacity-70', isLoading);
    }

    async function performSearch() {
        const keyword = (searchInput.value || '').trim();

        if (keyword.length < 2) {
            showToast('info', 'Masukkan minimal 2 karakter untuk mencari member.');
            return;
        }

        if (searchController) {
            searchController.abort();
        }

        searchController = new AbortController();

        setLoadingState(true);
        resetResults();

        resultsWrapper.classList.remove('hidden');

        try {
            const response = await fetch(`${searchUrl}?q=${encodeURIComponent(keyword)}&t=${Date.now()}`, {
                method: 'GET',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: searchController.signal,
            });

            if (!response.ok) {
                throw new Error('Gagal memuat data member');
            }

            const data = await response.json();
            searchResults = Array.isArray(data) ? data : [];

            if (searchResults.length === 0) {
                if (resultsEmpty) {
                    resultsEmpty.classList.remove('hidden');
                }
                resultsWrapper.classList.remove('hidden');
                updateMeta(0);
                return;
            }

            renderedCount = 0;
            resultsContainer.innerHTML = '';
            renderNextChunk();
            initObserver();
            updateMeta(searchResults.length);
            resultsWrapper.classList.remove('hidden');
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error(error);
                showToast('error', error.message || 'Gagal memuat data member');
            }
        } finally {
            setLoadingState(false);
        }
    }

    searchButton.addEventListener('click', performSearch);

    searchInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            performSearch();
        }
    });

    searchInput.addEventListener('input', () => {
        if (searchInput.value.trim().length === 0) {
            resetResults();
        }
    });

    clearButton.addEventListener('click', () => {
        if (searchController) {
            searchController.abort();
        }
        searchInput.value = '';
        resetResults();
    });

    clearSelectedButton.addEventListener('click', () => {
        selectedMembers.clear();
        renderSelected();
    });

    batchCheckInButton.addEventListener('click', async () => {
        if (selectedMembers.size === 0) {
            return;
        }

        const memberIds = Array.from(selectedMembers.values()).map((member) => Number(member.id));

        batchCheckInButton.disabled = true;
        clearSelectedButton.disabled = true;
        batchCheckInButton.classList.add('opacity-70');

        try {
            const response = await fetch(batchUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ member_ids: memberIds }),
            });

            const result = await response.json();

            if (!response.ok) {
                const reason = result?.skipped?.[0]?.reason ?? 'Batch check-in gagal diproses.';
                showToast('error', reason);
                return;
            }

            const succeeded = Array.isArray(result.checked_in) ? result.checked_in : [];
            const skipped = Array.isArray(result.skipped) ? result.skipped : [];

            succeeded.forEach((entry) => {
                selectedMembers.delete(String(entry.member_id));
            });

            renderSelected();

            const successMessage = succeeded.length > 0
                ? `${succeeded.length} member berhasil check-in.`
                : 'Tidak ada member yang berhasil check-in.';

            const skippedMessage = skipped.length > 0
                ? `\n${skipped.length} member dilewati: ${skipped.map((item) => item.reason).join(', ')}`
                : '';

            showToast(result.success ? 'success' : 'info', `${successMessage}${skippedMessage}`.trim());
            if ((searchInput.value || '').trim().length >= 2) {
                performSearch();
            }
        } catch (error) {
            console.error(error);
            showToast('error', 'Terjadi kesalahan saat memproses batch check-in.');
        } finally {
            batchCheckInButton.disabled = selectedMembers.size === 0;
            clearSelectedButton.disabled = selectedMembers.size === 0;
            batchCheckInButton.classList.remove('opacity-70');
        }
    });

    renderSelected();
}

document.addEventListener('DOMContentLoaded', initCheckInApp);
