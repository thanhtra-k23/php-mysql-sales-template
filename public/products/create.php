<?php

$pageTitle = 'Thêm sản phẩm';

require_once '/var/www/src/config/database.php';

$error = '';


// Lấy danh sách danh mục
$sqlCategories = "
    SELECT
        CategoryID,
        CategoryName
    FROM categories
    ORDER BY CategoryName
";

$categories = $conn->query($sqlCategories);


// Lấy danh sách nhà cung cấp
$sqlSuppliers = "
    SELECT
        SupplierID,
        SupplierName
    FROM suppliers
    ORDER BY SupplierName
";

$suppliers = $conn->query($sqlSuppliers);


// Xử lý khi người dùng bấm nút Thêm
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


    // Kiểm tra dữ liệu

    if ($productCode === '') {

        $error = 'Mã sản phẩm không được để trống.';

    } elseif ($productName === '') {

        $error = 'Tên sản phẩm không được để trống.';

    } elseif ($price < 0) {

        $error = 'Giá sản phẩm không hợp lệ.';

    } elseif ($stockQuantity < 0) {

        $error = 'Số lượng tồn kho không hợp lệ.';

    } elseif ($categoryID <= 0) {

        $error = 'Vui lòng chọn danh mục.';

    } elseif ($supplierID <= 0) {

        $error = 'Vui lòng chọn nhà cung cấp.';

    } else {

        // Thêm sản phẩm vào database

        $sql = "
            INSERT INTO products
            (
                ProductCode,
                ProductName,
                Description,
                Unit,
                Price,
                StockQuantity,
                IsActive,
                SupplierID,
                CategoryID
            )
            VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = $conn->prepare($sql);


        $stmt->bind_param(
            'ssssdiiii',
            $productCode,
            $productName,
            $description,
            $unit,
            $price,
            $stockQuantity,
            $isActive,
            $supplierID,
            $categoryID
        );


        if ($stmt->execute()) {

            $stmt->close();

            $conn->close();

            header('Location: /products/');

            exit;

        }


        $error = 'Không thể thêm sản phẩm.';

        $stmt->close();
    }
}


require_once '/var/www/src/includes/header.php';

require_once '/var/www/src/includes/navbar.php';

?>


<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h2>Thêm sản phẩm</h2>

        <a
            href="/products/"
            class="btn btn-secondary"
        >
            Quay lại
        </a>

    </div>


    <?php if ($error !== ''): ?>

        <div class="alert alert-danger">

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    <form
        method="post"
        action=""
    >

        <!-- Mã sản phẩm -->

        <div class="mb-3">

            <label class="form-label">
                Mã sản phẩm
            </label>

            <input
                type="text"
                name="product_code"
                class="form-control"
                value="<?= htmlspecialchars($_POST['product_code'] ?? '') ?>"
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
                value="<?= htmlspecialchars($_POST['product_name'] ?? '') ?>"
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
                rows="3"
            ><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>

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
                value="<?= htmlspecialchars($_POST['unit'] ?? '') ?>"
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
                min="0"
                step="0.01"
                value="<?= htmlspecialchars($_POST['price'] ?? '') ?>"
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
                min="0"
                value="<?= htmlspecialchars($_POST['stock_quantity'] ?? '') ?>"
                required
            >

        </div>


        <!-- Category -->

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
                        <?= (
                            ($_POST['category_id'] ?? '') == $category['CategoryID']
                        ) ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($category['CategoryName']) ?>
                    </option>

                <?php endwhile; ?>

            </select>

        </div>


        <!-- Supplier -->

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
                        <?= (
                            ($_POST['supplier_id'] ?? '') == $supplier['SupplierID']
                        ) ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($supplier['SupplierName']) ?>
                    </option>

                <?php endwhile; ?>

            </select>

        </div>


        <!-- Trạng thái -->

        <div class="form-check mb-3">

            <input
                type="checkbox"
                name="is_active"
                class="form-check-input"
                id="is_active"
                value="1"
                <?= isset($_POST['is_active']) || $_SERVER['REQUEST_METHOD'] !== 'POST'
                    ? 'checked'
                    : '' ?>
            >

            <label
                class="form-check-label"
                for="is_active"
            >
                Đang kinh doanh
            </label>

        </div>


        <!-- Nút -->

        <button
            type="submit"
            class="btn btn-primary"
        >
            Thêm sản phẩm
        </button>


        <a
            href="/products/"
            class="btn btn-secondary"
        >
            Hủy
        </a>

    </form>

</div>


<?php

require_once '/var/www/src/includes/footer.php';

$conn->close();

?>