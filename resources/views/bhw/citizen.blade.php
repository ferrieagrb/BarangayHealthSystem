@extends('templates.layout')

@section('content')

{{-- PAGE TOP HEADER --}}
<div class="page-top">
    <div class="page-title-group">
        <h1>Citizen Management</h1>
        <p>Manage resident profiles, monitor status, and update citizen health-related information.</p>
    </div>
    <a href="{{ route('citizen.add') }}" class="btn-primary">+ Add Citizen</a>
</div>

<<<<<<< HEAD
{{-- SUMMARY CARDS --}}
<div class="summary-cards">
    <div class="summary-card">
        <span>Total Citizens</span>
        <strong>{{ $totalCitizens }}</strong>
=======
   <div class="summary-cards-wrapper" style="dsplay: grid; grid-template-columns: 1fr 2fr;gap:20px;margin-bottom:25px;">
    
    <!-- Total Citizens Summary Crads -->

    <div class="summary-card" style="background:#fff; padding: 25px; border-radius:8px; box-shadow:0 2px 4px rgba(0,0,0,0.05); display:flex; flex-direction:column; justify-content:center; align-items:center; text-align:center">
        <span style="font-size:14px; color:#64748b; font-weight: 500; text-transform:uppercase; letter-spacing: 0.5px;"> Total Citizens </span>
        <strong style="font-size: 38px; color: #1e293b; margin-top:10px;"> {{$totalCitizens}} </strong>
>>>>>>> main
    </div>
    <div class="summary-card">
        <span>Kids</span>
        <strong>{{ $kids }}</strong>
    </div>
    <div class="summary-card">
        <span>Adults</span>
        <strong>{{ $adults }}</strong>
    </div>
    <div class="summary-card">
        <span>Seniors</span>
        <strong>{{ $seniors }}</strong>
    </div>
</div>

<<<<<<< HEAD
{{-- TOOLBAR (SEARCH & FILTERS) --}}
<div class="toolbar">
    <div class="toolbar-left" style="display: flex; gap: 10px; flex-wrap: wrap; flex: 1;">
        <div class="search-box">
            <form method="GET" action="{{ route('citizenlist') }}" id="filterForm">
                <input type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search citizen name or ID"
                    oninput="this.form.submit()">
=======
    <!-- Demograpphics ApexChart Card -->

    <div class="chart-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 5px;">
            <h3 style="font-size:15px; color:#1e293b; font-weight: 600; margin:0;"> Demographics Breakdown </h3>
        </div>
        
        <div id="demographicsApexChart"></div>

    </div>
    </div>


    <div class="toolbar">
        <div class="toolbar-left">
            <div class="search-box">
                <form method="GET" action="{{ route('citizenlist') }}" id="filterForm">
                    <input type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search citizen name or ID"
                        oninput="this.form.submit()">
                </form>
            </div>
            <div class="filter-box">
                <form method="GET" action="{{ route('citizenlist') }}">
                    <select name="purok" onchange="this.form.submit()">
                        <option value="all">All Purok</option>
                        <option value="Purok 1" {{ request('purok')=='Purok 1'?'selected':'' }}>Purok 1</option>
                        <option value="Purok 2" {{ request('purok')=='Purok 2'?'selected':'' }}>Purok 2</option>
                        <option value="Purok 3" {{ request('purok')=='Purok 3'?'selected':'' }}>Purok 3</option>
                    </select>
                </form>
            </div>
            <div class="filter-box">
                <select>
                    <option>All Status</option>
                    <option>Active</option>
                    <option>Under Monitoring</option>
                </select>
            </div>
        </div>

        <!-- RIGHT TOOLBAR: EXPORT & IMPORT BUTTONS -->
        <div style="display: flex; gap: 10px; align-items: center;">
            <button class="btn-secondary">Export List</button>

            <!-- Hidden File Form for Import -->
            <form action="{{ route('citizen.import') }}" method="POST" enctype="multipart/form-data" id="importForm" style="display: none;">
                @csrf
                <input type="file" name="file" id="excelFileInput" accept=".csv, .xlsx, .xls" onchange="document.getElementById('importForm').submit()">
