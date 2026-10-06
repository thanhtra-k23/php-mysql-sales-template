<?php

require_once '/var/www/src/config/database.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    die('ID sản phẩm không hợp lệ.');
}

/*
|--------------------------------------------------------------------------
| Xử lý khi nhấn nút Cập nhật
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $productCode = trim($_POST['product_code'] ?? '');
    $productName = trim($_POST['product_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $unit = trim($_POST['unit'] ?? '');

    $price = (float) ($_POST['price'] ?? 0);
    $stockQuantity = (int) ($_POST['stock_quantity'] ?? 0);

    $categoryID = (int) ($_POST['category_id'] ?? 0);
    $supplierID = (int) ($_POST['supplier_id'] ?? 0);

    $isActive = isset($_POST['is_active']) ? 1 : 0;

    /*
    |--------------------------------------------------------------------------
    | Cập nhật sản phẩm
    |--------------------------------------------------------------------------
    */

    $sql = "
        UPDATE products
        SET
            ProductCode = ?,
            ProductName = ?,
            Description = ?,
            Unit = ?,
            Price = ?,
            StockQuantity = ?,
            CategoryID = ?,
            SupplierID = ?,
            IsActive = ?
        WHERE ProductID = ?
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die('Lỗi chuẩn bị SQL: ' . $conn->error);
    }

    $stmt->bind_param(
        "ssssddiiii",
        $productCode,
        $productName,
        $description,
        $unit,
        $price,
        $stockQuantity,
        $categoryID,
        $supplierID,
        $isActive,
        $id
    );

    if ($stmt->execute()) {

        $stmt->close();

        header('Location: /products/');
        exit;

    } else {

        die('Lỗi cập nhật sản phẩm: ' . $stmt->error);
    }
}

/*
|--------------------------------------------------------------------------
| Lấy thông tin sản phẩm
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        ProductID,
        ProductCode,
        ProductName,
        Description,
        Unit,
        Price,
        StockQuantity,
        CategoryID,
        SupplierID,
        IsActive
    FROM products
    WHERE ProductID = ?
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die('Lỗi SQL: ' . $conn->error);
}

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

$product = $result->fetch_assoc();

$stmt->close();

if (!$product) {
    die('Không tìm thấy sản phẩm.');
}

/*
|--------------------------------------------------------------------------
| Lấy danh mục
|--------------------------------------------------------------------------
*/

$categories = $conn->query("
    SELECT CategoryID, CategoryName
    FROM categories
    ORDER BY CategoryName
");

/*
|--------------------------------------------------------------------------
| Lấy nhà cung cấp
|--------------------------------------------------------------------------
*/

$suppliers = $conn->query("
    SELECT SupplierID, SupplierName
    FROM suppliers
    ORDER BY SupplierName
");

$pageTitle = 'Sửa sản phẩm';

require_once '/var/www/src/includes/header.php';
require_once '/var/www/src/includes/navbar.php';

?>

<div class="container mt-4">

    <h2>Sửa sản phẩm</h2>

    <form method="post">

        <!-- Mã sản phẩm -->

        <div class="mb-3">

            <label class="form-label">
                Mã sản phẩm
            </label>

            <input
                type="text"
                name="product_code"
                class="form-control"
                value="<?= htmlspecialchars($product['ProductCode']) ?>"
                required
            >

        </div>


        <!-- Tên sản phẩm -->

        <div class="mb-3">

            <label class="form-label">
                Tên sản phẩm
            </label>

            <input
                type="text"
                name="product_name"
                class="form-control"
                value="<?= htmlspecialchars($product['ProductName']) ?>"
                required
            >

        </div>


        <!-- Mô tả -->

        <div class="mb-3">

            <label class="form-label">
                Mô tả
            </label>

            <textarea
                name="description"
                class="form-control"
                rows="4"
            ><?= htmlspecialchars($product['Description'] ?? '') ?></textarea>

        </div>


        <!-- Đơn vị -->

        <div class="mb-3">

            <label class="form-label">
                Đơn vị
            </label>

            <input
                type="text"
                name="unit"
                class="form-control"
                value="<?= htmlspecialchars($product['Unit'] ?? '') ?>"
            >

        </div>


        <!-- Giá -->

        <div class="mb-3">

            <label class="form-label">
                Giá
            </label>

            <input
                type="number"
                name="price"
                class="form-control"
                value="<?= htmlspecialchars($product['Price']) ?>"
                min="0"
                step="0.01"
                required
            >

        </div>


        <!-- Tồn kho -->

        <div class="mb-3">

            <label class="form-label">
                Số lượng tồn kho
            </label>

            <input
                type="number"
                name="stock_quantity"
                class="form-control"
                value="<?= htmlspecialchars($product['StockQuantity']) ?>"
                min="0"
                required
            >

        </div>


        <!-- Danh mục -->

        <div class="mb-3">

            <label class="form-label">
                Danh mục
            </label>

            <select
                name="category_id"
                class="form-select"
                required
            >

                <option value="">
                    -- Chọn danh mục --
                </option>

                <?php while ($category = $categories->fetch_assoc()): ?>

                    <option
                        value="<?= $category['CategoryID'] ?>"
                        <?= ((int)$category['CategoryID'] === (int)$product['CategoryID']) ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($category['CategoryName']) ?>
                    </option>

                <?php endwhile; ?>

            </select>

        </div>


        <!-- Nhà cung cấp -->

        <div class="mb-3">

            <label class="form-label">
                Nhà cung cấp
            </label>

            <select
                name="supplier_id"
                class="form-select"
                required
            >

                <option value="">
                    -- Chọn nhà cung cấp --
                </option>

                <?php while ($supplier = $suppliers->fetch_assoc()): ?>

                    <option
                        value="<?= $supplier['SupplierID'] ?>"
                        <?= ((int)$supplier['SupplierID'] === (int)$product['SupplierID']) ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($supplier['SupplierName']) ?>
                    </option>

                <?php endwhile; ?>

            </select>

        </div>


        <div class="form-check mb-3">

            <input
                type="checkbox"
                name="is_active"
                class="form-check-input"
                id="is_active"
                value="1"
                <?= ((int)$product['IsActive'] === 1) ? 'checked' : '' ?>
            >

            <label
                class="form-check-label"
                for="is_active"
            >
                Đang bán
            </label>

        </div>


    
        <button
            type="submit"
            class="btn btn-primary"
        >
            Cập nhật sản phẩm
        </button>


        <a
            href="/products/"
            class="btn btn-secondary"
        >
            Quay lại
        </a>

    </form>

</div>

<?php

require_once '/var/www/src/includes/footer.php';

$conn->close();