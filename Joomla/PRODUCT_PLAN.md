# Kaar Booking Platform — Product and Technical Plan

## 1. Product goal

Build a multi-channel transport-booking platform whose first operational backend is a Joomla component. It will support:

- car and vehicle rental by day, half-day, hours, package, or distance;
- vehicles with a driver or, where the operator permits it, self-drive;
- configured vehicle classes such as hatchback, sedan, SUV, minivan, van, minibus, and bus;
- tour packages, weddings/events, full-vehicle hire, and per-seat bookings;
- vehicle, driver, and regulatory-document management;
- document-expiry reminders and automatic blocking of non-compliant resources;
- online requests, quotations, payments, operator confirmation, email/SMS/push notifications;
- one Flutter app with role-based customer and driver interfaces;
- real-time, on-demand ride booking in a later phase;
- a later WordPress adapter/plugin using the same API and business rules.

The platform should be usable by a single fleet operator first, while keeping `operator_id` in core records so a marketplace or multi-vendor edition can be added without replacing the data model.

## 2. Recommended delivery boundary

### Release 1 — Scheduled booking MVP

Deliver first:

- vehicle types, vehicles, features, capacity, photos, and availability;
- drivers and assignments;
- compliance document upload, verification, expiry, reminders, and blocking;
- service zones and distance-based pricing;
- day, half-day, hourly, airport/point-to-point, and predefined tour-package bookings;
- customer self-service search and booking requests for future dates;
- mandatory administrator review, confirmation, price finalisation where applicable, and vehicle/driver allocation for every scheduled vehicle or tour booking;
- with-driver/self-drive controls at global, vehicle-type, and vehicle levels;
- customer checkout, payment attempt tracking, operator confirmation, and notifications;
- customer KYC with email OTP verification, address-proof upload, manual review, and privacy-preserving deletion;
- Joomla administrator dashboard and customer-facing Joomla pages;
- versioned REST API for Flutter and the later WordPress plugin;
- mobile banners, promotions, and app-home layout configuration exposed as JSON.

### Release 2 — Group and event products

- full bus/vehicle hire;
- fixed-departure trips with seat inventory and passenger manifests;
- wedding/event enquiries, quotations, deposits, and staged payments;
- coupons, taxes, cancellation policies, refunds, and invoices;
- corporate/customer accounts and approval flows.

### Release 3 — Real-time ride hailing

- customer and driver modes in the unified app, with live location enabled only for the active role and journey;
- nearby-driver discovery, offer/accept/timeout dispatch;
- pickup ETA, trip PIN/OTP, start/end trip, live tracking, and route history;
- metered fares, waiting time, tolls, surge rules, cancellation/no-show fees;
- SOS, trusted contacts, incident reporting, masked calling, ratings, and support;
- driver online/offline state, wallet/settlement, and operations console.

Real-time dispatch must not be represented as a normal Joomla page request. Use a dedicated realtime service and message/notification infrastructure, with Joomla remaining the control plane and master admin interface.

### Booking responsibility boundary

- **Scheduled vehicle and tour bookings:** customers can search, choose future dates, select a vehicle class/package, enter trip/passenger details, and submit a booking request. An administrator manually reviews and confirms every request and manually assigns the actual vehicle and driver. No scheduled request is automatically confirmed merely because availability was displayed or payment was received.
- **Online driver rides:** the later realtime flow may automatically offer the ride to eligible online drivers and confirm through the dispatch state machine.
- Administrators may also create either kind of booking on behalf of a customer, with actor/source recorded as `admin`, `website`, `flutter_customer`, or `realtime_dispatch`.

### Release 4 — WordPress distribution

- WordPress plugin for catalogue/search/checkout widgets and account pages;
- connection wizard for the Joomla/API backend;
- webhook receiver and cached read models;
- optional standalone WordPress mode only after shared domain rules have been extracted into a framework-neutral PHP package.

## 3. Roles and permissions

Use Joomla ACL actions rather than hard-coded role names.

| Role | Main capabilities |
|---|---|
| Super administrator | System configuration, permissions, integrations, audit access |
| Fleet administrator | Vehicle, driver, document, availability, and assignment management |
| Compliance officer | Review/reject documents, overrides, expiry policies, compliance reports |
| Booking operator | Enquiries, quotes, bookings, confirmations, cancellations, allocations |
| Dispatcher | Live jobs, driver offers, trip monitoring, incident escalation |
| Finance operator | Payments, refunds, invoices, settlements, tax reports |
| Content/marketing manager | Packages, banners, promotions, app layout, notifications |
| Driver | Own profile/documents, availability, assigned trips, trip status |
| Customer | Profile/KYC, passengers, bookings, payments, invoices, notifications, support |

Sensitive identity/document data should have separate `view`, `create`, `verify`, and `delete` permissions. Customer KYC originals must not expose a download action through the package. Reviewers receive only a controlled preview during manual verification; vehicle/driver operational documents follow their separately approved access and retention policy.

## 4. Booking products

Model a booking through a product type plus a pricing plan, not separate unrelated implementations.

| Product type | Inventory unit | Typical pricing |
|---|---|---|
| Local full day | Vehicle/time block | Included km + extra km + extra hour |
| Local half day | Vehicle/time block | Included hours/km + overage |
| Hourly rental | Vehicle/time block | Hour bands + included km |
| Outstation/short trip | Vehicle/time block | Per km, minimum km/day, driver allowance |
| Point-to-point/airport | Vehicle/time block | Fixed route or distance/time quote |
| Tour package | Vehicle/time block | Fixed price or vehicle-class price |
| Full bus/event | Entire vehicle | Quote, fixed package, or day rate |
| Seat booking | Seat on scheduled departure | Per seat/passenger type |
| On-demand ride | Vehicle/trip | Base + distance + time + fees |

