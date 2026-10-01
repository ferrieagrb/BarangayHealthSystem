@extends('templates.superadmin')

@section('content')
<div class="page-top" style="margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1>System Settings</h1>
        <p style="color: #6b7280; font-size: 14px;">Manage global application behavior, branding, security policies, and automation.</p>
    </div>
    <!-- Live Status Pill with Smooth Fade Animation -->
    <div id="save-status-badge" class="badge-hidden" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(37, 99, 235, 0.05); transition: all 0.3s ease; opacity: 0; transform: translateY(-5px);">
        <span style="width: 8px; height: 8px; background: #2563eb; border-radius: 50%; display: inline-block; animation: pulse 1.5s infinite;"></span> Unsaved Changes
    </div>
</div>

<form action="#" method="POST" enctype="multipart/form-data" id="settings-form">
    @csrf

    <div style="display: flex; flex-direction: column; gap: 20px;">

        <!-- 1. GENERAL & BRANDING -->
        <div class="setting-card">
            <h3 style="margin-bottom: 15px; font-size: 16px; color: #1e3a8a; border-bottom: 2px solid #eff6ff; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                <span style="color: #2563eb;">🎨</span> General & Branding
            </h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 5px;">System Application Name</label>
                    <input type="text" name="app_name" value="BHAKAS Health System" class="interactive-input" style="width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 5px;">Default Timezone</label>
                    <select name="timezone" class="interactive-input" style="width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: white; outline: none;">
                        <option value="Asia/Manila" selected>Asia/Manila (GMT+8)</option>
                        <option value="UTC">UTC</option>
                    </select>
                </div>
            </div>

            <!-- Upload Assets Row -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; padding-top: 15px; border-top: 1px solid #f3f4f6;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 5px;">Website Logo</label>
                    <input type="file" name="app_logo" accept="image/*" class="interactive-input" style="width: 100%; font-size: 13px; color: #4b5563;">
                    <span style="display: block; font-size: 11px; color: #6b7280; margin-top: 3px;">Recommended: PNG or SVG (Max 2MB)</span>
                </div>
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 5px;">Website Favicon / Icon</label>
                    <input type="file" name="app_icon" accept="image/png, image/x-icon" class="interactive-input" style="width: 100%; font-size: 13px; color: #4b5563;">
                    <span style="display: block; font-size: 11px; color: #6b7280; margin-top: 3px;">Recommended: 32x32px ICO or PNG</span>
                </div>
            </div>
        </div>

        <!-- 2. USER PREFERENCES & EXPERIENCE BOX -->
        <div class="setting-card">
            <h3 style="margin-bottom: 15px; font-size: 16px; color: #1e3a8a; border-bottom: 2px solid #eff6ff; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                <span style="color: #2563eb;">👤</span> User Preferences & Experience
            </h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <!-- Allow Dark Mode -->
                <div style="display: flex; align-items: center; justify-content: space-between; background: #f9fafb; padding: 12px 16px; border: 1px solid #e5e7eb; border-radius: 8px;">
                    <div>
                        <strong style="font-size: 13px; color: #374151; display: block;">Allow User Dark Mode</strong>
                        <span style="font-size: 11px; color: #6b7280;">Let users toggle a dark theme interface.</span>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="allow_dark_mode" checked>
                        <span class="slider"></span>
                    </label>
                </div>

                <!-- Force Password Change on Next Login -->
                <div style="display: flex; align-items: center; justify-content: space-between; background: #f9fafb; padding: 12px 16px; border: 1px solid #e5e7eb; border-radius: 8px;">
                    <div>
                        <strong style="font-size: 13px; color: #374151; display: block;">Force Password Change</strong>
                        <span style="font-size: 11px; color: #6b7280;">Require users to update password on next login.</span>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="force_password_change">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>

            <!-- Additional UX Controls Row -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; padding-top: 15px; border-top: 1px solid #f3f4f6;">
                <div style="display: flex; align-items: center; justify-content: space-between; background: #f9fafb; padding: 12px 16px; border: 1px solid #e5e7eb; border-radius: 8px;">
                    <div>
                        <strong style="font-size: 13px; color: #374151; display: block;">Enable Profile Bio Editing</strong>
                        <span style="font-size: 11px; color: #6b7280;">Allow standard users to edit profile info.</span>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="allow_profile_editing" checked>
                        <span class="slider"></span>
                    </label>
                </div>
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 5px;">Idle Session Warning Prompt</label>
                    <select name="idle_warning_time" class="interactive-input" style="width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: white; outline: none;">
                        <option value="5">Warn 5 minutes before timeout</option>
                        <option value="10" selected>Warn 10 minutes before timeout</option>
                        <option value="disabled">Disabled</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 3. INVENTORY & THRESHOLD CONTROLS -->
        <div class="setting-card">
            <h3 style="margin-bottom: 15px; font-size: 16px; color: #1e3a8a; border-bottom: 2px solid #eff6ff; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                <span style="color: #2563eb;">📦</span> Inventory & Operations
            </h3>
            
            <div style="max-width: 50%;">
                <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 5px;">Low Stock Warning Threshold</label>
                <input type="number" name="low_stock_threshold" value="5" class="interactive-input" style="width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; outline: none;">
                <span style="display: block; font-size: 12px; color: #6b7280; margin-top: 4px;">Items falling at or below this quantity will trigger visual low stock alerts.</span>
            </div>
        </div>

        <!-- 4. SECURITY & SESSION POLICIES -->
        <div class="setting-card">
            <h3 style="margin-bottom: 15px; font-size: 16px; color: #1e3a8a; border-bottom: 2px solid #eff6ff; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                <span style="color: #2563eb;">🔒</span> Security & Access Control
            </h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 5px;">Minimum Password Length</label>
                    <input type="number" name="min_password_length" value="8" class="interactive-input" style="width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 5px;">Session Timeout (Minutes)</label>
                    <input type="number" name="session_timeout" value="120" class="interactive-input" style="width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; outline: none;">
                </div>
            </div>
        </div>

        <!-- 5. EMAIL & NOTIFICATIONS -->
        <div class="setting-card">
            <h3 style="margin-bottom: 15px; font-size: 16px; color: #1e3a8a; border-bottom: 2px solid #eff6ff; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                <span style="color: #2563eb;">✉️</span> Email & Notifications
            </h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 5px;">System Sender Email</label>
                    <input type="email" name="system_email" value="no-reply@bhakas.local" class="interactive-input" style="width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; outline: none;">
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 25px;">
                    <div>
                        <strong style="font-size: 14px; color: #1f2937; display: block;">Enable Email Alerts</strong>
                        <span style="font-size: 12px; color: #6b7280;">Send system alerts regarding low stock and security.</span>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="email_alerts" checked>
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
        </div>

        <!-- 6. DATA & BACKUP AUTOMATION -->
        <div class="setting-card">
            <h3 style="margin-bottom: 15px; font-size: 16px; color: #1e3a8a; border-bottom: 2px solid #eff6ff; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                <span style="color: #2563eb;">💾</span> Data & Backup Automation
            </h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; align-items: center;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 5px;">Audit Log Retention Period</label>
                    <select name="log_retention" class="interactive-input" style="width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: white; outline: none;">
                        <option value="30">30 Days</option>
                        <option value="90" selected>90 Days</option>
                        <option value="365">1 Year</option>
                        <option value="forever">Keep Forever</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 5px;">Automatic Backup Frequency</label>
                    <select name="auto_backup_frequency" class="interactive-input" style="width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: white; outline: none;">
                        <option value="disabled">Disabled</option>
                        <option value="daily" selected>Daily Backup</option>
                        <option value="weekly">Weekly Backup</option>
                        <option value="monthly">Monthly Backup</option>
                    </select>
                </div>
                <div style="padding-top: 20px;">
                    <button type="button" onclick="alert('Backup triggered successfully!')" style="width: 100%; padding: 10px 16px; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; transition: background 0.2s; text-align: center;">
                        📥 Download Backup Now
                    </button>
                </div>
            </div>
        </div>

        <!-- 7. SYSTEM MAINTENANCE MODE -->
        <div class="setting-card" id="maintenance-card">
            <h3 style="margin-bottom: 15px; font-size: 16px; color: #1e3a8a; border-bottom: 2px solid #eff6ff; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                <span style="color: #2563eb;">🛡</span> System Status
            </h3>
            
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <strong style="font-size: 14px; color: #1f2937; display: block;">Maintenance Mode</strong>
                    <span style="font-size: 13px; color: #6b7280;" id="maintenance-desc">When active, only Super Admins can access the platform. BHW users will see a maintenance notice.</span>
                </div>
                <!-- Interactive Toggle Switch -->
                <label class="toggle-switch maintenance-switch">
                    <input type="checkbox" name="maintenance_mode" id="maintenance-toggle">
                    <span class="slider"></span>
                </label>
            </div>
        </div>

        <!-- SAVE BUTTON BAR -->
        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
            <button type="reset" id="reset-btn" class="btn-action" style="background: #f3f4f6; color: #374151; border: 1px solid #d1d5db;">Cancel</button>
            <button type="submit" class="btn-action" style="background: #2563eb; color: white; border: none; box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);">Save Changes</button>
        </div>

    </div>
