@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/shared-ui.css') }}">
<link rel="stylesheet" href="{{ asset('css/bhw/families.css') }}">
@endsection

@section('content')
<div class="family-container bhw-families-page">
    @if(session('success'))
        <div class="family-container-alert" id="familyPageAlert" role="status">
            <div class="family-alert-content">
                <svg viewBox="0 0 20 20" aria-hidden="true"><path d="M10 1.7a8.3 8.3 0 1 0 0 16.6 8.3 8.3 0 0 0 0-16.6Zm3.7 6.1-4.2 4.7a.9.9 0 0 1-1.3.1L6.4 11a.9.9 0 1 1 1.2-1.3l1.2 1.1 3.6-4a.9.9 0 0 1 1.3 1.2Z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" class="family-alert-close" aria-label="Dismiss notification" onclick="document.getElementById('familyPageAlert').remove()">&times;</button>
        </div>
    @endif

    <header class="family-page-header">
        <div>
            <h1>Family Registry &amp; Household Management</h1>
            <p>Manage barangay family records, citizen associations, and purok subgroup assignments.</p>
        </div>
        <button type="button" class="btn btn-secondary header-browse-button" onclick="openSubgroupPicker('browse')">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 5.5 8 3l8 3 5-2.5v15L16 21l-8-3-5 2.5v-15ZM8 3v15m8-12v15M5.5 8.5 6.7 8m7.1.7.7.3m-8.3 4.8.5-.3"/></svg>
            Browse Puroks
        </button>
    </header>

    <!-- Register Family Form -->
    @if(auth()->user()->hasWriteAccess('family'))
    <section class="card family-card family-register-card">
        <h2 class="section-card-title">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v15H4z"/><path d="M12 8v9M7.5 12.5h9"/></svg>
            Register New Family
        </h2>
        <form action="{{ route('bhw.families.store') }}" method="POST">
            @csrf
            <div class="form-row register-form-row">
                <label class="family-field">
                    <span>Family Name</span>
                    <input type="text" name="family_name" placeholder="e.g., Santos Family" class="form-control" required>
                </label>
                <label class="family-field">
                    <span>Purok / Subgroup</span>
                    <span class="family-select-trigger">
                        <input type="hidden" name="subgroup_id" id="registerSubgroupId" required>
                        <input type="text" id="registerSubgroupDisplay" class="form-control" placeholder="-- Select Purok / Subgroup --" readonly onclick="openSubgroupPicker('register')" required aria-label="Select Purok or subgroup">
                        <svg viewBox="0 0 20 20" aria-hidden="true"><path d="m5 7 5 5 5-5"/></svg>
                    </span>
                </label>
                <button type="button" class="btn btn-secondary register-browse-button" onclick="openSubgroupPicker('register')">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                    Browse Puroks
                </button>
            </div>
            <button type="submit" class="btn btn-primary create-family-button">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                Create Family Record
            </button>
        </form>
    </section>
    @endif

    <!-- Registered Families Table -->
    <div class="card family-card family-registry-card">
        <div class="family-list-heading">
            <div>
                <h2>Registered Families</h2>
                <p>Showing all registered households and assigned citizens.</p>
                <p class="family-filter-notice" id="familyFilterNotice" hidden></p>
            </div>
            <div class="family-search">
                <input type="search" id="familySearchInput" class="form-control" placeholder="Search family or ID..." aria-label="Search registered families">
            </div>
        </div>
        <div class="family-table-wrap"><table class="family-table">
            <thead>
                <tr>
                    <th>Family ID</th>
                    <th>Family Name</th>
                    <th>Purok & Subgroup</th>
                    <th>Members Count</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="familiesTableBody">
                @forelse($families as $fam)
                    <tr class="family-row" data-subgroup-id="{{ $fam->subgroup_id }}">
                        <td><strong>F-{{ str_pad($fam->id, 4, '0', STR_PAD_LEFT) }}</strong></td>
                        <td><strong>{{ $fam->family_name }}</strong></td>
                        <td>
                            {{ $fam->purok->name ?? 'N/A' }} 
                            @if($fam->subgroup)
                                <span style="color: #666; font-size: 13px;">({{ $fam->subgroup->name }})</span>
                            @endif
                        </td>
                        <td>{{ $fam->members->count() }} citizen(s)</td>
                        <td class="family-actions-cell">
                            <div class="family-actions">
                                @if(auth()->user()->hasWriteAccess('family'))
                                <button type="button" class="btn-sm" data-family='{!! json_encode($fam) !!}' onclick="openFamilyModal(this)">
                                    <svg viewBox="0 0 20 20" aria-hidden="true"><path d="M11.8 4.2 15.8 8.2M4 16l3.6-.8 8.9-8.9a1.8 1.8 0 0 0-2.6-2.6L5 12.6 4 16Z"/></svg>
                                    Manage / Edit
                                </button>
                                @else
                                <button type="button" class="btn-sm" data-family='{!! json_encode($fam) !!}' onclick="openFamilyModal(this)">
                                    <svg viewBox="0 0 20 20" aria-hidden="true"><path d="M2 10s2.8-5 8-5 8 5 8 5-2.8 5-8 5-8-5-8-5Z"/><circle cx="10" cy="10" r="2"/></svg>
                                    View
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #7f8c8d; font-style: italic;">No families registered yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table></div>
    </div>
