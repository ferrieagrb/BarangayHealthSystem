<style>
    .push-notification-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 20px 24px;
        margin-bottom: 24px;
        border: 1px solid #dbe3ef;
        border-radius: 12px;
        background: #fff;
        color: #172033;
    }

    .push-notification-card h2 {
        margin: 0 0 6px;
        font-size: 18px;
    }

    .push-notification-card p {
        margin: 0;
        color: #64748b;
        font-size: 14px;
        line-height: 1.5;
    }

    .push-notification-controls {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .push-notification-controls button {
        min-height: 40px;
        padding: 9px 14px;
        border: 1px solid #1d4ed8;
        border-radius: 7px;
        background: #1d4ed8;
        color: #fff;
        font: inherit;
        font-weight: 600;
        cursor: pointer;
    }

    .push-notification-controls button:disabled {
        cursor: not-allowed;
        opacity: .6;
    }

    .push-notification-card [data-push-status] {
        max-width: 280px;
    }

    .push-device-section {
        margin: -8px 0 24px;
        padding: 18px 24px;
        border: 1px solid #dbe3ef;
        border-radius: 12px;
        background: #fff;
        color: #172033;
    }

    .push-device-section h3 {
        margin: 0 0 8px;
        font-size: 16px;
    }

    .push-device-list {
        list-style: none;
        margin: 12px 0 0;
        padding: 0;
    }

    .push-device-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 12px 0;
        border-top: 1px solid #e2e8f0;
    }

    .push-device-name {
        margin: 0 0 4px;
        font-size: 14px;
        font-weight: 600;
    }

    .push-device-meta {
        color: #64748b;
        font-size: 13px;
    }

    .push-device-current {
        display: inline-block;
        margin-left: 8px;
        color: #047857;
        font-size: 12px;
        font-weight: 600;
    }

    .push-device-remove {
        min-height: 36px;
        padding: 7px 12px;
        border: 1px solid #b91c1c;
        border-radius: 7px;
        background: #fff;
        color: #b91c1c;
        font: inherit;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }

    .push-device-remove:disabled {
        cursor: not-allowed;
        opacity: .6;
    }

    body.theme-dark .push-notification-card {
        border-color: #3b4658;
        background: #1e293b;
        color: #f8fafc;
    }

    body.theme-dark .push-notification-card p {
        color: #cbd5e1;
    }

    body.theme-dark .push-device-section {
        border-color: #3b4658;
        background: #1e293b;
        color: #f8fafc;
    }

    body.theme-dark .push-device-row {
        border-color: #3b4658;
    }

    body.theme-dark .push-device-meta {
        color: #cbd5e1;
    }

    body.theme-dark .push-device-remove {
        background: #1e293b;
    }

    @media (max-width: 600px) {
        .push-notification-card,
        .push-notification-controls {
            align-items: stretch;
            flex-direction: column;
        }

        .push-notification-card {
            padding: 18px;
        }

        .push-device-section {
            padding: 16px 18px;
        }

        .push-device-row {
            align-items: flex-start;
        }

        .push-notification-card [data-push-status] {
            max-width: none;
        }
    }
</style>

<section
    class="push-notification-card"
    data-push-settings
    data-public-key="{{ config('webpush.vapid.public_key') }}"
    data-devices-url="{{ route('push-subscriptions.index') }}"
    data-subscribe-url="{{ route('push-subscriptions.store') }}"
    data-unsubscribe-url="{{ route('push-subscriptions.destroy') }}"
    data-revoke-url="{{ url('/push-subscriptions') }}"
    aria-labelledby="pushNotificationTitle"
>
    <div>
        <h2 id="pushNotificationTitle">Browser Notifications</h2>
        <p>Get an alert when a new barangay health announcement is posted.</p>
    </div>
    <div class="push-notification-controls">
        <p data-push-status role="status" aria-live="polite">Checking this device…</p>
        <button type="button" data-push-toggle disabled>Loading</button>
    </div>
    <form data-push-csrf hidden>@csrf</form>
</section>

<section class="push-device-section" aria-labelledby="pushDeviceTitle">
    <h3 id="pushDeviceTitle">My Devices</h3>
    <p data-push-device-status role="status" aria-live="polite">Loading registered devices…</p>
    <ul class="push-device-list" data-push-device-list></ul>
</section>

<script src="{{ asset('js/push-notification-settings.js') }}" defer></script>