# Kaar Booking test plan

`verify.ps1` is the local release gate. PHP lint and PHPUnit run when a PHP executable and Composer dependencies are available; structural, XML, schema, route, privacy, and archive checks always run.

## Automated suites required before production

- Unit: booking transitions, pricing snapshots/rules, availability overlap, compliance expiry boundaries, OTP verification, token issue/rotation/reuse, cancellation/refund rules.
- Integration: clean install and upgrade on Joomla 6 and latest Joomla 5, database transactions, concurrent resource/seat holds, task execution, mail/push/payment adapters.
- API: public-route allowlist, authentication, role and object ownership, validation, rate limits, idempotency, pagination, token expiry/logout/refresh reuse.
- Security: OTP enumeration/brute force, bearer-token leakage, forged/replayed webhooks, KYC MIME/upload attacks, KYC preview authorization/no-store/expiry, absence of download/export paths, log redaction.
- UI: keyboard, focus, RTL, 200%/400% zoom, mobile/narrow Metro columns, two instances per page, details hidden/visible with Book persistent.
- End-to-end: future vehicle request → manual review → payment → manual confirmation → allocation; two-day bus package; KYC verify/delete; ride request → driver arrival → trip → cancellation; WordPress handoff.

Provider-dependent tests must run against sandbox accounts and local fakes. A provider stub returning success is not acceptable evidence for payment, messaging, OCR, maps, or realtime dispatch.