</div>

<!-- MAIN FAMILY MANAGEMENT MODAL -->
<dialog id="familyModal" class="family-modal family-management-modal" aria-labelledby="modalTitle">
    <div class="modal-header family-management-header">
        <div class="family-modal-title-wrap">
            <span class="family-modal-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V10Z"/><path d="M7 11h.01M17 11h.01"/></svg>
            </span>
            <div>
                <h3 id="modalTitle">Manage Family</h3>
                <p>Household details &amp; member directory</p>
            </div>
        </div>
        <button type="button" class="modal-close-button" onclick="closeFamilyModal()" aria-label="Close family details">&times;</button>
    </div>

    <div class="modal-body family-management-body">
        <!-- Edit Family Details -->
        <form id="editFamilyForm" method="POST" class="family-edit-form">
            @csrf
            @method('PUT')
            <h4>Edit Family Details</h4>
            <div class="form-row family-edit-row">
                <label class="family-field">
                    <span>Family Name</span>
                    <input type="text" name="family_name" id="modalFamilyName" class="form-control" placeholder="Family Name" required>
                </label>
                <label class="family-field">
                    <span>Purok &amp; Subgroup</span>
                    <span class="family-select-trigger">
                        <input type="hidden" name="subgroup_id" id="modalSubgroupId" required>
                        <input type="text" id="modalSubgroupDisplay" class="form-control" placeholder="-- Select Purok / Subgroup --" readonly onclick="openSubgroupPicker('modal')" required aria-label="Select Purok or subgroup">
                        <svg viewBox="0 0 20 20" aria-hidden="true"><path d="m5 7 5 5 5-5"/></svg>
                    </span>
                </label>
                @if(auth()->user()->hasWriteAccess('family'))
                <button type="submit" class="btn btn-primary update-family-button">Update Details</button>
                @endif
            </div>
        </form>

        <hr class="family-modal-divider">

        <!-- Add Citizen to Family -->
        <section class="family-member-add-section">
            <div class="family-section-heading">
                <h4>Add Family Member</h4>
            </div>
            <form id="addMemberForm" method="POST">
                @csrf
                <div class="form-row add-member-row">
                    <input type="hidden" name="citizen_id" id="selectedCitizenId" required>
                    <label class="family-field member-select-field">
                        <span class="visually-hidden">Selected unassigned citizen</span>
                        <span class="family-select-trigger">
                            <input type="text" id="selectedCitizenDisplay" class="form-control" placeholder="-- Select Unassigned Citizen --" readonly onclick="openCitizenPicker()" aria-label="Select unassigned citizen">
                            <svg viewBox="0 0 20 20" aria-hidden="true"><path d="m5 7 5 5 5-5"/></svg>
                        </span>
                    </label>
                    @if(auth()->user()->hasWriteAccess('family'))
                    <button type="submit" class="btn btn-success add-member-button">Add Family</button>
                    @else
                    <span class="read-only-note">View only</span>
                    @endif
                </div>
            </form>
        </section>

        <hr class="family-modal-divider">

        <!-- Current Members -->
        <section class="family-current-members">
            <div class="family-section-heading">
                <h4>Current Members</h4>
                <span class="family-count-badge" id="modalMemberCount">0 member(s)</span>
            </div>
            <div class="modal-table-wrap">
                <table class="family-members-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Age</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="modalMembersList">
                        <tr><td colspan="3" class="empty-members-message">No members added to this family yet.</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
    <div class="modal-footer family-management-footer">
        <button type="button" class="btn btn-secondary" onclick="closeFamilyModal()">Close</button>
    </div>