Every quote should record an immutable pricing snapshot: base amount, included units, extra-unit rates, fees, discount, tax, deposit, balance, currency, and the rule version used. Future price changes must not alter an existing quote or booking.

### Future-date standalone vehicle booking

The website module and unified Flutter app must support future-dated standalone vehicle requests for:

- outstation one-way;
- outstation round-trip;
- airport transfer;
- hourly, half-day, and full-day rental;
- configurable additional scheduled service types.

Core inputs are service type, origin, destination where relevant, optional intermediate stops, departure date, optional return date, pickup time, passenger count, luggage/capacity needs, vehicle type, with/without-driver option when enabled, and notes. The calendar disables past dates and dates outside the configured advance-booking window. Return must be after departure and time-zone/cross-midnight rules must be explicit.

Search results show indicative eligible vehicle types and pricing, not a promise that a particular physical vehicle is assigned. Submission creates `pending_admin_review`, reserves only the configured provisional hold (if any), and clearly tells the customer that final confirmation follows administrator review.

### Multi-day holiday and bus packages

A package can span a configured duration such as two days and can reserve a bus, another whole vehicle, or seats on a scheduled departure. Package records require:

- title, destination/route, duration in days/nights, start options, pickup points, images, summary, and itinerary;
- eligible vehicle types and capacity, whole-vehicle versus per-seat mode, minimum/maximum participants;
- included items as ordered structured rows (for example vehicle, driver, accommodation, meals, sightseeing, toll/parking, taxes, guide, insurance);
- excluded items, optional add-ons, terms, cancellation policy, price/deposit, and availability window;
- admin-configured detail visibility for the module while retaining the full accessible detail page;
- a visible `Book` button in every card configuration, including compact or details-hidden layouts.

Clicking `Book` opens the package request/checkout with the package already selected. Submission goes to the same manual administrator confirmation and allocation queue.

## 5. Configuration hierarchy

Resolve settings from most specific to least specific:

1. Vehicle override
2. Vehicle-type override
3. Product/pricing-plan rule
4. Operator/global default

Important controls include:

- enable with-driver and/or self-drive;
- available booking products;
- minimum notice and maximum advance booking;
- service areas, pickup/drop restrictions, and garage/base location;
- capacity for passengers, luggage, wheelchairs, and seats;
- minimum kilometres/day and included hours/kilometres;
- extra-km, extra-hour, night, driver-allowance, toll, parking, interstate, and cleaning rules;
- deposit/security deposit and accepted payment methods;
- cancellation/no-show/refund policy;
- required document types and expiry grace periods;
- scheduled products always require administrator confirmation; any future automatic confirmation setting is scoped only to the realtime online-driver dispatch product.

## 6. Core data model

Use Joomla database tables prefixed with `#__kaar_`. All mutable tables should include creation/modification metadata, state, ordering where useful, and optimistic concurrency/version fields.

### Identity and operations

- `operators`: future-ready tenant/fleet owner record.
- `customers`: Joomla user link, contact information, verification flags.
- `customer_addresses`: labelled and geocoded addresses.
- `customer_kyc`: customer, KYC status, verified email, proof type, masked document metadata, consent, reviewer, decision, and verification timestamps.
- `otp_challenges`: purpose, subject, hashed OTP, expiry, attempt count, resend/rate-limit state, consumption timestamp, and audit metadata; never store a plaintext OTP.
- `drivers`: Joomla user link, employment/contract status, emergency contact, verification state.
- `driver_availability`: shifts, leave, online/offline status.
- `service_zones`: polygon/radius/postcode coverage and base locations.

### Fleet

- `vehicle_types`: configurable classes and capacity defaults.
- `vehicles`: registration, make/model/year, fuel/transmission, odometer, condition, ownership, status.
- `vehicle_features` and `vehicle_feature_map`: AC, wheelchair access, luggage, etc.
- `vehicle_media`: photos with ordering and approval.
- `vehicle_availability`: maintenance blocks, manual blocks, and schedules.
- `vehicle_condition_reports`: inspections, odometer, fuel, damage photos, notes.
- `driver_vehicle_assignments`: dated primary/temporary assignments.

Do not store a driver licence under a vehicle record. Driver documents belong to the driver; vehicle registration, permit, insurance, fitness, and PUC belong to the vehicle.

### Compliance

- `document_types`: configurable subject type, required status, expiry rules, reminder offsets, blocking severity.
- `documents`: subject type/id, document number (encrypted/masked), issue/expiry dates, status, verification data.
- `document_files`: private file reference, MIME type, hash, version, uploaded-by and scan status.
- `document_reviews`: reviewer, decision, reason, timestamp, extracted-versus-confirmed values.
- `compliance_events`: reminder, expiry, block/unblock, override, and audit events.

Initial configurable types should include vehicle registration certificate, commercial/tourist/PSV permit as applicable, commercial insurance, fitness certificate, PUC, and driver licence/address or identity proof. Requirements vary by jurisdiction and operation type, so they must remain configurable and be reviewed by a qualified local compliance adviser before launch.

Customer address proof is a separate KYC subject and policy from vehicle/driver compliance. Its original file may be deleted after successful manual verification at the customer's request, while retaining the minimal verification record, proof category, masked reference/hash where permitted, reviewer, decision, and timestamps required for fraud prevention and audit.

### Catalogue and pricing