</form>

<!-- STYLES & INTERACTIVE TOGGLE SWITCH FIX -->
<style>
    .setting-card {
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        border-left: 4px solid #2563eb; 
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .setting-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.08), 0 4px 6px -4px rgba(37, 99, 235, 0.04);
    }
    .interactive-input {
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .interactive-input:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }
    .btn-action {
        padding: 10px 20px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-action:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }

    /* ROBUST TOGGLE SWITCH STYLING WITH POINTER-EVENTS FIX */
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 48px;
        height: 24px;
        cursor: pointer;
        flex-shrink: 0;
    }
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
        position: absolute;
    }
    .toggle-switch .slider {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #cbd5e1;
        transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 24px;
        pointer-events: none; /* Ensures clicks pass through to the label container */
    }
    .toggle-switch .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 50%;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }
    .toggle-switch input:checked + .slider {
        background-color: #2563eb;
    }
    .toggle-switch input:checked + .slider:before {
        transform: translateX(24px);
    }

    /* MAINTENANCE RED ACCENT OVERRIDE */
    .maintenance-switch input:checked + .slider {
        background-color: #ef4444 !important;
    }

    @keyframes pulse {
        0% { transform: scale(0.95); opacity: 0.8; }
        50% { transform: scale(1.2); opacity: 1; }
        100% { transform: scale(0.95); opacity: 0.8; }
    }
