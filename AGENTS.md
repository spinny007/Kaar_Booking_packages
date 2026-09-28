# Kaar Booking Repository — Agent Engineering Rules

## Purpose and scope

These instructions apply to the whole repository. More specific `AGENTS.md` files may add rules for a subtree but must not weaken security, privacy, compliance, payment, or test requirements here.

The repository contains three delivery areas:

- `Joomla/`: primary backend, administrator, website, API, modules, plugins, and installable package.
- `Android/`: one Flutter application with role-separated customer and driver interfaces; the directory name is retained even if shared Flutter code also targets iOS.
- `WordPress/`: later API-connected WordPress plugin.

Read `Joomla/PRODUCT_PLAN.md` before designing or implementing product behaviour.

## Product boundaries

- Scheduled bookings are the first production scope. Realtime ride hailing is a later bounded subsystem, not a variation of a normal page request.
- Customers may submit future-dated standalone vehicle and tour/package bookings, but every scheduled booking requires manual administrator review, confirmation, and vehicle/driver allocation. Only the later realtime online-driver flow may use automatic dispatch.
- Joomla is the initial system of record and control plane.
- Joomla 6 is the primary target. Support the latest Joomla 5 release when it can be done through documented public APIs and isolated compatibility code.
- Keep the system single-operator usable while preserving `operator_id` in core domain records for future multi-operator support.
- Never infer jurisdiction-specific legal compliance from an uploaded file or OCR result. Requirements are configurable and require qualified human approval.

## Architecture rules

- Prefer a modular monolith for the Joomla MVP. Introduce a separate service only for a demonstrated operational need such as realtime dispatch/location volume.
- Separate Domain, Application, Infrastructure, Administrator, Site, and API responsibilities.
- Domain rules must not live in controllers, views, templates, modules, or Flutter screens.
- Depend inward: UI and infrastructure depend on application/domain contracts, not the reverse.
- Use explicit value objects for money, currency, distance, duration, date ranges, document expiry, and identifiers where practical.
- Use a clock abstraction for expiry, holds, pricing effective dates, and tests. Store timestamps in UTC and render in the configured/operator timezone.
- Make booking, payment, refund, webhook, notification, and dispatch mutations idempotent.
- Preserve an append-only history for state transitions and material administrative actions.
- Do not duplicate pricing, availability, compliance, or state-machine logic in Joomla modules, WordPress, or Flutter clients.
- Prefer small provider interfaces for payments, maps/routing, OCR, storage, malware scanning, email, SMS/WhatsApp, and push.

## Joomla engineering rules

- Target Joomla 6 public APIs and avoid APIs deprecated for removal in Joomla 6. Do not require the backward-compatibility plugin.
- Use namespaced classes, service providers, dependency injection, Joomla forms, ACL, language files, prepared database queries, Web Asset Manager, and update SQL/migrations.
- Do not use static `Factory` access when the required application, database, dispatcher, router, document, or service can be injected.
- Use Joomla ACL for every administrator and site action, then apply object-level ownership/access checks.
- Every schema change requires forward installation/update support and an automated migration test. Never edit an already released migration in a way that changes its historical meaning.
- Build one installable package with component, modules, and plugins versioned together unless a documented reason requires separate delivery.
- Menu item views require metadata XML, language strings, routing/SEF coverage, access checks, and page metadata behaviour.
- Modules must be independently renderable, repeatable on the same page, assignable per menu item, and safe inside page-builder columns.
- The future-booking module must support service type, route/stops, future departure/return dates, pickup time, and responsive accessible date selection. The package module must support structured inclusions and configurable detail visibility while keeping Book visible whenever the package is bookable.
- If a builder integration is needed, depend on an official stable extension point. Keep it in an optional adapter; the core package must work without the builder.

## Frontend and Metro Page Builder compatibility

- The verified builder baseline is Metro Page Builder `2.41.5`, package SHA-256 `C79458F7377ADD6D37F42A321D30AC7E695B0A22CE3F3C0287F2895DFC5D839E`.
- Integrate through Metro's built-in `Joomla Module` block. Prefer module ID; title is editable/non-unique, and position intentionally applies Joomla menu assignment.
- Metro `2.41.5` has a fixed internal `BlockRegistry` and no public external block-registration API. Do not patch, subclass, replace, or copy Metro internals to add Kaar blocks.
- Metro's administrator editor displays a diagnostic for site modules rather than rendering them. Always verify modules on the frontend/live page too.
- Do not style Metro's `.mpb-*` classes. Kaar modules must remain correct outside Metro and inside its `.mpb-block`/`.mpb-module` wrappers.
- Default module outer backgrounds, padding, borders, and shadows to neutral so Metro's surround styling can own them without double surfaces.

- Scope public styles under `.kaar-booking`; do not style global elements or generic Joomla/template classes.
- Use `--kaar-*` design tokens, logical CSS properties, Grid/Flexbox, mobile-first breakpoints, and low selector specificity.
- Do not hard-code container widths, template positions, `Itemid`, site paths, Bootstrap availability, or a specific page-builder DOM.
- A module must fit a narrow column without horizontal overflow and must offer an inherit alignment mode.
- Use unique IDs generated per rendered instance; initialise JavaScript from each module root using `data-kaar-*` attributes.
- Load only the assets required by the current view/module through Joomla Web Asset Manager.
- Keep server-rendered content functional before JavaScript enhancement. Support keyboard use, visible focus, reduced motion, RTL, responsive reflow, and WCAG 2.2 AA.
- Never modify or overwrite Metro Page Builder, template, or Joomla core files. Use module placement, layouts, overrides, CSS tokens, and optional documented adapters.
- Builder preview must be side-effect free: no real booking, hold, payment, notification, or analytics conversion.