- `products`: product type and booking-mode definition.
- `packages`: tour/event/package content, itinerary, inclusions, exclusions, cancellation terms.
- `departures`: dated seat-based departures.
- `departure_inventory`: class/capacity/sold/held counts.
- `pricing_plans`: applicability, currency, tax class, effective dates, priority.
- `pricing_rules`: fixed, distance, time, day, zone, surcharge, allowance, and tier rules.
- `promotions`: eligibility, usage limits, coupon codes, effective dates.

### Booking and finance

- `quotes` and `quote_lines`: calculated, versioned offer with expiry.
- `bookings`: public reference, product, customer, schedule, locations, status, payment status.
- `booking_passengers`: passenger/manifests for seat and tour products.
- `booking_resources`: held/assigned vehicles, drivers, or seats.
- `booking_status_history`: append-only state transitions with actor/reason.
- `payments`: gateway-neutral payment attempts and verified outcomes.
- `refunds`: gateway references, amount, state, and reason.
- `invoices`: immutable numbering and financial snapshot.
- `webhook_events`: provider event ID, payload hash, processing and retry state.

### Mobile content and communication

- `app_layouts`: draft/published version per app/platform/locale.
- `app_sections`: section type and ordered configuration.
- `app_banners`: media, link action, audience, schedule, and analytics tags.
- `notification_templates`: email/SMS/push/call-task templates.
- `notification_queue`: channel, recipient, booking/event reference, attempts, status.
- `device_tokens`: user/device/platform and revocation.
- `notification_preferences`: per-user transactional and marketing channel consent, locale, quiet hours, and changes over time.
- `audit_log`: security- and business-relevant actions with before/after metadata.

## 7. State machines

### Booking

`draft → pending_admin_review → quoted → pending_payment → paid_pending_confirmation → confirmed → allocated → in_progress → completed`

Alternative terminal paths: `expired`, `cancelled`, `rejected`, `no_show`, `refunded`, and `partially_refunded`.

For all scheduled vehicle and tour products, payment success never means fleet confirmation. Successful payment creates `paid_pending_confirmation`, queues an administrator review/call task, sends an acknowledgement, and moves to `confirmed` only after an authorised administrator confirms it. Automatic confirmation is limited to a future, explicitly designed realtime-dispatch flow.

### Document

`uploaded → scanning → needs_review → verified → expiring → expired`

Other states: `rejected`, `superseded`, and `revoked`.

### Customer KYC

`not_started → email_otp_pending → email_verified → proof_uploaded → under_review → verified`

Alternative states: `rejected`, `resubmission_required`, `revoked`, and `original_deleted` (verification remains valid only according to the configured KYC policy).

- Email verification uses a single-use OTP with a short expiry, attempt limit, resend cooldown, generic responses, and abuse rate limiting by account, address, IP, and device signals.
- OTP values are generated with a cryptographically secure generator, delivered only by email, stored as a keyed hash, and invalidated on success, expiry, email change, or too many failed attempts.
- The customer uploads an accepted address-proof type and explicitly consents to its use for KYC.
- An authorised administrator manually previews the proof, records approve/reject/resubmission and reason, and cannot retrieve it through a package download endpoint or button.
- Once verified, the customer can request deletion of the original from the customer dashboard or unified app. Deletion removes the original and derivatives from active storage and queues provider/backup lifecycle handling, but preserves the minimal non-file audit record allowed by policy.
- If business/legal rules require the original to be retained, the UI must explain this before upload and replace immediate deletion with a tracked deletion request. The retention rule must not be silently overridden.

### Vehicle eligibility

A vehicle is bookable only when all gates pass:

- operational status is active;
- no maintenance/manual block overlaps the requested time;
- required vehicle documents are verified and valid through the booking end time plus configured buffer;
- the selected mode is enabled for that vehicle/type;
- capacity and service-zone rules pass;
- a qualified, available, compliant driver can be assigned when required;
- no conflicting confirmed allocation or active inventory hold exists.

An expired mandatory document must automatically set a compliance block. Existing future bookings should be flagged for reassignment and operator action; they should not be silently cancelled.

## 8. Document handling and OCR

### Safe lifecycle

1. Upload to private storage; never expose raw filesystem paths.
2. Virus/malware scan and verify file signature, MIME type, and size.
3. Hash the file for integrity and duplicate detection.
4. Run OCR/document extraction asynchronously.
5. Show extracted number, issue date, expiry date, subject name, and confidence to an authorised reviewer.
6. Require human confirmation for low-confidence or safety-critical fields.
7. Mark the new version verified, then mark the old version `superseded`.
8. Retain or purge old versions according to the approved retention policy; do not immediately hard-delete audit evidence by default.

For customer KYC originals, manual verification uses an authenticated, short-lived preview stream rendered inline with `Content-Disposition: inline`, `Cache-Control: no-store`, a restrictive content security policy, and an audit event. The package exposes no stable public URL and no administrator download route or button. Preview can be watermarked with reviewer/time/session. This reduces casual copying but cannot prevent screenshots, browser developer tools, or an authorised reviewer from photographing the screen; policy, least privilege, audit, and staff controls remain necessary.

OCR is an assistant, not proof that a document is genuine. Add provider adapters later for authoritative government/insurer verification where lawful and technically available. Store extraction provenance, confidence, reviewer, and timestamps.

### Scheduler jobs

- nightly compliance recalculation;
- reminders at configurable offsets such as 60/30/15/7/1 days;
- immediate recheck on document verification, revocation, or vehicle/driver change;
- booking revalidation before allocation and again before dispatch;
- failed notification and webhook retries with exponential backoff;
- orphaned inventory-hold release.

## 9. Joomla architecture

### Supported Joomla versions

