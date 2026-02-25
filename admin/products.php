<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

$pageTitle = 'Products';
$db = getDB();
$message = '';

// Handle add/edit/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int) $_POST['id'];
        $db->query("UPDATE products SET is_active = 0 WHERE id = $id");
        $message = 'Product deactivated.';

    } elseif ($action === 'add' || $action === 'edit') {
        $id = (int) ($_POST['id'] ?? 0);
        $name = sanitize($_POST['name'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $price = (float) ($_POST['price'] ?? 0);
        $old_price = (float) ($_POST['old_price'] ?? 0);
        $stock = (int) ($_POST['stock'] ?? 0);
        $category_id = (int) ($_POST['category_id'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        // Handle image upload
        $image = $_POST['existing_image'] ?? '';
        if (!empty($_FILES['image']['name'])) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (in_array($ext, $allowed)) {
                $filename = uniqid('prod_') . '.' . $ext;
                $dest = __DIR__ . '/../uploads/products/' . $filename;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                    $image = $filename;
                }
            }
        }

        if ($action === 'add') {
            $stmt = $db->prepare("INSERT INTO products (name, description, price, old_price, stock, category_id, image, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssddiiis", $name, $description, $price, $old_price, $stock, $category_id, $is_active, $image);
        } else {
            $stmt = $db->prepare("UPDATE products SET name=?, description=?, price=?, old_price=?, stock=?, category_id=?, image=?, is_active=? WHERE id=?");
            $stmt->bind_param("ssddiisii", $name, $description, $price, $old_price, $stock, $category_id, $image, $is_active, $id);
        }
        $stmt->execute();
        $message = $action === 'add' ? 'Product added successfully!' : 'Product updated!';
    }
}

$products = $db->query("
    SELECT p.*, c.name as category_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    ORDER BY p.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

$categories = $db->query("SELECT id, name FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/admin_header.php';
?>

<main class="admin-main">
    <div class="admin-page-header" style="display:flex;justify-content:space-between;align-items:center;">
        <div>
            <h1>Products</h1>
            <p>
                <?php echo count($products); ?> products in catalog
            </p>
        </div>
        <button class="admin-btn admin-btn-primary" onclick="document.getElementById('modalAdd').style.display='flex'">
            <ion-icon name="add-outline"></ion-icon> Add Product
        </button>
    </div>

    <?php if ($message): ?>
        <div class="flash-message flash-success"
            style="position:relative;top:auto;right:auto;margin-bottom:18px;animation:none;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="admin-card">
        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p):
                        $imgSrc = getProductImageUrl($p['image']);
                        ?>
                        <tr>
                            <td><img src="<?php echo $imgSrc; ?>"
                                    style="width:44px;height:44px;object-fit:cover;border-radius:8px;"></td>
                            <td style="font-weight:600;">
                                <?php echo sanitize($p['name']); ?>
                            </td>
                            <td>
                                <?php echo sanitize($p['category_name']); ?>
                            </td>
                            <td>
                                <?php echo formatPrice($p['price']); ?>
                            </td>
                            <td>
                                <span
                                    style="color:<?php echo $p['stock'] <= 5 ? 'hsl(0,70%,50%)' : 'hsl(152,51%,40%)'; ?>;font-weight:600;">
                                    <?php echo (int) $p['stock']; ?>
                                </span>
                            </td>
                            <td>
                                <span
                                    style="background:<?php echo $p['is_active'] ? 'hsl(152,60%,92%)' : 'hsl(0,100%,96%)'; ?>;color:<?php echo $p['is_active'] ? 'hsl(152,51%,30%)' : 'hsl(0,70%,45%)'; ?>;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">
                                    <?php echo $p['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <div style="display:flex;gap:8px;">
                                    <button class="admin-btn admin-btn-secondary" style="padding:6px 12px;font-size:12px;"
                                        onclick='editProduct(<?php echo json_encode($p); ?>)'>Edit</button>
                                    <form method="POST" style="display:inline;"
                                        onsubmit="return confirm('Deactivate this product?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                        <button class="admin-btn admin-btn-danger"
                                            style="padding:6px 12px;font-size:12px;">Remove</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- ADD Modal -->
<div id="modalAdd"
    style="display:none;position:fixed;inset:0;background:hsla(0,0%,0%,0.5);z-index:999;align-items:center;justify-content:center;padding:20px;">
    <div
        style="background:white;border-radius:14px;width:100%;max-width:640px;max-height:90vh;overflow-y:auto;padding:30px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:22px;">
            <h2 style="font-size:18px;font-weight:700;">Add New Product</h2>
            <button onclick="document.getElementById('modalAdd').style.display='none'"
                style="background:none;border:none;font-size:22px;cursor:pointer;color:hsl(220,12%,55%);">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="admin-form">
            <input type="hidden" name="action" value="add">
            <div class="admin-form-grid">
                <div class="form-group full">
                    <label>Product Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Premium Cotton T-Shirt">
                </div>
                <div class="form-group full">
                    <label>Description</label>
                    <textarea name="description" rows="3" placeholder="Product description..."></textarea>
                </div>
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>">
                                <?php echo sanitize($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Product Image</label>
                    <input type="file" name="image" accept="image/*">
                </div>
                <div class="form-group">
                    <label>Price (₹) *</label>
                    <input type="number" name="price" step="0.01" min="0" required placeholder="29.99">
                </div>
                <div class="form-group">
                    <label>Old Price (₹) <small style="color:hsl(220,12%,55%);">(for discount)</small></label>
                    <input type="number" name="old_price" step="0.01" min="0" placeholder="49.99">
                </div>
                <div class="form-group">
                    <label>Stock *</label>
                    <input type="number" name="stock" min="0" required placeholder="100">
                </div>
                <div class="form-group" style="display:flex;align-items:center;gap:10px;padding-top:24px;">
                    <input type="checkbox" name="is_active" id="newActive" checked
                        style="width:18px;height:18px;accent-color:var(--admin-accent);">
                    <label for="newActive" style="margin:0;cursor:pointer;">Active (visible in store)</label>
                </div>
            </div>
            <div style="margin-top:20px;display:flex;gap:10px;">
                <button type="submit" class="admin-btn admin-btn-primary">Add Product</button>
                <button type="button" class="admin-btn admin-btn-secondary"
                    onclick="document.getElementById('modalAdd').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT Modal -->
<div id="modalEdit"
    style="display:none;position:fixed;inset:0;background:hsla(0,0%,0%,0.5);z-index:999;align-items:center;justify-content:center;padding:20px;">
    <div
        style="background:white;border-radius:14px;width:100%;max-width:640px;max-height:90vh;overflow-y:auto;padding:30px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:22px;">
            <h2 style="font-size:18px;font-weight:700;">Edit Product</h2>
            <button onclick="document.getElementById('modalEdit').style.display='none'"
                style="background:none;border:none;font-size:22px;cursor:pointer;color:hsl(220,12%,55%);">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="admin-form" id="editForm">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="editId">
            <input type="hidden" name="existing_image" id="editExistingImage">
            <div class="admin-form-grid">
                <div class="form-group full">
                    <label>Product Name *</label>
                    <input type="text" name="name" id="editName" required>
                </div>
                <div class="form-group full">
                    <label>Description</label>
                    <textarea name="description" id="editDesc" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category_id" id="editCatId" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>">
                                <?php echo sanitize($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>New Image (optional)</label>
                    <input type="file" name="image" accept="image/*">
                </div>
                <div class="form-group">
                    <label>Price (₹) *</label>
                    <input type="number" name="price" id="editPrice" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label>Old Price (₹)</label>
                    <input type="number" name="old_price" id="editOldPrice" step="0.01" min="0">
                </div>
                <div class="form-group">
                    <label>Stock *</label>
                    <input type="number" name="stock" id="editStock" min="0" required>
                </div>
                <div class="form-group" style="display:flex;align-items:center;gap:10px;padding-top:24px;">
                    <input type="checkbox" name="is_active" id="editActive"
                        style="width:18px;height:18px;accent-color:var(--admin-accent);">
                    <label for="editActive" style="margin:0;cursor:pointer;">Active</label>
                </div>
            </div>
            <div style="margin-top:20px;display:flex;gap:10px;">
                <button type="submit" class="admin-btn admin-btn-primary">Save Changes</button>
                <button type="button" class="admin-btn admin-btn-secondary"
                    onclick="document.getElementById('modalEdit').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editProduct(p) {
        document.getElementById('editId').value = p.id;
        document.getElementById('editName').value = p.name;
        document.getElementById('editDesc').value = p.description || '';
        document.getElementById('editPrice').value = p.price;
        document.getElementById('editOldPrice').value = p.old_price || '';
        document.getElementById('editStock').value = p.stock;
        document.getElementById('editCatId').value = p.category_id;
        document.getElementById('editActive').checked = p.is_active == 1;
        document.getElementById('editExistingImage').value = p.image || '';
        document.getElementById('modalEdit').style.display = 'flex';
    }
    window.addEventListener('click', function (e) {
        if (e.target.id === 'modalAdd') document.getElementById('modalAdd').style.display = 'none';
        if (e.target.id === 'modalEdit') document.getElementById('modalEdit').style.display = 'none';
    });
</script>

<?php include __DIR__ . '/admin_footer.php'; ?>