<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost and Found System</title>
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
            margin-bottom: 36px;
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
        .title-sub { font-size: 13px; color: #6b7280; margin-top: 4px; font-weight: 300; }

        .btn-add {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #22c55e;
            color: #0a1a10;
            border: none;
            border-radius: 10px;
            padding: 11px 20px;
            font-size: 13px;
            font-weight: 600;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            text-decoration: none;
            letter-spacing: 0.01em;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .btn-add:hover {
            background: #16a34a;
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(34, 197, 94, 0.3);
        }

        /* Stats */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 28px;
        }
        .stat-card {
            background: #171c26;
            border: 0.5px solid #2a2f3d;
            border-radius: 12px;
            padding: 16px 18px;
        }
        .stat-label {
            font-size: 11px;
            color: #6b7280;
            font-family: 'Space Mono', monospace;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            margin-bottom: 6px;
        }
        .stat-value { font-size: 24px; font-weight: 600; color: #f0f0f0; }
        .stat-badge {
            display: inline-block;
            font-size: 10px;
            background: #1a2e1a;
            color: #22c55e;
            padding: 2px 7px;
            border-radius: 20px;
            font-weight: 500;
            margin-top: 4px;
            border: 0.5px solid #22c55e33;
        }

        /* Category Cards */
        .section-label {
            font-family: 'Space Mono', monospace;
            font-size: 10px;
            letter-spacing: 0.16em;
            color: #5a7a6a;
            text-transform: uppercase;
            margin-bottom: 12px;
        }
        .category-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 28px;
        }
        .cat-card {
            background: #171c26;
            border: 0.5px solid #2a2f3d;
            border-radius: 14px;
            padding: 20px 16px 18px;
            cursor: pointer;
            text-align: center;
            transition: border-color 0.2s, background 0.2s, transform 0.15s;
            position: relative;
            overflow: hidden;
        }
        .cat-card:hover { border-color: #3a4050; background: #1a2030; transform: translateY(-2px); }
        .cat-card .cat-icon { font-size: 30px; margin-bottom: 10px; display: block; line-height: 1; }
        .cat-card .cat-label { font-size: 13px; font-weight: 600; color: #e8e8e8; margin-bottom: 4px; }
        .cat-card .cat-count { font-family: 'Space Mono', monospace; font-size: 10px; color: #6b7280; }
        .cat-card .cat-accent { position: absolute; top: 0; left: 0; right: 0; height: 2px; border-radius: 14px 14px 0 0; }
        .cat-card.person .cat-accent { background: #60a5fa; }
        .cat-card.things .cat-accent { background: #22c55e; }
        .cat-card.pet    .cat-accent { background: #fb923c; }
        .cat-card.money  .cat-accent { background: #facc15; }
        .cat-card.person:hover { border-color: #60a5fa44; }
        .cat-card.things:hover { border-color: #22c55e44; }
        .cat-card.pet:hover    { border-color: #fb923c44; }
        .cat-card.money:hover  { border-color: #facc1544; }

        /* ── MAP SECTION ── */
        .map-section { margin-bottom: 28px; }
        .map-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .map-filter-bar { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
        .map-filter-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #1e2330;
            border: 0.5px solid #2a2f3d;
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 11px;
            font-weight: 500;
            color: #9ca3af;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            transition: all 0.15s;
        }
        .map-filter-btn:hover { border-color: #3a4050; color: #e8e8e8; }
        .map-filter-btn.active              { border-color: #22c55e55; background: #1a2e1a; color: #22c55e; }
        .map-filter-btn.cat-person.active   { border-color: #60a5fa55; background: #18202e; color: #60a5fa; }
        .map-filter-btn.cat-things.active   { border-color: #22c55e55; background: #1a2e1a; color: #22c55e; }
        .map-filter-btn.cat-pet.active      { border-color: #fb923c55; background: #2a1c10; color: #fb923c; }
        .map-filter-btn.cat-money.active    { border-color: #facc1555; background: #252208; color: #facc15; }

        .map-layout {
            display: grid;
            grid-template-columns: 1fr 280px;
            gap: 12px;
        }
        .map-wrap {
            background: #171c26;
            border: 0.5px solid #2a2f3d;
            border-radius: 14px;
            overflow: hidden;
            height: 420px;
        }
        #map { width: 100%; height: 100%; }

        /* Leaflet popup dark override */
        .leaflet-popup-content-wrapper {
            background: #171c26 !important;
            border: 0.5px solid #2a2f3d !important;
            border-radius: 12px !important;
            color: #e8e8e8 !important;
            box-shadow: 0 8px 32px rgba(0,0,0,0.6) !important;
            font-family: 'DM Sans', sans-serif !important;
            padding: 0 !important;
        }
        .leaflet-popup-tip { background: #171c26 !important; }
        .leaflet-popup-content { margin: 0 !important; width: auto !important; }
        .popup-inner { padding: 13px 15px; min-width: 190px; }
        .popup-cat { font-family: 'Space Mono', monospace; font-size: 9px; letter-spacing: 0.14em; text-transform: uppercase; margin-bottom: 5px; }
        .popup-name { font-size: 13px; font-weight: 600; color: #f0f0f0; margin-bottom: 3px; }
        .popup-desc { font-size: 11px; color: #9ca3af; line-height: 1.5; margin-bottom: 6px; }
        .popup-loc  { font-size: 10px; color: #6b7280; font-family: 'Space Mono', monospace; }
        .popup-status { display: inline-block; font-size: 10px; font-weight: 600; border-radius: 20px; padding: 3px 9px; margin-top: 7px; }
        .popup-status.found   { background: #1a2e1a; color: #22c55e; border: 0.5px solid #22c55e33; }
        .popup-status.claimed { background: #1a1a2e; color: #818cf8; border: 0.5px solid #818cf833; }
        .leaflet-control-zoom a { background: #171c26 !important; color: #e8e8e8 !important; border-color: #2a2f3d !important; }
        .leaflet-control-zoom a:hover { background: #1e2330 !important; }
        .leaflet-control-attribution { background: rgba(15,17,23,0.85) !important; color: #6b7280 !important; font-size: 9px !important; }
        .leaflet-control-attribution a { color: #5a7a6a !important; }

        /* Map sidebar */
        .map-sidebar {
            display: flex;
            flex-direction: column;
            gap: 6px;
            height: 420px;
            overflow-y: auto;
        }
        .map-sidebar::-webkit-scrollbar { width: 3px; }
        .map-sidebar::-webkit-scrollbar-track { background: transparent; }
        .map-sidebar::-webkit-scrollbar-thumb { background: #2a2f3d; border-radius: 4px; }

        .map-sc {
            background: #171c26;
            border: 0.5px solid #2a2f3d;
            border-left: 2px solid transparent;
            border-radius: 10px;
            padding: 10px 12px;
            cursor: pointer;
            transition: background 0.12s;
            flex-shrink: 0;
        }
        .map-sc:hover { background: #1a2030; }
        .map-sc.cat-person { border-left-color: #60a5fa; }
        .map-sc.cat-things { border-left-color: #22c55e; }
        .map-sc.cat-pet    { border-left-color: #fb923c; }
        .map-sc.cat-money  { border-left-color: #facc15; }
        .map-sc.sc-hidden  { display: none; }
        .sc-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 3px; }
        .sc-name { font-size: 12px; font-weight: 500; color: #e8e8e8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sc-tag  { font-family: 'Space Mono', monospace; font-size: 9px; text-transform: uppercase; padding: 2px 6px; border-radius: 20px; white-space: nowrap; flex-shrink: 0; }
        .sc-tag.cat-person { background: #18202e; color: #60a5fa; }
        .sc-tag.cat-things { background: #1a2e1a; color: #22c55e; }
        .sc-tag.cat-pet    { background: #2a1c10; color: #fb923c; }
        .sc-tag.cat-money  { background: #252208; color: #facc15; }
        .sc-loc { font-size: 10px; color: #6b7280; font-family: 'Space Mono', monospace; }

        .map-legend { display: flex; gap: 14px; flex-wrap: wrap; margin-top: 10px; }
        .legend-dot-item { display: flex; align-items: center; gap: 5px; font-size: 11px; color: #9ca3af; }
        .ldot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
        .map-empty { text-align: center; padding: 30px 12px; color: #6b7280; font-size: 12px; }

        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.65);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-overlay.active { display: flex; }
        .modal-box {
            background: #171c26;
            border: 0.5px solid #2a2f3d;
            border-radius: 18px;
            width: 100%;
            max-width: 560px;
            max-height: 80vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            animation: modalIn 0.18s ease;
        }
        @keyframes modalIn {
            from { opacity: 0; transform: translateY(12px) scale(0.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        .modal-header { display: flex; align-items: center; justify-content: space-between; padding: 18px 22px 16px; border-bottom: 0.5px solid #2a2f3d; flex-shrink: 0; }
        .modal-header-left { display: flex; align-items: center; gap: 12px; }
        .modal-header-icon { font-size: 22px; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: #1e2330; }
        .modal-title { font-size: 15px; font-weight: 600; color: #f0f0f0; }
        .modal-subtitle { font-size: 11px; color: #6b7280; font-family: 'Space Mono', monospace; margin-top: 2px; }
        .modal-close { background: #1e2330; border: 0.5px solid #2a2f3d; border-radius: 8px; color: #9ca3af; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 18px; transition: all 0.15s; line-height: 1; }
        .modal-close:hover { background: #2a2f3d; color: #e8e8e8; }
        .modal-body { overflow-y: auto; flex: 1; padding: 8px 0; }
        .modal-item { display: flex; align-items: center; gap: 14px; padding: 14px 22px; border-bottom: 0.5px solid #1e2330; transition: background 0.12s; }
        .modal-item:last-child { border-bottom: none; }
        .modal-item:hover { background: #1a2030; }
        .modal-item-img { width: 50px; height: 50px; border-radius: 10px; object-fit: cover; border: 0.5px solid #2a2f3d; background: #1e2330; flex-shrink: 0; }
        .modal-item-img-placeholder { width: 50px; height: 50px; border-radius: 10px; background: #1e2330; border: 0.5px solid #2a2f3d; display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #6b7280; font-size: 20px; }
        .modal-item-info { flex: 1; min-width: 0; }
        .modal-item-name { font-size: 13px; font-weight: 500; color: #e8e8e8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .modal-item-desc { font-size: 11px; color: #9ca3af; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .modal-item-loc { display: flex; align-items: center; gap: 4px; font-size: 10px; color: #6b7280; margin-top: 4px; font-family: 'Space Mono', monospace; }
        .modal-status { font-size: 10px; font-weight: 600; border-radius: 20px; padding: 3px 9px; white-space: nowrap; flex-shrink: 0; }
        .modal-status.found   { background: #1a2e1a; color: #22c55e; border: 0.5px solid #22c55e33; }
        .modal-status.claimed { background: #1a1a2e; color: #818cf8; border: 0.5px solid #818cf833; }
        .modal-empty { text-align: center; padding: 40px 20px; color: #6b7280; font-size: 13px; }
        .modal-empty span { font-size: 28px; display: block; margin-bottom: 8px; }
        .modal-footer { padding: 14px 22px; border-top: 0.5px solid #2a2f3d; display: flex; justify-content: flex-end; flex-shrink: 0; }
        .btn-add-modal { display: inline-flex; align-items: center; gap: 6px; background: #22c55e; color: #0a1a10; border: none; border-radius: 8px; padding: 8px 16px; font-size: 12px; font-weight: 600; font-family: 'DM Sans', sans-serif; cursor: pointer; text-decoration: none; transition: all 0.2s; }
        .btn-add-modal:hover { background: #16a34a; }

        /* Search */
        .search-row { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .search-input { flex: 1; min-width: 200px; background: #171c26; border: 0.5px solid #2a2f3d; border-radius: 10px; padding: 10px 16px; color: #e8e8e8; font-size: 13px; font-family: 'DM Sans', sans-serif; outline: none; transition: border-color 0.2s; }
        .search-input::placeholder { color: #444; }
        .search-input:focus { border-color: #22c55e55; }

        /* Table */
        .table-wrap { background: #171c26; border: 0.5px solid #2a2f3d; border-radius: 16px; overflow: hidden; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 700px; }
        thead tr { background: #1e2330; border-bottom: 0.5px solid #2a2f3d; }
        th { font-size: 10px; font-weight: 600; font-family: 'Space Mono', monospace; text-transform: uppercase; letter-spacing: 0.12em; color: #6b7280; padding: 14px 16px; text-align: left; }
        th:last-child { text-align: center; }
        tbody tr { border-bottom: 0.5px solid #1e2330; transition: background 0.15s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: #1a2030; }
        td { padding: 14px 16px; font-size: 13px; color: #c8c8c8; vertical-align: middle; }
        .id-cell { font-family: 'Space Mono', monospace; font-size: 11px; color: #5a7a6a; }
        .item-img { width: 60px; height: 60px; border-radius: 10px; object-fit: cover; border: 0.5px solid #2a2f3d; background: #1e2330; display: block; }
        .img-placeholder { width: 60px; height: 60px; border-radius: 10px; background: #1e2330; border: 0.5px solid #2a2f3d; display: flex; align-items: center; justify-content: center; }
        .item-name { font-weight: 500; color: #e8e8e8; }
        .desc-cell { color: #9ca3af; font-size: 12px; line-height: 1.5; }
        .location-badge { display: inline-flex; align-items: center; gap: 5px; background: #1a2030; border: 0.5px solid #2a2f3d; border-radius: 20px; padding: 4px 10px; font-size: 11px; color: #9ca3af; }
        .actions { display: flex; align-items: center; justify-content: center; gap: 6px; }
        .btn-edit, .btn-del { border: none; border-radius: 7px; padding: 6px 12px; font-size: 11px; font-weight: 600; cursor: pointer; font-family: 'DM Sans', sans-serif; text-decoration: none; letter-spacing: 0.02em; transition: all 0.15s; display: inline-block; }
        .btn-edit { background: #1e2c3a; color: #60a5fa; }
        .btn-edit:hover { background: #1e3a50; color: #93c5fd; }
        .btn-del { background: #2a1a1a; color: #f87171; }
        .btn-del:hover { background: #3a1a1a; color: #fca5a5; }
        .empty-state { text-align: center; padding: 48px 16px; color: #6b7280; font-size: 14px; }
        .status-badge { display: inline-flex; align-items: center; gap: 4px; border-radius: 20px; padding: 4px 10px; font-size: 11px; font-weight: 600; white-space: nowrap; }
        .status-badge.found   { background: #1a2e1a; color: #22c55e; border: 0.5px solid #22c55e33; }
        .status-badge.claimed { background: #1a1a2e; color: #818cf8; border: 0.5px solid #818cf833; }

        @media (max-width: 820px) {
            .map-layout { grid-template-columns: 1fr; }
            .map-sidebar { height: 220px; }
            .map-wrap { height: 320px; }
        }
        @media (max-width: 700px) {
            .stats-row { grid-template-columns: 1fr 1fr; }
            .category-grid { grid-template-columns: repeat(2, 1fr); }
            .page { padding: 20px 16px; }
        }
    </style>
</head>
<body>

<?php
    $total   = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM items"));
    $claimed = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM items WHERE status='claimed'"));
    $locs    = mysqli_num_rows(mysqli_query($conn, "SELECT DISTINCT location_found FROM items"));

    $cnt_person = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM items WHERE category='person'"));
    $cnt_things = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM items WHERE category='things'"));
    $cnt_pet    = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM items WHERE category='pet'"));
    $cnt_money  = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM items WHERE category='money'"));

    $items_person = mysqli_query($conn, "SELECT * FROM items WHERE category='person' ORDER BY id DESC");
    $items_things = mysqli_query($conn, "SELECT * FROM items WHERE category='things' ORDER BY id DESC");
    $items_pet    = mysqli_query($conn, "SELECT * FROM items WHERE category='pet'    ORDER BY id DESC");
    $items_money  = mysqli_query($conn, "SELECT * FROM items WHERE category='money'  ORDER BY id DESC");

    /* ── Map data ── */
    $map_result = mysqli_query($conn, "SELECT * FROM items ORDER BY id DESC");
    $map_items  = [];
    while ($r = mysqli_fetch_assoc($map_result)) $map_items[] = $r;

    /* Inabanga, Bohol landmark coordinates */
    $location_coords = [
        'municipal hall'                => [10.0318, 124.0672],
        'inabanga municipal hall'       => [10.0318, 124.0672],
        'plaza'                         => [10.0315, 124.0668],
        'inabanga plaza'                => [10.0315, 124.0668],
        'public market'                 => [10.0308, 124.0660],
        'inabanga public market'        => [10.0308, 124.0660],
        'palengke'                      => [10.0308, 124.0660],
        'inabanga national high school' => [10.0325, 124.0685],
        'inhs'                          => [10.0325, 124.0685],
        'inabanga central school'       => [10.0311, 124.0675],
        'central school'                => [10.0311, 124.0675],
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
        'highway'                       => [10.0310, 124.0667],
        'inabanga highway'              => [10.0310, 124.0667],
        'bus terminal'                  => [10.0300, 124.0655],
        'terminal'                      => [10.0300, 124.0655],
        'pier'                          => [10.0295, 124.0645],
        'inabanga pier'                 => [10.0295, 124.0645],
        'rural health unit'             => [10.0322, 124.0678],
        'rhu'                           => [10.0322, 124.0678],
        'hospital'                      => [10.0322, 124.0678],
        'inabanga'                      => [10.0310, 124.0667],
        'unknown'                       => [10.0310, 124.0667],
    ];
    $cat_colors = ['person'=>'#60a5fa','things'=>'#22c55e','pet'=>'#fb923c','money'=>'#facc15'];
    $cat_icons  = ['person'=>'🧍','things'=>'🎒','pet'=>'🐾','money'=>'💰'];

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
        $item['category']= $item['category'] ?? 'things';
        $item['color']   = $cat_colors[$item['category']] ?? '#22c55e';
        $item['caticon'] = $cat_icons[$item['category']]  ?? '📦';
    }
    unset($item);
?>

<div class="page">

    <!-- Header -->
    <div class="header">
        <div>
            <div class="title-eyebrow">📦 Item Registry</div>
            <div class="title-main">Lost &amp; Found</div>
            <div class="title-sub">Track, manage, and reunite items with their owners</div>
        </div>
        <a class="btn-add" href="add.php">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add New Item
        </a>
    </div>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-label">Total Items</div>
            <div class="stat-value"><?= $total ?></div>
            <div class="stat-badge">▲ Active</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Claimed</div>
            <div class="stat-value"><?= $claimed ?></div>
            <div class="stat-badge" style="background:#1a1a2e;color:#818cf8;border-color:#818cf833">This month</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Locations</div>
            <div class="stat-value"><?= $locs ?></div>
            <div class="stat-badge">Unique rooms</div>
        </div>
    </div>

    <!-- Category Cards -->
    <div class="section-label">Browse by category</div>
    <div class="category-grid">
        <div class="cat-card person" onclick="openModal('person')">
            <div class="cat-accent"></div>
            <span class="cat-icon">🧍</span>
            <div class="cat-label">Person</div>
            <div class="cat-count"><?= $cnt_person ?> missing</div>
        </div>
        <div class="cat-card things" onclick="openModal('things')">
            <div class="cat-accent"></div>
            <span class="cat-icon">🎒</span>
            <div class="cat-label">Things</div>
            <div class="cat-count"><?= $cnt_things ?> missing</div>
        </div>
        <div class="cat-card pet" onclick="openModal('pet')">
            <div class="cat-accent"></div>
            <span class="cat-icon">🐾</span>
            <div class="cat-label">Pet</div>
            <div class="cat-count"><?= $cnt_pet ?> missing</div>
        </div>
        <div class="cat-card money" onclick="openModal('money')">
            <div class="cat-accent"></div>
            <span class="cat-icon">💰</span>
            <div class="cat-label">Money</div>
            <div class="cat-count"><?= $cnt_money ?> missing</div>
        </div>
    </div>

    <!-- ══════════════════════════════════
         MAP — Inabanga, Bohol
    ══════════════════════════════════ -->
    <div class="map-section">
        <div class="map-section-header">
            <div class="section-label">📍 Location Map — Inabanga, Bohol</div>
            <div class="map-filter-bar">
                <button class="map-filter-btn active" id="mfbtn-all"    onclick="mapFilter('all')">All (<?= count($map_items) ?>)</button>
                <button class="map-filter-btn cat-person" id="mfbtn-person" onclick="mapFilter('person')">🧍 Person</button>
                <button class="map-filter-btn cat-things" id="mfbtn-things" onclick="mapFilter('things')">🎒 Things</button>
                <button class="map-filter-btn cat-pet"    id="mfbtn-pet"    onclick="mapFilter('pet')">🐾 Pet</button>
                <button class="map-filter-btn cat-money"  id="mfbtn-money"  onclick="mapFilter('money')">💰 Money</button>
            </div>
        </div>
        <div class="map-layout">
            <div class="map-wrap"><div id="map"></div></div>
            <div class="map-sidebar" id="map-sidebar">
                <?php if (empty($map_items)): ?>
                    <div class="map-empty">No items to display on map.</div>
                <?php else: foreach ($map_items as $mi):
                    $mcat   = htmlspecialchars($mi['category']);
                    $mpad   = str_pad($mi['id'], 3, '0', STR_PAD_LEFT);
                ?>
                <div class="map-sc cat-<?= $mcat ?>" data-cat="<?= $mcat ?>" data-id="<?= $mi['id'] ?>"
                     onclick="focusPin(<?= $mi['id'] ?>)">
                    <div class="sc-row">
                        <div class="sc-name"><?= htmlspecialchars($mi['item_name']) ?></div>
                        <span class="sc-tag cat-<?= $mcat ?>"><?= $mcat ?></span>
                    </div>
                    <div class="sc-loc">📍 <?= htmlspecialchars($mi['location_found']) ?> · #<?= $mpad ?></div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
        <div class="map-legend">
            <div class="legend-dot-item"><div class="ldot" style="background:#60a5fa"></div>Person</div>
            <div class="legend-dot-item"><div class="ldot" style="background:#22c55e"></div>Things</div>
            <div class="legend-dot-item"><div class="ldot" style="background:#fb923c"></div>Pet</div>
            <div class="legend-dot-item"><div class="ldot" style="background:#facc15"></div>Money</div>
        </div>
    </div>
    <!-- ══ END MAP ══ -->

    <!-- Search -->
    <div class="search-row">
        <input class="search-input" type="text" id="searchInput" placeholder="Search items, descriptions, or locations…" oninput="filterTable()" />
    </div>

    <!-- Table -->
    <div class="table-wrap">
        <table id="itemTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Item Name</th>
                    <th>Description</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
                $result = mysqli_query($conn, "SELECT * FROM items ORDER BY id DESC");
                while ($row = mysqli_fetch_assoc($result)):
                    $padded_id = str_pad($row['id'], 3, '0', STR_PAD_LEFT);
                    $status = $row['status'] ?? 'found';
            ?>
                <tr>
                    <td class="id-cell">#<?= $padded_id ?></td>
                    <td>
                        <?php if (!empty($row['image'])): ?>
                            <img class="item-img" src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['item_name']) ?>">
                        <?php else: ?>
                            <div class="img-placeholder">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td><div class="item-name"><?= htmlspecialchars($row['item_name']) ?></div></td>
                    <td><div class="desc-cell"><?= htmlspecialchars($row['description']) ?></div></td>
                    <td>
                        <div class="location-badge">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <?= htmlspecialchars($row['location_found']) ?>
                        </div>
                    </td>
                    <td>
                        <?php if ($status === 'claimed'): ?>
                            <span class="status-badge claimed">✓ Claimed</span>
                        <?php else: ?>
                            <span class="status-badge found">📦 Found</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="actions">
                            <a class="btn-edit" href="edit.php?id=<?= $row['id'] ?>">Edit</a>
                            <a class="btn-del" href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this item?')">Delete</a>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if ($total === 0): ?>
                <tr><td colspan="7" class="empty-state">No items found. Add your first item above.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── Category Modals ── -->
<?php
$categories = [
    'person' => ['label' => 'Missing Persons', 'icon' => '🧍', 'sub' => 'People reported missing',   'items' => $items_person],
    'things' => ['label' => 'Missing Things',  'icon' => '🎒', 'sub' => 'Lost objects & belongings', 'items' => $items_things],
    'pet'    => ['label' => 'Missing Pets',    'icon' => '🐾', 'sub' => 'Lost animals & pets',       'items' => $items_pet],
    'money'  => ['label' => 'Lost Money',      'icon' => '💰', 'sub' => 'Cash, cards & valuables',   'items' => $items_money],
];
foreach ($categories as $key => $cat):
    $rows = [];
    while ($r = mysqli_fetch_assoc($cat['items'])) $rows[] = $r;
?>
<div class="modal-overlay" id="modal-<?= $key ?>" onclick="closeModalOnBg(event,'<?= $key ?>')">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-header-left">
                <div class="modal-header-icon"><?= $cat['icon'] ?></div>
                <div>
                    <div class="modal-title"><?= $cat['label'] ?></div>
                    <div class="modal-subtitle"><?= count($rows) ?> record<?= count($rows)!==1?'s':'' ?> · <?= $cat['sub'] ?></div>
                </div>
            </div>
            <button class="modal-close" onclick="closeModal('<?= $key ?>')" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <?php if (empty($rows)): ?>
                <div class="modal-empty"><span><?= $cat['icon'] ?></span>No <?= strtolower($cat['label']) ?> recorded yet.</div>
            <?php else: foreach ($rows as $r):
                $status = $r['status'] ?? 'found';
                $padded = str_pad($r['id'], 3, '0', STR_PAD_LEFT);
            ?>
            <div class="modal-item">
                <?php if (!empty($r['image'])): ?>
                    <img class="modal-item-img" src="<?= htmlspecialchars($r['image']) ?>" alt="<?= htmlspecialchars($r['item_name']) ?>">
                <?php else: ?>
                    <div class="modal-item-img-placeholder"><?= $cat['icon'] ?></div>
                <?php endif; ?>
                <div class="modal-item-info">
                    <div class="modal-item-name">#<?= $padded ?> — <?= htmlspecialchars($r['item_name']) ?></div>
                    <div class="modal-item-desc"><?= htmlspecialchars($r['description']) ?></div>
                    <div class="modal-item-loc">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <?= htmlspecialchars($r['location_found']) ?>
                    </div>
                </div>
                <span class="modal-status <?= $status ?>"><?= $status==='claimed'?'✓ Claimed':'📦 Found' ?></span>
            </div>
            <?php endforeach; endif; ?>
        </div>
        <div class="modal-footer">
            <a class="btn-add-modal" href="add.php?category=<?= $key ?>">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add <?= ucfirst($key) ?>
            </a>
        </div>
    </div>
</div>
<?php endforeach; ?>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
/* ════════════════════════════
   MAP — Inabanga, Bohol
════════════════════════════ */
const map = L.map('map', { center: [10.0310, 124.0667], zoom: 14 });

L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> &copy; <a href="https://carto.com/">CARTO</a>',
    subdomains: 'abcd', maxZoom: 19
}).addTo(map);

const mapItems = <?= json_encode(array_map(function($i){
    return [
        'id'      => $i['id'],
        'name'    => $i['item_name'],
        'desc'    => $i['description'],
        'location'=> $i['location_found'],
        'category'=> $i['category'],
        'status'  => $i['status'] ?? 'found',
        'color'   => $i['color'],
        'icon'    => $i['caticon'],
        'lat'     => $i['lat'],
        'lng'     => $i['lng'],
    ];
}, $map_items)) ?>;

const pinMarkers = {};

function makePin(color, emoji) {
    return L.divIcon({
        className: '',
        html: `<div style="width:32px;height:32px;border-radius:50%;background:${color}22;border:2px solid ${color};display:flex;align-items:center;justify-content:center;font-size:14px;box-shadow:0 0 0 4px ${color}18;">${emoji}</div>`,
        iconSize: [32,32], iconAnchor: [16,16], popupAnchor: [0,-18]
    });
}

mapItems.forEach(item => {
    const padded = String(item.id).padStart(3,'0');
    const popup = `
        <div class="popup-inner">
            <div class="popup-cat" style="color:${item.color}">${item.category.toUpperCase()}</div>
            <div class="popup-name">${item.name}</div>
            <div class="popup-desc">${item.desc||'—'}</div>
            <div class="popup-loc">📍 ${item.location}</div>
            <div><span class="popup-status ${item.status}">${item.status==='claimed'?'✓ Claimed':'📦 Found'}</span></div>
        </div>`;
    const marker = L.marker([item.lat, item.lng], { icon: makePin(item.color, item.icon), title: item.name })
        .addTo(map).bindPopup(popup, { maxWidth: 230 });
    pinMarkers[item.id] = { marker, category: item.category };
});

function mapFilter(cat) {
    document.querySelectorAll('.map-filter-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('mfbtn-' + (cat==='all'?'all':cat)).classList.add('active');
    Object.entries(pinMarkers).forEach(([id, m]) => {
        if (cat==='all' || m.category===cat) map.addLayer(m.marker);
        else map.removeLayer(m.marker);
    });
    document.querySelectorAll('.map-sc').forEach(c => {
        c.classList.toggle('sc-hidden', cat!=='all' && c.dataset.cat!==cat);
    });
}

function focusPin(id) {
    const m = pinMarkers[id];
    if (!m) return;
    map.setView(m.marker.getLatLng(), 16, { animate: true });
    m.marker.openPopup();
    document.querySelectorAll('.map-sc').forEach(c => c.style.outline='');
    const card = document.querySelector(`.map-sc[data-id="${id}"]`);
    if (card) { card.style.outline='1px solid #22c55e44'; card.scrollIntoView({ behavior:'smooth', block:'nearest' }); }
}

/* ════════════════════════════
   Category Modals
════════════════════════════ */
function openModal(key) {
    document.getElementById('modal-'+key).classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeModal(key) {
    document.getElementById('modal-'+key).classList.remove('active');
    document.body.style.overflow = '';
}
function closeModalOnBg(e, key) {
    if (e.target===e.currentTarget) closeModal(key);
}
document.addEventListener('keydown', function(e) {
    if (e.key==='Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(m => m.classList.remove('active'));
        document.body.style.overflow = '';
    }
});

/* ════════════════════════════
   Table search
════════════════════════════ */
function filterTable() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('#itemTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}
</script>

</body>
</html>