- Primary target: Joomla 6 on its supported PHP and database versions.
- Secondary target: the latest supported Joomla 5 release, where this does not weaken the Joomla 6 design.
- Joomla 6 compatibility is the release gate. Joomla 5 support is verified by the automated compatibility matrix and may use a thin, isolated adapter only when public APIs differ.
- Do not depend on Joomla's backward-compatibility plugin. Do not call APIs deprecated for removal in Joomla 6.
- Use only documented public Joomla APIs, dependency injection, namespaced extension classes, service providers, prepared queries, form XML, language files, semantic versioning, and repeatable schema migrations.
- Maintain one source tree and one package when feasible; never fork business logic into Joomla 5 and Joomla 6 copies.

Create one installable package, initially named `pkg_kaarbooking`, containing:

- `com_kaarbooking`: administrator UI, customer site UI, domain/application services, API controllers;
- `plg_webservices_kaarbooking`: versioned REST route registration;
- `plg_task_kaarbooking`: scheduled compliance, reminder, hold-release, and retry jobs;
- `plg_user_kaarbooking`: optional customer/driver profile synchronisation;
- `plg_system_kaarbooking`: narrowly scoped runtime integration only where required;
- `mod_kaarbooking_search`: availability/search widget;
- `mod_kaarbooking_featured`: packages/vehicles/promotions widget;
- `mod_kaarbooking_account`: compact customer booking/account widget.

Additional page-builder-friendly modules:

- `mod_kaarbooking_booking_form`: configurable compact/full future vehicle search and booking-request form;
- `mod_kaarbooking_vehicle_grid`: filterable vehicle/type cards;
- `mod_kaarbooking_package_grid`: tour/holiday/bus package cards with structured inclusions and persistent Book action;
- `mod_kaarbooking_departures`: upcoming seat-based departures;
- `mod_kaarbooking_promotion`: one banner/promotion or carousel;
- `mod_kaarbooking_booking_lookup`: booking reference/status lookup;
- `mod_kaarbooking_compliance_badge`: optional public trust/compliance summary without sensitive details.

Suggested source layers inside the component:

- `Domain`: entities/value objects, policies, state transitions, pricing specification;
- `Application`: commands, queries, DTOs, transaction orchestration;
- `Infrastructure`: Joomla repositories, storage, payment, maps, OCR, notifications;
- `Administrator`: forms/views/controllers for back-office workflows;
- `Site`: public catalogue, checkout, account, and callback flows;
- `Api`: authenticated JSON resources and webhook endpoints.

Keep pricing, availability, compliance, and state transitions out of Joomla view/controller code. This makes them testable and enables reuse in WordPress through the API or a later shared Composer package.

### Metro Page Builder and page-builder compatibility

The verified integration target is Metro Page Builder `2.41.5`, inspected from `pkg_MetroPageBuilder_v2.41.5.zip` (SHA-256 `C79458F7377ADD6D37F42A321D30AC7E695B0A22CE3F3C0287F2895DFC5D839E`). The integration contract remains Joomla-native so Metro and other builders can place Kaar Booking features without owning business logic.

Metro `2.41.5` provides a built-in `Joomla Module` block that can select a site module by ID or exact title, or render modules in a template position. Selection by ID/title deliberately bypasses Joomla menu assignment for the named module while still enforcing published state, publish dates, enabled extension, view access, and language; selection by position follows Joomla menu assignment. This is the supported Kaar integration path.

Metro `2.41.5` does not expose a public external block-registration event/API: its `BlockRegistry` contains a fixed internal class list and notes external plugin registration as a future possibility. Therefore:

- do not patch Metro's `BlockRegistry`, renderer, database, or package;
- do not create `plg_system_kaarbookingmetro` for block registration in this release;
- deliver Kaar features as Joomla modules and embed them with Metro's existing `Joomla Module` block;
- prefer selection by module ID in authored pages because titles are editable and need not be unique;
- use selection by position only when Joomla menu-assignment behaviour is intentionally required;
- reconsider a thin adapter only if a later Metro release publishes and documents a stable external registration API.

- Every reusable storefront block is a normal Joomla site module. It must work in a standard template position and when a builder embeds a module by ID or position.
- Modules render independently, may appear multiple times on one page, and must not rely on the active component, a specific `Itemid`, or globally unique hard-coded DOM IDs.
- Each module exposes parameters for data source, item count, ordering, columns by breakpoint, card style, image ratio, text alignment, container width, gap, background, optional heading, call-to-action, and empty state.
- Module output uses Joomla layout files and supports template overrides. Keep data preparation out of layouts.
- Provide module chrome-neutral output: no forced outer card, heading, margin, background, or full-width container unless selected in module parameters.
- Builder preview/edit mode must not create bookings, payments, holds, tracking events, or other side effects. It should render representative data or a clear empty-state preview.
- AJAX and API calls derive their base URL through Joomla configuration/runtime data, not hard-coded paths. Multiple module instances must not share mutable global state.
- If a later Metro version offers an official addon SDK, any optional adapter must map its elements to the same module/view models. The package must remain fully functional without it.

#### Future vehicle booking module behaviour

`mod_kaarbooking_booking_form` uses the reference screenshots only for interaction direction. It must provide an original, accessible Kaar design with:

- service selectors presented as tabs/radio controls: one-way, round-trip, airport, hourly, half-day/full-day, plus configured services;
- origin and destination fields with an accessible swap action and configurable add-stop repeater;
- departure and conditional return date, pickup time, and clear local date/time labels;
- responsive dual-month calendar on wide screens and single-month calendar on narrow screens;
- disabled past/unavailable dates, keyboard navigation, focus management, and screen-reader date announcements;
- prominent Search/Continue action and inline validation without relying on colour alone;
- compact and full layouts, horizontal/stacked modes, inherited alignment, and configurable defaults;
- no trademarked third-party branding or pixel-for-pixel copying of the reference site.

