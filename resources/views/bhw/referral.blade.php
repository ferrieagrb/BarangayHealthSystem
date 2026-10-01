
@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/referral.css') }}">
@endsection

@section('content')

<div class="page-top">
    <div class="page-title">
        <h1>Referral Management</h1>
        <p>Manage and track patient referrals to hospitals and external healthcare providers.</p>
    </div>

    <button type="button" class="btn-primary add-referral-btn">
        + Create Referral
    </button>
</div>


<!-- SUMMARY CARDS -->

<div class="summary">

    <div class="summary-card">
        <span>Total Referrals</span>
        <strong>45</strong>
        <p>Total number of patient referrals recorded.</p>
    </div>

    <div class="summary-card">
        <span>Pending</span>
        <strong>12</strong>
        <p>Referrals awaiting review or approval.</p>
    </div>

    <div class="summary-card">
        <span>Approved</span>
        <strong>18</strong>
        <p>Referrals approved for healthcare assistance.</p>
    </div>

    <div class="summary-card">
        <span>Completed</span>
        <strong>15</strong>
        <p>Referrals successfully completed.</p>
    </div>

</div>


<!-- FILTER TOOLBAR -->

<div class="toolbar">

    <div class="referral-filters">

        <input
            type="text"
            placeholder="Search citizen or hospital"
        >

        <select>
            <option>All Status</option>
            <option>Pending</option>
            <option>Approved</option>
            <option>Completed</option>
            <option>Returned</option>
            <option>Rejected</option>
        </select>

        <select>
            <option>All Priority</option>
            <option>High</option>
            <option>Medium</option>
            <option>Low</option>
        </select>

        <button type="button" class="btn-primary search-btn">
            Search
        </button>

    </div>

</div>


<!-- REFERRAL TABLE -->

<div class="table-container">

    <div class="table-header">
        <div>
            <h2>Referral Records</h2>
            <p>View and manage patient referrals and their current status.</p>
        </div>
    </div>

    <div class="table-scroll">

        <table>

            <thead>
                <tr>
                    <th>Citizen</th>
                    <th>Hospital</th>
                    <th>Reason</th>
                    <th>Priority</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

                <!-- PENDING -->

                <tr>
                    <td>
                        <div class="citizen-name">Maria Santos</div>
                    </td>

                    <td>Dasma Hospital</td>
                    <td>Severe Fever</td>

                    <td>
                        <span class="priority priority-high">High</span>
                    </td>

                    <td>May 20, 2026</td>

                    <td>
                        <span class="badge pending">Pending</span>
                    </td>

                    <td>
                        <div class="action-group">
                            <button type="button" class="btn-primary">
                                Update
                            </button>

                            <button type="button" class="btn-secondary">
                                View
                            </button>

                            <button type="button" class="btn-danger">
                                Cancel
                            </button>
                        </div>
                    </td>
                </tr>


                <!-- APPROVED -->

                <tr>
                    <td>
                        <div class="citizen-name">Juan Dela Cruz</div>
                    </td>

                    <td>General Trias Medical Center</td>
                    <td>Hypertension</td>

                    <td>
                        <span class="priority priority-medium">Medium</span>
                    </td>

                    <td>May 18, 2026</td>

                    <td>
                        <span class="badge approved">Approved</span>
                    </td>

                    <td>
                        <div class="action-group">
                            <button type="button" class="btn-primary">
                                Update
                            </button>

                            <button type="button" class="btn-secondary">
                                View
                            </button>
                        </div>
                    </td>
                </tr>


                <!-- COMPLETED -->

                <tr>
                    <td>
                        <div class="citizen-name">Ana Reyes</div>
                    </td>

                    <td>Tagaytay Medical Center</td>
                    <td>Pregnancy Checkup</td>

                    <td>
                        <span class="priority priority-low">Low</span>
                    </td>

                    <td>May 15, 2026</td>

                    <td>
                        <span class="badge completed">Completed</span>
                    </td>

                    <td>
                        <div class="action-group">
                            <button type="button" class="btn-secondary">
                                View
                            </button>
                        </div>
                    </td>
                </tr>


                <!-- REJECTED -->

                <tr>
                    <td>
                        <div class="citizen-name">Pedro Villanueva</div>
                    </td>

                    <td>Dasma Hospital</td>
                    <td>Accident Injury</td>

                    <td>
                        <span class="priority priority-high">High</span>
                    </td>

                    <td>May 12, 2026</td>

                    <td>
                        <span class="badge rejected">Rejected</span>
                    </td>

                    <td>
                        <div class="action-group">
                            <button type="button" class="btn-secondary">
                                View
                            </button>
                        </div>
                    </td>
                </tr>


                <!-- RETURNED -->

                <tr>
                    <td>
                        <div class="citizen-name">Rosa Garcia</div>
                    </td>

                    <td>Amadeo District Hospital</td>
                    <td>Medical Assessment</td>

                    <td>
                        <span class="priority priority-medium">Medium</span>
                    </td>

                    <td>May 10, 2026</td>

                    <td>
                        <span class="badge returned">Returned</span>
                    </td>

                    <td>
                        <div class="action-group">
                            <button type="button" class="btn-primary">
                                Update
                            </button>

                            <button type="button" class="btn-secondary">
                                View
                            </button>
                        </div>
                    </td>
                </tr>

            </tbody>

        </table>

    </div>


    <!-- TABLE FOOTER -->

    <div class="table-footer">

        <span>Showing 1 to 5 of 45 referrals</span>

        <div class="pagination-buttons">

            <button type="button" class="pagination-btn">
                ‹
            </button>

            <button type="button" class="pagination-btn active">
                1
            </button>

            <button type="button" class="pagination-btn">
                2
            </button>

            <button type="button" class="pagination-btn">
                3
            </button>

            <button type="button" class="pagination-btn">
                ›
            </button>

        </div>

    </div>

</div>

@endsection