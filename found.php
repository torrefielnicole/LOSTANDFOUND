<?php include 'db.php'; ?>
<?php
/* ── Handle Add Found Item ── */
$success_msg = '';
$error_msg   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_found') {
    $item_name     = trim(mysqli_real_escape_string($conn, $_POST['item_name']     ?? ''));
    $description   = trim(mysqli_real_escape_string($conn, $_POST['description']   ?? ''));
    $location      = trim(mysqli_real_escape_string($conn, $_POST['location_found'] ?? ''));
    $category      = trim(mysqli_real_escape_string($conn, $_POST['category']      ?? 'things'));
    $found_by      = trim(mysqli_real_escape_string($conn, $_POST['found_by']      ?? ''));
    $contact       = trim(mysqli_real_escape_string($conn, $_POST['contact']       ?? ''));
    $date_found    = trim(mysqli_real_escape_string($conn, $_POST['date_found']    ?? date('Y-m-d')));
    $record_type   = 'found';
    $status        = 'found';

    $image = '';
    if (!empty($_FILES['image']['name'])) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $ext      = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('found_') . '.' . $ext;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
            $image = $upload_dir . $filename;
        }
    }

    if ($item_name && $location) {
        $sql = "INSERT INTO items (item_name, description, location_found, category, status, image, found_by, contact, date_found, record_type)
                VALUES ('$item_name','$description','$location','$category','$status','$image','$found_by','$contact','$date_found','$record_type')";
        if (mysqli_query($conn, $sql)) {
            $success_msg = "Found item reported successfully!";
        } else {
            /* Fallback: try without extra columns in case they don't exist yet */
            $sql2 = "INSERT INTO items (item_name, description, location_found, category, status, image)
                     VALUES ('$item_name','$description','$location','$category','$status','$image')";
            if (mysqli_query($conn, $sql2)) $success_msg = "Found item reported successfully!";
            else $error_msg = "DB Error: " . mysqli_error($conn);
        }
    } else {
        $error_msg = "Item name and location are required.";
    }
}

/* ── Handle Delete ── */
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    mysqli_query($conn, "DELETE FROM items WHERE id=" . intval($_GET['delete']) . " AND record_type='found'");
    header("Location: found.php");
    exit;
}

/* ── Handle Status Toggle ── */
if (isset($_GET['claim']) && is_numeric($_GET['claim'])) {
    mysqli_query($conn, "UPDATE items SET status='claimed' WHERE id=" . intval($_GET['claim']));
    header("Location: found.php");
    exit;
}

/* ── Fetch data ── */
$total_found   = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM items WHERE record_type='found'"));
$total_claimed = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM items WHERE record_type='found' AND status='claimed'"));
$total_pending = $total_found - $total_claimed;

$cnt_person = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM items WHERE record_type='found' AND category='person'"));
$cnt_things = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM items WHERE record_type='found' AND category='things'"));
$cnt_pet    = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM items WHERE record_type='found' AND category='pet'"));
$cnt_money  = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM items WHERE record_type='found' AND category='money'"));

