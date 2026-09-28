# Mobile API security

The initial authentication design was reviewed against the local Rudra Pro School ERP `1.3.55` package. Kaar adopts its sound opaque-session lifecycle while deliberately omitting long-lived Joomla API-token fallback and direct password-table login.

## Token lifecycle

- Email OTP is the customer sign-in factor. OTP lifetime is administrator-controlled from 3–20 minutes (default 10), single-use, attempt-limited, resend-limited, and stored only as a password hash.
- Access tokens are 256-bit random opaque values prefixed `kba_`. TTL is administrator-controlled from 5–60 minutes (default 15).
- Refresh tokens are 384-bit random opaque values prefixed `kbr_`. TTL is administrator-controlled from 1–90 days (default 30).
- The database stores SHA-256 token hashes, never token values. High-entropy random tokens make deterministic lookup hashes appropriate; passwords and OTPs use password hashing.
- Refresh tokens rotate on every use. The previous session row is atomically revoked before a replacement is issued.
- Reuse of a rotated/revoked refresh token revokes every active session in that token family.
- Administrators control the maximum active devices from 1–10 (default 5); oldest active sessions are revoked when the limit is exceeded.
- Logout revokes the current session. Password/account block, security events, and administrator actions should also revoke all user sessions.

## Transport and client storage

- Only HTTPS is supported in production. Tokens use `Authorization: Bearer`, never query parameters.
- Flutter stores both tokens in platform secure storage, never SharedPreferences, logs, analytics, crash metadata, notifications, or URLs.
- The access token is attached only to the configured API origin. Redirects to another origin must drop authorization.
- A single refresh operation is shared by concurrent failed requests to avoid refresh races.
- Push tokens are bound to a valid API session and removed/revoked with that session.

## Authorization

Authentication only establishes the Joomla user. Every controller must still enforce role/capability and object ownership. Customer identifiers, driver identifiers, booking ownership, and KYC access are derived from the authenticated identity rather than accepted from request bodies.

Public auth routes return generic OTP responses to reduce email enumeration. Apply an API gateway rate limit by hashed email, IP/device signals, route group, and authenticated user. Rate-limit storage failures should fail closed for OTP and token issuance.

## Remaining production work

- Configure the email provider and test deliverability.
- Add an API gate with durable rate-limit storage and trusted-proxy handling.
- Add session/device list and revoke endpoints.
- Add push-token registration bound to a session.
- Define incident-driven family/user revocation and security-event notifications.
- Run penetration tests for OTP brute force, enumeration, token replay/reuse, route authorization, proxy header handling, and sensitive logging.
