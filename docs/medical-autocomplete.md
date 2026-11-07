# Medical terminology autocomplete

The shared `MedicalTerminologySearch` service, `MedicalTerminologyController`, and
`medical-autocomplete.js` serve the existing forms through one authenticated
`POST /medical-terminology/search` endpoint. Its historical route name remains
`bhw.medical-terminology.search`. Citizen, BHW, Admin, and Super Admin accounts
can search terminology; this does not grant permission to write medical records.
The assets load once through `partials.appearance-assets` in each role layout.

## Sources

* **Medicine:** published DOH Philippine National Formulary and WHO Essential
  Medicines List references provide fast, sourced suggestions. The bundled
  reference was retrieved on 2026-11-07 and contains 1,013 distinct medicine names.
  It is separate from the BHEAMS inventory. When no reference name matches, the
  service searches NLM RxNorm and its spelling-suggestion endpoint. Named RXNORM,
  ATC, USP, INN, and BAN vocabulary results are accepted. Brand coverage depends
  on the official sources; a Philippine brand not listed must remain manual.
  Verified brand–generic records are loaded separately from
  `resources/data/medical-terminology/verified-brands.json`. The Fluimucil entry
  maps to Acetylcysteine, with Philippine FDA registration DR-XY22982 and HPRA
  cross-reference URLs retained as provenance. Exact brand, partial brand, typo,
  and generic-name searches use this same data-driven lookup in all medicine
  fields. Selecting a brand preserves that name and displays the generic name
  separately. Refreshing WHO/DOH generic references does not overwrite this file.
  This curated reference is not an exhaustive Philippine brand registry or a
  live registration check; adding other brands requires verified source records.
* **Diagnosis:** WHO ICD-11 when credentials are configured; NLM Medical Conditions
  supplies standardized condition names and ICD-10-CM mappings when WHO is absent,
  fails, or has no matches. NLM searches synonyms and partial terms, then uses a
  bounded fuzzy query for misspellings. Codes identify their classification system;
  ICD-10-CM codes are not represented as ICD-11 or vaccine codes. If online
  diagnosis providers fail, 407 condition names from WHO eEML remain available;
  these reference names are not assigned invented ICD codes.
  When the smaller condition list has no entry (for example IgA vasculitis), the
  service also searches NLM MeSH descriptors. Full names and multiword typos are
  matched against every query word to avoid unrelated conditions. MeSH identifiers
  are explicitly labeled MeSH, never ICD. This broader lookup is not limited to a
  hardcoded list of individual diagnoses.
* **Vaccines:** the existing CDC CVX integration and configured vaccine options.
  Terminology recognition does not establish age eligibility. Recording, age on
  administration date, historical recording, and signed selection tokens retain
  their existing backend validation.
* **Supply:** the selected category determines the source. Medicine and vaccine
  categories use the online sources above. Only ordinary supplies use catalog
  entries in the same category, labeled as unverified local supply names.

Medicine and diagnosis spelling suggestions never read inventory, maintenance,
medication, or diagnosis records. An API outage produces a visible message and
uses sourced WHO/DOH references when available, preserving manual entry otherwise.
No existing patient or inventory entries are corrected or renamed automatically.

## Form coverage

* Patient details and patient show: maintenance medicine and primary diagnosis.
* QR scanner: dynamically inserted diagnosis form.
* Vaccination recording and calendar scheduling: existing vaccine selectors,
  including dynamically inserted schedule rows.
* BHW Add Supply and Admin New Catalog Item: category-aware item autocomplete.
* Referrals: medication given and self-medication, using separated medicine names.

The current repository has no separate Edit Maintenance or Edit Supply view or
maintenance update route. These changes do not create new record-editing workflows.
Citizen and Super Admin pages have no medical record entry forms; their shared
layout supports the component without turning historical reports or filters into
external terminology searches. Clinical notes, symptoms, dosage, frequency,
quantity, and treatment periods are not rewritten or sent as search context.

## Reuse

Use `data-medical-autocomplete="medicine|diagnosis|vaccine|supply"` on an input,
textarea, or the existing vaccine select. Supply controls require a category
control named `category` in the same form. `data-medical-multiple` searches the
comma-, semicolon-, or newline-separated medicine at the caret and replaces only
that entry, preserving other medicines. Moving the caret starts the relevant
search. The mutation observer initializes dynamically added controls once; both
the Blade partial and frontend script guard against duplicate initialization.

Search begins at two characters, debounces for 300 ms, and sends only the search
term, type, and supply category. Outdated requests are aborted/ignored. The backend
uses a configurable 10-second timeout and existing cache settings; credentials stay server-side. The
authenticated endpoint allows 60 requests per minute. Dropdowns stay in form flow
to fit modal scroll containers, highlight matching text, and support arrows,
Enter, Escape, and outside clicks. Keyboard navigation skips disabled vaccine
options. Form reset clears suggestions and resynchronizes the vaccine search.
Selecting a name never populates dosage fields.

Catalog creation rejects equivalent names in the same category after normalizing
case, whitespace, and punctuation. Different strengths and formulations are not
merged using fuzzy matching. Existing stock and records are preserved.

## Configuration and checks

RxNorm, CDC CVX, and NLM Medical Conditions do not require API keys. Optional WHO
credentials are `WHO_ICD_CLIENT_ID` and `WHO_ICD_CLIENT_SECRET`; release, timeout,
and cache lifetime retain the existing environment settings. Outbound HTTPS must
be available to the configured official service hosts.

The DOH ePNF host was unavailable during implementation. This is not represented
as a working live DOH API. Its published formulary spreadsheet is linked in the
official DOH FOI response at
https://www.foi.gov.ph/requests/the-philippine-national-formulary-in-excel/.
`resources/data/medical-terminology/official-references.json` stores source URLs
and retrieval date. Refresh it with `php artisan medical:refresh-references`.
The command preserves the old file if a download fails or a source format changes.
This reference verifies terminology, not current registration, availability,
formulary inclusion changes, or treatment suitability.

Actual-page browser checks use complete Blade-rendered patient forms with
synthetic records and the real PHP search service:

```text
# Generate fixtures using AppearancePreferencesTest and BHEAMS_APPEARANCE_PAGES.
node tests/browser/medical-actual-pages.cjs
```

These checks caught and fixed invalid patient-page scripts, which isolated
autocomplete fixtures did not reveal. Search status now appears immediately.

Run:

```text
php artisan test --compact --filter="MedicalTerminologySearchTest|VaccinationEligibilityTest"
node tests/browser/medical-autocomplete.cjs
```

The browser check needs `playwright-core` (or `PLAYWRIGHT_MODULE` pointing to a
temporary installation) and Edge, or `BHEAMS_BROWSER` pointing to another Chromium
binary. It uses real shared frontend assets with mocked responses and no patient
database. Backend tests use fake providers and an in-memory test database.

Official API references:

* https://lhncbc.nlm.nih.gov/RxNav/APIs/api-RxNorm.getApproximateMatch.html
* https://lhncbc.nlm.nih.gov/RxNav/APIs/api-RxNorm.getSpellingSuggestions.html
* https://clinicaltables.nlm.nih.gov/apidoc/conditions/v3/doc.html
