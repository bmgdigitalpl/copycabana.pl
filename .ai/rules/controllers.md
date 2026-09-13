---
paths:
  - 'app/Http/Controllers/**'
---

# Controllers

## Bind idempotency keys to request payloads
Order and B2B submission endpoints require Idempotency-Key. Persist its scope-specific fingerprint and return 409 when a key is reused with different validated data; catch unique-key races by replaying the matching committed resource.
