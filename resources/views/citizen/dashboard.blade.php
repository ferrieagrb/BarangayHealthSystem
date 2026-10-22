@extends('templates.citizen')
@section('CSSown')
<style>
/* =========================================================
   CITIZEN DASHBOARD
   ========================================================= */

.c-home {
    width: 100%;
    max-width: none;
    margin: 0;
    padding: clamp(18px, 2.4vw, 34px);
    color: #172033;
}

.c-home p {
    line-height: 1.55;
}

.c-muted {
    color: #475569;
    font-size: 15px;
}

.c-section {
    margin: 0 0 72px;
    scroll-margin-top: 165px;
}

/* Give Health Information enough space so its tab remains active while reading. */
#health-information { min-height: 72vh; }
#health-information .c-health { padding: clamp(22px, 3vw, 42px); }
#health-information .c-stats { gap: 16px; }
#health-information .c-stat { min-height: 94px; padding: 18px; }
#health-information .c-stat span { font-size: 15px; }
#health-information .c-stat strong { font-size: 16px; line-height: 1.5; }
#health-information .c-record h3 { font-size: 17px; }
#health-information .c-table { font-size: 15px; }
#health-information .c-table th, #health-information .c-table td { padding: 13px 14px; }

.c-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 13px;
}

.c-heading h2 {
    margin: 0;
    color: #173b82;
    font-size: clamp(21px, 1.6vw, 25px);
    font-weight: 800;
}

.c-panel {
    overflow: hidden;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 15px;
    box-shadow: 0 5px 18px #0f172a0c;
}

.c-btn {
    display: inline-block;
    padding: 13px 18px;
    border-radius: 8px;
    background: #1d4ed8;
    color: #fff !important;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none !important;
}

.c-empty {
    padding: 26px;
    color: #64748b;
    text-align: center;
}

/* Announcement components use their existing base styles. */

/* Health information and records */
.c-health {
    padding: 60px;
}

.c-stats {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 11px;
    margin-bottom: 22px;
}

.c-stat {
    min-width: 0;
    padding: 13px;
    border: 1px solid #e2e8f0;
    border-radius: 9px;
    background: #f8fafc;
}

.c-stat span {
    display: block;
    margin-bottom: 5px;
    color: #64748b;
    font-size: 13px;
}

.c-stat strong {
    font-size: 14px;
    overflow-wrap: anywhere;
}

.c-record {
    margin-top: 20px;
}

.c-record h3 {
    margin: 0 0 9px;
    color: #173b82;
    font-size: 14px;
    font-weight: 800;
}

.c-tablewrap {
    overflow-x: auto;
}

.c-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.c-table th,
.c-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
}

.c-table th {
    background: #f1f5f9;
    color: #334155;
    font-size: 12px;
    text-transform: uppercase;
}

.c-table .none {
    padding: 15px;
    color: #64748b;
    text-align: center;
}

.c-actions {
    display: flex;
    justify-content: flex-end;
    padding: 0 23px 20px;
}

/* Referral section */
.c-referral {
    display: flex;
    align-items: center;
    gap: 18px;
    padding: 22px;
    background: linear-gradient(120deg, #f0fdf4, #fff);
}

.c-ref-icon {
    display: grid;
    flex-shrink: 0;
    width: 54px;
    height: 54px;
    place-items: center;
    border-radius: 14px;
    background: #dcfce7;
    color: #15803d;
    font-size: 27px;
}

.c-ref-copy {
    flex: 1;
}

.c-ref-copy h3 {
    margin: 0 0 5px;
    color: #166534;
    font-size: 17px;
}

.c-ref-copy p {
    margin: 0;
    color: #475569;
    font-size: 13px;
}

.c-green {
    background: #15803d;
}

/* =========================================================
   CITIZEN HEALTH CARD
   ========================================================= */

.c-home .c-id-card {
    display: flex;
    flex-direction: column;
    width: 100%;
    max-width: 1000px;
    height: auto;
    margin: 0 auto;
    overflow: hidden;
    box-sizing: border-box;
    border: 1px solid #cbd5e1;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
}

/* Blue header and barangay logo */
.c-home .c-id-header {
    display: flex;
    align-items: center;
    gap: 16px;
    min-height: 105px;
    padding: 15px 22px;
    box-sizing: border-box;
    border-bottom: 3px solid #bfdbfe;
    background: linear-gradient(110deg, #102b78, #1d4c9c);
    color: #fff;
}

.c-home .c-id-header > img {
    flex: 0 0 66px;
    width: 66px;
    height: 66px;
    padding: 3px;
    box-sizing: border-box;
    border: 2px solid #fff;
    border-radius: 50%;
    background: #fff;
    object-fit: contain;
}

.c-home .c-id-header-text {
    flex: 1;
    min-width: 0;
}

.c-home .c-id-header-text h3 {
    margin: 0 0 4px;
    color: #fff;
    font-size: 27px;
    font-weight: 900;
    line-height: 1.15;
}

.c-home .c-id-header-text strong {
    display: block;
    color: #fff;
    font-size: 13px;
}

.c-home .c-id-header-text small {
    display: block;
    margin-top: 5px;
    color: #dbeafe;
    font-size: 8px;
    font-weight: 700;
}

/* QR code in the header */
.c-home .c-id-qr {
    display: flex;
    flex: 0 0 76px;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    margin-left: auto;
    text-align: center;
}

.c-home .c-id-qr-frame {
    padding: 3px;
    border-radius: 5px;
    background: #fff;
}

.c-home .c-id-qr-frame img {
    display: block;
    width: 65px;
    height: 65px;
    object-fit: contain;
}

.c-home .c-id-qr > span {
    color: #fff;
    font-size: 7px;
    font-weight: 800;
}

/* Citizen photo and personal information */
.c-home .c-id-body {
    display: grid;
    grid-template-columns: 120px minmax(0, 1fr);
    align-items: center;
    gap: 22px;
    padding: 22px;
    box-sizing: border-box;
    background:
        linear-gradient(
            135deg,
            transparent 82%,
            #f3f6fc 82%,
            #f3f6fc 90%,
            transparent 90%
        ),
        #fff;
}

.c-home .c-id-body .c-photo {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    width: 120px;
    height: 150px;
    overflow: hidden;
    box-sizing: border-box;
    border: 1px solid #cbd5e1;
    border-radius: 5px;
    background: #f1f5f9;
}

.c-home .c-id-body .c-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.c-home .c-id-body .c-photo span {
    font-size: 30px;
    font-weight: 900;
}

.c-home .c-id-body .c-photo small {
    color: #64748b;
    font-size: 9px;
}

.c-home .c-id-details {
    min-width: 0;
}

.c-home .c-id-details > h3 {
    margin: 0 0 13px;
    color: #173b82;
    font-size: 17px;
    font-weight: 900;
}

.c-home .c-id-field {
    min-width: 0;
    margin-bottom: 10px;
    overflow-wrap: anywhere;
}

.c-home .c-id-field span {
    display: block;
    margin-bottom: 4px;
    color: #64748b;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 0.3px;
}

.c-home .c-id-field strong {
    color: #172554;
    font-size: 12px;
    font-weight: 800;
}

.c-home .c-id-field .c-id-number {
    color: #17469a;
    font-size: 15px;
}

.c-home .c-id-details-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0 16px;
}

/* Card footer */
.c-home .c-id-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    padding: 11px 22px;
    border-top: 1px solid #dbeafe;
    background: #f8fafc;
    color: #334e85;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: 0.3px;
}

