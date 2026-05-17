<?php
/**
 * geocode.php — Location resolver for Lost & Found
 *
 * 1. Checks a hardcoded Inabanga landmark table first (instant, no API call)
 * 2. Falls back to Nominatim geocoding API (free, no key)
 * 3. Caches results in the `location_cache` DB table so each unique string
 *    is only geocoded once
 *
 * Usage:
 *   include 'geocode.php';
 *   [$lat, $lng] = resolveLocation($conn, "inabanga market");
 */

/* ── 1. Hardcoded Inabanga, Bohol landmarks ─────────────────────────────── */
const INABANGA_LANDMARKS = [
    // Government
    'municipal hall'                  => [10.0318, 124.0672],
    'inabanga municipal hall'         => [10.0318, 124.0672],
    'municipal building'              => [10.0318, 124.0672],
    'town hall'                       => [10.0318, 124.0672],
    'plaza'                           => [10.0315, 124.0668],
    'inabanga plaza'                  => [10.0315, 124.0668],
    'town plaza'                      => [10.0315, 124.0668],
    'poblacion'                       => [10.0315, 124.0668],
    'police station'                  => [10.0320, 124.0670],
    'inabanga police station'         => [10.0320, 124.0670],
    'pnp'                             => [10.0320, 124.0670],
    'fire station'                    => [10.0316, 124.0674],
    'post office'                     => [10.0314, 124.0669],

    // Market / Commercial
    'public market'                   => [10.0308, 124.0660],
    'inabanga public market'          => [10.0308, 124.0660],
    'inabanga market'                 => [10.0308, 124.0660],
    'palengke'                        => [10.0308, 124.0660],
    'wet market'                      => [10.0308, 124.0660],
    'market'                          => [10.0308, 124.0660],
    'supermarket'                     => [10.0309, 124.0662],

    // Schools
    'inabanga national high school'   => [10.0325, 124.0685],
    'inhs'                            => [10.0325, 124.0685],
    'national high school'            => [10.0325, 124.0685],
    'high school'                     => [10.0325, 124.0685],
    'inabanga central school'         => [10.0311, 124.0675],
    'central school'                  => [10.0311, 124.0675],
    'elementary school'               => [10.0311, 124.0675],
    'inabanga community college'      => [10.0330, 124.0690],

    // Church
    'saint john the baptist church'   => [10.0312, 124.0663],
    'st john the baptist church'      => [10.0312, 124.0663],
    'church'                          => [10.0312, 124.0663],
    'simbahan'                        => [10.0312, 124.0663],
    'chapel'                          => [10.0312, 124.0663],
    'parish'                          => [10.0312, 124.0663],

    // Health
    'rural health unit'               => [10.0322, 124.0678],
    'rhu'                             => [10.0322, 124.0678],
    'health center'                   => [10.0322, 124.0678],
    'hospital'                        => [10.0322, 124.0678],
    'inabanga hospital'               => [10.0322, 124.0678],
    'clinic'                          => [10.0322, 124.0678],

    // Transport
    'highway'                         => [10.0310, 124.0667],
    'inabanga highway'                => [10.0310, 124.0667],
    'national highway'                => [10.0310, 124.0667],
    'bus terminal'                    => [10.0300, 124.0655],
    'terminal'                        => [10.0300, 124.0655],
    'bus stop'                        => [10.0300, 124.0655],
    'pier'                            => [10.0295, 124.0645],
    'inabanga pier'                   => [10.0295, 124.0645],
    'wharf'                           => [10.0295, 124.0645],
    'port'                            => [10.0295, 124.0645],

    // Sports / Recreation
    'gymnasium'                       => [10.0317, 124.0671],
    'gym'                             => [10.0317, 124.0671],
    'main ground'                     => [10.0316, 124.0669],
    'sports complex'                  => [10.0316, 124.0669],
    'basketball court'                => [10.0316, 124.0669],
    'covered court'                   => [10.0316, 124.0669],
    'park'                            => [10.0315, 124.0668],

    // Barangays
    'barangay cogon'                  => [10.0290, 124.0640],
    'cogon'                           => [10.0290, 124.0640],
    'barangay badiang'                => [10.0340, 124.0710],
    'badiang'                         => [10.0340, 124.0710],
    'barangay malinao'                => [10.0355, 124.0730],
    'malinao'                         => [10.0355, 124.0730],
    'barangay cambuhat'               => [10.0270, 124.0620],
    'cambuhat'                        => [10.0270, 124.0620],
    'barangay camambugan'             => [10.0280, 124.0650],
    'camambugan'                      => [10.0280, 124.0650],
    'barangay bulog'                  => [10.0360, 124.0750],
    'bulog'                           => [10.0360, 124.0750],
    'barangay mabini'                 => [10.0295, 124.0695],
    'mabini'                          => [10.0295, 124.0695],
    'barangay napo'                   => [10.0330, 124.0700],
    'napo'                            => [10.0330, 124.0700],
    'barangay bonbon'                 => [10.0345, 124.0715],
    'bonbon'                          => [10.0345, 124.0715],
    'barangay ubujan'                 => [10.0285, 124.0635],
    'ubujan'                          => [10.0285, 124.0635],
    'barangay ondol'                  => [10.0265, 124.0615],
    'ondol'                           => [10.0265, 124.0615],
    'barangay lapacan norte'          => [10.0375, 124.0745],
    'lapacan norte'                   => [10.0375, 124.0745],
    'barangay lapacan sur'            => [10.0365, 124.0740],
    'lapacan sur'                     => [10.0365, 124.0740],

    // Fallbacks
    'inabanga'                        => [10.0310, 124.0667],
    'inabanga bohol'                  => [10.0310, 124.0667],
    'unknown'                         => [10.0310, 124.0667],
    'n/a'                             => [10.0310, 124.0667],
    ''                                => [10.0310, 124.0667],
];

