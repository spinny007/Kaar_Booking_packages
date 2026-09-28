# Kaar Booking Connector

This WordPress plugin is an API-connected presentation adapter for the Joomla Kaar Booking backend. It intentionally does not duplicate pricing, booking confirmation, KYC, payment, allocation, or dispatch logic.

1. Install the `kaar-booking` directory as a WordPress plugin.
2. Configure the Joomla API base at Settings → Kaar Booking, ending in `/api/index.php/v1/kaar`.
3. Use `[kaar_booking_form]` and `[kaar_packages limit="6" show_details="1"]`.

Booking submission is handed to the authoritative Joomla customer flow. A future authenticated API checkout can replace this handoff without changing the shortcode contract.
