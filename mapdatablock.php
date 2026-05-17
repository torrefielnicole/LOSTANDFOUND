<?php
/**
 * MAP DATA BLOCK — drop this into index.php and map.php
 * replacing the old $location_coords / foreach block.
 *
 * Place geocode.php in the same folder as your PHP files.
 */

include 'geocode.php';   // ← add this near the top of your file with the other includes

/* ── Pull all items ── */
$map_result = mysqli_query($conn, "SELECT * FROM items ORDER BY id DESC");
$map_items  = [];
while ($r = mysqli_fetch_assoc($map_result)) $map_items[] = $r;

$cat_colors = ['person'=>'#60a5fa','things'=>'#22c55e','pet'=>'#fb923c','money'=>'#facc15'];
$cat_icons  = ['person'=>'🧍','things'=>'🎒','pet'=>'🐾','money'=>'💰'];

foreach ($map_items as &$item) {
    /* Use resolveLocation() — checks landmarks, then DB cache, then Nominatim API */
    [$lat, $lng] = resolveLocation($conn, $item['location_found'] ?? '');

    /* Optionally store resolved coords back into items row so next load is instant */
    if (empty($item['lat']) || empty($item['lng'])) {
        mysqli_query($conn,
            "UPDATE items SET lat=$lat, lng=$lng WHERE id=" . (int)$item['id']
        );
    }

    $item['lat']     = $lat;
    $item['lng']     = $lng;
    $item['category']= $item['category'] ?? 'things';
    $item['color']   = $cat_colors[$item['category']] ?? '#22c55e';
    $item['caticon'] = $cat_icons[$item['category']]  ?? '📦';
}
unset($item);
?>

<!--
WHAT CHANGED vs your old code
══════════════════════════════
OLD (broken):
  $coords = $location_coords[$loc_key] ?? null;
  // Only ~40 hardcoded strings. "inabanga market", "police station",
  // "main ground" all fell through to the same default [10.0310, 124.0667]

NEW (fixed):
  [$lat, $lng] = resolveLocation($conn, $location);
  // 1. Checks 70+ hardcoded Inabanga landmarks (exact + substring)
  // 2. Checks location_cache DB table (instant after first lookup)
  // 3. Calls Nominatim geocoding API for anything still unknown
  // 4. Caches the result so the API is never hit twice for the same string
  // 5. Falls back to town centre only as last resort

SETUP CHECKLIST
═══════════════
1. Run map_fix_migration.sql in phpMyAdmin
2. Copy geocode.php into your project folder
3. In index.php: replace the old $location_coords block with this file's logic
   OR just add `include 'geocode.php';` at top and change the foreach to use resolveLocation()
4. In map.php: same change

QUICK PATCH for index.php and map.php
══════════════════════════════════════
Find this block in both files:

    foreach ($map_items as &$item) {
        $loc_key = strtolower(trim($item['location_found'] ?? ''));
        $coords  = $location_coords[$loc_key] ?? null;
        if (!$coords) {
            foreach ($location_coords as $key => $c) {
                if (strlen($key) > 3 && strpos($loc_key, $key) !== false) { $coords = $c; break; }
            }
        }
        $item['lat']     = $coords[0] ?? 10.0310;
        $item['lng']     = $coords[1] ?? 124.0667;
        ...
    }

Replace with:

    foreach ($map_items as &$item) {
        [$item['lat'], $item['lng']] = resolveLocation($conn, $item['location_found'] ?? '');
        $item['category'] = $item['category'] ?? 'things';
        $item['color']    = $cat_colors[$item['category']] ?? '#22c55e';
        $item['caticon']  = $cat_icons[$item['category']]  ?? '📦';
    }
    unset($item);

Also delete the entire $location_coords = [...] array — it's no longer needed.
-->