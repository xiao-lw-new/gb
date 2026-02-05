<?php

return [
    /**
     * Enable header address login:
     *   address: 0x...
     *
     * When enabled, if request header contains an EVM address and an existing user
     * can be found by that address, treat the request as authenticated as that user.
     *
     * IMPORTANT: This is powerful; only enable in trusted environments.
     */
    'address_header_token_login_enabled' => (bool) env('ADDRESS_HEADER_TOKEN_LOGIN_ENABLED', false),

    /**
     * Enable special address bearer token login:
     *   Authorization: Bearer _token_{address}_{suffix}
     *
     * This config reads from env so you still control it via .env, but using
     * config() keeps it compatible with `php artisan config:cache`.
     */
    'address_token_login_enabled' => (bool) env('ADDRESS_TOKEN_LOGIN_ENABLED', false),

    /**
     * Suffix secret used in the token format above.
     * In production, use a long random secret.
     */
    'address_token_login_suffix' => (string) env('ADDRESS_TOKEN_LOGIN_SUFFIX', ''),
];
