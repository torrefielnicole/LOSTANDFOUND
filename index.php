<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost and Found System</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
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

        /* ── Category Cards ── */
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
        .cat-card:hover {
            border-color: #3a4050;
            background: #1a2030;
            transform: translateY(-2px);
        }
        .cat-card .cat-icon {
            font-size: 30px;
            margin-bottom: 10px;
            display: block;
            line-height: 1;
        }
        .cat-card .cat-label {
            font-size: 13px;
            font-weight: 600;
            color: #e8e8e8;
            margin-bottom: 4px;
        }
        .cat-card .cat-count {
            font-family: 'Space Mono', monospace;
            font-size: 10px;
            color: #6b7280;
        }
        .cat-card .cat-accent {
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            border-radius: 14px 14px 0 0;
        }
        .cat-card.person  .cat-accent { background: #60a5fa; }
        .cat-card.things  .cat-accent { background: #22c55e; }
        .cat-card.pet     .cat-accent { background: #fb923c; }
        .cat-card.money   .cat-accent { background: #facc15; }

        .cat-card.person:hover  { border-color: #60a5fa44; }
        .cat-card.things:hover  { border-color: #22c55e44; }
        .cat-card.pet:hover     { border-color: #fb923c44; }
        .cat-card.money:hover   { border-color: #facc1544; }

        /* ── Modal Overlay ── */
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

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 22px 16px;
            border-bottom: 0.5px solid #2a2f3d;
            flex-shrink: 0;
        }
        .modal-header-left { display: flex; align-items: center; gap: 12px; }
        .modal-header-icon {
            font-size: 22px;
            width: 40px; height: 40px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            background: #1e2330;
        }
        .modal-title {
            font-size: 15px;
            font-weight: 600;
            color: #f0f0f0;
        }
        .modal-subtitle {
            font-size: 11px;
            color: #6b7280;
            font-family: 'Space Mono', monospace;
            margin-top: 2px;
        }
        .modal-close {
            background: #1e2330;
            border: 0.5px solid #2a2f3d;
            border-radius: 8px;
            color: #9ca3af;
            width: 32px; height: 32px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            font-size: 18px;
            transition: all 0.15s;
            line-height: 1;
        }
        .modal-close:hover { background: #2a2f3d; color: #e8e8e8; }

        .modal-body {
            overflow-y: auto;
            flex: 1;
            padding: 8px 0;
        }

        /* Item rows inside modal */
        .modal-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 22px;
            border-bottom: 0.5px solid #1e2330;
            transition: background 0.12s;
        }
        .modal-item:last-child { border-bottom: none; }
        .modal-item:hover { background: #1a2030; }

        .modal-item-img {
            width: 50px; height: 50px;
            border-radius: 10px;
            object-fit: cover;
            border: 0.5px solid #2a2f3d;
            background: #1e2330;
            flex-shrink: 0;
        }
        .modal-item-img-placeholder {
            width: 50px; height: 50px;
            border-radius: 10px;
            background: #1e2330;
            border: 0.5px solid #2a2f3d;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            color: #6b7280;
            font-size: 20px;
        }
        .modal-item-info { flex: 1; min-width: 0; }
        .modal-item-name {
            font-size: 13px;
            font-weight: 500;
            color: #e8e8e8;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .modal-item-desc {
            font-size: 11px;
            color: #9ca3af;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .modal-item-loc {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 10px;
            color: #6b7280;
            margin-top: 4px;
            font-family: 'Space Mono', monospace;
        }
        .modal-status {
            font-size: 10px;
            font-weight: 600;
            border-radius: 20px;
            padding: 3px 9px;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .modal-status.found   { background: #1a2e1a; color: #22c55e; border: 0.5px solid #22c55e33; }
        .modal-status.claimed { background: #1a1a2e; color: #818cf8; border: 0.5px solid #818cf833; }

        .modal-empty {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
            font-size: 13px;
        }
        .modal-empty span { font-size: 28px; display: block; margin-bottom: 8px; }

        .modal-footer {
            padding: 14px 22px;
            border-top: 0.5px solid #2a2f3d;
            display: flex;
            justify-content: flex-end;
            flex-shrink: 0;
        }
        .btn-add-modal {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #22c55e;
            color: #0a1a10;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 600;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-add-modal:hover { background: #16a34a; }

        /* Search bar */
        .search-row { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .search-input {
            flex: 1;
            min-width: 200px;
            background: #171c26;
            border: 0.5px solid #2a2f3d;
            border-radius: 10px;
            padding: 10px 16px;
            color: #e8e8e8;
            font-size: 13px;
            font-family: 'DM Sans', sans-serif;
            outline: none;
            transition: border-color 0.2s;
        }
        .search-input::placeholder { color: #444; }
        .search-input:focus { border-color: #22c55e55; }

        /* Table */
        .table-wrap {
            background: #171c26;
            border: 0.5px solid #2a2f3d;
            border-radius: 16px;
            overflow: hidden;
            overflow-x: auto;
        }
        table { width: 100%; border-collapse: collapse; min-width: 700px; }
        thead tr { background: #1e2330; border-bottom: 0.5px solid #2a2f3d; }
        th {
            font-size: 10px;
            font-weight: 600;
            font-family: 'Space Mono', monospace;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #6b7280;
            padding: 14px 16px;
            text-align: left;
        }
        th:last-child { text-align: center; }

        tbody tr { border-bottom: 0.5px solid #1e2330; transition: background 0.15s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: #1a2030; }
        td { padding: 14px 16px; font-size: 13px; color: #c8c8c8; vertical-align: middle; }

        .id-cell { font-family: 'Space Mono', monospace; font-size: 11px; color: #5a7a6a; }

        .item-img {
            width: 60px; height: 60px;
            border-radius: 10px;
            object-fit: cover;
            border: 0.5px solid #2a2f3d;
            background: #1e2330;
            display: block;
        }
        .img-placeholder {
            width: 60px; height: 60px;
            border-radius: 10px;
            background: #1e2330;
            border: 0.5px solid #2a2f3d;
            display: flex; align-items: center; justify-content: center;
        }

        .item-name { font-weight: 500; color: #e8e8e8; }
        .desc-cell { color: #9ca3af; font-size: 12px; line-height: 1.5; }

        .location-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #1a2030;
            border: 0.5px solid #2a2f3d;
            border-radius: 20px;
            padding: 4px 10px;
            font-size: 11px;
            color: #9ca3af;
        }

        .actions { display: flex; align-items: center; justify-content: center; gap: 6px; }
        .btn-edit, .btn-del {
            border: none;
            border-radius: 7px;
            padding: 6px 12px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            text-decoration: none;
            letter-spacing: 0.02em;
            transition: all 0.15s;
            display: inline-block;
        }
        .btn-edit { background: #1e2c3a; color: #60a5fa; }
        .btn-edit:hover { background: #1e3a50; color: #93c5fd; }
        .btn-del { background: #2a1a1a; color: #f87171; }
        .btn-del:hover { background: #3a1a1a; color: #fca5a5; }

        .empty-state { text-align: center; padding: 48px 16px; color: #6b7280; font-size: 14px; }

        .status-badge {
            display: inline-flex; align-items: center; gap: 4px;
            border-radius: 20px; padding: 4px 10px; font-size: 11px; font-weight: 600;
            white-space: nowrap;
        }
        .status-badge.found   { background: #1a2e1a; color: #22c55e; border: 0.5px solid #22c55e33; }
        .status-badge.claimed { background: #1a1a2e; color: #818cf8; border: 0.5px solid #818cf833; }

        @media (max-width: 700px) {
            .stats-row { grid-template-columns: 1fr 1fr; }
            .category-grid { grid-template-columns: repeat(2, 1fr); }
            .page { padding: 20px 16px; }
        }
        @media (max-width: 400px) {
            .category-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>

<?php
    $total   = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM items"));
    $claimed = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM items WHERE status='claimed'"));
    $locs    = mysqli_num_rows(mysqli_query($conn, "SELECT DISTINCT location_found FROM items"));

    // Fetch counts per category
    $cnt_person = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM items WHERE category='person'"));
    $cnt_things = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM items WHERE category='things'"));
    $cnt_pet    = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM items WHERE category='pet'"));
    $cnt_money  = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM items WHERE category='money'"));

    // Fetch items per category for modals
    $items_person = mysqli_query($conn, "SELECT * FROM items WHERE category='person' ORDER BY id DESC");
    $items_things = mysqli_query($conn, "SELECT * FROM items WHERE category='things' ORDER BY id DESC");
    $items_pet    = mysqli_query($conn, "SELECT * FROM items WHERE category='pet'    ORDER BY id DESC");
    $items_money  = mysqli_query($conn, "SELECT * FROM items WHERE category='money'  ORDER BY id DESC");
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

<!-- ── Modals ── -->
<?php
$categories = [
    'person' => ['label' => 'Missing Persons', 'icon' => '🧍', 'sub' => 'People reported missing', 'items' => $items_person],
    'things' => ['label' => 'Missing Things',  'icon' => '🎒', 'sub' => 'Lost objects & belongings', 'items' => $items_things],
    'pet'    => ['label' => 'Missing Pets',    'icon' => '🐾', 'sub' => 'Lost animals & pets',      'items' => $items_pet],
    'money'  => ['label' => 'Lost Money',      'icon' => '💰', 'sub' => 'Cash, cards & valuables',  'items' => $items_money],
];

foreach ($categories as $key => $cat):
    $rows = [];
    while ($r = mysqli_fetch_assoc($cat['items'])) $rows[] = $r;
?>
<div class="modal-overlay" id="modal-<?= $key ?>" onclick="closeModalOnBg(event, '<?= $key ?>')">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-header-left">
                <div class="modal-header-icon"><?= $cat['icon'] ?></div>
                <div>
                    <div class="modal-title"><?= $cat['label'] ?></div>
                    <div class="modal-subtitle"><?= count($rows) ?> record<?= count($rows) !== 1 ? 's' : '' ?> · <?= $cat['sub'] ?></div>
                </div>
            </div>
            <button class="modal-close" onclick="closeModal('<?= $key ?>')" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <?php if (empty($rows)): ?>
                <div class="modal-empty">
                    <span><?= $cat['icon'] ?></span>
                    No <?= strtolower($cat['label']) ?> recorded yet.
                </div>
            <?php else: ?>
                <?php foreach ($rows as $r):
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
                    <span class="modal-status <?= $status ?>">
                        <?= $status === 'claimed' ? '✓ Claimed' : '📦 Found' ?>
                    </span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
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

<script>
function openModal(key) {
    document.getElementById('modal-' + key).classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeModal(key) {
    document.getElementById('modal-' + key).classList.remove('active');
    document.body.style.overflow = '';
}
function closeModalOnBg(e, key) {
    if (e.target === e.currentTarget) closeModal(key);
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(function(m) {
            m.classList.remove('active');
        });
        document.body.style.overflow = '';
    }
});
function filterTable() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#itemTable tbody tr');
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}
</script>

</body>
</html>