# Roadmap: Automation & Payments

This roadmap is a lightweight checklist for the next sprint. It outlines the minimum steps for OTP verification, password recovery, Midtrans Snap integration, and invoices. Keep implementation lean and production-safe.

## 1) OTP Email Verification (Register)
- Add `email_otps` table:
  - `id`, `user_id`, `code_hash`, `expires_at`, `consumed_at`, `created_at`
- Service layer for email OTP
  - Generate OTP, hash code, store with 5-minute expiry
  - Send email with OTP
  - Verify OTP, mark consumed, activate account
- UI:
  - OTP input page (with resend + countdown)
- Middleware:
  - `EnsureOtpVerified` for protected actions

## 2) Forgot Password via Email
- Use Laravel password reset tokens (already available by default)
- Ensure email templates for reset link
- Add rate limiting to avoid abuse
- Add audit log entry on request + reset completion

## 3) Midtrans Snap (Payment)
- Tables:
  - `orders` and `order_items` (already present)
  - `payment_transactions`:
    - `id`, `order_id`, `provider`, `status`, `payload_json`, `created_at`
- Service: `MidtransService`
  - Create Snap token for `order_id`
  - Use server key only in backend config
- Frontend:
  - Use Snap JS popup, no page reload
- Webhook:
  - Verify signature
  - Update `status_pembayaran` + `status_pesanan`
  - Generate `booking_code` and `paid_at`
  - Log all callbacks

## 4) Digital Invoice
- Table:
  - `invoices`:
    - `id`, `order_id`, `invoice_number`, `payload_json`, `created_at`
- HTML/PDF preview
- Downloadable PDF (dompdf/snappy)
- Access control: only order owner + admin

## 5) Security & Ops
- Validate all webhooks (signature + IP allowlist if needed)
- Use queues for email + invoice generation
- Add audit logs for admin actions
- Centralize payment errors in logs
