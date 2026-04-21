import { softReload } from './navigation.js';

// Filters & Sorting

// Clears all filters by reloading page with base URL (preserving tab if needed)
export function clearFilters(tableType) {
    const url = window.location.pathname + (tableType !== 'store' ? '?tab=' + tableType : '');
    softReload(url); // uses custom soft reload (no full page refresh)
}


// Handles sorting logic + UI state (arrows, active button)
export function handleVisualSort(buttonElement, tableType, sortColumn) {
    const params = new URLSearchParams(window.location.search);
    const activeTab = params.get('tab') || 'users';

    // Define default sorting column depending on table
    const defaultSort = tableType === 'games' ? 'game_id' : 'id';
    
    let actualCurrentSort;
    let actualCurrentDir;
    
    // Determine current sorting state based on URL params
    if ((activeTab === tableType || tableType === 'store') && params.has('sort')) {
        actualCurrentSort = params.get('sort');
        actualCurrentDir = params.get('dir') || 'asc';
    } else {
        actualCurrentSort = defaultSort;
        actualCurrentDir = 'asc';
    }

    // Toggle direction if clicking the same column again
    let newDir = 'asc';
    if (actualCurrentSort === sortColumn) {
        newDir = actualCurrentDir === 'asc' ? 'desc' : 'asc';
    }

    // Update URL params
    params.set('sort', sortColumn);
    params.set('dir', newDir);

    if (tableType !== 'store') {
        params.set('tab', tableType);
    }

    const url = window.location.pathname + '?' + params.toString();
    softReload(url);
}


// Applies filters from form inputs and updates URL params
export function applyFilters(formElement, tableType) {
    const formData = new FormData(formElement);
    const params = new URLSearchParams();
    const oldParams = new URLSearchParams(window.location.search);

    // Preserve existing sorting when applying filters
    if (oldParams.has('sort')) params.set('sort', oldParams.get('sort'));
    if (oldParams.has('dir')) params.set('dir', oldParams.get('dir'));

    // Add only non-empty filter values
    for (let [key, value] of formData.entries()) {
        if (value.trim() !== '') params.append(key, value); 
    }

    // Maintain correct tab context
    if (tableType !== 'store') params.set('tab', tableType);

    // Mark that filters are active (used later in restoreState)
    params.set('filtered', '1'); 
    
    const url = window.location.pathname + '?' + params.toString();
    softReload(url);
}

// Category Checkbox Logic

// Handles "Select All" checkbox behavior for categories
export function initCategoryLogic() {
    const allCheckbox = document.getElementById('cb-cat-all');
    const itemCheckboxes = document.querySelectorAll('.cb-cat-item');

    if (!allCheckbox || itemCheckboxes.length === 0) return;

    // When "All" is toggled → update all category checkboxes
    allCheckbox.addEventListener('change', (e) => {
        const isChecked = e.target.checked;
        itemCheckboxes.forEach(cb => cb.checked = isChecked);
    });

    // When any individual checkbox changes → update "All" checkbox state
    itemCheckboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            const allChecked = Array.from(itemCheckboxes).every(c => c.checked);
            allCheckbox.checked = allChecked;
        });
    });
}

// State Restoration (after reload)

// Restores UI state (filters, sorting, checkboxes) from URL params
export function restoreState() {
    const params = new URLSearchParams(window.location.search);
    const activeTab = params.get('tab') || 'users';

    const isStore = document.getElementById('store-grid') !== null;
    const isFiltered = params.has('filtered');
    
    // Restore text inputs
    document.querySelectorAll('input.form-input').forEach(input => {
        if (params.has(input.name)) input.value = params.get(input.name);
    });

    // Restore user role checkboxes (default = all selected if no filters)
    if (activeTab === 'users' && !isStore) {
        const roleUser = document.querySelector('input[name="role_user"]');
        const roleAdmin = document.querySelector('input[name="role_admin"]');

        if (roleUser) roleUser.checked = !isFiltered || params.has('role_user');
        if (roleAdmin) roleAdmin.checked = !isFiltered || params.has('role_admin');
    }

    // Store-specific filters
    if (isStore) {
        // Restore type filters
        ['type_free', 'type_paid', 'type_upcoming'].forEach(name => {
            const cb = document.querySelector(`input[name="${name}"]`);
            if (cb) cb.checked = !isFiltered || params.has(name);
        });

        // Restore category filters
        const allCatCb = document.getElementById('cb-cat-all');
        const itemCbs = document.querySelectorAll('.cb-cat-item');
        
        if (allCatCb && itemCbs.length > 0) {
            if (!isFiltered || params.has('cat_all')) {
                // Default: all categories selected
                allCatCb.checked = true;
                itemCbs.forEach(cb => cb.checked = true);
            } else {
                allCatCb.checked = false;

                // Restore selected categories from URL
                const selectedCats = params.getAll('categories[]');
                itemCbs.forEach(cb => cb.checked = selectedCats.includes(cb.value));

                // If all selected manually → check "All"
                if (Array.from(itemCbs).every(c => c.checked)) {
                    allCatCb.checked = true;
                }
            }
        }
    }

    // Restore sorting UI (active button + arrow direction)
    document.querySelectorAll('.btn-sort-item').forEach(btn => {
        btn.classList.remove('active');

        const i = btn.querySelector('i');
        if (i) i.className = 'fa-solid fa-sort'; 
        
        // Extract tableType and column from inline onclick
        const onclickAttr = btn.getAttribute('onclick') || '';
        const args = onclickAttr.match(/handleVisualSort\([^,]+,\s*'([^']+)',\s*'([^']+)'\)/);
        if (!args) return;
        
        const btnTableType = args[1];
        const btnSortCol = args[2];

        const defaultSort = btnTableType === 'games' || btnTableType === 'store'
            ? 'game_id'
            : 'id';
        
        let targetSort, targetDir;

        // Determine current sorting for this button
        if (activeTab === btnTableType || btnTableType === 'store') {
            targetSort = params.get('sort') || defaultSort;
            targetDir = params.get('dir') || 'asc';
        } else {
            targetSort = defaultSort;
            targetDir = 'asc';
        }

        // Highlight active sort button
        if (btnSortCol === targetSort) {
            btn.classList.add('active');

            if (i) {
                i.className = targetDir === 'asc'
                    ? 'fa-solid fa-arrow-up'
                    : 'fa-solid fa-arrow-down';
            }
        }
    });
}