/* Print / full health card button outside the card */
.c-home .c-id-actions-outside {
    display: flex;
    justify-content: flex-end;
    max-width: 1000px;
    margin: 12px auto 0;
}

.c-home .c-id-actions-outside .c-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 44px;
    margin: 0;
    padding: 11px 16px;
    box-sizing: border-box;
    border-radius: 7px;
    background: #1d4ed8;
    color: #fff;
    font-size: 12px;
    font-weight: 800;
    text-align: center;
    text-decoration: none;
}

/* =========================================================
   RESPONSIVE STYLES
   ========================================================= */

@media (max-width: 760px) {
    .c-home {
        padding: 20px 14px 28px;
    }

    .c-heading {
        align-items: flex-start;
    }

    .c-heading h2 {
        font-size: 18px;
    }


    .c-ann-cover {
        min-height: 125px;
        padding: 17px;
    }

    .c-ann-body {
        padding: 15px;
    }

    .c-section { scroll-margin-top: 150px; }
    #health-information { min-height: 78vh; }
    #health-information .c-health { padding: 20px 16px; }
    #health-information .c-stat { min-height: 82px; padding: 14px; }
    #health-information .c-stat span { font-size: 14px; }
    #health-information .c-stat strong { font-size: 15px; }
    #health-information .c-table { min-width: 650px; font-size: 14px; }

    .c-stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .c-referral {
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .c-ref-copy {
        min-width: 65%;
    }
}

@media (max-width: 600px) {
    .c-home .c-id-card {
        max-width: 100%;
        border-radius: 10px;
    }

    .c-home .c-id-header {
        gap: 9px;
        min-height: 0;
        padding: 12px;
    }

    .c-home .c-id-header > img {
        flex-basis: 42px;
        width: 42px;
        height: 42px;
    }

    .c-home .c-id-header-text h3 {
        font-size: 18px;
    }

    .c-home .c-id-header-text strong {
        font-size: 10px;
    }

    .c-home .c-id-header-text small {
        font-size: 6px;
        line-height: 1.4;
    }

    .c-home .c-id-qr {
        flex-basis: 49px;
    }

    .c-home .c-id-qr-frame img {
        width: 43px;
        height: 43px;
    }

    .c-home .c-id-qr > span {
        font-size: 6px;
    }

    .c-home .c-id-body {
        grid-template-columns: 68px minmax(0, 1fr);
        align-items: start;
        gap: 12px;
        padding: 14px 11px;
    }

    .c-home .c-id-body .c-photo {
        width: 68px;
        height: 88px;
    }

    .c-home .c-id-body .c-photo span {
        font-size: 22px;
    }

    .c-home .c-id-details > h3 {
        margin-bottom: 10px;
        font-size: 14px;
    }

    .c-home .c-id-field span {
        font-size: 8px;
    }

    .c-home .c-id-field strong {
        font-size: 11px;
    }

    .c-home .c-id-field .c-id-number {
        font-size: 13px;
    }

    .c-home .c-id-details-grid {
        grid-template-columns: 1fr;
        gap: 0;
    }

    .c-home .c-id-footer {
        padding: 9px 11px;
        font-size: 7px;
        letter-spacing: 0;
    }

    .c-home .c-id-actions-outside {
        margin-top: 10px;
    }

    .c-home .c-id-actions-outside .c-btn {
        width: 100%;
    }

    .c-section {
        scroll-margin-top: 125px;
    }

    .c-muted {
        font-size: 13px;
    }

    .c-ann-cover {
        min-height: 112px;
    }

    .c-ann-cover h3 {
        font-size: 18px;
    }

    .c-ann-body p {
        font-size: 13px;
    }
}

@media (max-width: 420px) {
    .c-home {
        padding: 18px 12px 24px;
    }

    .c-heading h2 {
        font-size: 19px;
    }

    .c-stats {
        grid-template-columns: 1fr;
    }

    .c-health {
        padding: 16px;
    }

    .c-actions {
        padding: 0 16px 17px;
    }
}

@media (max-width: 360px) {
    .c-home .c-id-header {
        gap: 6px;
        padding: 9px;
    }

    .c-home .c-id-header > img {
        flex-basis: 35px;
        width: 35px;
        height: 35px;
    }

    .c-home .c-id-header-text h3 {
        font-size: 15px;
    }

    .c-home .c-id-header-text strong {
        font-size: 9px;
    }

    .c-home .c-id-header-text small {
        font-size: 5px;
    }

    .c-home .c-id-qr {
        flex-basis: 42px;
    }

    .c-home .c-id-qr-frame img {
        width: 36px;
        height: 36px;
    }

    .c-home .c-id-footer {
        font-size: 6px;
    }
}


/* =========================================================
   REFINED GOVERNMENT-STYLE CITIZEN DASHBOARD
   Preserves the existing sections, routes, and Blade logic.
   ========================================================= */
.c-home {
    --c-primary: #173b82;
    --c-primary-dark: #102b5c;
    --c-accent: #d9a441;
    --c-text: #172033;
    --c-muted: #475569;
    --c-border: #d7e0ec;
    --c-surface: #ffffff;
    --c-page: #f3f6fb;
    color: var(--c-text);
    background: var(--c-page);
    font-size: 16px;
    line-height: 1.65;
}

.c-home * { box-sizing: border-box; }
.c-home p { line-height: 1.7; }
.c-home .c-muted { color: #4b5d75; font-size: 16px; }
.c-home .c-section {
    margin: 0 0 clamp(38px, 5vw, 62px);
    scroll-margin-top: 145px;
}
.c-home .c-heading {
    align-items: flex-end;
    margin-bottom: 18px;
    padding: 0 0 13px;
    border-bottom: 2px solid #dbe5f2;
}
.c-home .c-heading h2 {
    position: relative;
    color: var(--c-primary);
    font-size: clamp(23px, 2vw, 29px);
    font-weight: 850;
    letter-spacing: -0.025em;
    line-height: 1.3;
}
.c-home .c-heading h2::before {
    content: "";
    display: inline-block;
    width: 5px;
    height: 1em;
    margin: 0 11px 0 0;
    border-radius: 4px;
    background: var(--c-accent);
    vertical-align: -0.1em;
}
.c-home .c-panel {
    border: 1px solid var(--c-border);
    border-radius: 16px;
    background: var(--c-surface);
    box-shadow: 0 8px 24px rgba(23, 59, 130, 0.07);
}
.c-home .c-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 46px;
    padding: 12px 18px;
    border: 1px solid transparent;
    border-radius: 9px;
    background: var(--c-primary);
    box-shadow: 0 3px 8px rgba(16, 43, 92, 0.14);
    color: #fff !important;
    font-size: 15px;
    font-weight: 750;
    line-height: 1.35;
    text-align: center;
    transition: background-color .18s ease, box-shadow .18s ease, transform .18s ease;
}
.c-home .c-btn:hover,
.c-home .c-btn:focus-visible {
    background: var(--c-primary-dark);
    box-shadow: 0 5px 12px rgba(16, 43, 92, 0.2);
}
.c-home .c-btn:focus-visible,
.c-home a:focus-visible {
    outline: 3px solid #f2c96d;
    outline-offset: 3px;
}
.c-home .c-empty {
    padding: clamp(24px, 4vw, 40px);
    border: 1px dashed #b9c8dc;
    border-radius: 14px;
    background: #fff;
    color: #4b5d75;
    font-size: 16px;
}

/* Announcement slider */
.c-home .c-ann-slider { position: relative; overflow: hidden; padding: 4px 2px 12px; }
.c-home .c-ann-track { display: flex; align-items: stretch; gap: 18px; transition: transform .45s ease; will-change: transform; }
.c-home .c-ann-slide { flex: 0 0 100%; min-width: 0; }
.c-home .c-ann-card {
    min-width: 0;
    overflow: hidden;
    border: 1px solid var(--c-border);
    border-radius: 15px;
    background: #fff;
    box-shadow: 0 6px 18px rgba(23, 59, 130, 0.06);
    transition: transform .18s ease, box-shadow .18s ease;
}
.c-home .c-ann-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(23, 59, 130, 0.1);
}
.c-home .c-ann-cover {
    min-height: 150px;
    padding: 22px;
    background: linear-gradient(120deg, #102b5c, #1d4c9c);
    color: #fff;
}
.c-home .c-ann-label {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 12px;
    padding: 5px 10px;
    border: 1px solid rgba(255,255,255,.32);
    border-radius: 999px;
    background: rgba(255,255,255,.12);
    color: #fff;
    font-size: 13px;
    font-weight: 750;
}
.c-home .c-ann-cover h3 {
    margin: 0;
    color: #fff;
    font-size: clamp(19px, 2vw, 23px);
    font-weight: 800;
    line-height: 1.4;
    overflow-wrap: anywhere;
}
.c-home .c-ann-body { padding: 20px 22px; }
.c-home .c-ann-body p {
    margin: 0 0 18px;
    color: #334155;
    font-size: 16px;
    line-height: 1.75;
}
.c-home .c-ann-footer {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px 14px;
    padding-top: 13px;
    border-top: 1px solid #e5ebf3;
    color: #53657c;
    font-size: 13px;
    font-weight: 650;
}
.c-home .c-ann-controls { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 8px; }
.c-home .c-ann-control-buttons { display: flex; gap: 8px; }
.c-home .c-ann-arrow { display: inline-flex; align-items: center; justify-content: center; width: 42px; height: 42px; border: 1px solid #c9d8ed; border-radius: 10px; background: #fff; color: #173b82; font-size: 22px; cursor: pointer; }
.c-home .c-ann-arrow:hover { background: #edf4ff; }
.c-home .c-ann-arrow:disabled { opacity: .4; cursor: not-allowed; }
.c-home .c-ann-indicator { color: #53657c; font-size: 13px; font-weight: 700; }
.c-home .c-ann-priority { display: inline-block; margin: 0 0 10px 7px; padding: 4px 8px; border-radius: 999px; background: #fee2e2; color: #991b1b; font-size: 11px; font-weight: 800; vertical-align: middle; }
.c-home .c-heading a {
    display: inline-flex;
    align-items: center;
    min-height: 44px;
    padding: 8px 13px;
    border: 1px solid #c9d8ed;
    border-radius: 9px;
    background: #fff;
    color: var(--c-primary) !important;
    font-size: 15px !important;
}
.c-home .c-heading a:hover { background: #edf4ff; }

/* Citizen ID card */
.c-home .c-id-card {
    border: 1px solid #c8d5e6;
    border-radius: 16px;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.12);
}
.c-home .c-id-header {
    min-height: 116px;
    padding: 18px 24px;
    border-bottom: 4px solid var(--c-accent);
    background: linear-gradient(115deg, #102b5c 0%, #173b82 58%, #2457a5 100%);
}
.c-home .c-id-header > img {
    flex-basis: 70px;
    width: 70px;
    height: 70px;
    border: 3px solid rgba(255,255,255,.9);
    box-shadow: 0 2px 8px rgba(0,0,0,.12);
}
.c-home .c-id-header-text h3 {
    font-size: clamp(24px, 3vw, 30px);
    letter-spacing: .04em;
}
.c-home .c-id-header-text strong { font-size: 14px; letter-spacing: .04em; }
.c-home .c-id-header-text small { font-size: 10px; line-height: 1.5; letter-spacing: .04em; }
.c-home .c-id-qr-frame { padding: 5px; border-radius: 7px; }
.c-home .c-id-qr-frame img { width: 68px; height: 68px; }
.c-home .c-id-qr > span { font-size: 9px; letter-spacing: .04em; }
.c-home .c-id-body {
    grid-template-columns: 132px minmax(0, 1fr);
    gap: 26px;
    padding: clamp(22px, 3vw, 32px);
}
.c-home .c-id-body .c-photo {
    width: 132px;
    height: 164px;
    border: 2px solid #d5dfec;
    border-radius: 8px;
    box-shadow: 0 3px 10px rgba(15, 23, 42, .06);
}
.c-home .c-id-details > h3 {
    margin-bottom: 17px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e2eaf4;
    color: var(--c-primary);
    font-size: 18px;
    letter-spacing: .04em;
}
.c-home .c-id-field { margin-bottom: 14px; }
.c-home .c-id-field span { font-size: 11px; letter-spacing: .06em; }
.c-home .c-id-field strong { font-size: 14px; line-height: 1.5; }
.c-home .c-id-field .c-id-number { font-size: 17px; }
.c-home .c-id-footer {
    min-height: 42px;
    padding: 12px 24px;
    border-top: 1px solid #dbe5f2;
    background: #f4f7fb;
    color: #334e85;
    font-size: 10px;
}
.c-home .c-id-actions-outside { margin-top: 15px; }

/* Health information and record tables */
.c-home .c-health { padding: clamp(22px, 3vw, 36px); }
.c-home .c-stats { gap: 15px; margin-bottom: 28px; }
.c-home .c-stat {
    padding: 18px;
    border: 1px solid #dbe5f0;
    border-radius: 12px;
    background: linear-gradient(145deg, #fff, #f7faff);
}
.c-home .c-stat span {
    margin-bottom: 8px;
    color: #52657e;
    font-size: 15px;
    line-height: 1.45;
}
.c-home .c-stat strong {
    color: #173b82;
    font-size: 17px;
    line-height: 1.5;
}
.c-home .c-record { margin-top: 28px; }
.c-home .c-record h3 {
    margin-bottom: 13px;
    color: var(--c-primary);
    font-size: 19px;
}
.c-home .c-tablewrap {
    border: 1px solid #dbe4ef;
    border-radius: 11px;
    background: #fff;
}
.c-home .c-table { font-size: 15px; }
.c-home .c-table th,
.c-home .c-table td {
    padding: 14px 15px;
    vertical-align: top;
    line-height: 1.55;
}
.c-home .c-table th {
    background: #eaf0f8;
    color: #233d66;
    font-size: 13px;
    letter-spacing: .035em;
}
.c-home .c-table tbody tr:nth-child(even) { background: #f8fafd; }
.c-home .c-table tbody tr:hover { background: #eef5ff; }
.c-home .c-table .none { padding: 20px; }
.c-home .c-actions { padding: 22px 0 0; }

/* Referral card */
.c-home .c-referral {
    gap: 22px;
    padding: clamp(22px, 3vw, 30px);
    border: 1px solid #c9e5d4;
    border-radius: 16px;
    background: linear-gradient(120deg, #f0fdf4, #fff 78%);
}
.c-home .c-ref-icon {
    width: 62px;
    height: 62px;
    border: 1px solid #b8e4c8;
    border-radius: 16px;
    background: #dcfce7;
    font-size: 32px;
}
.c-home .c-ref-copy h3 { font-size: 19px; line-height: 1.45; }
.c-home .c-ref-copy p { font-size: 16px; line-height: 1.7; }
.c-home .c-green { background: #166534; }
.c-home .c-green:hover,
.c-home .c-green:focus-visible { background: #14532d; }

@media (max-width: 760px) {
    .c-home .c-ann-track { gap: 12px; }
    .c-home .c-section { scroll-margin-top: 125px; }
    .c-home .c-heading { align-items: flex-start; }
    .c-home .c-heading h2 { font-size: 22px; }
    .c-home .c-ann-cover { min-height: 130px; }
    .c-home .c-ann-body { padding: 18px; }
    .c-home .c-stats { gap: 12px; }
    .c-home .c-referral { align-items: flex-start; }
}

@media (max-width: 600px) {
    .c-home .c-id-header { gap: 9px; padding: 12px; }
    .c-home .c-id-header > img { flex-basis: 42px; width: 42px; height: 42px; }
    .c-home .c-id-header-text h3 { font-size: 18px; }
    .c-home .c-id-header-text strong { font-size: 10px; }
    .c-home .c-id-header-text small { font-size: 6px; }
    .c-home .c-id-qr { flex-basis: 49px; }
    .c-home .c-id-qr-frame img { width: 43px; height: 43px; }
    .c-home .c-id-qr > span { font-size: 6px; }
    .c-home .c-id-body {
        grid-template-columns: 76px minmax(0, 1fr);
        align-items: start;
        gap: 13px;
        padding: 15px 12px;
    }
    .c-home .c-id-body .c-photo { width: 76px; height: 96px; }
    .c-home .c-id-details > h3 { font-size: 14px; }
    .c-home .c-id-field span { font-size: 8px; }
    .c-home .c-id-field strong { font-size: 12px; }
    .c-home .c-id-field .c-id-number { font-size: 14px; }
    .c-home .c-id-details-grid { grid-template-columns: 1fr; gap: 0; }
    .c-home .c-id-footer { padding: 9px 11px; font-size: 7px; }
    .c-home .c-id-actions-outside .c-btn { width: 100%; }
    .c-home .c-health { padding: 18px 14px; }
    .c-home .c-stats { grid-template-columns: 1fr; }
    .c-home .c-stat { min-height: auto; }
    .c-home .c-table { min-width: 650px; font-size: 14px; }
    .c-home .c-referral { flex-direction: column; }
    .c-home .c-ref-copy { min-width: 0; }
    .c-home .c-referral .c-btn { width: 100%; }
    .c-home .c-section { scroll-margin-top: 110px; }
}

@media (max-width: 360px) {
    .c-home .c-id-header { gap: 6px; padding: 9px; }
    .c-home .c-id-header > img { flex-basis: 35px; width: 35px; height: 35px; }
    .c-home .c-id-header-text h3 { font-size: 15px; }
    .c-home .c-id-header-text strong { font-size: 9px; }
    .c-home .c-id-header-text small { font-size: 5px; }
    .c-home .c-id-qr { flex-basis: 42px; }
    .c-home .c-id-qr-frame img { width: 36px; height: 36px; }
    .c-home .c-id-footer { font-size: 6px; }
}


/* =========================================================
   COMPACT MOBILE LAYOUT
   Keeps the desktop section order and visual hierarchy,
   while reducing oversized spacing on smaller screens.
   ========================================================= */

@media (max-width: 760px) {
    .c-home {
        padding: 18px 16px 26px;
        font-size: 15px;
    }

    .c-home .c-section {
        margin-bottom: 34px;
        scroll-margin-top: 105px;
    }

    .c-home .c-heading {
        gap: 10px;
        margin-bottom: 13px;
        padding-bottom: 10px;
    }

    .c-home .c-heading h2 {
        font-size: 21px;
        line-height: 1.35;
    }

    .c-home .c-muted {
        font-size: 14px;
        line-height: 1.5;
    }


    .c-home .c-ann-cover {
        min-height: 0;
        padding: 16px 18px;
    }

    .c-home .c-ann-label {
        margin-bottom: 8px;
        font-size: 12px;
    }

    .c-home .c-ann-cover h3 {
        font-size: 19px;
        line-height: 1.4;
    }

    .c-home .c-ann-body {
        padding: 15px 18px;
    }

    .c-home .c-ann-body p {
        margin-bottom: 12px;
        font-size: 15px;
        line-height: 1.6;
    }

    .c-home .c-ann-footer {
        padding-top: 10px;
        font-size: 12px;
    }

    .c-home .c-panel {
        border-radius: 13px;
    }

    .c-home .c-id-header {
        min-height: 0;
        gap: 10px;
        padding: 13px 14px;
    }

    .c-home .c-id-header > img {
        flex-basis: 46px;
        width: 46px;
        height: 46px;
    }

    .c-home .c-id-header-text h3 {
        font-size: 19px;
        line-height: 1.2;
    }

    .c-home .c-id-header-text strong {
        font-size: 10px;
        line-height: 1.4;
    }

    .c-home .c-id-header-text small {
        margin-top: 4px;
        font-size: 8px;
        line-height: 1.35;
    }

    .c-home .c-id-qr {
        flex-basis: 52px;
        gap: 4px;
    }

    .c-home .c-id-qr-frame {
        padding: 3px;
    }

    .c-home .c-id-qr-frame img {
        width: 44px;
        height: 44px;
    }

    .c-home .c-id-qr > span {
        font-size: 7px;
        line-height: 1.25;
    }

    .c-home .c-id-body {
        grid-template-columns: 76px minmax(0, 1fr);
        align-items: start;
        gap: 14px;
        padding: 17px 14px;
    }

    .c-home .c-id-body .c-photo {
        width: 76px;
        height: 96px;
    }

    .c-home .c-id-details > h3 {
        margin-bottom: 12px;
        padding-bottom: 8px;
        font-size: 14px;
    }

    .c-home .c-id-field {
        margin-bottom: 10px;
    }

    .c-home .c-id-field span {
        margin-bottom: 3px;
        font-size: 9px;
        line-height: 1.4;
    }

    .c-home .c-id-field strong {
        font-size: 12px;
        line-height: 1.45;
    }

    .c-home .c-id-field .c-id-number {
        font-size: 14px;
    }

    .c-home .c-id-details-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0 10px;
    }

    .c-home .c-id-footer {
        min-height: 36px;
        gap: 8px;
        padding: 10px 13px;
        font-size: 8px;
        line-height: 1.35;
    }

    .c-home .c-id-actions-outside {
        margin-top: 10px;
    }

    .c-home .c-id-actions-outside .c-btn {
        width: auto;
        min-height: 43px;
        padding: 11px 15px;
        font-size: 14px;
    }

    .c-home .c-health {
        padding: 20px 16px;
    }

    .c-home .c-stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 22px;
    }

    .c-home .c-stat {
        min-height: 0;
        padding: 13px;
        border-radius: 10px;
    }

    .c-home .c-stat span {
        margin-bottom: 6px;
        font-size: 13px;
        line-height: 1.4;
    }

    .c-home .c-stat strong {
        font-size: 14px;
        line-height: 1.45;
    }

    .c-home .c-record {
        margin-top: 22px;
    }

    .c-home .c-record h3 {
        margin-bottom: 10px;
        font-size: 17px;
        line-height: 1.4;
    }

    .c-home .c-tablewrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .c-home .c-table {
        min-width: 590px;
        font-size: 14px;
    }

    .c-home .c-table th,
    .c-home .c-table td {
        padding: 11px 12px;
        line-height: 1.45;
    }

    .c-home .c-table th {
        font-size: 12px;
    }

    .c-home .c-actions {
        padding-top: 16px;
    }

    .c-home .c-actions .c-btn {
        width: 100%;
        font-size: 14px;
    }

    .c-home .c-referral {
        flex-direction: row;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 13px;
        padding: 18px;
    }

    .c-home .c-ref-icon {
        flex: 0 0 46px;
        width: 46px;
        height: 46px;
        border-radius: 12px;
        font-size: 26px;
    }

    .c-home .c-ref-copy {
        flex: 1 1 calc(100% - 62px);
        min-width: 0;
    }

    .c-home .c-ref-copy h3 {
        font-size: 17px;
        line-height: 1.4;
    }

    .c-home .c-ref-copy p {
        font-size: 14px;
        line-height: 1.6;
    }

    .c-home .c-referral .c-btn {
        width: 100%;
        margin-top: 2px;
    }
}

@media (max-width: 420px) {
    .c-home {
        padding: 15px 12px 22px;
    }

    .c-home .c-section {
        margin-bottom: 28px;
    }

    .c-home .c-heading h2 {
        font-size: 19px;
    }

    .c-home .c-heading a {
        min-height: 40px;
        padding: 7px 10px;
        font-size: 13px !important;
        white-space: nowrap;
    }

    .c-home .c-id-header {
        gap: 7px;
        padding: 11px 10px;
    }

    .c-home .c-id-header > img {
        flex-basis: 38px;
        width: 38px;
        height: 38px;
    }

    .c-home .c-id-header-text h3 {
        font-size: 17px;
    }

    .c-home .c-id-header-text strong {
        font-size: 9px;
    }

    .c-home .c-id-header-text small {
        font-size: 7px;
    }

    .c-home .c-id-qr {
        flex-basis: 45px;
    }

    .c-home .c-id-qr-frame img {
        width: 38px;
        height: 38px;
    }

    .c-home .c-id-body {
        grid-template-columns: 65px minmax(0, 1fr);
        gap: 10px;
        padding: 14px 10px;
    }

    .c-home .c-id-body .c-photo {
        width: 65px;
        height: 84px;
    }

    .c-home .c-id-details-grid {
        grid-template-columns: 1fr;
        gap: 0;
    }

    .c-home .c-id-field span {
        font-size: 9px;
    }

    .c-home .c-id-field strong {
        font-size: 12px;
    }

    .c-home .c-id-footer {
        padding: 9px 10px;
        font-size: 7px;
    }

    .c-home .c-health {
        padding: 15px 12px;
    }

    .c-home .c-stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
    }

    .c-home .c-stat {
        padding: 11px 10px;
    }

    .c-home .c-stat span {
        font-size: 12px;
    }

    .c-home .c-stat strong {
        font-size: 13px;
    }

    .c-home .c-referral {
        gap: 10px;
        padding: 15px;
    }

    .c-home .c-ref-icon {
        flex-basis: 40px;
        width: 40px;
        height: 40px;
    }
}

@media (max-width: 340px) {
    .c-home .c-stats {
        grid-template-columns: 1fr;
    }

    .c-home .c-id-header-text small {
        display: none;
    }

    .c-home .c-id-footer {
        flex-wrap: wrap;
    }
}




</style>

@endsection
@section('content')
    <div class="c-home">
        {{-- 1. ANNOUNCEMENTS --}}
        <section class="c-section" id="announcements">
            <div class="c-heading">
                <div>
                    <h2>Announcements</h2>
                    <p class="c-muted">Latest health center notices and community updates</p>
                </div>
                <a href="{{ route('citizen.announcements') }}"
                    style="font-size:13px;color:#1d4ed8;font-weight:700;text-decoration:none">
                    View all →
                </a>
            </div>
            @if(isset($announcements) && $announcements->count())
                @php
                    /*
                     * Upcoming events are sorted by date, with the nearest event first.
                     * Past events follow upcoming events, and announcements without
                     * an event date appear last. Urgency is based on date proximity.
                     */
                    $today = \Carbon\Carbon::today();
                    $sortedAnnouncements = collect($announcements)->sortBy(function ($announcement) use ($today) {
                        $attributes = $announcement->getAttributes();

                        $eventDateValue = $attributes['event_date']
                            ?? $attributes['event_start_date']
                            ?? $attributes['schedule_date']
                            ?? $attributes['date_of_event']
                            ?? $attributes['start_date']
                            ?? $attributes['event_datetime']
                            ?? $attributes['date']
                            ?? null;

                        try {
                            $eventDate = $eventDateValue
                                ? \Carbon\Carbon::parse($eventDateValue)->startOfDay()
                                : null;
                        } catch (\Throwable $e) {
                            $eventDate = null;
                        }

                        if ($eventDate === null) {
                            $group = 3;
                            $dateKey = 0;
                        } elseif ($eventDate->greaterThanOrEqualTo($today)) {
                            $group = 0;
                            $dateKey = $eventDate->timestamp;
                        } else {
                            $group = 1;
                            $dateKey = $eventDate->timestamp;
                        }

                        return sprintf('%d-%012d-%012d', $group, $dateKey, -(optional($announcement->created_at)->timestamp ?? 0));
                    })->values();
                @endphp

                <div class="c-ann-slider" id="citizenAnnouncementSlider" aria-label="Health announcements carousel">
                    <div class="c-ann-track" id="citizenAnnouncementTrack">
                        @foreach($sortedAnnouncements as $announcement)
                            @php
                                $announcementAttributes = $announcement->getAttributes();
                                $eventDateValue = $announcementAttributes['event_date']
                                    ?? $announcementAttributes['event_start_date']
                                    ?? $announcementAttributes['schedule_date']
                                    ?? $announcementAttributes['date_of_event']
                                    ?? $announcementAttributes['start_date']
                                    ?? $announcementAttributes['event_datetime']
                                    ?? $announcementAttributes['date']
                                    ?? null;

                                try {
                                    $parsedEventDate = $eventDateValue
                                        ? \Carbon\Carbon::parse($eventDateValue)->startOfDay()
                                        : null;
                                    $displayEventDate = $parsedEventDate ? $parsedEventDate->format('M d, Y') : null;
                                    $isUrgentAnnouncement = $parsedEventDate !== null
                                        && $parsedEventDate->greaterThanOrEqualTo(\Carbon\Carbon::today())
                                        && $parsedEventDate->lessThanOrEqualTo(\Carbon\Carbon::today()->addDays(7));
                                } catch (\Throwable $e) {
                                    $displayEventDate = null;
                                    $isUrgentAnnouncement = false;
                                }
                            @endphp
                            <div class="c-ann-slide">
                                <article class="c-ann-card">
                                    <div class="c-ann-cover">
                                        <span class="c-ann-label">📢 Health Advisory</span>
                                        @if($isUrgentAnnouncement)
                                            <span class="c-ann-priority">URGENT</span>
                                        @endif
                                        <h3>{{ $announcement->title }}</h3>
                                    </div>
                                    <div class="c-ann-body">
                                        <p>
                                            {{ \Illuminate\Support\Str::limit(
                                                $announcement->content
                                                ?? $announcement->description
                                                ?? 'Please contact the Barangay Health Center for more information.',
                                                180
                                            ) }}
                                        </p>
                                        <div class="c-ann-footer">
                                            <span>
                                                @if($displayEventDate)
                                                    Event date: {{ $displayEventDate }}
                                                @else
                                                    Posted: {{ optional($announcement->created_at)->format('M d, Y') }}
                                                @endif
                                            </span>
                                            <span>Barangay Amuyong</span>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                    </div>
                    @if($sortedAnnouncements->count() > 1)
                        <div class="c-ann-controls">
                            <span class="c-ann-indicator" id="citizenAnnouncementIndicator">Announcement 1 of {{ $sortedAnnouncements->count() }}</span>
                            <div class="c-ann-control-buttons">
                                <button type="button" class="c-ann-arrow" id="citizenAnnouncementPrev" aria-label="Previous announcement">‹</button>
                                <button type="button" class="c-ann-arrow" id="citizenAnnouncementNext" aria-label="Next announcement">›</button>
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="c-panel c-empty">
                    No announcements available right now.
                </div>
            @endif
        </section>
        

{{-- 2. CITIZEN CARD --}}
<section class="c-section" id="citizen-card">
    <div class="c-heading">
        <div>
            <h2>My Citizen Health Card</h2>
            <p class="c-muted">Your registered identity details</p>
        </div>
    </div>

    @if($citizen)
        {{-- ACTUAL ID CARD --}}
        <div class="c-panel c-id-card">

            {{-- BLUE HEADER --}}
            <div class="c-id-header">
                <img src="{{ asset('images/amuyong.png') }}"
                     alt="Barangay Amuyong Logo">

                <div class="c-id-header-text">
                    <h3>BHEAMS</h3>
                    <strong>BARANGAY AMUYONG</strong>
                    <small>BARANGAY HEALTH AND EMERGENCY MANAGEMENT SYSTEM</small>
                </div>

            </div>

            {{-- ID BODY --}}
            <div class="c-id-body">

                {{-- CITIZEN PHOTO --}}
                <div class="c-photo">
                    @if($citizen->photo_path)
                        <img src="{{ asset('storage/'.$citizen->photo_path) }}"
                             alt="Citizen photo">
                    @else
                        <span>
                            {{ strtoupper(substr($citizen->Citizen_FName ?? 'C', 0, 1).substr($citizen->Citizen_LName ?? 'I', 0, 1)) }}
                        </span>
                        <small>NO PHOTO</small>
                    @endif
                </div>

                {{-- CITIZEN DETAILS --}}
                <div class="c-id-details">
                    <h3>CITIZEN HEALTH ID</h3>

                    <div class="c-id-field">
                        <span>FULL NAME</span>
                        <strong>
                            {{ strtoupper($citizen->Citizen_LName) }},
                            {{ strtoupper($citizen->Citizen_FName) }}
                        </strong>
                    </div>

                    <div class="c-id-field">
                        <span>CITIZEN IDENTIFICATION NUMBER</span>
                        <strong class="c-id-number">
                            C-{{ str_pad($citizen->id, 4, '0', STR_PAD_LEFT) }}
                        </strong>
                    </div>

                    <div class="c-id-details-grid">
                        <div class="c-id-field">
                            <span>DATE OF BIRTH</span>
                            <strong>
                                {{ $citizen->Citizen_BirthDate
                                    ? \Carbon\Carbon::parse($citizen->Citizen_BirthDate)->format('M d, Y')
                                    : 'N/A' }}
                            </strong>
                        </div>

                        <div class="c-id-field">
                            <span>CONTACT NUMBER</span>
                            <strong>{{ $citizen->Citizen_ContactNo ?: 'N/A' }}</strong>
                        </div>

                        <div class="c-id-field">
                            <span>ADDRESS / PUROK</span>
                            <strong>{{ $citizen->Citizen_Purok ?: 'N/A' }}</strong>
                        </div>
                    </div>
                </div>

            {{-- CENTERED QR CODE, MATCHING THE MY HEALTH CARD PAGE --}}
            <div class="c-id-qr c-id-qr-centered">
                <div class="c-id-qr-frame">
                    <img src="{{ $qrImage }}" alt="Citizen Verification QR Code">
                </div>
                <span>SCAN TO VERIFY</span>
            </div>
            </div>

            {{-- FOOTER AT THE BOTTOM OF THE ID --}}
            <div class="c-id-footer">
                <span>BARANGAY AMUYONG</span>
                <span>OFFICIAL CITIZEN IDENTIFICATION</span>
            </div>
        </div>

        {{-- BUTTON OUTSIDE THE ID CARD --}}
        <div class="c-id-actions-outside">
            <a class="c-btn"
               href="{{ route('citizen.ecard') }}">
                Open Health Card & Print Options
            </a>
        </div>

    @else
        <div class="c-panel c-empty">
            Your account is not linked to a citizen profile yet.
            Please contact the Barangay Health Worker.
        </div>
    @endif
</section>


        {{-- 3. FULL HEALTH INFORMATION --}}
        <section class="c-section" id="health-information">
            <div class="c-heading">
                <div>
                    <h2>Full Health Information</h2>
                    <p class="c-muted">Immunization and medicine records from the health center</p>
                </div>
            </div>
            <div class="c-panel c-health">
                @if($citizen)
                    <div class="c-stats">
                        <div class="c-stat">
                            <span>Citizen name</span>
                            <strong>{{ $citizen->Citizen_LName }}, {{ $citizen->Citizen_FName }}</strong>
                        </div>
                        <div class="c-stat">
                            <span>Date of birth / age</span>
                            <strong>{{ $citizen->Citizen_BirthDate ?: 'N/A' }} / {{ $citizen->Citizen_Age ?? 'N/A' }}</strong>
                        </div>
                        <div class="c-stat">
                            <span>Purok</span>
                            <strong>{{ $citizen->Citizen_Purok ?: 'N/A' }}</strong>
                        </div>
                        <div class="c-stat">
                            <span>Contact</span>
                            <strong>{{ $citizen->Citizen_ContactNo ?: 'N/A' }}</strong>
                        </div>
                    </div>
                    <div class="c-record">
                        <h3>Immunization History</h3>
                        <div class="c-tablewrap">
                            <table class="c-table">
                                <thead>
                                    <tr>
                                        <th>Vaccine</th>
                                        <th>Product / Dose</th>
                                        <th>Date administered</th>
                                        <th>Administered by</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($citizen->vaccinations as $vax)<tr>
                                        <td>COVID-19 / Health Vax</td>
                                        <td>{{ $vax->vaccine_name }} — {{ $vax->dose_number }}</td>
                                        <td>{{ $vax->date_administered ?: 'N/A' }}</td>
                                        <td>{{ $vax->administered_by ?: 'N/A' }}</td>
                                    </tr>
                                @empty<tr>
                                    <td class="none" colspan="4">No vaccination records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="c-record">
                <h3>Dispensed Medicines</h3>
                <div class="c-tablewrap">
                    <table class="c-table">
                        <thead>
                            <tr>
                                <th>Medicine</th>
                                <th>Dosage</th>
                                <th>Quantity</th>
                                <th>Date dispensed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($citizen->medications as $med)<tr>
                                <td>{{ $med->medicine_name }}</td>
                                <td>{{ $med->dosage ?: 'N/A' }}</td>
                                <td>{{ $med->quantity_dispensed }}</td>
                                <td>{{ $med->date_dispensed ?: 'N/A' }}</td>
                            </tr>
                        @empty<tr>
                            <td class="none" colspan="4">No medication records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="c-actions" style="padding:20px 0 0">
        <a class="c-btn" href="{{ route('citizen.ecard') }}">View Printable Health Record</a>
    </div>
@else <div class="c-empty">Health information will appear here once your account is linked to a citizen profile.</div>
@endif
</div>
</section>
{{-- 4. REQUEST REFERRAL --}}
<section class="c-section" id="request-referral">
    <div class="c-heading">
        <div>
            <h2>Request a Referral</h2>
            <p class="c-muted">Need assistance being referred to another health facility?</p>
        </div>
    </div>
    <div class="c-panel c-referral">
        <div class="c-ref-icon">＋</div>
        <div class="c-ref-copy">
            <h3>Talk to your Barangay Health Worker</h3>
            <p>Contact or visit the BHW so your concern can be assessed and your referral can be recorded. This page currently provides referral guidance; an online referral submission form is not yet available. For emergencies, go to the nearest emergency facility.</p>
        </div>
        <a class="c-btn c-green" href="tel:+639587891234">Call Health Center</a>
    </div>
</section>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {
    // Announcement slider: one announcement per slide.
    const announcementTrack = document.getElementById('citizenAnnouncementTrack');
    const announcementSlides = announcementTrack
        ? Array.from(announcementTrack.querySelectorAll('.c-ann-slide'))
        : [];
    const announcementPrev = document.getElementById('citizenAnnouncementPrev');
    const announcementNext = document.getElementById('citizenAnnouncementNext');
    const announcementIndicator = document.getElementById('citizenAnnouncementIndicator');
    let announcementIndex = 0;

    function updateAnnouncementSlider() {
        if (!announcementTrack || !announcementSlides.length) return;
        const slideWidth = announcementSlides[0].getBoundingClientRect().width;
        const gap = parseFloat(getComputedStyle(announcementTrack).gap) || 0;
        announcementTrack.style.transform = `translateX(-${announcementIndex * (slideWidth + gap)}px)`;
        if (announcementIndicator) {
            announcementIndicator.textContent = `Announcement ${announcementIndex + 1} of ${announcementSlides.length}`;
        }
        if (announcementPrev) announcementPrev.disabled = announcementIndex === 0;
        if (announcementNext) announcementNext.disabled = announcementIndex === announcementSlides.length - 1;
    }

    if (announcementSlides.length > 1) {
        announcementPrev?.addEventListener('click', function () {
            announcementIndex = Math.max(0, announcementIndex - 1);
            updateAnnouncementSlider();
        });
        announcementNext?.addEventListener('click', function () {
            announcementIndex = Math.min(announcementSlides.length - 1, announcementIndex + 1);
            updateAnnouncementSlider();
        });
        window.addEventListener('resize', updateAnnouncementSlider);
        updateAnnouncementSlider();
    }

    const nav = document.getElementById('citizenSectionNav');
    if (!nav) return;

    const links = [...nav.querySelectorAll('a[href^="#"]')];

    const sections = links
        .map(link => document.getElementById(
            link.getAttribute('href').substring(1)
        ))
        .filter(Boolean);

    if (!sections.length) return;

    function setActive(section) {
        links.forEach(link => {
            const active =
                link.getAttribute('href') === '#' + section.id;

            link.classList.toggle('active', active);

            if (active) {
                link.setAttribute('aria-current', 'location');
            } else {
                link.removeAttribute('aria-current');
            }
        });
    }

    function updateActiveTab() {
        const header = document.querySelector('.top-bar');
        const headerHeight = header ? header.offsetHeight : 0;

        let current = sections[0];
        const activationPoint = headerHeight + 25;

        sections.forEach(section => {
            if (section.getBoundingClientRect().top <= activationPoint) {
                current = section;
            }
        });

        // Only select the final tab when the referral section itself is in view.
        const lastSection = sections[sections.length - 1];
        if (lastSection && lastSection.getBoundingClientRect().top < window.innerHeight * 0.72) {
            current = lastSection;
        }

        setActive(current);
    }

    links.forEach(link => {
        link.addEventListener('click', function (event) {
            const target = document.getElementById(
                this.getAttribute('href').substring(1)
            );

            if (!target) return;

            event.preventDefault();
            setActive(target);

            const header = document.querySelector('.top-bar');
            const headerHeight = header ? header.offsetHeight : 0;

            const targetY =
                target.getBoundingClientRect().top +
                window.scrollY -
                headerHeight -
                12;

            window.scrollTo({
                top: Math.max(0, targetY),
                behavior: 'smooth'
            });
        });
    });

    window.addEventListener('scroll', updateActiveTab, {
        passive: true
    });

    window.addEventListener('resize', updateActiveTab);

    updateActiveTab();
});
</script>





<style>
/* Dashboard health card matches My Health Card: photo left, details middle, QR right. */
.c-home .c-id-body {
    display: grid;
    grid-template-columns: 132px minmax(0, 1fr) 150px;
    align-items: center;
    gap: 24px;
}
.c-home .c-id-body > .c-id-qr-centered {
    grid-column: 3;
    grid-row: 1;
    width: 100%;
    margin: 0;
    padding: 0;
    align-self: center;
    justify-self: end;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    gap: 6px;
}
.c-home .c-id-body > .c-id-qr-centered .c-id-qr-frame {
    padding: 6px;
    border: 1px solid #dbeafe;
    border-radius: 8px;
    background: #fff;
}
.c-home .c-id-body > .c-id-qr-centered .c-id-qr-frame img {
    display: block;
    width: 100px;
    height: 100px;
    object-fit: contain;
}
.c-home .c-id-body > .c-id-qr-centered > span {
    color: #173b82;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .5px;
}
@media (max-width: 760px) {
    .c-home .c-id-body {
        grid-template-columns: 76px minmax(0, 1fr) 90px;
        gap: 10px;
        padding: 15px 12px;
    }
    .c-home .c-id-body > .c-id-qr-centered {
        grid-column: 3;
        grid-row: 1;
        padding: 0;
    }
    .c-home .c-id-body > .c-id-qr-centered .c-id-qr-frame img {
        width: 72px;
        height: 72px;
    }
}
@media (max-width: 420px) {
    .c-home .c-id-body {
        grid-template-columns: 58px minmax(0, 1fr) 66px;
        gap: 7px;
        padding: 12px 8px;
    }
    .c-home .c-id-body .c-photo {
        width: 58px;
        height: 76px;
    }
    .c-home .c-id-body > .c-id-qr-centered .c-id-qr-frame img {
        width: 54px;
        height: 54px;
    }
    .c-home .c-id-body > .c-id-qr-centered .c-id-qr-frame {
        padding: 3px;
    }
    .c-home .c-id-body > .c-id-qr-centered > span {
        font-size: 6px;
    }
}
</style>

@endsection
