---
paths:
  - app/Services/PayuService.php
---

# Services

## Keep mock payments local only
PAYMENT_PROVIDER=mock is exclusively for local and testing environments. It uses the internal local payment page and must throw rather than process a mock payment in production; production uses PAYMENT_PROVIDER=payu.
