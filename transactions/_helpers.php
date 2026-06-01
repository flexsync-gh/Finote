<?php

function transaction_options($conn, $userId)
{
    return [
        'accounts' => db_fetch_all($conn, 'SELECT id, name, type FROM accounts WHERE userid = ? ORDER BY name', 'i', [$userId]),
        'categories' => db_fetch_all($conn, 'SELECT id, name, type FROM categories WHERE userid = ? ORDER BY type, name', 'i', [$userId]),
    ];
}

function find_transaction($conn, $userId, $transactionId)
{
    return db_fetch_one(
        $conn,
        "SELECT t.*, a.name AS account_name, c.name AS category_name
         FROM transactions t
         LEFT JOIN accounts a ON a.id = t.accountid
         LEFT JOIN categories c ON c.id = t.categoryid
         WHERE t.id = ? AND t.userid = ?
         LIMIT 1",
        'ii',
        [$transactionId, $userId]
    );
}

function valid_transaction_date($date)
{
    $parsed = DateTime::createFromFormat('Y-m-d', $date);
    return $parsed && $parsed->format('Y-m-d') === $date;
}

function validate_transaction_input($conn, $userId, $data)
{
    $errors = [];
    $type = $data['type'] ?? '';
    $accountId = (int) ($data['accountid'] ?? 0);
    $categoryId = (int) ($data['categoryid'] ?? 0);
    $amount = (float) ($data['amount'] ?? 0);
    $date = trim($data['transaction_date'] ?? '');

    if (!in_array($type, ['income', 'expense'], true)) {
        $errors[] = 'Choose income or expense.';
    }

    if ($amount <= 0) {
        $errors[] = 'Amount must be a positive number.';
    }

    if (!valid_transaction_date($date)) {
        $errors[] = 'Choose a valid transaction date.';
    }

    $account = db_fetch_one($conn, 'SELECT id FROM accounts WHERE id = ? AND userid = ?', 'ii', [$accountId, $userId]);
    if (!$account) {
        $errors[] = 'Choose a valid account.';
    }

    $category = db_fetch_one($conn, 'SELECT id FROM categories WHERE id = ? AND userid = ? AND type = ?', 'iis', [$categoryId, $userId, $type]);
    if (!$category) {
        $errors[] = 'Choose a category that matches the transaction type.';
    }

    return $errors;
}

function transaction_form($action, $transaction, $accounts, $categories, $errors = [])
{
    $type = $transaction['type'] ?? 'expense';
    ?>
    <?php if (!empty($errors)) { ?>
        <div class="alert alert-danger">
            <strong>Please fix these details:</strong>
            <ul class="mb-0">
                <?php foreach ($errors as $error) { ?>
                    <li><?php echo e($error); ?></li>
                <?php } ?>
            </ul>
        </div>
    <?php } ?>

    <form method="POST" action="<?php echo e($action); ?>">
        <?php echo csrf_field(); ?>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="type">Type</label>
                <select id="type" name="type" class="form-select" required>
                    <option value="expense" <?php echo $type === 'expense' ? 'selected' : ''; ?>>Expense</option>
                    <option value="income" <?php echo $type === 'income' ? 'selected' : ''; ?>>Income</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold" for="amount">Amount</label>
                <input id="amount" class="form-control" type="number" step="0.01" min="0.01" name="amount" value="<?php echo e($transaction['amount'] ?? ''); ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold" for="accountid">Account</label>
                <select id="accountid" name="accountid" class="form-select" required>
                    <option value="">Choose account</option>
                    <?php foreach ($accounts as $account) { ?>
                        <option value="<?php echo e($account['id']); ?>" <?php echo (int)($transaction['accountid'] ?? 0) === (int)$account['id'] ? 'selected' : ''; ?>>
                            <?php echo e($account['name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold" for="categoryid">Category</label>
                <select id="categoryid" name="categoryid" class="form-select" required>
                    <option value="">Choose category</option>
                    <?php foreach ($categories as $category) { ?>
                        <option data-type="<?php echo e($category['type']); ?>" value="<?php echo e($category['id']); ?>" <?php echo (int)($transaction['categoryid'] ?? 0) === (int)$category['id'] ? 'selected' : ''; ?>>
                            <?php echo e($category['name']); ?> (<?php echo e($category['type']); ?>)
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold" for="transaction_date">Transaction Date</label>
                <input id="transaction_date" class="form-control" type="date" name="transaction_date" value="<?php echo e($transaction['transaction_date'] ?? date('Y-m-d')); ?>" required>
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold" for="description">Description</label>
                <textarea id="description" class="form-control" name="description" rows="4" maxlength="1000" placeholder="Optional note"><?php echo e($transaction['description'] ?? ''); ?></textarea>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-4">
            <button class="btn btn-primary" type="submit">Save Transaction</button>
            <a class="btn btn-outline-secondary" href="<?php echo e(app_url('transactions/index.php')); ?>">Cancel</a>
        </div>
    </form>

    <script>
    (function () {
        const typeSelect = document.getElementById('type');
        const categorySelect = document.getElementById('categoryid');

        function syncCategories() {
            const type = typeSelect.value;
            let visibleSelected = false;

            Array.from(categorySelect.options).forEach((option) => {
                if (!option.value) return;
                const visible = option.dataset.type === type;
                option.hidden = !visible;
                option.disabled = !visible;
                if (visible && option.selected) visibleSelected = true;
            });

            if (!visibleSelected) {
                categorySelect.value = '';
            }
        }

        typeSelect.addEventListener('change', syncCategories);
        syncCategories();
    })();
    </script>
    <?php
}
