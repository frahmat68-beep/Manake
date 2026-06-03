<?php

return [
    'login_max_attempts' => (int) env('LOGIN_MAX_ATTEMPTS', 5),
    'login_lockout_seconds' => (int) env('LOGIN_LOCKOUT_SECONDS', 300),
    'admin_login_max_attempts' => (int) env('ADMIN_LOGIN_MAX_ATTEMPTS', 5),
    'admin_login_lockout_seconds' => (int) env('ADMIN_LOGIN_LOCKOUT_SECONDS', 300),
    'email_verification_resend_cooldown_seconds' => (int) env('EMAIL_VERIFY_RESEND_COOLDOWN_SECONDS', 60),
    'email_verification_resend_max_per_hour' => (int) env('EMAIL_VERIFY_RESEND_MAX_PER_HOUR', 5),
];