</dialog>

<!-- SECONDARY CITIZEN PICKER MODAL (Paginated & Searchable) -->
<dialog id="citizenPickerModal" class="family-modal" style="max-width: 600px;">
    <div class="modal-header">
        <h3 style="margin: 0;">Select Unassigned Citizen</h3>
        <button type="button" onclick="closeCitizenPicker()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #7f8c8d;">&times;</button>
    </div>
    <div class="modal-body">
        <input type="text" id="citizenSearch" class="form-control" placeholder="Search by name..." style="width: 100%; margin-bottom: 15px;" oninput="filterCitizens()">
        
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Age</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody id="pickerTableBody">
                <!-- Populated via JS pagination -->
            </tbody>
        </table>

        <div class="pagination-controls">
            <button type="button" class="btn btn-secondary" id="prevPageBtn" onclick="changeCitizenPage(-1)">Previous</button>
            <span id="citizenPageInfo" style="font-size: 14px; color: #555;">Page 1 of 1</span>
            <button type="button" class="btn btn-secondary" id="nextPageBtn" onclick="changeCitizenPage(1)">Next</button>
        </div>
    </div>
</dialog>

<!-- SUBGROUP PICKER / PUROK BROWSER -->
<dialog id="subgroupPickerModal" class="family-modal subgroup-picker-modal" aria-labelledby="subgroupPickerTitle">
    <div class="modal-header">
        <h3 id="subgroupPickerTitle">Barangay Purok Subgroups</h3>
        <button type="button" class="modal-close-button" onclick="closeSubgroupPicker()" aria-label="Close purok list">&times;</button>
    </div>
    <div class="modal-body subgroup-picker-body">
        <p id="subgroupPickerDescription" class="subgroup-picker-description">Select a purok to view details or filter family registry records:</p>
        <input type="text" id="subgroupSearch" class="form-control subgroup-search-input" placeholder="Search purok or subgroup..." oninput="filterSubgroups()" aria-label="Search purok or subgroup">
        <div id="subgroupPickerList" class="subgroup-picker-list" aria-live="polite"></div>
        <div class="pagination-controls subgroup-pagination" id="subgroupPagination">
            <button type="button" class="btn btn-secondary" id="prevSubgroupPageBtn" onclick="changeSubgroupPage(-1)">Previous</button>
            <span id="subgroupPageInfo">Page 1 of 1</span>
            <button type="button" class="btn btn-secondary" id="nextSubgroupPageBtn" onclick="changeSubgroupPage(1)">Next</button>
        </div>
    </div>
    <div class="modal-footer subgroup-picker-footer">
        <button type="button" class="btn btn-link" id="resetFamilyFilterButton" onclick="resetFamilySubgroupFilter()">Reset Filter (Show All)</button>
        <button type="button" class="btn btn-secondary" onclick="closeSubgroupPicker()">Close</button>
    </div>
</dialog>

