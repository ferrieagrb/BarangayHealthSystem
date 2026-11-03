# Medical terminology suggestions

The BHW clinical-entry forms use one authenticated search endpoint for medicine,
vaccine, and diagnosis suggestions. It receives only the typed term and search
type; citizen identifiers, names, records, and form contents are never sent to
terminology providers. Suggestions do not save or replace a record until the
BHW explicitly selects one. Manual medicine and diagnosis entry remains
available when a provider is offline.

## Sources and coverage

- **Medicines:** NLM RxNorm approximate-term search is used for generic and
  brand-name concepts and spelling variants. RxNorm is a U.S.-focused
  normalized terminology. NLM's current RxNorm release is updated monthly;
  RxNav reflects the monthly release. This is not a complete Philippine
  formulary. Matching
  medicine names from BHEAMS records are also available locally to the BHW
  within their assigned citizen access, along with in-stock inventory items
  whose category identifies them as medicines or pharmaceuticals.
- **Vaccines:** the official CDC CVX XML code set is cached for 24 hours and
  only active records are considered. Search results retain the CDC short name,
  official full vaccine name/formulation, CVX code, and terminology notes.
  Recognition in CVX is separate from clinical eligibility: Available and
  Unavailable are based only on a matching BHEAMS vaccine-specific age rule
  evaluated on the administration date. Terms with conditional, risk-based
  eligibility (including unmapped pneumococcal formulations) are labeled
  Conditional; other recognized terms without a configured age rule are
  labeled Age eligibility not established. These suggestions can be selected
  only through the explicitly labeled historical-recording pathway, which
  retains eligibility uncertainty instead of treating the vaccine as age
  eligible. BHW users do not need a separate approval to document an actual
  prior vaccination. The backend verifies the signed CDC selection, rechecks
  configured age and dose rules, and prevents duplicate citizen/dose/date
  records. CDC records include their own last-updated date.
- **Diagnoses:** the WHO ICD-11 MMS API uses the configured release (default
  `2026-01`). WHO adds releases and language versions through its API. WHO
  suggestions include a classification code when supplied. Existing diagnosis
  text is not changed, and ICD codes are not written to the database.
  The current 2026-01 MMS language list includes English but not Filipino.
- Local diagnosis suggestions are restricted to records the signed-in BHW is
  permitted to view. They are labeled as BHEAMS records and are never sent to
  an external service.

Provider responses are cached for 24 hours by default. Search requests time out
after five seconds by default. Adjust these values with
`MEDICAL_TERMINOLOGY_CACHE_MINUTES` and `MEDICAL_TERMINOLOGY_TIMEOUT`.

## WHO ICD-11 setup

WHO requires an API account and OAuth2 client credentials. Configure these
server-side in the deployment `.env`; do not commit credentials or expose them
in browser code:

```dotenv
WHO_ICD_CLIENT_ID=
WHO_ICD_CLIENT_SECRET=
WHO_ICD_RELEASE=2026-01
```

Without credentials, WHO suggestions are reported as unavailable and BHW users
can continue with local matches or manual entry. The API key must be obtained
from the [WHO ICD API portal](https://icd.who.int/icdapi). WHO requires the
`icdapi_access` scope and documents the token flow and API usage in its
[authentication guide](https://icd.who.int/docs/icd-api/API-Authentication/).

## Source terms and licensing

The RxNorm API is provided by the U.S. National Library of Medicine. Review
current NLM/UMLS and source-vocabulary terms before redistributing terminology
outside the application, especially when using vocabularies with separate
rights. This integration requests RxNorm-sourced terms only. NLM asks products
using RxNorm to include this acknowledgement:

> This product uses publicly available data courtesy of the U.S. National
> Library of Medicine (NLM), National Institutes of Health, Department of
> Health and Human Services; NLM is not responsible for the product and does
> not endorse or recommend this or any other product.

See the [NLM RxNorm resources](https://www.nlm.nih.gov/research/umls/rxnorm/),
[RxNorm FAQ](https://www.nlm.nih.gov/research/umls/rxnorm/faq.html), and [RxNorm
Terms of Service](https://www.nlm.nih.gov/research/umls/rxnorm/docs/termsofservice.html)
and [RxNav API](https://rxnav.nlm.nih.gov/REST/approximateTerm.json?term=metformin).

The vaccine feed is the [official CDC IIS code-set source](https://www.cdc.gov/iis/code-sets/index.html).
WHO ICD-11 requires registration and use under WHO's current API and
classification terms. Confirm the current terms for each source before
redistributing or republishing its content.