#### Holiday/package module behaviour

`mod_kaarbooking_package_grid` supports grid, list, carousel, and single-featured layouts. Administrator parameters include package/category selection, ordering, count, columns, image ratio, price visibility, duration, excerpt, itinerary preview, inclusions, exclusions, capacity, departure date, and terms link.

Detail visibility can be controlled globally per module and overridden per displayed field. `Show details` expands an accessible disclosure or opens the package detail page; `Hide details` produces a compact card. The `Book` action cannot be disabled or hidden for a bookable published package and remains visible without expanding details. When a package is sold out, unpublished, outside its sale window, or otherwise not bookable, replace the action with a clear configured state such as `Join waitlist` or `Enquire`—never a dead button.

### CSS and layout contract

- Scope all selectors under `.kaar-booking` and use a documented BEM-style naming scheme such as `.kaar-booking__vehicle-grid` and `.kaar-booking__card`.
- Use CSS custom properties (`--kaar-*`) for colour, spacing, radius, shadow, typography, container width, and breakpoints so a template or builder can theme the extension without copying core CSS.
- Use CSS Grid/Flexbox, logical properties (`margin-inline`, `padding-block`, `text-align: start`), and mobile-first responsive rules.
- Do not style generic tags or global framework classes. Avoid `!important`, fixed page widths, negative positioning, framework resets, and assumptions about Bootstrap utility availability.
- Default embedded modules to `width: 100%`, `max-width: 100%`, `min-width: 0`, and `box-sizing: border-box` within their own scope to prevent builder-column overflow.
- Offer alignment parameters: inherit, start, centre, end, and stretch where meaningful. “Inherit” is the safe default for page builders.
- Images require explicit aspect ratio, intrinsic dimensions where known, `object-fit`, responsive sources, meaningful alt text, and lazy loading below the fold.
- JavaScript enhancements must preserve server-rendered content, initialise per module root using `data-kaar-*`, and be safe when content is inserted after initial page load.
- Register and load CSS/JavaScript only through Joomla Web Asset Manager. Assets are split by feature so a promotion module does not load checkout code.
- Support left-to-right and right-to-left documents, keyboard navigation, visible focus, reduced motion, zoom/reflow, and WCAG 2.2 AA as the target.
- Include a low-specificity compatibility stylesheet and documented override examples; never patch Metro or the active template's files.
- Treat Metro's `.mpb-block` and `.mpb-module` wrappers as external containers only. Kaar selectors must not override them, and Metro selectors must not be required for Kaar to render correctly.
- Let Metro own its surround controls (background, padding, radius, border, shadow, and text/link colours). A Kaar module's default outer surface must be transparent and spacing-neutral so the same visual layer is not applied twice.
- Where useful, Kaar colours may fall back to Metro's public page token without depending on it, for example `var(--kaar-accent, var(--mpb-accent, currentColor))`.
- Metro's editor cannot render a site module in the administrator application; it shows a diagnostic instead. Acceptance testing must therefore verify the live/frontend preview as well as the builder canvas.

### Joomla menu item types

Provide XML metadata and SEF-friendly site views for these selectable menu item types:

- Booking search / availability;
- Vehicles and vehicle types;
- Vehicle detail;
- Tour package catalogue;
- Tour package detail;
- Scheduled departures / seat booking;
- Day, half-day, and outstation booking landing page;
- Wedding/event/full-bus enquiry;
- Customer dashboard;
- My bookings;
- Booking detail (normally reached dynamically, with access checks);
- Booking lookup;
- Checkout/payment return (normally hidden from navigation);
- Driver dashboard and assignments (authorised users only);
- Contact/support and booking terms where component-managed.

Menu metadata must expose useful parameters such as default product, package/category filter, service zone, initial layout, page heading, metadata, canonical behaviour, and login requirement. Modules must respect Joomla menu assignment, language, access level, and active-menu routing.

### Compatibility verification

The continuous-integration matrix must install and test the package on:

- the latest supported Joomla 6 patch with its supported PHP/database combinations;
- the latest supported Joomla 5 patch with the selected overlapping PHP/database combination;
- Cassiopeia or the Joomla 6 default site template as the clean baseline;
- Metro Page Builder `2.41.5`, using its `Joomla Module` block by module ID, title, and position;
- any later Metro release only after the same contract tests pass.

Required visual fixtures include full-width, narrow builder column, nested column, module repeated twice, RTL, high zoom, long translated text, empty results, and validation errors. Maintain a compatibility record by Joomla, PHP, database, template, and builder version.

## 10. API strategy

Expose `/api/index.php/v1/kaar/...` with explicit versioning and consistent error envelopes.

Initial resources:

- authentication/session bootstrap;
- public configuration and serviceability;
- vehicle types and features;
- products, packages, departures, and mobile home content;
- availability search and quote creation;
- booking creation/read/cancel;
- payment-order creation and status;
- customer profile, passengers, invoices, and notifications;
- customer email-OTP challenge/verify, KYC status, address-proof upload, and original-deletion request;
- driver profile, compliance summary, availability, and assignments;
- document upload/finalise/status;
- admin content preview/publish.

Later realtime resources:

- driver presence/location stream;
- ride request and dispatch offers;
- trip state and live route;
- SOS/incident events;
- ratings, earnings, and settlements.

API requirements:

- token-based authentication appropriate to Joomla and the mobile clients;
- object-level authorisation on every private resource;
- idempotency keys for booking, payment, refund, and webhook mutations;
- pagination, filtering, locale, currency, and server timestamps;
- rate limiting, request IDs, structured audit logging, and replay protection;
- OpenAPI contract generated/maintained with the API;
- no trust in client-calculated totals, distance, discounts, or payment success.

