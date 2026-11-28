# Email confirmation on every sign-in

Every website login requires the account password, approval from the email review page, and a six-digit code entered in the browser that started the login. Approval links and codes expire after 10 minutes. Five incorrect codes block that attempt. Resending is limited to once per minute per account and replaces the current browser's old link/code. Email links can be reviewed on another device; merely opening a link never approves a login.

## Deployment

1. Run `php artisan migrate --force` to create `login_challenges` before releasing the updated login code.
2. Configure a working mail transport in the server environment (for SMTP: `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_SCHEME`, `MAIL_FROM_ADDRESS`, and `MAIL_FROM_NAME`). Match the provider's transport settings. The default `log` transport only writes emails to application logs; it does not deliver them.
3. Set `APP_URL` to the public HTTPS website address so emailed signed links point to the correct domain. Run `php artisan config:clear` after changing environment values, or rebuild the deployment's configuration cache.
4. Ensure every account has an accessible email address before rollout, including citizen accounts previously created without email. Accounts without a valid email cannot sign in; an administrator must update them. Do not disable email confirmation to work around missing addresses.
5. Check a real mailbox on the deployed domain: password entry must leave the browser signed out; opening the email must show Yes/No; Yes plus the code must complete login; No must block that attempt. Email delivery still requires this live check even when automated tests pass.

Emails are sent synchronously, so no queue worker is required. Delivery errors keep the user signed out. Password/email changes, deletion, and account locks invalidate outstanding challenges. Existing authenticated sessions are unaffected by this login change. No automatic login is performed by the email review link. Denying a request blocks that individual attempt; it does not reset the password or revoke existing sessions. The rejection screen links to password reset.

Keep application logs private when using the log mailer during development, because logged emails contain codes and signed review links. Expired records may be periodically deleted from `login_challenges` according to the site's retention policy.
