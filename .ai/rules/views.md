---
paths:
  - 'resources/views/**'
  - 'resources/views/{home,prace-dyplomowe,druk-pdf,druk-dla-firm}.blade.php'
---

# Views

## Main production layout is layouts/main; old pages live under archive/
The main production pages (home, prace-dyplomowe, druk-pdf, druk-dla-firm) extend layouts/main and load css/concept.css + js/concept.js. Older pages that the concept replaced (old home, services) were moved under resources/views/archive and keep extending layouts/site; they are served only from /archive/* routes. Do not point new pages back at layouts/site.

## Production views reuse the x-concept.* component namespace
The migrated concept pages still use the components/concept/* Blade components via x-concept.* tags and Alpine data components (ccConfigurator, ccPdfConfigurator, ccB2bConfigurator, ccDropzone) defined in public/js/concept.js. The "concept" prefix is now just an internal namespace for the main pages — keep using it for these templates.