$records = mysqli_query($conn, "SELECT * FROM items WHERE record_type='found' ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Found Items – Lost & Found</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DM Sans', sans-serif; background: #0f1117; color: #e8e8e8; min-height: 100vh; }
        .page { padding: 32px 28px; max-width: 1200px; margin: 0 auto; }

        /* Nav */
        .nav-bar {
            display: flex; align-items: center; gap: 8px;
            margin-bottom: 28px; flex-wrap: wrap;
        }
        .nav-link {
            display: inline-flex; align-items: center; gap: 6px;
            background: #171c26; border: 0.5px solid #2a2f3d; border-radius: 9px;
            padding: 8px 14px; font-size: 12px; font-weight: 500; color: #9ca3af;
            text-decoration: none; transition: all 0.15s;
        }
        .nav-link:hover { background: #1e2330; color: #e8e8e8; }
        .nav-link.active-found  { background: #1a3a20; border-color: #22c55e55; color: #22c55e; }
        .nav-link.active-lost   { background: #3a1a1a; border-color: #f8717155; color: #f87171; }

        /* Header */
        .header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 28px; flex-wrap: wrap; gap: 16px; }
        .title-eyebrow { font-family: 'Space Mono', monospace; font-size: 10px; letter-spacing: 0.18em; color: #22c55e; text-transform: uppercase; margin-bottom: 6px; }
        .title-main { font-size: 26px; font-weight: 600; color: #f0f0f0; line-height: 1.1; }
        .title-sub  { font-size: 13px; color: #6b7280; margin-top: 4px; font-weight: 300; }

        .btn-open-form {
            display: inline-flex; align-items: center; gap: 8px;
            background: #22c55e; color: #0a1a10; border: none; border-radius: 10px;
            padding: 11px 20px; font-size: 13px; font-weight: 600;
            font-family: 'DM Sans', sans-serif; cursor: pointer; text-decoration: none;
            transition: all 0.2s; white-space: nowrap;
        }
        .btn-open-form:hover { background: #16a34a; transform: translateY(-1px); box-shadow: 0 4px 16px rgba(34,197,94,0.3); }

        /* Stats */
        .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 28px; }
        .stat-card { background: #171c26; border: 0.5px solid #2a2f3d; border-radius: 12px; padding: 16px 18px; }
        .stat-label { font-size: 11px; color: #6b7280; font-family: 'Space Mono', monospace; text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 6px; }
        .stat-value { font-size: 24px; font-weight: 600; color: #f0f0f0; }
        .stat-badge { display: inline-block; font-size: 10px; padding: 2px 7px; border-radius: 20px; font-weight: 500; margin-top: 4px; }
        .badge-green  { background: #1a2e1a; color: #22c55e; border: 0.5px solid #22c55e33; }
        .badge-purple { background: #1a1a2e; color: #818cf8; border: 0.5px solid #818cf833; }
        .badge-yellow { background: #252208; color: #facc15; border: 0.5px solid #facc1533; }

        /* Category mini pills */
        .cat-pills { display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; }
        .cat-pill {
            display: inline-flex; align-items: center; gap: 5px;
            background: #171c26; border: 0.5px solid #2a2f3d; border-radius: 20px;
            padding: 5px 12px; font-size: 11px; font-weight: 500; color: #9ca3af;
            cursor: pointer; font-family: 'DM Sans', sans-serif; transition: all 0.15s;
        }
        .cat-pill:hover { border-color: #3a4050; color: #e8e8e8; }
        .cat-pill.active { border-color: #22c55e44; background: #1a2e1a; color: #22c55e; }

        /* Alert */
        .alert { padding: 12px 16px; border-radius: 10px; font-size: 13px; margin-bottom: 20px; }
        .alert-success { background: #1a2e1a; color: #22c55e; border: 0.5px solid #22c55e33; }
        .alert-error   { background: #2a1a1a; color: #f87171; border: 0.5px solid #f8717133; }

        /* Modal (Add form) */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.65); z-index: 1000; align-items: center; justify-content: center; padding: 20px; }
        .modal-overlay.active { display: flex; }
        .modal-box {
            background: #171c26; border: 0.5px solid #2a2f3d; border-radius: 18px;
            width: 100%; max-width: 540px; max-height: 90vh;
            display: flex; flex-direction: column; overflow: hidden;
            animation: modalIn 0.18s ease;
        }
        @keyframes modalIn { from { opacity:0; transform:translateY(12px) scale(0.98); } to { opacity:1; transform:none; } }
        .modal-header { display: flex; align-items: center; justify-content: space-between; padding: 18px 22px 16px; border-bottom: 0.5px solid #2a2f3d; flex-shrink: 0; }
        .modal-title { font-size: 15px; font-weight: 600; color: #f0f0f0; }
        .modal-subtitle { font-size: 11px; color: #6b7280; font-family: 'Space Mono', monospace; margin-top: 2px; }
        .modal-close { background: #1e2330; border: 0.5px solid #2a2f3d; border-radius: 8px; color: #9ca3af; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 18px; transition: all 0.15s; line-height: 1; }
        .modal-close:hover { background: #2a2f3d; color: #e8e8e8; }
        .modal-body { overflow-y: auto; flex: 1; padding: 20px 22px; }
        .modal-footer { padding: 14px 22px; border-top: 0.5px solid #2a2f3d; display: flex; justify-content: flex-end; gap: 8px; flex-shrink: 0; }

        /* Form */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group.full { grid-column: 1 / -1; }
        .form-label { font-size: 11px; font-weight: 600; font-family: 'Space Mono', monospace; text-transform: uppercase; letter-spacing: 0.1em; color: #6b7280; }
        .form-input, .form-select, .form-textarea {
            background: #1e2330; border: 0.5px solid #2a2f3d; border-radius: 9px;
            padding: 10px 12px; color: #e8e8e8; font-size: 13px;
            font-family: 'DM Sans', sans-serif; outline: none; transition: border-color 0.2s;
            width: 100%;
        }
        .form-input::placeholder, .form-textarea::placeholder { color: #444; }
        .form-input:focus, .form-select:focus, .form-textarea:focus { border-color: #22c55e55; }
        .form-select option { background: #1e2330; }
        .form-textarea { resize: vertical; min-height: 72px; }

        .btn-cancel { background: #1e2330; border: 0.5px solid #2a2f3d; border-radius: 9px; color: #9ca3af; padding: 9px 18px; font-size: 13px; font-weight: 500; font-family: 'DM Sans', sans-serif; cursor: pointer; transition: all 0.15s; }
        .btn-cancel:hover { background: #2a2f3d; color: #e8e8e8; }
        .btn-submit { background: #22c55e; color: #0a1a10; border: none; border-radius: 9px; padding: 9px 20px; font-size: 13px; font-weight: 600; font-family: 'DM Sans', sans-serif; cursor: pointer; transition: all 0.2s; }
        .btn-submit:hover { background: #16a34a; }

        /* Search */
        .search-row { display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }
        .search-input { flex: 1; min-width: 200px; background: #171c26; border: 0.5px solid #2a2f3d; border-radius: 10px; padding: 10px 16px; color: #e8e8e8; font-size: 13px; font-family: 'DM Sans', sans-serif; outline: none; transition: border-color 0.2s; }
        .search-input::placeholder { color: #444; }
        .search-input:focus { border-color: #22c55e55; }

        /* Table */
        .table-wrap { background: #171c26; border: 0.5px solid #2a2f3d; border-radius: 16px; overflow: hidden; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 750px; }
        thead tr { background: #1e2330; border-bottom: 0.5px solid #2a2f3d; }
        th { font-size: 10px; font-weight: 600; font-family: 'Space Mono', monospace; text-transform: uppercase; letter-spacing: 0.12em; color: #6b7280; padding: 14px 16px; text-align: left; }
        th:last-child { text-align: center; }
        tbody tr { border-bottom: 0.5px solid #1e2330; transition: background 0.15s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: #1a2030; }
        td { padding: 13px 16px; font-size: 13px; color: #c8c8c8; vertical-align: middle; }
        .id-cell { font-family: 'Space Mono', monospace; font-size: 11px; color: #5a7a6a; }
        .item-img { width: 52px; height: 52px; border-radius: 9px; object-fit: cover; border: 0.5px solid #2a2f3d; display: block; }
        .img-placeholder { width: 52px; height: 52px; border-radius: 9px; background: #1e2330; border: 0.5px solid #2a2f3d; display: flex; align-items: center; justify-content: center; }
        .item-name { font-weight: 500; color: #e8e8e8; }
        .item-sub  { font-size: 11px; color: #6b7280; margin-top: 2px; font-family: 'Space Mono', monospace; }
        .desc-cell { color: #9ca3af; font-size: 12px; line-height: 1.5; max-width: 180px; }
        .loc-badge { display: inline-flex; align-items: center; gap: 5px; background: #1a2030; border: 0.5px solid #2a2f3d; border-radius: 20px; padding: 4px 10px; font-size: 11px; color: #9ca3af; }
        .cat-badge { display: inline-flex; align-items: center; gap: 4px; border-radius: 20px; padding: 3px 9px; font-size: 10px; font-weight: 600; white-space: nowrap; }
        .cat-badge.person { background: #18202e; color: #60a5fa; }
        .cat-badge.things { background: #1a2e1a; color: #22c55e; }
        .cat-badge.pet    { background: #2a1c10; color: #fb923c; }
        .cat-badge.money  { background: #252208; color: #facc15; }
        .status-badge { display: inline-flex; align-items: center; gap: 4px; border-radius: 20px; padding: 4px 10px; font-size: 11px; font-weight: 600; white-space: nowrap; }
        .status-badge.found   { background: #1a2e1a; color: #22c55e; border: 0.5px solid #22c55e33; }
        .status-badge.claimed { background: #1a1a2e; color: #818cf8; border: 0.5px solid #818cf833; }
        .actions { display: flex; align-items: center; justify-content: center; gap: 5px; flex-wrap: wrap; }
        .btn-sm { border: none; border-radius: 7px; padding: 5px 11px; font-size: 11px; font-weight: 600; cursor: pointer; font-family: 'DM Sans', sans-serif; text-decoration: none; transition: all 0.15s; display: inline-block; white-space: nowrap; }
        .btn-claim  { background: #1a1a2e; color: #818cf8; }
        .btn-claim:hover  { background: #22224a; color: #a5b4fc; }
        .btn-edit   { background: #1e2c3a; color: #60a5fa; }
        .btn-edit:hover   { background: #1e3a50; color: #93c5fd; }
        .btn-del    { background: #2a1a1a; color: #f87171; }
        .btn-del:hover    { background: #3a1a1a; color: #fca5a5; }
        .empty-state { text-align: center; padding: 48px 16px; color: #6b7280; font-size: 14px; }

        /* Accent top border for found page */
        body::before { content:''; display:block; height:3px; background: linear-gradient(90deg, #22c55e, #16a34a); }

        @media (max-width: 700px) {
            .stats-row { grid-template-columns: 1fr 1fr; }
            .form-grid  { grid-template-columns: 1fr; }
            .page { padding: 20px 16px; }
        }
    </style>
</head>
<body>

<div class="page">

    <!-- Nav -->
    <div class="nav-bar">
        <a class="nav-link" href="index.php">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
            Dashboard
        </a>
        <a class="nav-link active-found" href="found.php">✅ Found Items</a>
        <a class="nav-link" href="lost.php" style="background:#2a1a1a;border-color:#f8717133;color:#f87171;">🔍 Lost Reports</a>
    </div>

    <!-- Header -->
    <div class="header">
        <div>
            <div class="title-eyebrow">✅ Found Registry</div>
            <div class="title-main">Found Items</div>
            <div class="title-sub">Items that have been found and are awaiting their owners</div>
        </div>
        <button class="btn-open-form" onclick="openForm()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Report Found Item
        </button>
    </div>

    <?php if ($success_msg): ?>
        <div class="alert alert-success">✅ <?= htmlspecialchars($success_msg) ?></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-label">Total Found</div>
            <div class="stat-value"><?= $total_found ?></div>
            <div class="stat-badge badge-green">Reported</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Returned</div>
            <div class="stat-value"><?= $total_claimed ?></div>
            <div class="stat-badge badge-purple">Claimed back</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Unclaimed</div>
            <div class="stat-value"><?= $total_pending ?></div>
            <div class="stat-badge badge-yellow">Awaiting owner</div>
        </div>
    </div>

    <!-- Category filter pills -->
    <div class="cat-pills">
        <button class="cat-pill active" id="pill-all"    onclick="pillFilter('all')">All (<?= $total_found ?>)</button>
        <button class="cat-pill" id="pill-person" onclick="pillFilter('person')">🧍 Person (<?= $cnt_person ?>)</button>
        <button class="cat-pill" id="pill-things" onclick="pillFilter('things')">🎒 Things (<?= $cnt_things ?>)</button>
        <button class="cat-pill" id="pill-pet"    onclick="pillFilter('pet')">🐾 Pet (<?= $cnt_pet ?>)</button>
        <button class="cat-pill" id="pill-money"  onclick="pillFilter('money')">💰 Money (<?= $cnt_money ?>)</button>
    </div>

    <!-- Search -->
    <div class="search-row">
        <input class="search-input" type="text" id="searchInput" placeholder="Search found items, location, finder…" oninput="searchTable()"/>
    </div>

    <!-- Table -->
    <div class="table-wrap">
        <table id="foundTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Item</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Location Found</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (mysqli_num_rows($records) === 0): ?>
                <tr><td colspan="8" class="empty-state">No found items recorded yet. Click "Report Found Item" to add one.</td></tr>
            <?php else:
                mysqli_data_seek($records, 0);
                while ($row = mysqli_fetch_assoc($records)):
                    $padded = str_pad($row['id'], 3, '0', STR_PAD_LEFT);
                    $status = $row['status'] ?? 'found';
                    $cat    = $row['category'] ?? 'things';
                    $cat_icons = ['person'=>'🧍','things'=>'🎒','pet'=>'🐾','money'=>'💰'];
            ?>
            <tr data-cat="<?= htmlspecialchars($cat) ?>">
                <td class="id-cell">#<?= $padded ?></td>
                <td>
                    <?php if (!empty($row['image'])): ?>
                        <img class="item-img" src="<?= htmlspecialchars($row['image']) ?>" alt="">
                    <?php else: ?>
                        <div class="img-placeholder">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        </div>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="item-name"><?= htmlspecialchars($row['item_name']) ?></div>
                    <?php if (!empty($row['found_by'])): ?>
                        <div class="item-sub">by <?= htmlspecialchars($row['found_by']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($row['date_found'])): ?>
                        <div class="item-sub"><?= htmlspecialchars($row['date_found']) ?></div>
                    <?php endif; ?>
                </td>
                <td><div class="desc-cell"><?= htmlspecialchars($row['description']) ?></div></td>
                <td><span class="cat-badge <?= $cat ?>"><?= ($cat_icons[$cat] ?? '📦') . ' ' . ucfirst($cat) ?></span></td>
                <td>
                    <div class="loc-badge">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <?= htmlspecialchars($row['location_found']) ?>
                    </div>
                </td>
                <td>
                    <?php if ($status === 'claimed'): ?>
                        <span class="status-badge claimed">✓ Returned</span>
                    <?php else: ?>
                        <span class="status-badge found">📦 Unclaimed</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="actions">
                        <?php if ($status !== 'claimed'): ?>
                            <a class="btn-sm btn-claim" href="found.php?claim=<?= $row['id'] ?>" onclick="return confirm('Mark as returned to owner?')">✓ Return</a>
                        <?php endif; ?>
                        <a class="btn-sm btn-edit" href="edit.php?id=<?= $row['id'] ?>">Edit</a>
                        <a class="btn-sm btn-del"  href="found.php?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this record?')">Delete</a>
                    </div>
                </td>
            </tr>
            <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Found Item Modal -->
<div class="modal-overlay" id="addModal" onclick="closeFormOnBg(event)">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <div class="modal-title">✅ Report Found Item</div>
                <div class="modal-subtitle">Fill in the details of the item you found</div>
            </div>
            <button class="modal-close" onclick="closeForm()">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_found">
                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label">Item Name *</label>
                        <input class="form-input" type="text" name="item_name" placeholder="e.g. Brown leather wallet" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Description</label>
                        <textarea class="form-textarea" name="description" placeholder="Color, brand, contents, notable marks…"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="category">
                            <option value="things">🎒 Things</option>
                            <option value="person">🧍 Person</option>
                            <option value="pet">🐾 Pet</option>
                            <option value="money">💰 Money</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date Found</label>
                        <input class="form-input" type="date" name="date_found" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Location Found *</label>
                        <input class="form-input" type="text" name="location_found" placeholder="e.g. Public Market, Inabanga" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Found By (name)</label>
                        <input class="form-input" type="text" name="found_by" placeholder="Your name">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact</label>
                        <input class="form-input" type="text" name="contact" placeholder="Phone or email">
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Photo (optional)</label>
                        <input class="form-input" type="file" name="image" accept="image/*" style="padding:7px 12px;">
                    </div>
                </div>
                <div style="margin-top:20px; display:flex; justify-content:flex-end; gap:8px;">
                    <button type="button" class="btn-cancel" onclick="closeForm()">Cancel</button>
                    <button type="submit" class="btn-submit">✅ Submit Found Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openForm()  { document.getElementById('addModal').classList.add('active'); document.body.style.overflow='hidden'; }
function closeForm() { document.getElementById('addModal').classList.remove('active'); document.body.style.overflow=''; }
function closeFormOnBg(e) { if (e.target===e.currentTarget) closeForm(); }
document.addEventListener('keydown', e => { if (e.key==='Escape') closeForm(); });

<?php if ($success_msg || $error_msg): ?>
/* Auto-open form again on error so user sees message */
<?php if ($error_msg): ?> window.addEventListener('load', openForm); <?php endif; ?>
<?php endif; ?>

let currentCat = 'all';

function pillFilter(cat) {
    currentCat = cat;
    document.querySelectorAll('.cat-pill').forEach(p => p.classList.remove('active'));
    document.getElementById('pill-' + (cat==='all'?'all':cat)).classList.add('active');
    applyFilters();
}

function searchTable() { applyFilters(); }

function applyFilters() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('#foundTable tbody tr').forEach(row => {
        const catMatch = currentCat==='all' || row.dataset.cat===currentCat;
        const textMatch = row.textContent.toLowerCase().includes(q);
        row.style.display = (catMatch && textMatch) ? '' : 'none';
    });
}
</script>
</body>
</html>