>>>>>>> main
            </form>
        </div>
        <div class="filter-box">
            <form method="GET" action="{{ route('citizenlist') }}">
                <select name="purok" onchange="this.form.submit()">
                    <option value="all">All Purok</option>
                    <option value="Purok 1" {{ request('purok')=='Purok 1'?'selected':'' }}>Purok 1</option>
                    <option value="Purok 2" {{ request('purok')=='Purok 2'?'selected':'' }}>Purok 2</option>
                    <option value="Purok 3" {{ request('purok')=='Purok 3'?'selected':'' }}>Purok 3</option>
                </select>
            </form>
        </div>
        <div class="filter-box">
            <select>
                <option>All Status</option>
                <option>Active</option>
                <option>Under Monitoring</option>
            </select>
        </div>
    </div>

    {{-- EXPORT & IMPORT BUTTONS --}}
    <div style="display: flex; gap: 10px; align-items: center;">
        <button type="button" class="btn-secondary">Export List</button>

        {{-- Hidden Form para sa File Import --}}
        <form action="{{ route('citizen.import') }}" method="POST" enctype="multipart/form-data" id="importForm" style="display: none;">
            @csrf
            <input type="file" name="file" id="excelFileInput" accept=".csv, .xlsx, .xls" onchange="document.getElementById('importForm').submit()">
        </form>

        <button type="button" class="btn-secondary" onclick="document.getElementById('excelFileInput').click();">
            Import Excel
        </button>
    </div>
</div>

{{-- TABLE CONTAINER --}}
<div class="table-container">
    <div class="card-header" style="margin-bottom: 20px;">
        <h2>Citizen Records</h2>
        <p>Showing registered barangay residents and their current profile status.</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Name / ID</th>
                <th>Age</th>
                <th>Birth Date</th>
                <th>Contact</th>
                <th>Purok</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($citizens as $citizen)
                <tr>
                    <td>
                        {{-- Inayos ang classes dito para tumugma sa global CSS --}}
                        <span class="record-name">
                            {{ $citizen->Citizen_FName }} {{ $citizen->Citizen_LName }}
                        </span>
                        <span class="record-sub">
                            ID: C-{{ str_pad($citizen->id, 4, '0', STR_PAD_LEFT) }}
                        </span>
                    </td>

                    <td>{{ $citizen->Citizen_Age }}</td>
                    <td>{{ $citizen->Citizen_BirthDate }}</td>
                    <td>{{ $citizen->Citizen_ContactNo }}</td>
                    <td>{{ $citizen->Citizen_Purok }}</td>

                    <td>
                        <div class="action-group">
                            <a href="{{ route('citizendetails', $citizen->id) }}" class="btn-primary">
                                View
                            </a>

<<<<<<< HEAD
                            <form action="{{ route('citizen.delete', $citizen->id) }}" method="POST" onsubmit="return confirm('Delete this citizen?')">
                                @csrf
                                @method('DELETE')
=======
        <a href="{{ route('bhw.citizen.ecard', $citizen->id) }}" class="btn-primary">
            Manage E-Card
        </a>

        <form action="{{ route('citizen.delete', $citizen->id) }}" method="POST" onsubmit="return confirm('Delete this citizen?')">
            @csrf
            @method('DELETE')
>>>>>>> main

                                <button type="submit" class="btn-danger">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="empty-text" style="text-align: center; padding: 20px;">
                        No citizen records found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- PAGINATION AREA --}}
    <div style="margin-top: 15px;">
        {{ $citizens->links('pagination::bootstrap-5') }}
    </div>
</div>

    <!-- ApexCharts CDN & Initialization -->

    <script src = "https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        var options = {
            series:[{
                name: 'Count',
                data:[{{$kids}},{{$adults}},{{$seniors}}]
            }],

            chart:{
                type:'bar',
                height:160,
                toolbar:{
                    show:false
                }
            },
            plotOptions:{
                bar:{
                    boraderRadius:4,
                    dstributed:true,
                    columnWidth:'45%',
                }
            },
            color:['#3b82f6','#14b8a6','#f97316'],// Kids(Blue), Adults(Teal), Seniors(Orange)
            dataLabels:{
                enabled:false
            },
            legend:{
                show:false
            },
            xaxis:{
                categories:['Kids','Adults','Seniors'],
                axisBorder:{
                    show:false
                },
                axisTicks:{
                    show:false
                }
            },
            yaxis:{
                tickAmount:3,
                labels:{
                    formatter:function (val){
                        return Math.floor(val);
                    }
                }
            },
            grid:{
                strokeDashArray:4,
                xaxis:{
                    lines:{
                        show:false

                    }
                }
            }
        }

        var chart = new ApexCharts(document.querySelector("#demographicsApexChart"), options); chart.render();
    </script>


@endsection