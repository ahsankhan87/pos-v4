# Review Findings: Sales New Page

Reviewed file: app/Views/sales/new.php

## 🔴 CRITICAL

None.

## 🟡 WARNING

1. IMEI cart rows did not hydrate available IMEIs when the row was rendered. Fixed by preloading the IMEI endpoint and restoring selected IMEIs before reinitializing Select2.
   - Reference: [app/Views/sales/new.php](app/Views/sales/new.php#L1423)
   - Why it matters: IMEI-enabled products could appear in the cart with an empty selector, preventing the cashier from choosing valid IMEIs.
   - Status: ✅ Fixed

## 🔵 INFO

1. The cart IMEI selector now reuses the existing `api/products/available-imeis` response shape instead of relying on a late Select2 fetch.
   - Reference: [app/Views/sales/new.php](app/Views/sales/new.php#L1466)

## ✅ GOOD

1. The endpoint already scopes IMEIs to the active store and product.
2. Selected IMEIs are preserved across cart re-renders.
