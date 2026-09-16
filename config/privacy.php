<?php

return [
    'policy_version' => env('PRIVACY_POLICY_VERSION', '2026-09'),
    'cookies_policy_version' => env('COOKIES_POLICY_VERSION', '2026-09'),
    'client_retention_days' => (int) env('PRIVACY_CLIENT_RETENTION_DAYS', 365),
    'uploaded_file_retention_days' => (int) env('PRIVACY_UPLOADED_FILE_RETENTION_DAYS', 7),
    'audit_retention_days' => (int) env('PRIVACY_AUDIT_RETENTION_DAYS', 365),
    'consent_retention_days' => (int) env('PRIVACY_CONSENT_RETENTION_DAYS', 1095),
];