<!-- Modal Controller Script -->
<script>
    // Flatten puroks and subgroups into a clean searchable array for the picker
    const rawPuroks = {!! json_encode($puroks ?? []) !!};
    let flattenedSubgroups = [];
    rawPuroks.forEach(purok => {
        if (purok.subgroups && purok.subgroups.length > 0) {
            purok.subgroups.forEach(sub => {
                flattenedSubgroups.push({
                    id: sub.id,
                    purokName: purok.name,
                    subgroupName: sub.name,
                    displayName: `${purok.name} (${sub.name})`
                });
            });
        }
    });

    let currentSubgroupTarget = 'register'; // 'register' or 'modal'
    let currentSubgroupPage = 1;
    const subgroupPageSize = 10; // Paginate after 10 items
    let filteredSubgroups = [...flattenedSubgroups];

    // Citizen data arrays
    const allAvailableCitizens = {!! json_encode($availableCitizens ?? []) !!};
    let currentCitizenPage = 1;
    const citizenPageSize = 5;
    let filteredCitizens = [...allAvailableCitizens];

    function openFamilyModal(button) {
        const family = JSON.parse(button.getAttribute('data-family'));
        const modal = document.getElementById('familyModal');
        
        document.getElementById('modalTitle').textContent = `F-${String(family.id).padStart(4, '0')} · ${family.family_name}`;
        document.getElementById('modalMemberCount').textContent = `${(family.members || []).length} member(s)`;
        document.getElementById('modalFamilyName').value = family.family_name;
        
        // Set existing subgroup values for edit modal
        document.getElementById('modalSubgroupId').value = family.subgroup_id || '';
        const foundSub = flattenedSubgroups.find(s => s.id == family.subgroup_id);
        document.getElementById('modalSubgroupDisplay').value = foundSub ? foundSub.displayName : '';

        // Reset selected citizen inputs
        document.getElementById('selectedCitizenId').value = '';
        document.getElementById('selectedCitizenDisplay').value = '';

        document.getElementById('editFamilyForm').action = `/bhw/families/${family.id}`;
        document.getElementById('addMemberForm').action = `/bhw/families/${family.id}/members`;

        const membersListContainer = document.getElementById('modalMembersList');
        membersListContainer.innerHTML = '';

        if (family.members && family.members.length > 0) {
            family.members.forEach(member => {
                const tr = document.createElement('tr');
                const nameCell = document.createElement('td');
                nameCell.textContent = `${member.Citizen_FName} ${member.Citizen_LName}`;
                const ageCell = document.createElement('td');
                ageCell.textContent = member.Citizen_Age ?? 'N/A';
                const actionCell = document.createElement('td');
                const form = document.createElement('form');
                form.action = `/bhw/families/members/${member.id}`;
                form.method = 'POST';
                form.onsubmit = () => confirm('Remove citizen from family?');

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = @json(csrf_token());
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.append(csrfInput, methodInput);

                @if(auth()->user()->hasWriteAccess('family'))
                const removeButton = document.createElement('button');
                removeButton.type = 'submit';
                removeButton.className = 'btn-danger';
                removeButton.textContent = 'Remove';
                form.appendChild(removeButton);
                @else
                const readOnly = document.createElement('span');
                readOnly.style.cssText = 'color: #6b7280; font-size: 0.85rem;';
                readOnly.textContent = '-';
                form.appendChild(readOnly);
                @endif

                actionCell.appendChild(form);
                tr.append(nameCell, ageCell, actionCell);
                membersListContainer.appendChild(tr);
            });
        } else {
            membersListContainer.innerHTML = `
                <tr>
                    <td colspan="3" class="empty-members-message">No members added to this family yet.</td>
                </tr>
            `;
        }

        modal.showModal();
    }

    function closeFamilyModal() {
        document.getElementById('familyModal').close();
    }

    // Subgroup picker and the top-level Purok browser.
    function openSubgroupPicker(target) {
        currentSubgroupTarget = target;
        filteredSubgroups = [...flattenedSubgroups];
        currentSubgroupPage = 1;

        const isBrowseMode = target === 'browse';
        document.getElementById('subgroupPickerTitle').textContent = isBrowseMode
            ? 'Barangay Purok Subgroups'
            : 'Select Purok / Subgroup';
        document.getElementById('subgroupPickerDescription').textContent = isBrowseMode
            ? 'Select a purok to view details or filter family registry records:'
            : 'Choose a purok subgroup to assign to this family record:';
        document.getElementById('subgroupSearch').value = '';
        document.getElementById('subgroupSearch').hidden = isBrowseMode;
        document.getElementById('subgroupPagination').hidden = isBrowseMode;
        document.getElementById('resetFamilyFilterButton').hidden = !isBrowseMode;
        document.getElementById('subgroupPickerModal').classList.toggle('is-browse-mode', isBrowseMode);
        renderSubgroupPickerTable();
        document.getElementById('subgroupPickerModal').showModal();
    }

    function closeSubgroupPicker() {
        document.getElementById('subgroupPickerModal').close();
    }

    function filterSubgroups() {
        const query = document.getElementById('subgroupSearch').value.trim().toLowerCase();
        filteredSubgroups = flattenedSubgroups.filter(s => s.displayName.toLowerCase().includes(query));
        currentSubgroupPage = 1;
        renderSubgroupPickerTable();
    }

    function getSubgroupHouseholdCount(subgroupId) {
        return Array.from(document.querySelectorAll('.bhw-families-page .family-row'))
            .filter(row => String(row.dataset.subgroupId) === String(subgroupId)).length;
    }

    function renderSubgroupPickerTable() {
        const list = document.getElementById('subgroupPickerList');
        list.innerHTML = '';
        const isBrowseMode = currentSubgroupTarget === 'browse';
        const start = (currentSubgroupPage - 1) * subgroupPageSize;
        const items = isBrowseMode ? filteredSubgroups : filteredSubgroups.slice(start, start + subgroupPageSize);

        if (items.length) {
            items.forEach(item => {
                const option = document.createElement('button');
                option.type = 'button';
                option.className = 'subgroup-option';
                option.setAttribute('aria-label', `${item.displayName}, ${getSubgroupHouseholdCount(item.id)} households`);

                const name = document.createElement('span');
                name.className = 'subgroup-option-name';
                name.textContent = item.displayName;
                const count = document.createElement('span');
                count.className = 'subgroup-option-count';
                const householdCount = getSubgroupHouseholdCount(item.id);
                count.textContent = `${householdCount} household${householdCount === 1 ? '' : 's'}`;
                option.append(name, count);

                option.addEventListener('click', () => {
                    if (isBrowseMode) {
                        applyFamilySubgroupFilter(item.id, item.displayName);
                        closeSubgroupPicker();
                    } else {
                        selectSubgroup(item.id, item.displayName);
                    }
                });
                list.appendChild(option);
            });
        } else {
            const empty = document.createElement('p');
            empty.className = 'subgroup-empty-state';
            empty.textContent = 'No puroks or subgroups found.';
            list.appendChild(empty);
        }

        const totalPages = Math.ceil(filteredSubgroups.length / subgroupPageSize) || 1;
        document.getElementById('subgroupPageInfo').textContent = `Page ${currentSubgroupPage} of ${totalPages}`;
        document.getElementById('prevSubgroupPageBtn').disabled = currentSubgroupPage === 1;
        document.getElementById('nextSubgroupPageBtn').disabled = currentSubgroupPage >= totalPages;
        document.getElementById('subgroupPagination').hidden = isBrowseMode || filteredSubgroups.length <= subgroupPageSize;
    }

    function changeSubgroupPage(direction) {
        const totalPages = Math.ceil(filteredSubgroups.length / subgroupPageSize) || 1;
        currentSubgroupPage = Math.min(totalPages, Math.max(1, currentSubgroupPage + direction));
        renderSubgroupPickerTable();
    }

    function selectSubgroup(id, displayName) {
        if (currentSubgroupTarget === 'register') {
            document.getElementById('registerSubgroupId').value = id;
            document.getElementById('registerSubgroupDisplay').value = displayName;
        } else if (currentSubgroupTarget === 'modal') {
            document.getElementById('modalSubgroupId').value = id;
            document.getElementById('modalSubgroupDisplay').value = displayName;
        }
        closeSubgroupPicker();
    }

    let activeFamilySubgroupFilter = null;
    function applyFamilySubgroupFilter(subgroupId, displayName) {
        activeFamilySubgroupFilter = String(subgroupId);
        document.getElementById('familySearchInput').value = '';
        const notice = document.getElementById('familyFilterNotice');
        notice.textContent = `Filtered by ${displayName}`;
        notice.hidden = false;
        applyFamilyFilters();
    }

    function resetFamilySubgroupFilter() {
        activeFamilySubgroupFilter = null;
        const search = document.getElementById('familySearchInput');
        if (search) search.value = '';
        const notice = document.getElementById('familyFilterNotice');
        if (notice) notice.hidden = true;
        applyFamilyFilters();
        if (document.getElementById('subgroupPickerModal').open) closeSubgroupPicker();
    }

    function applyFamilyFilters() {
        const search = document.getElementById('familySearchInput');
        const query = search ? search.value.trim().toLowerCase() : '';
        document.querySelectorAll('.bhw-families-page .family-row').forEach(row => {
            const matchesSearch = row.textContent.toLowerCase().includes(query);
            const matchesSubgroup = activeFamilySubgroupFilter === null || String(row.dataset.subgroupId) === activeFamilySubgroupFilter;
            row.hidden = !(matchesSearch && matchesSubgroup);
        });
    }

    // Citizen Picker Controls
    function openCitizenPicker() {
        filteredCitizens = [...allAvailableCitizens];
        currentCitizenPage = 1;
        renderPickerTable();
        document.getElementById('citizenSearch').value = '';
        document.getElementById('citizenPickerModal').showModal();
    }

    function closeCitizenPicker() {
        document.getElementById('citizenPickerModal').close();
    }

    function filterCitizens() {
        const query = document.getElementById('citizenSearch').value.toLowerCase();
        filteredCitizens = allAvailableCitizens.filter(c => {
            const fullName = `${c.Citizen_FName} ${c.Citizen_LName}`.toLowerCase();
            return fullName.includes(query);
        });
        currentCitizenPage = 1;
        renderPickerTable();
    }

    function renderPickerTable() {
        const tbody = document.getElementById('pickerTableBody');
        tbody.innerHTML = '';

        const start = (currentCitizenPage - 1) * citizenPageSize;
        const end = start + citizenPageSize;
        const paginatedItems = filteredCitizens.slice(start, end);

        if (paginatedItems.length > 0) {
            paginatedItems.forEach(citizen => {
                const tr = document.createElement('tr');
                const nameCell = document.createElement('td');
                const fullName = `${citizen.Citizen_FName} ${citizen.Citizen_LName}`;
                nameCell.textContent = fullName;
                const ageCell = document.createElement('td');
                ageCell.textContent = citizen.Citizen_Age ?? 'N/A';
                const actionCell = document.createElement('td');
                actionCell.style.textAlign = 'right';
                const selectButton = document.createElement('button');
                selectButton.type = 'button';
                selectButton.className = 'btn btn-sm';
                selectButton.textContent = 'Select';
                selectButton.addEventListener('click', () => selectCitizen(citizen.id, fullName));
                actionCell.appendChild(selectButton);
                tr.append(nameCell, ageCell, actionCell);
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="3" style="text-align: center; color: #7f8c8d; font-style: italic;">No citizens found.</td>
                </tr>
            `;
        }

        const totalPages = Math.ceil(filteredCitizens.length / citizenPageSize) || 1;
        document.getElementById('citizenPageInfo').textContent = `Page ${currentCitizenPage} of ${totalPages}`;
        document.getElementById('prevPageBtn').disabled = currentCitizenPage === 1;
        document.getElementById('nextPageBtn').disabled = currentCitizenPage >= totalPages;
    }

    function changeCitizenPage(direction) {
        const totalPages = Math.ceil(filteredCitizens.length / citizenPageSize) || 1;
        currentCitizenPage += direction;
        if (currentCitizenPage < 1) currentCitizenPage = 1;
        if (currentCitizenPage > totalPages) currentCitizenPage = totalPages;
        renderPickerTable();
    }

    function selectCitizen(id, fullName) {
        document.getElementById('selectedCitizenId').value = id;
        document.getElementById('selectedCitizenDisplay').value = fullName;
        closeCitizenPicker();
    }
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const search = document.getElementById('familySearchInput');
    if (search) search.addEventListener('input', applyFamilyFilters);

    // Flash success messages as a brief popup toast instead of a page banner.
    const toast = document.getElementById('familyPageAlert');
    if (toast) {
        window.setTimeout(function () {
            if (!toast.isConnected) return;
            toast.classList.add('is-hiding');
            window.setTimeout(function () {
                if (toast.isConnected) toast.remove();
            }, 220);
        }, 4500);
    }
});
</script>
@endsection