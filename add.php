<?php
include 'db.php';

if(isset($_POST['submit'])){
    $name     = mysqli_real_escape_string($conn, $_POST['item_name']);
    $desc     = mysqli_real_escape_string($conn, $_POST['description']);
    $location = mysqli_real_escape_string($conn, $_POST['location_found']);
    $status   = in_array($_POST['status'], ['found','claimed']) ? $_POST['status'] : 'found';
    $category = in_array($_POST['category'], ['person','things','pet','money']) ? $_POST['category'] : 'things';

    $image_data = '';
    if(!empty($_FILES['image']['tmp_name'])){
        $raw        = file_get_contents($_FILES['image']['tmp_name']);
        $mime       = mime_content_type($_FILES['image']['tmp_name']);
        $base64     = base64_encode($raw);
        $image_data = "data:{$mime};base64,{$base64}";
    }

    $image_data = mysqli_real_escape_string($conn, $image_data);

    mysqli_query($conn, "INSERT INTO items(item_name, description, location_found, image, status, category)
        VALUES('$name','$desc','$location','$image_data','$status','$category')");

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Item – Lost & Found</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Space+Mono:wght@700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: #0f1117; color: #e8e8e8;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center; padding: 24px;
        }
        .modal-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.65); backdrop-filter: blur(4px);
            display: flex; align-items: center; justify-content: center;
            padding: 20px; z-index: 100;
        }
        .modal {
            background: #171c26; border: 0.5px solid #2a2f3d;
            border-radius: 20px; width: 100%; max-width: 480px;
            padding: 32px; position: relative;
            box-shadow: 0 24px 60px rgba(0,0,0,0.5);
            animation: popIn 0.22s cubic-bezier(0.34,1.56,0.64,1);
            max-height: 90vh; overflow-y: auto;
        }
        @keyframes popIn {
            from { opacity:0; transform: scale(0.94) translateY(12px); }
            to   { opacity:1; transform: scale(1) translateY(0); }
        }
        .modal-close {
            position: absolute; top: 18px; right: 18px;
            background: #1e2330; border: 0.5px solid #2a2f3d;
            border-radius: 8px; width: 32px; height: 32px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; color: #6b7280; transition: all 0.15s;
            font-size: 16px; text-decoration: none;
        }
        .modal-close:hover { background: #2a1a1a; color: #f87171; }
        .modal-eyebrow {
            font-family: 'Space Mono', monospace; font-size: 10px;
            letter-spacing: 0.16em; color: #5a7a6a;
            text-transform: uppercase; margin-bottom: 6px;
        }
        .modal-title { font-size: 20px; font-weight: 600; color: #f0f0f0; margin-bottom: 24px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group.full { grid-column: 1 / -1; }
        label {
            font-size: 11px; font-weight: 600; color: #9ca3af;
            text-transform: uppercase; letter-spacing: 0.1em;
            font-family: 'Space Mono', monospace;
        }
        input[type=text], textarea {
            background: #1e2330; border: 0.5px solid #2a2f3d;
            border-radius: 10px; padding: 11px 14px;
            color: #e8e8e8; font-size: 13px; font-family: 'DM Sans', sans-serif;
            outline: none; transition: border-color 0.2s, background 0.2s;
            width: 100%; resize: none;
        }
        input[type=text]:focus, textarea:focus { border-color: #22c55e66; background: #1a2030; }
        input[type=text]::placeholder, textarea::placeholder { color: #3a4050; }
        textarea { height: 90px; }
        select {
            background: #1e2330; border: 0.5px solid #2a2f3d;
            border-radius: 10px; padding: 11px 14px;
            color: #e8e8e8; font-size: 13px; font-family: 'DM Sans', sans-serif;
            outline: none; transition: border-color 0.2s; width: 100%;
            cursor: pointer; appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 14px center;
        }
        select:focus { border-color: #22c55e66; background-color: #1a2030; }

        /* Category Pills */
        .category-pills {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
        }
        .cat-pill {
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            gap: 4px; padding: 12px 8px;
            background: #1e2330; border: 0.5px solid #2a2f3d;
            border-radius: 12px; cursor: pointer;
            transition: all 0.15s; position: relative;
        }
        .cat-pill input[type=radio] {
            position: absolute; opacity: 0; width: 0; height: 0;
        }
        .cat-pill .pill-icon { font-size: 22px; line-height: 1; }
        .cat-pill .pill-label {
            font-size: 11px; font-weight: 600; color: #9ca3af;
            font-family: 'DM Sans', sans-serif; text-transform: none;
            letter-spacing: 0;
        }
        .cat-pill:hover { border-color: #3a4050; background: #1a2030; }
        .cat-pill.selected-person  { border-color: #60a5fa; background: #1a2030; }
        .cat-pill.selected-things  { border-color: #22c55e; background: #1a2a1a; }
        .cat-pill.selected-pet     { border-color: #fb923c; background: #1e1a12; }
        .cat-pill.selected-money   { border-color: #facc15; background: #1e1c10; }
        .cat-pill.selected-person .pill-label { color: #60a5fa; }
        .cat-pill.selected-things .pill-label { color: #22c55e; }
        .cat-pill.selected-pet    .pill-label { color: #fb923c; }
        .cat-pill.selected-money  .pill-label { color: #facc15; }

        .upload-zone {
            border: 1px dashed #2a2f3d; border-radius: 12px;
            padding: 20px 16px; text-align: center;
            cursor: pointer; transition: all 0.2s; position: relative;
            background: #1e2330;
        }
        .upload-zone:hover { border-color: #22c55e55; background: #1a2a1a; }
        .upload-zone input[type=file] {
            position: absolute; inset: 0; opacity: 0;
            cursor: pointer; width: 100%; height: 100%;
        }
        .upload-icon { font-size: 26px; margin-bottom: 6px; opacity: 0.5; }
        .upload-label { font-size: 13px; color: #6b7280; }
        .upload-label span { color: #22c55e; font-weight: 500; }
        .upload-hint { font-size: 11px; color: #3a4050; margin-top: 4px; }
        .preview-wrap { display: none; margin-top: 14px; position: relative; }
        .preview-wrap img {
            width: 100%; max-height: 180px; object-fit: cover;
            border-radius: 10px; border: 0.5px solid #2a2f3d; display: block;
        }
        .preview-name {
            font-size: 11px; color: #22c55e; margin-top: 6px;
            display: flex; align-items: center; gap: 5px;
        }
        .preview-name::before { content: '✓'; font-weight: 700; }
        .btn-remove-img {
            position: absolute; top: 8px; right: 8px;
            background: rgba(0,0,0,0.6); border: none; border-radius: 6px;
            color: #f87171; font-size: 13px; cursor: pointer;
            padding: 3px 8px; transition: background 0.15s;
        }
        .btn-remove-img:hover { background: rgba(180,30,30,0.7); color: #fff; }
        .modal-footer { display: flex; gap: 10px; margin-top: 24px; }
        .btn-cancel {
            flex: 1; background: #1e2330; border: 0.5px solid #2a2f3d;
            border-radius: 10px; padding: 12px; color: #9ca3af;
            font-size: 13px; font-weight: 500; cursor: pointer;
            font-family: 'DM Sans', sans-serif; transition: all 0.15s;
            text-decoration: none; text-align: center;
        }
        .btn-cancel:hover { color: #e8e8e8; border-color: #3a4050; }
        .btn-save {
            flex: 2; background: #22c55e; border: none;
            border-radius: 10px; padding: 12px; color: #0a1a10;
            font-size: 13px; font-weight: 600; cursor: pointer;
            font-family: 'DM Sans', sans-serif; transition: all 0.2s;
            letter-spacing: 0.01em;
        }
        .btn-save:hover { background: #16a34a; box-shadow: 0 4px 16px rgba(34,197,94,0.3); }
    </style>
</head>
<body>
<div class="modal-overlay">
    <div class="modal">
        <a class="modal-close" href="index.php" title="Cancel">✕</a>
        <div class="modal-eyebrow">📦 New Entry</div>
        <div class="modal-title">Add Lost / Found Item</div>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-grid">

                <div class="form-group full">
                    <label for="item_name">Item Name</label>
                    <input type="text" id="item_name" name="item_name"
                           placeholder="e.g. Blue umbrella, Black wallet…"
                           value="<?= htmlspecialchars($_POST['item_name'] ?? '') ?>" required>
                </div>

                <div class="form-group full">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"
                              placeholder="Color, brand, size, any identifying marks…"
                              required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>

                <div class="form-group full">
                    <label for="location_found">Location Found</label>
                    <input type="text" id="location_found" name="location_found"
                           placeholder="e.g. Room 101, Cafeteria, Main Hall…"
                           value="<?= htmlspecialchars($_POST['location_found'] ?? '') ?>" required>
                </div>

                <div class="form-group full">
                    <label>Category</label>
                    <div class="category-pills" id="catPills">
                        <?php
                        $cats = [
                            'things' => ['🎒', 'Things'],
                            'person' => ['🧍', 'Person'],
                            'pet'    => ['🐾', 'Pet'],
                            'money'  => ['💰', 'Money'],
                        ];
                        $selectedCat = $_POST['category'] ?? 'things';
                        foreach($cats as $val => [$icon, $label]):
                            $checked = $selectedCat === $val ? 'checked' : '';
                            $selClass = $selectedCat === $val ? "selected-{$val}" : '';
                        ?>
                        <label class="cat-pill <?= $selClass ?>" id="pill-<?= $val ?>">
                            <input type="radio" name="category" value="<?= $val ?>" <?= $checked ?> onchange="updatePills()">
                            <span class="pill-icon"><?= $icon ?></span>
                            <span class="pill-label"><?= $label ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group full">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="found"   <?= (($_POST['status'] ?? '') === 'found'   ? 'selected' : '') ?>>📦 Found</option>
                        <option value="claimed" <?= (($_POST['status'] ?? '') === 'claimed' ? 'selected' : '') ?>>✓ Claimed</option>
                    </select>
                </div>

                <div class="form-group full">
                    <label>Upload Image</label>
                    <div class="upload-zone" id="dropZone">
                        <input type="file" name="image" id="imageInput"
                               accept="image/*" onchange="previewImage(this)">
                        <div class="upload-icon">🖼</div>
                        <div class="upload-label"><span>Click to upload</span> or drag & drop</div>
                        <div class="upload-hint">PNG, JPG, WEBP — saved directly, no folder needed</div>
                    </div>
                    <div class="preview-wrap" id="previewWrap">
                        <img id="previewImg" src="" alt="Preview">
                        <button type="button" class="btn-remove-img" onclick="removeImage()">✕</button>
                        <div class="preview-name" id="previewName"></div>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <a class="btn-cancel" href="index.php">Cancel</a>
                <button type="submit" name="submit" class="btn-save">💾 Save Item</button>
            </div>
        </form>
    </div>
</div>

<script>
function updatePills() {
    const radios = document.querySelectorAll('input[name="category"]');
    radios.forEach(r => {
        const pill = document.getElementById('pill-' + r.value);
        pill.className = 'cat-pill' + (r.checked ? ' selected-' + r.value : '');
    });
}
function previewImage(input) {
    if(!input.files || !input.files[0]) return;
    const file = input.files[0];
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('previewImg').src = e.target.result;
        document.getElementById('previewName').textContent = file.name;
        document.getElementById('previewWrap').style.display = 'block';
        document.getElementById('dropZone').style.display = 'none';
    };
    reader.readAsDataURL(file);
}
function removeImage() {
    document.getElementById('imageInput').value = '';
    document.getElementById('previewImg').src = '';
    document.getElementById('previewWrap').style.display = 'none';
    document.getElementById('dropZone').style.display = 'block';
}
const zone = document.getElementById('dropZone');
zone.addEventListener('dragover',  e => { e.preventDefault(); zone.style.borderColor = '#22c55e'; });
zone.addEventListener('dragleave', () => { zone.style.borderColor = ''; });
zone.addEventListener('drop',      () => { zone.style.borderColor = ''; });
</script>
</body>
</html>