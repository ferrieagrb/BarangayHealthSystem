# Web Push Notifications

The app sends generic browser notifications when a new BHW announcement is published. Push payloads do not include announcement titles, descriptions, or health-record details.

## Setup

1. Run `php artisan webpush:keys` and add the printed `VAPID_PUBLIC_KEY` and `VAPID_PRIVATE_KEY` values to the server's `.env`. Keep the private key secret.
2. Set `VAPID_SUBJECT` to the production site URL or a monitored `mailto:` address. Set `APP_URL` to the site's HTTPS URL.
3. Run `php artisan migrate` to create the per-device subscription table.
4. Ensure a Laravel queue worker is running in production, for example `php artisan queue:work`.
5. Deploy over HTTPS. On the device, sign in, open Settings, and enable Browser Notifications. On iPhone, add the site to the Home Screen first.

Push delivery depends on the browser and operating system. Users must grant permission, the device must have internet access, and browser push services may expire subscriptions; expired subscriptions are removed when delivery reports them.

Users can review and remove their own notification devices in Settings. Device names are best-effort labels derived from the browser's User-Agent. “Last checked” updates when that device opens Settings; it is not a record of every login or push delivery.

## Local Development

`http://localhost` is treated as a secure context by modern browsers for service workers. Configure VAPID keys locally and run the queue worker; the worker can use `QUEUE_CONNECTION=sync` for a simple local test.