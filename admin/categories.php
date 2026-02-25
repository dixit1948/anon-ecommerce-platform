<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

$pageTitle = 'Categories';
$db = getDB();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    if ($action === 'add') {
        $name = sanitize($_POST['name'] ?? '');
        $stmt = $db->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $message = 'Category added!';
    } elseif ($action === 'delete') {
        $id = (int) $_POST['id'];
        $db->query("DELETE FROM categories WHERE id = $id");
        $message = 'Category deleted!';
    } elseif ($action === 'edit') {
        $id = (int) $_POST['id'];
        $name = sanitize($_POST['name'] ?? '');
        $stmt = $db->prepare("UPDATE categories SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
        $message = 'Category updated!';
    }
}

$categories = $db->query("
    SELECT c.*, COUNT(p.id) as product_count
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
    GROUP BY c.id ORDER BY c.name
")->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/admin_header.php';
?>

<main class="admin-main">
    <div class="admin-page-header" style="display:flex;justify-content:space-between;align-items:center;">
        <div>
            <h1>Categories</h1>
            <p>
                <?php echo count($categories); ?> categories
            </p>
        </div>
        <button class="admin-btn admin-btn-primary"
            onclick="document.getElementById('addCatForm').style.display='block'">
            <ion-icon name="add-outline"></ion-icon> Add Category
        </button>
    </div>

    <?php if ($message): ?>
        <div class="flash-message flash-success"
            style="position:relative;top:auto;right:auto;margin-bottom:18px;animation:none;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Add Category Form -->
    <div id="addCatForm" style="display:none;margin-bottom:20px;" class="admin-card">
        <div style="padding:22px;">
            <h3 style="font-size:15px;font-weight:700;margin-bottom:14px;">Add New Category</h3>
            <form method="POST" class="admin-form" style="display:flex;gap:12px;align-items:flex-end;">
                <input type="hidden" name="action" value="add">
                <div class="form-group" style="flex:1;margin:0;">
                    <label>Category Name</label>
                    <input type="text" name="name" required placeholder="e.g. Electronics">
                </div>
                <button type="submit" class="admin-btn admin-btn-primary">Add</button>
                <button type="button" class="admin-btn admin-btn-secondary"
                    onclick="document.getElementById('addCatForm').style.display='none'">Cancel</button>
            </form>
        </div>
    </div>

    <div class="admin-card">
        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Products</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $i => $cat): ?>
                        <tr>
                            <td>
                                <?php echo $i + 1; ?>
                            </td>
                            <td id="name-<?php echo $cat['id']; ?>" style="font-weight:600;">
                                <?php echo sanitize($cat['name']); ?>
                            </td>
                            <td>
                                <?php echo (int) $cat['product_count']; ?>
                            </td>
                            <td>
                                <div style="display:flex;gap:8px;">
                                    <button class="admin-btn admin-btn-secondary" style="padding:5px 12px;font-size:12px;"
                                        onclick="editCat(<?php echo $cat['id']; ?>, '<?php echo addslashes($cat['name']); ?>')">Edit</button>
                                    <form method="POST" style="display:inline;"
                                        onsubmit="return confirm('Delete this category?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                        <button class="admin-btn admin-btn-danger"
                                            style="padding:5px 12px;font-size:12px;">Delete</button>
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

<!-- Edit Modal -->
<div id="editCatModal"
    style="display:none;position:fixed;inset:0;background:hsla(0,0%,0%,0.5);z-index:999;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:14px;padding:30px;width:400px;">
        <h3 style="margin-bottom:18px;font-weight:700;">Edit Category</h3>
        <form method="POST" class="admin-form">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="editCatId">
            <div class="form-group">
                <label>Category Name</label>
                <input type="text" name="name" id="editCatName" required>
            </div>
            <div style="display:flex;gap:10px;margin-top:12px;">
                <button type="submit" class="admin-btn admin-btn-primary">Save</button>
                <button type="button" class="admin-btn admin-btn-secondary"
                    onclick="document.getElementById('editCatModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editCat(id, name) {
        document.getElementById('editCatId').value = id;
        document.getElementById('editCatName').value = name;
        document.getElementById('editCatModal').style.display = 'flex';
    }
</script>

<?php include __DIR__ . '/admin_footer.php'; ?>