<?php

session_start();

$products = [
    [
        'id' => 1,
        'name' => 'Laptop',
        'description' => 'A powerful laptop for everyday use.',
        'price' => 1200.00,
        'category' => 'Electronics'
    ],
    [
        'id' => 2,
        'name' => 'Headphones',
        'description' => 'Noise-cancelling wireless headphones.',
        'price' => 150.50,
        'category' => 'Electronics'
    ]
];

if (isset($_SESSION['products'])) {
    $products = $_SESSION['products'];
}

$errors = [];
$submittedData = [];
$successMessage = '';
$editProductId = null;
$editingProduct = null;

if (isset($_GET['edit_id'])) {
    $editProductId = (int)$_GET['edit_id'];
    $foundProductKey = array_search($editProductId, array_column($products, 'id'));
    if ($foundProductKey !== false) {
        $editingProduct = $products[$foundProductKey];
        $submittedData = $editingProduct;
    } else {
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $deleteId = (int)$_POST['delete_id'];
        $products = array_filter($products, function($product) use ($deleteId) {
            return $product['id'] != $deleteId;
        });
        $_SESSION['products'] = array_values($products);
        $_SESSION['success_message'] = 'Product deleted successfully!';
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    $submittedData = $_POST;
    $isUpdate = isset($submittedData['id']) && !empty($submittedData['id']);

    $name = trim($submittedData['name'] ?? '');
    $description = trim($submittedData['description'] ?? '');
    $price = trim($submittedData['price'] ?? '');
    $category = trim($submittedData['category'] ?? '');

    if (empty($name)) {
        $errors['name'] = 'Product name is required.';
    }
    if (strlen($description) < 10) {
        $errors['description'] = 'Description must be at least 10 characters long.';
    }
    if (!is_numeric($price) || $price <= 0) {
        $errors['price'] = 'Price must be a positive number.';
    }
    if (empty($category)) {
        $errors['category'] = 'Category is required.';
    }

    if (empty($errors)) {
        if ($isUpdate) {
            $updateId = (int)$submittedData['id'];
            $foundProductKey = array_search($updateId, array_column($products, 'id'));
            if ($foundProductKey !== false) {
                $products[$foundProductKey]['name'] = htmlspecialchars($name);
                $products[$foundProductKey]['description'] = htmlspecialchars($description);
                $products[$foundProductKey]['price'] = (float)$price;
                $products[$foundProductKey]['category'] = htmlspecialchars($category);
                $_SESSION['products'] = $products;
                $_SESSION['success_message'] = 'Product updated successfully!';
            }
        } else {
            $newId = 1;
            if (!empty($products)) {
                $newId = max(array_column($products, 'id')) + 1;
            }

            $newProduct = [
                'id' => $newId,
                'name' => htmlspecialchars($name),
                'description' => htmlspecialchars($description),
                'price' => (float)$price,
                'category' => htmlspecialchars($category)
            ];

            $products[] = $newProduct;
            $_SESSION['products'] = $products;
            $_SESSION['success_message'] = 'Product added successfully!';
        }
        
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Product Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
    <div class="container my-5">
        <h1 class="text-center mb-4">Product Management</h1>
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Product List</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($products)): ?>
                            <div class="alert alert-info" role="alert">
                                No products found. Add a new one!
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Price</th>
                                            <th>Category</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($products as $product): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($product['id']) ?></td>
                                                <td><?= htmlspecialchars($product['name']) ?></td>
                                                <td><?= htmlspecialchars($product['description']) ?></td>
                                                <td>$<?= number_format($product['price'], 2) ?></td>
                                                <td><?= htmlspecialchars($product['category']) ?></td>
                                                <td>
                                                    <a href="?edit_id=<?= htmlspecialchars($product['id']) ?>" class="btn btn-sm btn-outline-warning">Edit</a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= htmlspecialchars($product['id']) ?>">
                                                        Delete
                                                    </button>
                                                </td>
                                            </tr>
                                            <div class="modal fade" id="deleteModal<?= htmlspecialchars($product['id']) ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= htmlspecialchars($product['id']) ?>" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="deleteModalLabel<?= htmlspecialchars($product['id']) ?>">Confirm Deletion</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Are you sure you want to delete **<?= htmlspecialchars($product['name']) ?>**? This action cannot be undone.
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" style="display:inline;">
                                                                <input type="hidden" name="delete_id" value="<?= htmlspecialchars($product['id']) ?>">
                                                                <button type="submit" class="btn btn-danger">Delete</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-<?= $editingProduct ? 'warning' : 'success' ?> text-white">
                        <h5 class="mb-0"><?= $editingProduct ? 'Edit Product' : 'Add New Product' ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($successMessage)): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?= $successMessage ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger" role="alert">
                                Please correct the errors in the form.
                            </div>
                        <?php endif; ?>

                        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" novalidate>
                            <?php if ($editingProduct): ?>
                                <input type="hidden" name="id" value="<?= htmlspecialchars($editingProduct['id']) ?>">
                            <?php endif; ?>
                            <div class="mb-3">
                                <label for="name" class="form-label">Product Name</label>
                                <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" id="name" name="name" value="<?= htmlspecialchars($submittedData['name'] ?? '') ?>" required>
                                <?php if (isset($errors['name'])): ?>
                                    <div class="invalid-feedback">
                                        <?= $errors['name'] ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>" id="description" name="description" rows="3" required><?= htmlspecialchars($submittedData['description'] ?? '') ?></textarea>
                                <?php if (isset($errors['description'])): ?>
                                    <div class="invalid-feedback">
                                        <?= $errors['description'] ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label for="price" class="form-label">Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control <?= isset($errors['price']) ? 'is-invalid' : '' ?>" id="price" name="price" value="<?= htmlspecialchars($submittedData['price'] ?? '') ?>" required>
                                    <?php if (isset($errors['price'])): ?>
                                        <div class="invalid-feedback">
                                            <?= $errors['price'] ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select <?= isset($errors['category']) ? 'is-invalid' : '' ?>" id="category" name="category" required>
                                    <option value="">Choose...</option>
                                    <?php 
                                        $categories = ['Electronics', 'Books', 'Home Goods', 'Apparel'];
                                        foreach ($categories as $cat):
                                            $selected = ($submittedData['category'] ?? '') === $cat ? 'selected' : '';
                                    ?>
                                        <option value="<?= htmlspecialchars($cat) ?>" <?= $selected ?>><?= htmlspecialchars($cat) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['category'])): ?>
                                    <div class="invalid-feedback">
                                        <?= $errors['category'] ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <button type="submit" class="btn btn-<?= $editingProduct ? 'warning' : 'success' ?> w-100">
                                <?= $editingProduct ? 'Update Product' : 'Add Product' ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>