/* ── 2. Main resolver ────────────────────────────────────────────────────── */
function resolveLocation(mysqli $conn, string $location): array
{
    $key = strtolower(trim($location));

    /* 2a. Exact match in landmark table */
    if (isset(INABANGA_LANDMARKS[$key])) {
        return INABANGA_LANDMARKS[$key];
    }

    /* 2b. Substring match in landmark table */
    foreach (INABANGA_LANDMARKS as $landmark => $coords) {
        if (strlen($landmark) >= 4 && strpos($key, $landmark) !== false) {
            return $coords;
        }
    }
    /* also check if landmark is substring of key */
    foreach (INABANGA_LANDMARKS as $landmark => $coords) {
        if (strlen($key) >= 4 && strpos($landmark, $key) !== false) {
            return $coords;
        }
    }

    /* 2c. Check DB cache */
    $cached = getCachedCoords($conn, $location);
    if ($cached) return $cached;

    /* 2d. Geocode via Nominatim */
    $coords = nominatimGeocode($location);
    if ($coords) {
        cacheCoords($conn, $location, $coords[0], $coords[1]);
        return $coords;
    }

    /* 2e. Fallback — Inabanga town centre */
    return [10.0310, 124.0667];
}

/* ── 3. DB cache helpers ─────────────────────────────────────────────────── */
function getCachedCoords(mysqli $conn, string $location): ?array
{
    $stmt = $conn->prepare("SELECT lat, lng FROM location_cache WHERE location_key = ? LIMIT 1");
    if (!$stmt) return null;
    $stmt->bind_param("s", $location);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return [(float)$row['lat'], (float)$row['lng']];
    }
    return null;
}

function cacheCoords(mysqli $conn, string $location, float $lat, float $lng): void
{
    $stmt = $conn->prepare("INSERT IGNORE INTO location_cache (location_key, lat, lng) VALUES (?, ?, ?)");
    if (!$stmt) return;
    $stmt->bind_param("sdd", $location, $lat, $lng);
    $stmt->execute();
}

/* ── 4. Nominatim geocoding ──────────────────────────────────────────────── */
function nominatimGeocode(string $location): ?array
{
    /* Bias search toward Inabanga, Bohol, Philippines */
    $queries = [
        $location . ', Inabanga, Bohol, Philippines',
        $location . ', Bohol, Philippines',
        $location . ', Philippines',
    ];

    foreach ($queries as $query) {
        $url = 'https://nominatim.openstreetmap.org/search?'
             . http_build_query([
                 'q'              => $query,
                 'format'         => 'json',
                 'limit'          => 1,
                 'countrycodes'   => 'ph',
                 'viewbox'        => '124.04,10.06,124.10,9.99', // Inabanga bounding box
                 'bounded'        => 0,
               ]);

        $ctx = stream_context_create(['http' => [
            'timeout' => 4,
            'header'  => "User-Agent: LostFoundSystem/1.0 (inabanga.bohol@example.com)\r\n",
        ]]);

        $json = @file_get_contents($url, false, $ctx);
        if (!$json) continue;

        $data = json_decode($json, true);
        if (!empty($data[0]['lat']) && !empty($data[0]['lon'])) {
            return [(float)$data[0]['lat'], (float)$data[0]['lon']];
        }

        usleep(300000); // 300ms delay between requests (Nominatim rate limit: 1/sec)
    }

    return null;
}