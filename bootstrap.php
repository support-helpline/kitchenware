<?php
// bootstrap.php
// Purpose: Detect real client IP behind proxies (Cloudflare + DO App Platform) and start session early.

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$realIp = null;

/**
 * Helper: validate IP
 */
$validIp = function ($ip) {
    return is_string($ip) && $ip !== '' && filter_var($ip, FILTER_VALIDATE_IP);
};

/**
 * 1) DigitalOcean App Platform often provides the real IP here (more reliable than XFF).
 */
if (!empty($_SERVER['HTTP_X_REAL_IP']) && $validIp($_SERVER['HTTP_X_REAL_IP'])) {
    $realIp = $_SERVER['HTTP_X_REAL_IP'];
}

// 2) Overwrite common IP fields so any script (even if it reads the "wrong" header) gets the real IP.
if ($realIp) {
    $_SERVER['REMOTE_ADDR'] = $realIp;

    // Force common proxy headers to the same real IP (prevents "wrong header" parsers from using CF edge IP)
    $_SERVER['HTTP_X_FORWARDED_FOR']  = $realIp;
    $_SERVER['HTTP_X_REAL_IP']        = $realIp;
    $_SERVER['HTTP_TRUE_CLIENT_IP']   = $realIp;
    $_SERVER['HTTP_CF_CONNECTING_IP'] = $realIp;
}

/**
 * 3) Fallback to X-Forwarded-For FIRST IP (the visitor) if present.
 * Note: this can be spoofed if you are not behind a trusted proxy, but on App Platform it’s typically safe.
 */
elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $first = trim($parts[0] ?? '');
    if ($validIp($first)) {
        $realIp = $first;
    }
}

if ($realIp) {
    // Make ALL common server vars consistent so downstream scripts (Adspect) read the right IP.
    $_SERVER['REMOTE_ADDR'] = $realIp;

    // Adspect checks these first — set them so it never falls back to the wrong XFF parsing.
    $_SERVER['HTTP_CF_CONNECTING_IP'] = $realIp;
    $_SERVER['HTTP_TRUE_CLIENT_IP']  = $realIp;

    // Keep these aligned too
    $_SERVER['HTTP_X_REAL_IP'] = $realIp;
    $_SERVER['HTTP_REAL_IP']   = $realIp;

    // OPTIONAL but useful: Adspect extracts the LAST IP from XFF, so normalize it to a single IP.
    $_SERVER['HTTP_X_FORWARDED_FOR'] = $realIp;
}