## Data, documents, and privacy

- Treat identity, licence, address proof, vehicle papers, location, and payment metadata as sensitive.
- Store private documents outside the public web root or in private object storage. Access requires authorisation and short-lived delivery.
- Validate actual content/MIME/signature, scan uploads, enforce size limits, hash files, and log access to sensitive documents.
- Encrypt or tokenise sensitive identifiers and secrets; mask them in UI and logs.
- A replacement document creates a new version and supersedes the prior version. Do not hard-delete evidence unless the approved retention policy requires it.
- OCR output is untrusted input. Record confidence/provenance and require authorised review for compliance decisions.
- Never place secrets, tokens, personal document contents, full payment payloads, or precise location trails in normal logs, fixtures, screenshots, or commits.
- Customer KYC requires email OTP plus manual address-proof review. Store OTPs only as keyed hashes with expiry, attempt/resend limits, single use, and abuse rate limiting.
- The package must provide no administrator download route or download button for customer KYC originals. Authorised manual review uses a short-lived, audited, inline `no-store` preview. Do not claim this prevents screenshots or browser-level capture.
- After verification, honour a customer's permitted request to delete the original and all active derivatives/caches while retaining only the minimal verification/audit record allowed by policy. If law or an approved policy requires retention, expose a deletion-request state and explain the restriction rather than silently deleting or refusing.
- Do not email customer KYC files, include them in exports, expose stable media URLs, or reuse them as public thumbnails.

## Unified Flutter app

- Maintain one Flutter app with customer and driver feature boundaries, explicit role-aware routing, and server-enforced authorisation.
- Request background location and driver-only permissions only for driver workflows that require them.
- Marketing promotions require opt-in; booking confirmation, driver arrival, cancellation, payment, KYC/security, and safety messages are transactional.
- Push payloads contain safe summaries and opaque identifiers. Fetch authorised current state after open; never embed sensitive documents, payment data, or unnecessary precise locations.

## Booking, inventory, and payments

- Persist an immutable pricing snapshot for every quote/booking. Never recalculate an old booking from current rules.
- The server is authoritative for totals, distance, discounts, tax, eligibility, availability, and payment state.
- Use expiring holds and transactional allocation. UI checks alone never prevent double booking or overselling seats.
- Revalidate availability and compliance at quote acceptance, allocation, and dispatch.
- Payment redirects are not proof of payment. Verify signed provider webhooks/server responses, amount, currency, order reference, and idempotency.
- For scheduled vehicle and tour/package requests, successful payment does not confirm the booking. Only an authorised administrator transition may confirm and allocate it.
- Automatic compliance blocks prevent new allocation. Flag affected future bookings for operator action; do not silently cancel them.
- State transitions go through explicit application services/state machines and write actor, time, reason, and correlation ID.

## Code quality

- Prefer clear, typed, cohesive code over clever abstractions. Apply SOLID where it improves change isolation; avoid speculative frameworks.
- Keep functions/classes focused, make invalid states difficult to represent, and return structured domain errors rather than ambiguous booleans.
- Validate at system boundaries and encode invariants in domain/application services.
- Use stable public contracts and semantic versioning. Breaking API changes require a new API version or an explicit migration path.
- All user-visible text belongs in localisation resources. Do not concatenate translated fragments.
- Add concise comments for why, invariants, external constraints, and non-obvious tradeoffs—not narration of obvious code.
- Keep dependencies minimal, maintained, licence-compatible, pinned/locked, and reviewed for security impact.
- Do not commit generated packages, dependency directories, secrets, local environment files, private uploads, or production data unless repository policy explicitly says otherwise.

## Testing and definition of done

Every change must be tested in proportion to risk. At minimum:

- domain rule changes have deterministic unit tests;
- database/repository changes have migration and integration tests;
- API changes have authentication, authorisation, validation, and contract tests;
- booking/inventory changes test conflicts and concurrent attempts;
- payment/webhook changes test forgery, duplicate, reordered, failed, and retried events;
- document changes test permissions, invalid files, versioning, expiry boundaries, and compliance blocking;
- modules/menu items test repeated instances, empty/error states, routing, access, and builder-column responsiveness;
- UI changes test keyboard/focus, narrow/mobile layout, RTL, long translations, zoom/reflow, and reduced motion where applicable.

Before marking work complete:

1. Run the narrowest relevant checks during development, then the affected suite.
2. Verify Joomla 6 first and Joomla 5 in the compatibility matrix when the change touches Joomla runtime behaviour.
3. Confirm no unrelated user changes were overwritten.
4. Update product/API/architecture documentation for changed behaviour or decisions.
5. Report tests run, results, migrations, compatibility impact, and remaining risks.

## Agent workflow

- Inspect existing files, repository status, and nearby conventions before editing.
- Preserve unrelated and uncommitted user work. Never reset, discard, or rewrite it without explicit authorization.
- Use small, reviewable patches and keep refactors separate from behaviour changes when practical.
- Do not invent provider credentials, legal requirements, business rules, builder APIs, or production configuration. Use interfaces/configuration and document the unresolved decision.
- When a request is ambiguous but a safe reversible implementation is possible, state the assumption and proceed. Ask before irreversible actions, external publication, data deletion, or a choice that materially changes product behaviour.
- Do not claim compatibility, successful installation, security, or test completion without evidence from the corresponding check.
