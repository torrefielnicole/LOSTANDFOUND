<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map – Lost & Found</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #0f1117;
            color: #e8e8e8;
            min-height: 100vh;
        }

        .page { padding: 32px 28px; max-width: 1200px; margin: 0 auto; }

        /* Header */
        .header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 28px;
            flex-wrap: wrap;
            gap: 16px;
        }
        .title-eyebrow {
            font-family: 'Space Mono', monospace;
            font-size: 10px;
            letter-spacing: 0.18em;
            color: #5a7a6a;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        .title-main { font-size: 26px; font-weight: 600; color: #f0f0f0; line-height: 1.1; }
        .title-sub  { font-size: 13px; color: #6b7280; margin-top: 4px; font-weight: 300; }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #171c26;
            color: #e8e8e8;
            border: 0.5px solid #2a2f3d;
            border-radius: 10px;
            padding: 10px 18px;
            font-size: 13px;
            font-weight: 500;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .btn-back:hover { background: #1e2330; border-color: #3a4050; }

        /* Filter bar */
        .filter-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .filter-label {
            font-family: 'Space Mono', monospace;
            font-size: 10px;
            letter-spacing: 0.12em;
            color: #6b7280;
            text-transform: uppercase;
            margin-right: 4px;
        }
        .filter-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #171c26;
            border: 0.5px solid #2a2f3d;
            border-radius: 20px;
            padding: 5px 13px;
            font-size: 11px;
            font-weight: 500;
            color: #9ca3af;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            transition: all 0.15s;
        }
        .filter-btn:hover  { border-color: #3a4050; color: #e8e8e8; }
        .filter-btn.active { border-color: #22c55e55; background: #1a2e1a; color: #22c55e; }
        .filter-btn.person.active  { border-color: #60a5fa55; background: #1a2030; color: #60a5fa; }
        .filter-btn.things.active  { border-color: #22c55e55; background: #1a2e1a; color: #22c55e; }
        .filter-btn.pet.active     { border-color: #fb923c55; background: #2a1a0a; color: #fb923c; }
        .filter-btn.money.active   { border-color: #facc1555; background: #2a2a0a; color: #facc15; }

        /* Layout: map + sidebar */
        .map-layout {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 16px;
            align-items: start;
        }

        /* Map container */
        .map-wrap {
            background: #171c26;
            border: 0.5px solid #2a2f3d;
            border-radius: 16px;
            overflow: hidden;
            height: 560px;
            position: relative;
        }
        #map { width: 100%; height: 100%; }

        /* Leaflet popup override */
        .leaflet-popup-content-wrapper {
            background: #171c26 !important;
            border: 0.5px solid #2a2f3d !important;
            border-radius: 12px !important;
            color: #e8e8e8 !important;
            box-shadow: 0 8px 32px rgba(0,0,0,0.5) !important;
            font-family: 'DM Sans', sans-serif !important;
            padding: 0 !important;
        }
        .leaflet-popup-tip { background: #171c26 !important; }
        .leaflet-popup-content { margin: 0 !important; width: auto !important; }
        .popup-inner { padding: 14px 16px; min-width: 200px; }
        .popup-cat {
            font-family: 'Space Mono', monospace;
            font-size: 9px;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        .popup-name { font-size: 13px; font-weight: 600; color: #f0f0f0; margin-bottom: 4px; }
        .popup-desc { font-size: 11px; color: #9ca3af; line-height: 1.5; margin-bottom: 8px; }
        .popup-loc  { font-size: 10px; color: #6b7280; font-family: 'Space Mono', monospace; }
        .popup-status {
            display: inline-block;
            font-size: 10px; font-weight: 600;
            border-radius: 20px; padding: 3px 9px;
            margin-top: 8px;
        }
        .popup-status.found   { background: #1a2e1a; color: #22c55e; border: 0.5px solid #22c55e33; }
        .popup-status.claimed { background: #1a1a2e; color: #818cf8; border: 0.5px solid #818cf833; }
        .leaflet-control-zoom a {
            background: #171c26 !important;
            color: #e8e8e8 !important;
            border-color: #2a2f3d !important;
        }
        .leaflet-control-zoom a:hover { background: #1e2330 !important; }
        .leaflet-control-attribution { background: rgba(23,28,38,0.85) !important; color: #6b7280 !important; font-size: 9px !important; }
        .leaflet-control-attribution a { color: #5a7a6a !important; }

        /* Sidebar */
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 8px;
            max-height: 560px;
            overflow-y: auto;
        }
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-track { background: transparent; }
        .sidebar::-webkit-scrollbar-thumb { background: #2a2f3d; border-radius: 4px; }

        .sidebar-card {
            background: #171c26;
            border: 0.5px solid #2a2f3d;
            border-radius: 12px;
            padding: 12px 14px;
            cursor: pointer;
            transition: border-color 0.15s, background 0.15s;
            border-left: 2px solid transparent;
        }
        .sidebar-card:hover { background: #1a2030; }
        .sidebar-card.person  { border-left-color: #60a5fa; }
        .sidebar-card.things  { border-left-color: #22c55e; }
        .sidebar-card.pet     { border-left-color: #fb923c; }
        .sidebar-card.money   { border-left-color: #facc15; }
        .sidebar-card.hidden  { display: none; }

        .sc-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px; }
        .sc-name { font-size: 12px; font-weight: 500; color: #e8e8e8; }
        .sc-cat {
            font-family: 'Space Mono', monospace;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 2px 7px;
            border-radius: 20px;
        }
        .sc-cat.person  { background: #1a2030; color: #60a5fa; }
        .sc-cat.things  { background: #1a2e1a; color: #22c55e; }
        .sc-cat.pet     { background: #2a1a0a; color: #fb923c; }
        .sc-cat.money   { background: #2a2a0a; color: #facc15; }
        .sc-loc { font-size: 10px; color: #6b7280; font-family: 'Space Mono', monospace; }

        .no-items {
            text-align: center; padding: 40px 16px;
            color: #6b7280; font-size: 13px;
        }

        /* Legend */
        .legend {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            margin-top: 14px;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            color: #9ca3af;
        }
        .legend-dot {
            width: 10px; height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        @media (max-width: 820px) {
            .map-layout { grid-template-columns: 1fr; }
            .sidebar { max-height: 260px; }
            .map-wrap { height: 380px; }
            .page { padding: 20px 16px; }
        }
    </style>
</head>
<body>

<?php
    /* ── Pull all items with a location ── */
    $result = mysqli_query($conn, "SELECT * FROM items ORDER BY id DESC");
    $items  = [];
    while ($r = mysqli_fetch_assoc($result)) $items[] = $r;

    /*
     * Location → lat/lng mapping for Inabanga, Bohol landmarks.
     * Extend this array as your system grows.
     * Keys should match what staff type in location_found.
     */
    $location_coords = [
        /* town-centre & government */
        'municipal hall'         => [10.0318, 124.0672],
        'inabanga municipal hall'=> [10.0318, 124.0672],
        'plaza'                  => [10.0315, 124.0668],
        'inabanga plaza'         => [10.0315, 124.0668],
        'public market'          => [10.0308, 124.0660],
        'inabanga public market' => [10.0308, 124.0660],
        'palengke'               => [10.0308, 124.0660],

        /* schools */
        'inabanga national high school' => [10.0325, 124.0685],
        'inhs'                          => [10.0325, 124.0685],
        'inabanga central school'       => [10.0311, 124.0675],
        'central school'                => [10.0311, 124.0675],

        /* church & barangays */
        'saint john the baptist church' => [10.0312, 124.0663],
        'church'                        => [10.0312, 124.0663],
        'poblacion'                     => [10.0315, 124.0668],
        'barangay cogon'                => [10.0290, 124.0640],
        'cogon'                         => [10.0290, 124.0640],
        'barangay badiang'              => [10.0340, 124.0710],
        'badiang'                       => [10.0340, 124.0710],
        'barangay malinao'              => [10.0355, 124.0730],
        'malinao'                       => [10.0355, 124.0730],
        'barangay cambuhat'             => [10.0270, 124.0620],
        'cambuhat'                      => [10.0270, 124.0620],
        'barangay camambugan'           => [10.0280, 124.0650],
        'camambugan'                    => [10.0280, 124.0650],
        'barangay bulog'                => [10.0360, 124.0750],
        'bulog'                         => [10.0360, 124.0750],
        'barangay mabini'               => [10.0295, 124.0695],
        'mabini'                        => [10.0295, 124.0695],
        'barangay napo'                 => [10.0330, 124.0700],
        'napo'                          => [10.0330, 124.0700],

        /* roads & transport */
        'highway'                => [10.0310, 124.0667],
        'inabanga highway'       => [10.0310, 124.0667],
        'bus terminal'           => [10.0300, 124.0655],
        'terminal'               => [10.0300, 124.0655],
        'pier'                   => [10.0295, 124.0645],
        'inabanga pier'          => [10.0295, 124.0645],

        /* health */
        'rural health unit'      => [10.0322, 124.0678],
        'rhu'                    => [10.0322, 124.0678],
        'hospital'               => [10.0322, 124.0678],

        /* default fallback — town centre */
        'inabanga'               => [10.0310, 124.0667],
        'unknown'                => [10.0310, 124.0667],
    ];

    /* ── Assign coordinates to each item ── */
    $cat_colors = [
        'person' => '#60a5fa',
        'things' => '#22c55e',
        'pet'    => '#fb923c',
        'money'  => '#facc15',
    ];
    $cat_icons = [
        'person' => '🧍',
        'things' => '🎒',
        'pet'    => '🐾',
        'money'  => '💰',
    ];

    foreach ($items as &$item) {
        $loc_key = strtolower(trim($item['location_found'] ?? ''));
        $coords  = $location_coords[$loc_key] ?? null;

        /* fuzzy match: check if any key is contained in the location string */
        if (!$coords) {
            foreach ($location_coords as $key => $c) {
                if (strlen($key) > 3 && strpos($loc_key, $key) !== false) {
                    $coords = $c;
                    break;
                }
            }
        }
        /* final fallback */
        $item['lat'] = $coords[0] ?? 10.0310;
        $item['lng'] = $coords[1] ?? 124.0667;
        $item['category'] = $item['category'] ?? 'things';
        $item['color']    = $cat_colors[$item['category']] ?? '#22c55e';
        $item['icon']     = $cat_icons[$item['category']]  ?? '📦';
    }
    unset($item);
?>

<div class="page">

    <!-- Header -->
    <div class="header">
        <div>
            <div class="title-eyebrow">🗺 Location Map</div>
            <div class="title-main">Inabanga, Bohol</div>
            <div class="title-sub">Visual map of all lost &amp; found reports by location</div>
        </div>
        <a class="btn-back" href="index.php">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
            Back to List
        </a>
    </div>

    <!-- Filter -->
    <div class="filter-bar">
        <span class="filter-label">Filter:</span>
        <button class="filter-btn active" id="btn-all"    onclick="filterMap('all')">All (<?= count($items) ?>)</button>
        <button class="filter-btn person" id="btn-person" onclick="filterMap('person')">🧍 Person</button>
        <button class="filter-btn things" id="btn-things" onclick="filterMap('things')">🎒 Things</button>
        <button class="filter-btn pet"    id="btn-pet"    onclick="filterMap('pet')">🐾 Pet</button>
        <button class="filter-btn money"  id="btn-money"  onclick="filterMap('money')">💰 Money</button>
    </div>

    <!-- Map + Sidebar -->
    <div class="map-layout">
        <div class="map-wrap"><div id="map"></div></div>

        <div class="sidebar" id="sidebar">
            <?php foreach ($items as $item):
                $cat    = htmlspecialchars($item['category']);
                $padded = str_pad($item['id'], 3, '0', STR_PAD_LEFT);
                $status = $item['status'] ?? 'found';
            ?>
            <div class="sidebar-card <?= $cat ?>" data-cat="<?= $cat ?>" data-id="<?= $item['id'] ?>"
                 onclick="focusMarker(<?= $item['id'] ?>)">
                <div class="sc-top">
                    <div class="sc-name"><?= htmlspecialchars($item['item_name']) ?></div>
                    <span class="sc-cat <?= $cat ?>"><?= $cat ?></span>
                </div>
                <div class="sc-loc">📍 <?= htmlspecialchars($item['location_found']) ?> · #<?= $padded ?></div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($items)): ?>
                <div class="no-items">No items recorded yet.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Legend -->
    <div class="legend">
        <div class="legend-item"><div class="legend-dot" style="background:#60a5fa"></div>Person</div>
        <div class="legend-item"><div class="legend-dot" style="background:#22c55e"></div>Things</div>
        <div class="legend-item"><div class="legend-dot" style="background:#fb923c"></div>Pet</div>
        <div class="legend-item"><div class="legend-dot" style="background:#facc15"></div>Money</div>
    </div>

</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
/* ── Map init ── */
const map = L.map('map', {
    center: [10.0310, 124.0667],
    zoom: 14,
    zoomControl: true
});

/* Dark tile layer via CartoDB Dark Matter */
L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/">CARTO</a>',
    subdomains: 'abcd',
    maxZoom: 19
}).addTo(map);

/* ── Item data from PHP ── */
const items = <?= json_encode(array_map(function($i) {
    return [
        'id'       => $i['id'],
        'name'     => $i['item_name'],
        'desc'     => $i['description'],
        'location' => $i['location_found'],
        'category' => $i['category'],
        'status'   => $i['status'] ?? 'found',
        'color'    => $i['color'],
        'icon'     => $i['icon'],
        'lat'      => $i['lat'],
        'lng'      => $i['lng'],
    ];
}, $items)) ?>;

/* ── Custom circle markers ── */
const markers = {};

function makeIcon(color, emoji) {
    return L.divIcon({
        className: '',
        html: `<div style="
            width:34px;height:34px;border-radius:50%;
            background:${color}22;border:2px solid ${color};
            display:flex;align-items:center;justify-content:center;
            font-size:15px;cursor:pointer;
            box-shadow:0 0 0 4px ${color}18;
        ">${emoji}</div>`,
        iconSize: [34, 34],
        iconAnchor: [17, 17],
        popupAnchor: [0, -20]
    });
}

items.forEach(item => {
    const padded = String(item.id).padStart(3, '0');
    const catColor = {
        person: '#60a5fa', things: '#22c55e', pet: '#fb923c', money: '#facc15'
    }[item.category] || '#22c55e';

    const popupHtml = `
        <div class="popup-inner">
            <div class="popup-cat" style="color:${catColor}">${item.category.toUpperCase()}</div>
            <div class="popup-name">${item.name}</div>
            <div class="popup-desc">${item.desc || '—'}</div>
            <div class="popup-loc">📍 ${item.location}</div>
            <div><span class="popup-status ${item.status}">${item.status === 'claimed' ? '✓ Claimed' : '📦 Found'}</span></div>
        </div>`;

    const marker = L.marker([item.lat, item.lng], {
        icon: makeIcon(catColor, item.icon),
        title: item.name
    })
    .addTo(map)
    .bindPopup(popupHtml, { maxWidth: 240 });

    markers[item.id] = { marker, category: item.category };
});

/* ── Filter ── */
let currentFilter = 'all';

function filterMap(cat) {
    currentFilter = cat;

    /* update buttons */
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('btn-' + (cat === 'all' ? 'all' : cat)).classList.add('active');
    if (cat === 'all') document.getElementById('btn-all').classList.add('active');

    /* toggle markers */
    Object.entries(markers).forEach(([id, m]) => {
        if (cat === 'all' || m.category === cat) {
            map.addLayer(m.marker);
        } else {
            map.removeLayer(m.marker);
        }
    });

    /* toggle sidebar cards */
    document.querySelectorAll('.sidebar-card').forEach(card => {
        const show = cat === 'all' || card.dataset.cat === cat;
        card.classList.toggle('hidden', !show);
    });
}

/* ── Focus a marker from sidebar click ── */
function focusMarker(id) {
    const m = markers[id];
    if (!m) return;
    map.setView(m.marker.getLatLng(), 16, { animate: true });
    m.marker.openPopup();

    /* highlight sidebar card */
    document.querySelectorAll('.sidebar-card').forEach(c => c.style.background = '');
    const card = document.querySelector(`.sidebar-card[data-id="${id}"]`);
    if (card) {
        card.style.background = '#1e2330';
        card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}
</script>

</body>
</html>