## 11. Unified Flutter application

Deliver one Flutter application with a shared authenticated shell and strictly separated role capabilities:

- Customer mode: registration/email OTP, KYC/address proof, discovery, serviceability, quote, booking, payment, tracking, packages, tickets, account, notifications, and support.
- Driver mode: onboarding/documents, compliance summary, availability, job offers, navigation handoff, trip workflow, earnings, notifications, and support/safety.
- Dual-role users may switch modes explicitly. A customer cannot reach driver APIs merely by navigating to a hidden screen; server-side ACL and object-level authorisation enforce every action.
- Driver-only background location and operational permissions are requested only when driver mode needs them. Customer tracking receives trip-scoped data and must not expose the driver's unrelated location history.

Use feature packages inside the single app for API client, authentication, customer, driver, booking, trip, KYC, payments, notifications, design system, localisation, maps, analytics, and support. Role-aware routing may hide unavailable features, but authorization remains server-side.

### Customer dashboard

Available both in Joomla web views and customer mode in Flutter:

- profile, verified email, KYC status, and address-proof upload/resubmission;
- request deletion of a verified KYC original and view deletion status;
- saved addresses/passengers with privacy controls;
- quotes, upcoming/active/past bookings, live ride status, cancellations, refunds, invoices, and support;
- notification centre and marketing consent/preferences;
- active sessions/devices and sign-out/revocation.

### Driver dashboard

Available as an authorised Joomla web view where operationally useful and in driver mode in Flutter:

- profile, onboarding and compliance status, document renewal, assigned vehicle;
- online/offline state, availability/leave, offers, assigned/upcoming/active/completed trips;
- pickup/passenger/contact controls, navigation handoff, arrival/start/end workflow;
- earnings/settlements when enabled, incident/SOS/support, notification centre;
- clear blocking reasons when a document, vehicle, assignment, or permission prevents work.

### Server-driven mobile home designer

The Joomla admin should edit a constrained component schema, not arbitrary executable UI. Supported blocks can include:

- hero carousel/banner;
- booking shortcut grid;
- featured vehicles;
- tour packages;
- upcoming departures;
- promotional card;
- rich-text/info strip;
- support/contact block.

Publish immutable layout versions. The app fetches published JSON by platform, app version, audience, and locale; validates it against a schema; caches the last-known-good version; and ignores unknown block types. Banner actions must use an allowlist such as product, package, search preset, booking detail, support, or approved web URL.

Example response shape:

```json
{
  "schemaVersion": 1,
  "contentVersion": 12,
  "locale": "en-IN",
  "publishedAt": "2026-09-28T12:00:00Z",
  "sections": [
    {
      "id": "home-hero",
      "type": "bannerCarousel",
      "items": [
        {
          "image": { "url": "https://cdn.example.com/banner.webp", "alt": "Weekend tour" },
          "action": { "type": "package", "id": "42" }
        }
      ]
    }
  ]
}
```

Admin workflow: draft → preview (including phone-size preview) → schedule/publish → rollback. Keep media derivatives for common phone sizes and WebP/AVIF where supported.

## 12. Payments and confirmations

Use a gateway adapter so the deployment can select providers without changing booking logic.

Recommended sequence:

1. Server calculates and saves a short-lived quote.
2. Customer accepts it; server creates booking and inventory hold.
3. Server creates a gateway order using the authoritative amount.
4. Customer completes gateway UI.
5. Server verifies the provider signature/webhook and amount/currency.
6. Payment becomes captured/authorised according to policy.
7. A scheduled booking becomes `paid_pending_confirmation`; only the later realtime-dispatch product may become automatically confirmed under its own dispatch rules.
8. Notifications and an operator call task are queued.
9. If rejected, start the configured void/refund flow and inform the customer.

Do not confirm from the browser/app redirect alone. Webhooks must be idempotent, logged, and safely retryable.

## 13. Notifications

Make channels provider-neutral:

- transactional email;
- SMS and optional WhatsApp where consent/templates permit;
- role-aware push notifications in the unified Flutter app;
- in-app notification centre;
- administrator tasks for required confirmation calls.

Required unified-app push/in-app events include:

- promotions, only for customers who opted into marketing and with an unsubscribe/preference action;
- booking/ride confirmation and material schedule or allocation changes;
- driver assigned, driver en route, driver arrived, and pickup instructions;
- cancellation by customer, driver, or operator, including actor, reason category, fee/refund outcome, and next action;
- payment success/failure/refund, trip start/completion, and safety/support events;
- KYC OTP/security alert, proof received, verified, rejected/resubmission required, and original-deletion completion;
- driver job offer/timeout, assignment change, customer cancellation, compliance warning/block, and document expiry.

Push payloads contain opaque event/booking identifiers and safe summary text, not full address proof, licence numbers, payment data, or unnecessary precise location. The app fetches authorised current state after opening the notification. Transactional ride/booking notifications do not depend on marketing consent; promotions do.

Templates need locale, variables, preview, test send, effective state, and immutable rendered-message logs where legally appropriate. Respect channel consent and quiet-hour rules except for critical operational/safety messages.

## 14. Security, privacy, and audit controls

