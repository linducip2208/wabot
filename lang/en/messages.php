<?php

return [
    'warning' => [
        'select_plan' => 'Please select a plan to continue.',
        'subscription_required' => 'You need an active subscription for this plan.',
    ],
    'error' => [
        'limit_reached' => 'Limit :feature reached (:current/:limit). Upgrade your plan.',
        'access_denied' => 'Access denied.',
        'license_connection_failed' => 'Could not connect to license server: :error',
        'activation_failed' => 'Activation failed.',
        'license_server_error' => 'Server error (HTTP :status).',
        'signature_validation_failed' => 'Server response signature validation failed. Contact support.',
        'license_encrypt_failed' => 'Failed to encrypt license payload.',
    ],
    'flows' => [
        'choose_one' => 'Choose one:',
    ],
];
