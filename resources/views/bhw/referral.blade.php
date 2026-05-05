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
        <button class="btn-primary">+ Create Referral</button>
    </div>

    <div class="summary">
        <div class="summary-card">
            <span>Total Referrals</span>
            <strong>45</strong>
        </div>
        <div class="summary-card">
            <span>Pending</span>
            <strong>12</strong>
        </div>
        <div class="summary-card">
            <span>Approved</span>
            <strong>18</strong>
        </div>
        <div class="summary-card">
            <span>Completed</span>
            <strong>15</strong>
        </div>
    </div>

    <div class="toolbar">
        <input type="text" placeholder="Search citizen or hospital">
        <select>
            <option>All Status</option>
            <option>Pending</option>
            <option>Approved</option>
            <option>Completed</option>
            <option>Rejected</option>
        </select>
        <select>
            <option>All Priority</option>
            <option>High</option>
            <option>Medium</option>
            <option>Low</option>
        </select>
    </div>

    <div class="table-container">
        <table>
            <tr>
                <th>Citizen</th>
                <th>Hospital</th>
                <th>Reason</th>
                <th>Priority</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>

            <tr>
                <td>Maria Santos</td>
                <td>Dasma Hospital</td>
                <td>Severe Fever</td>
                <td class="priority-high">High</td>
                <td>May 20, 2026</td>
                <td><span class="badge pending">Pending</span></td>
                <td>
                    <div class="action-group">
                        <button class="btn-primary">Update</button>
                        <button class="btn-secondary">View</button>
                        <button class="btn-danger">Cancel</button>
                    </div>
                </td>
            </tr>

            <tr>
                <td>Juan Dela Cruz</td>
                <td>General Trias Medical Center</td>
                <td>Hypertension</td>
                <td class="priority-medium">Medium</td>
                <td>May 18, 2026</td>
                <td><span class="badge approved">Approved</span></td>
                <td>
                    <div class="action-group">
                        <button class="btn-primary">Update</button>
                        <button class="btn-secondary">View</button>
                    </div>
                </td>
            </tr>

            <tr>
                <td>Ana Reyes</td>
                <td>Tagaytay Medical Center</td>
                <td>Pregnancy Checkup</td>
                <td class="priority-low">Low</td>
                <td>May 15, 2026</td>
                <td><span class="badge completed">Completed</span></td>
                <td>
                    <div class="action-group">
                        <button class="btn-secondary">View</button>
                    </div>
                </td>
            </tr>

            <tr>
                <td>Pedro Villanueva</td>
                <td>Dasma Hospital</td>
                <td>Accident Injury</td>
                <td class="priority-high">High</td>
                <td>May 12, 2026</td>
                <td><span class="badge rejected">Rejected</span></td>
                <td>
                    <div class="action-group">
                        <button class="btn-secondary">View</button>
                    </div>
                </td>
            </tr>

        </table>
    </div>

@endsection