- Store uploads outside the public web root or in private object storage using short-lived signed access.
- Encrypt highly sensitive identifiers and secrets; mask identifiers in normal screens/logs.
- Separate public media from identity and compliance documents.
- Enforce least privilege, MFA for privileged operators where available, and session/device revocation.
- Apply CSRF protection to Joomla forms and strict CORS to APIs.
- Validate image/document content, not only filename extensions.
- Keep payment card data out of the platform; use hosted/tokenised gateway flows.
- Record administrative overrides, price changes, document decisions, booking transitions, refunds, exports, and downloads.
- Customer KYC originals have no package download endpoint. Manual review uses audited, no-store, expiring inline preview; prohibit indexing, thumbnail leakage, email attachment forwarding, and inclusion in exports/backups beyond the approved encrypted lifecycle.
- Customer-requested deletion must cover the active original, generated previews/thumbnails, caches, and queued processing copies. Backups expire through the documented retention schedule rather than unsafe selective backup editing.
- Establish retention/deletion rules for identity documents, location trails, invoices, audit logs, and abandoned enquiries.
- Provide privacy consent, purpose notices, data export/correction/deletion processes, subject to statutory retention.
- Back up encrypted data and uploads; regularly test restoration.
- Complete legal, tax, permit, insurance, labour/contractor, consumer, and privacy review for each operating jurisdiction before production.

## 15. Availability and concurrency rules

- Use temporary holds with expiry during checkout.
- Allocate inventory inside database transactions and enforce uniqueness/overlap protection.
- Recheck price, capacity, compliance, zone, and availability when accepting a quote.
- Treat seat inventory separately from entire-vehicle inventory.
- Add vehicle turnaround/buffer time between bookings.
- Support tentative resource class reservation followed by actual vehicle/driver assignment.
- Never depend on UI checks to prevent double booking.

## 16. Administration dashboards

### Operations dashboard

- today's pickups, active trips, returns, unassigned confirmed bookings;
- pending-payment and paid-pending-confirmation queues;
- availability/conflict alerts and failed integrations;
- quick customer contact and documented call outcome.
- manual booking queue for new future vehicle and tour/package requests, with approve, reject, request-change, quote, payment, vehicle/driver assignment, and customer-contact actions;
- list and calendar views filtered by booking reference, customer, source, product/service type, package, travel dates, route, vehicle type, payment state, confirmation state, assignment state, and responsible administrator;
- bulk-safe operational actions only where each booking is revalidated; confirmation and allocation remain individually audited.

### Compliance dashboard

- expired, blocked, expiring by window, missing, rejected, and awaiting review;
- affected future bookings and reassignment actions;
- filters by vehicle type, vehicle, driver, document type, and operator;
- explicit, time-limited override with reason and higher permission.

### Customer KYC dashboard

- pending review, verified, rejected, resubmission required, deletion requested/completed, and anomalous OTP activity;
- inline, short-lived, watermarked proof preview for authorised reviewers with no package download control;
- approve/reject/resubmission action with reason and immutable audit entry;
- no bulk export of original customer proofs.

### Commercial dashboard

- enquiries, conversion, revenue, refunds, utilisation, occupancy, route/package performance;
- prices and promotions with effective dates;
- downloadable reports with permission and audit controls.

### Analytics dashboard

The administrator landing dashboard should combine actionable queues with date-filtered analytics:

- requests received, reviewed, confirmed, rejected, cancelled, completed, and awaiting action;
- gross booking value, captured payments, refunds, net booking value, average booking value, and outstanding balance;
- website versus Flutter versus administrator versus realtime source;
- conversion funnel: search → result selection → request → payment → admin confirmation → completed;
- scheduled lead time, review/confirmation time, cancellation rate and reasons, no-shows;
- vehicle-type demand, assigned-vehicle utilisation, unavailable/compliance-blocked capacity, and unassigned confirmed bookings;
- package views, Book clicks, requests, confirmations, revenue, occupancy/seats sold, and popular inclusions/routes;
- customer cohorts and repeat bookings without exposing unnecessary KYC data;
- notification delivery/failure and payment/provider health summaries.

All metrics require an explicit timezone, currency treatment, date range, comparison period, and definition tooltip. Permission-controlled CSV export contains aggregate or operational fields only and never customer KYC originals.

### Mobile content dashboard

- media library, layout builder, validation, device previews, scheduling, audience/locale targeting, publish history, rollback.

## 17. Testing and quality gates

- Unit tests: pricing, date/time, eligibility, document expiry, cancellation/refund, state transitions.
- Integration tests: repositories, transactional allocation, tasks, payment and webhook idempotency.
- API contract tests: authentication, authorisation, validation, pagination, version compatibility.
- End-to-end tests: search → quote → payment → confirmation → allocation → completion; expiry block; seat sale contention.
- Manual-flow tests: future request → admin queue → reviewed quote/payment → admin confirmation → manual vehicle/driver allocation; package details hidden/visible while Book remains available.
- Security tests: ACL/object access, upload attacks, webhook forgery/replay, injection, rate limits, sensitive logging.
- KYC tests: OTP expiry/replay/brute-force/resend limits, email change, upload validation, reviewer ACL, absence of download routes, preview expiry/no-store headers, deletion of derivatives, and retained minimal audit record.
- Load tests: availability search, quote calculation, campaign traffic, dispatch/location ingestion.
- Mobile tests: offline/poor network, cached layout fallback, deep links, notification routing, location permission loss.
- Operational tests: backup restore, gateway outage, notification retry, OCR failure, expired credentials.

A release cannot pass solely on happy-path UI testing. Pricing totals, double-booking prevention, payment verification, and compliance blocking are launch-critical automated tests.

## 18. Delivery roadmap

Assuming a small cross-functional team, use milestones rather than committing to dates before discovery and UX sizing.

### Milestone 0 — Discovery and decisions

