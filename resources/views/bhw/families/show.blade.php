@extends('templates.layout')

@section('CSSown')
<style>
    .family-container { max-width: 900px; margin: 40px auto; padding: 0 20px; font-family: Arial, sans-serif; color: #333; }
    .card { background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 20px; }
    .form-row { display: flex; gap: 12px; margin-bottom: 12px; flex-wrap: wrap; align-items: center; }
    .form-control { flex: 1; min-width: 200px; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
    .btn { padding: 10px 16px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; }
    .btn-primary { background: #3498db; color: #fff; }
    .btn-secondary { background: #95a5a6; color: #fff; }
    .btn-danger { background: #e74c3c; color: #fff; padding: 6px 12px; font-size: 12px; border-radius: 4px; }
    .btn-sm { padding: 6px 12px; font-size: 12px; background: #2ecc71; color: #fff; text-decoration: none; border-radius: 4px; cursor: pointer; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px; }
    th { background: #f8f9fa; color: #2c3e50; }

    /* Modal Styling - Centered */
    dialog.family-modal {
        border: none;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        width: 100%;
        max-width: 650px;
        padding: 0;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        margin: 0;
    }
    dialog.family-modal::backdrop {
        background: rgba(0, 0, 0, 0.5);
    }
    .modal-header {
        background: #f8f9fa;
        padding: 16px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #eee;
    }
    .modal-body {
        padding: 24px;
        max-height: 80vh;
        overflow-y: auto;
    }
    .pagination-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 15px;
    }
</style>
@endsection

@section('content')
<div class="family-container">
    @if(session('success'))
        <div style="background:#d4edda; color:#155724; padding:12px; border-radius:4px; margin-bottom:15px;">
            {{ session('success') }}
        </div>
    @endif

    <h1>Family Registry & Household Management</h1>

    <!-- Register Family Form -->
    @if(auth()->user()->hasWriteAccess('family'))
    <div class="card">
        <h2>Register New Family</h2>
        <form action="{{ route('bhw.families.store') }}" method="POST">
            @csrf
            <div class="form-row">
                <input type="text" name="family_name" placeholder="Family Name (e.g., Santos Family)" class="form-control" required>
                
                <!-- Purok Picker Input Trigger -->
                <input type="hidden" name="subgroup_id" id="registerSubgroupId" required>
                <input type="text" id="registerSubgroupDisplay" class="form-control" placeholder="-- Select Purok / Subgroup --" readonly style="background: #f9f9f9; cursor: pointer;" onclick="openSubgroupPicker('register')" required>
                <button type="button" class="btn btn-secondary" onclick="openSubgroupPicker('register')">Browse Puroks</button>
            </div>
            <button type="submit" class="btn btn-primary">Create Family Record</button>
        </form>
    </div>
    @endif

    <!-- Registered Families Table -->
    <div class="card">
        <h2>Registered Families</h2>
        <table>
            <thead>
                <tr>
                    <th>Family Name</th>
                    <th>Purok & Subgroup</th>
                    <th>Members Count</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($families as $fam)
                    <tr>
                        <td><strong>{{ $fam->family_name }}</strong></td>
                        <td>
                            {{ $fam->purok->name ?? 'N/A' }} 
                            @if($fam->subgroup)
                                <span style="color: #666; font-size: 13px;">({{ $fam->subgroup->name }})</span>
                            @endif
                        </td>
                        <td>{{ $fam->members->count() }} citizen(s)</td>
                        <td>
                            @if(auth()->user()->hasWriteAccess('family'))
                            <button type="button" class="btn-sm" data-family='{!! json_encode($fam) !!}' onclick="openFamilyModal(this)">Manage / Edit</button>
                            @else
                            <button type="button" class="btn-sm" data-family='{!! json_encode($fam) !!}' onclick="openFamilyModal(this)">View</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #7f8c8d; font-style: italic;">No families registered yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- MAIN FAMILY MANAGEMENT MODAL -->
<dialog id="familyModal" class="family-modal">
    <div class="modal-header">
        <h3 id="modalTitle" style="margin: 0; color: #2c3e50;">Manage Family</h3>
        <button type="button" onclick="closeFamilyModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #7f8c8d;">&times;</button>
    </div>
    
    <div class="modal-body">
        <!-- 1. Edit Family Purok / Subgroup Form -->
        <form id="editFamilyForm" method="POST">
            @csrf
            @method('PUT')
            <h4 style="margin-top: 0; color: #2c3e50;">Edit Family Details</h4>
            <div class="form-row">
                <input type="text" name="family_name" id="modalFamilyName" class="form-control" placeholder="Family Name" required>
                
                <!-- Edit Modal Purok Picker Input Trigger -->
                <input type="hidden" name="subgroup_id" id="modalSubgroupId" required>
                <input type="text" id="modalSubgroupDisplay" class="form-control" placeholder="-- Select Purok / Subgroup --" readonly style="background: #f9f9f9; cursor: pointer;" onclick="openSubgroupPicker('modal')" required>
                @if(auth()->user()->hasWriteAccess('family'))
                <button type="button" class="btn btn-secondary" onclick="openSubgroupPicker('modal')">Browse</button>
                @endif
            </div>
            @if(auth()->user()->hasWriteAccess('family'))
            <button type="submit" class="btn btn-primary" style="margin-bottom: 20px;">Update Details</button>
            @endif
        </form>

        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

        <!-- 2. Add Citizen to Family Form using Paginated Modal Picker -->
        <h4 style="color: #2c3e50;">Add Citizen Member</h4>
        <form id="addMemberForm" method="POST">
            @csrf
            <div class="form-row">
                <input type="hidden" name="citizen_id" id="selectedCitizenId" required>
                <input type="text" id="selectedCitizenDisplay" class="form-control" placeholder="-- Select Unassigned Citizen --" readonly style="background: #f9f9f9; cursor: pointer;" onclick="openCitizenPicker()">
                @if(auth()->user()->hasWriteAccess('supplies'))
                <button type="button" class="btn btn-secondary" onclick="openCitizenPicker()">Browse Citizens</button>
                <button type="submit" class="btn btn-primary">Add Member</button>
                @else
                <span style="color: #6b7280; font-size: 0.85rem;">-</span>
                @endif
            </div>
        </form>

        <!-- 3. Current Members Table inside Modal -->
        <h4 style="margin-top: 25px; color: #2c3e50;">Current Members</h4>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Age</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="modalMembersList">
                <!-- Populated dynamically via JavaScript -->
            </tbody>
        </table>
    </div>
</dialog>

<!-- SECONDARY CITIZEN PICKER MODAL (Paginated & Searchable) -->
<dialog id="citizenPickerModal" class="family-modal" style="max-width: 600px;">
    <div class="modal-header">
        <h3 style="margin: 0; color: #2c3e50;">Select Unassigned Citizen</h3>
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

<!-- SECONDARY SUBGROUP PICKER MODAL (Paginated & Searchable) -->
<dialog id="subgroupPickerModal" class="family-modal" style="max-width: 600px;">
    <div class="modal-header">
        <h3 style="margin: 0; color: #2c3e50;">Select Purok / Subgroup</h3>
        <button type="button" onclick="closeSubgroupPicker()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #7f8c8d;">&times;</button>
    </div>
    <div class="modal-body">
        <input type="text" id="subgroupSearch" class="form-control" placeholder="Search purok or subgroup..." style="width: 100%; margin-bottom: 15px;" oninput="filterSubgroups()">
        
        <table>
            <thead>
                <tr>
                    <th>Purok Name</th>
                    <th>Subgroup Name</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody id="subgroupPickerTableBody">
                <!-- Populated via JS pagination -->
            </tbody>
        </table>

        <div class="pagination-controls">
            <button type="button" class="btn btn-secondary" id="prevSubgroupPageBtn" onclick="changeSubgroupPage(-1)">Previous</button>
            <span id="subgroupPageInfo" style="font-size: 14px; color: #555;">Page 1 of 1</span>
            <button type="button" class="btn btn-secondary" id="nextSubgroupPageBtn" onclick="changeSubgroupPage(1)">Next</button>
        </div>
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
                    displayName: `${purok.name} → ${sub.name}`
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
        
        document.getElementById('modalTitle').textContent = `Manage: ${family.family_name}`;
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
                tr.innerHTML = `
                    <td>${member.Citizen_FName} ${member.Citizen_LName}</td>
                    <td>${member.Citizen_Age ?? 'N/A'}</td>
                    <td>
                        <form action="/bhw/families/members/${member.id}" method="POST" onsubmit="return confirm('Remove citizen from family?')">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="_method" value="DELETE">
                            @if(auth()->user()->hasWriteAccess('family'))
                            <button type="submit" class="btn-danger">Remove</button>
                            @else
                            <span style="color: #6b7280; font-size: 0.85rem;">-</span>
                            @endif
                        </form>
                    </td>
                `;
                membersListContainer.appendChild(tr);
            });
        } else {
            membersListContainer.innerHTML = `
                <tr>
                    <td colspan="3" style="text-align: center; color: #7f8c8d; font-style: italic;">No members added to this family yet.</td>
                </tr>
            `;
        }

        modal.showModal();
    }

    function closeFamilyModal() {
        document.getElementById('familyModal').close();
    }

    // Subgroup Picker Controls
    function openSubgroupPicker(target) {
        currentSubgroupTarget = target;
        filteredSubgroups = [...flattenedSubgroups];
        currentSubgroupPage = 1;
        renderSubgroupPickerTable();
        document.getElementById('subgroupSearch').value = '';
        document.getElementById('subgroupPickerModal').showModal();
    }

    function closeSubgroupPicker() {
        document.getElementById('subgroupPickerModal').close();
    }

    function filterSubgroups() {
        const query = document.getElementById('subgroupSearch').value.toLowerCase();
        filteredSubgroups = flattenedSubgroups.filter(s => {
            return s.displayName.toLowerCase().includes(query);
        });
        currentSubgroupPage = 1;
        renderSubgroupPickerTable();
    }

    function renderSubgroupPickerTable() {
        const tbody = document.getElementById('subgroupPickerTableBody');
        tbody.innerHTML = '';

        const start = (currentSubgroupPage - 1) * subgroupPageSize;
        const end = start + subgroupPageSize;
        const paginatedItems = filteredSubgroups.slice(start, end);

        if (paginatedItems.length > 0) {
            paginatedItems.forEach(item => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${item.purokName}</td>
                    <td>${item.subgroupName}</td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-sm" onclick="selectSubgroup(${item.id}, '${item.displayName}')">Select</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="3" style="text-align: center; color: #7f8c8d; font-style: italic;">No puroks or subgroups found.</td>
                </tr>
            `;
        }

        const totalPages = Math.ceil(filteredSubgroups.length / subgroupPageSize) || 1;
        document.getElementById('subgroupPageInfo').textContent = `Page ${currentSubgroupPage} of ${totalPages}`;
        document.getElementById('prevSubgroupPageBtn').disabled = currentSubgroupPage === 1;
        document.getElementById('nextSubgroupPageBtn').disabled = currentSubgroupPage >= totalPages;
    }

    function changeSubgroupPage(direction) {
        const totalPages = Math.ceil(filteredSubgroups.length / subgroupPageSize) || 1;
        currentSubgroupPage += direction;
        if (currentSubgroupPage < 1) currentSubgroupPage = 1;
        if (currentSubgroupPage > totalPages) currentSubgroupPage = totalPages;
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
                tr.innerHTML = `
                    <td>${citizen.Citizen_FName} ${citizen.Citizen_LName}</td>
                    <td>${citizen.Citizen_Age ?? 'N/A'}</td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-sm" onclick="selectCitizen(${citizen.id}, '${citizen.Citizen_FName} ${citizen.Citizen_LName}')">Select</button>
                    </td>
                `;
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
@endsection