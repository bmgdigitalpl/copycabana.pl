<?php

return [
    'policy_version' => env('PRIVACY_POLICY_VERSION', '2026-01'),
    'client_retention_days' => (int) env('PRIVACY_CLIENT_RETENTION_DAYS', 365),
    'audit_retention_days' => (int) env('PRIVACY_AUDIT_RETENTION_DAYS', 365),
    'consent_retention_days' => (int) env('PRIVACY_CONSENT_RETENTION_DAYS', 1095),
];