- jurisdictions, business entity/operator model, target Joomla/PHP/database versions;
- exact products, service areas, tax/invoice rules, cancellation policy;
- gateway, maps/geocoding/routing, email/SMS/push, storage, OCR providers;
- customer and operator journey maps, terminology, brand/design system;
- compliance matrix signed off by qualified stakeholders.
- exact Metro Page Builder product/vendor/version, its Joomla 6 support statement, embedding mechanism, preview mode, and supported addon API.
- resolved for initial engineering: package `2.41.5`; Joomla Module block is the embedding mechanism; its administrator preview is diagnostic-only; there is no public external block-registration API in this package. Joomla 6 runtime compatibility still requires installation tests.

Exit: product decision record, prioritised backlog, wireframes, initial threat model, API conventions.

### Milestone 1 — Platform foundation

- Joomla installable package skeleton, migrations, ACL, audit log, configuration;
- domain/application architecture, API conventions, CI tests;
- users/customers/operators, private file storage, provider interfaces.
- Joomla 6/5 CI matrix, Web Asset Manager setup, CSS namespace/tokens, first menu item, and builder-safe module shell.

Exit: repeatable install/update/uninstall, permissions tested, API health/version endpoint.

### Milestone 2 — Fleet and compliance

- vehicle types, vehicles, drivers, documents, versioning, review;
- expiry scheduler, reminders, compliance dashboard, eligibility service;
- availability/maintenance and assignment basics.

Exit: a non-compliant vehicle or driver cannot be newly allocated; affected bookings are surfaced.

### Milestone 3 — Catalogue, pricing, and availability

- products/packages, zones, pricing plans/rules, calculator explanation;
- availability search, holds, quote snapshot, conflict protection.
- future-date booking module and holiday/package module with persistent Book action.

Exit: deterministic price tests and concurrency tests pass.

### Milestone 4 — Booking, payment, and notifications

- checkout/account, gateway adapter, verified webhooks, confirmation-call queue;
- booking allocation/status, email/SMS templates, invoices/refunds basics.
- administrator booking list/calendar, manual review/confirmation/allocation queue, and initial analytics dashboard.

Exit: complete scheduled-booking journey works in staging and is recoverable after provider failures.

### Milestone 5 — Unified Flutter app: customer mode and mobile CMS

- API auth/email OTP, customer KYC, dashboard, discovery/search/quote/booking/payment/account;
- server-driven home schema, media variants, preview/publish/rollback;
- push notifications and analytics/consent.

Exit: store-ready release candidate against staging, with backward-compatible content fallback.

### Milestone 6 — Seats, events, and richer operations

- departures/seat inventory/manifests; full-bus and wedding quote flow;
- corporate accounts, advanced reports, promotions, staged payments.

### Milestone 7 — Realtime ride pilot

- driver mode in the unified app, driver dashboard, realtime service, dispatch, tracking, metered fare, safety/support;
- limited geography/fleet pilot with operational monitoring and incident playbooks.

### Milestone 8 — WordPress adapter

- API-connected plugin, blocks/shortcodes, checkout handoff/embedded flow;
- webhook/caching strategy and compatibility/security testing.

## 19. Initial epics and build order

1. Project packaging, migration framework, configuration, ACL, auditing.
2. Vehicle types, fleet, drivers, private media/documents.
3. Compliance rules, document review/versioning, expiry automation.
4. Product catalogue, packages, zones, pricing engine.
5. Availability, holds, quote snapshots, allocation.
6. Booking workflow and operator console.
7. Payments, refunds, invoices, webhook processing.
8. Notification queue and provider adapters.
9. Versioned REST API and OpenAPI contract.
10. Mobile content layout and banner management.
11. Unified Flutter app foundation, customer mode, dashboards, KYC, and notifications.
12. Seat/event products.
13. Driver mode and realtime dispatch.
14. WordPress plugin.

## 20. Decisions required before implementation

Record these as architecture/product decision records:

- supported Joomla/PHP/database matrix (Joomla 6 primary, latest Joomla 5 optional);
- Metro upgrade policy and supported-version window (initial integration is native module embedding on `2.41.5`, with no adapter);
- single operator versus marketplace at launch;
- initial cities/states/countries and compliance requirements;
- self-drive legality and business process, deposits, KYC, damage/return inspections;
- accepted customer address-proof types, KYC validity/reverification rules, reviewer roles, deletion timing, and legally required minimal retention;
- gateway and whether capture occurs before or after manual confirmation;
- maps/routing provider and how billable distance is determined;
- invoice/tax requirements and currency support;
- whether prices include tolls, parking, interstate tax, driver allowance, and accommodation;
- exact definition of day/half-day and overtime/cross-midnight handling;
- scheduled-booking advance window, provisional-hold policy, manual confirmation service level, and whether payment is collected before or after administrator approval;
- private storage, malware scan, OCR, SMS/WhatsApp, email, and push providers;
- cancellation, refund, reschedule, no-show, and force-majeure rules;
- document retention and location-history retention periods;
- languages, accessibility target, support channels, and emergency procedure;
- unified app role-switching rules and whether driver mode ships disabled until the realtime pilot;
- whether WordPress is API-only or eventually supports standalone operation.

## 21. Immediate next implementation slice

After the decisions above, the first code slice should be a Joomla package that installs successfully and provides:

- component dashboard and configuration;
- ACL actions and audit service;
- migrations for operator, vehicle type, vehicle, driver, document type, document, and review tables;
- vehicle/driver/document administrator CRUD;
- private upload abstraction;
- compliance evaluator with automated tests;
- scheduled expiry/reminder task;
- read-only API endpoints for vehicle types and compliance summary.

The next customer-facing slice after the foundation should deliver `mod_kaarbooking_booking_form`, `mod_kaarbooking_package_grid`, the future-request state, and the administrator manual-review list before any realtime driver dispatch work begins.

This slice proves packaging, permissions, storage, domain layering, API conventions, and the most safety-critical business rule before pricing and payment complexity is added.