</style>

<!-- INTERACTIVE SCRIPTS -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const form = document.getElementById("settings-form");
        const statusBadge = document.getElementById("save-status-badge");
        const maintenanceToggle = document.getElementById("maintenance-toggle");
        const maintenanceCard = document.getElementById("maintenance-card");
        const maintenanceDesc = document.getElementById("maintenance-desc");

        function showBadge() {
            statusBadge.style.opacity = "1";
            statusBadge.style.transform = "translateY(0)";
        }

        form.addEventListener("input", showBadge);
        form.addEventListener("change", showBadge);

        maintenanceToggle.addEventListener("change", function () {
            if (this.checked) {
                maintenanceCard.style.borderLeftColor = "#ef4444";
                maintenanceDesc.style.color = "#b91c1c";
                maintenanceDesc.innerText = "WARNING: Maintenance mode is ENABLED. Standard users are currently locked out.";
            } else {
                maintenanceCard.style.borderLeftColor = "#2563eb";
                maintenanceDesc.style.color = "#6b7280";
                maintenanceDesc.innerText = "When active, only Super Admins can access the platform. BHW users will see a maintenance notice.";
            }
        });

        form.addEventListener("reset", function () {
            statusBadge.style.opacity = "0";
            statusBadge.style.transform = "translateY(-5px)";
            maintenanceCard.style.borderLeftColor = "#2563eb";
            maintenanceDesc.style.color = "#6b7280";
            maintenanceDesc.innerText = "When active, only Super Admins can access the platform. BHW users will see a maintenance notice.";
        });
    });
</script>
@endsection