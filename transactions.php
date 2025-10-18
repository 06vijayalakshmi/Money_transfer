<?php
require_once 'config.php';
if (!isLoggedIn()) {
    redirect('index.php');
}

$user_id = $_SESSION['user_id'];

// Get all transactions
$stmt = $pdo->prepare("
    SELECT t.*, a.account_no as from_account_no 
    FROM transactions t 
    JOIN accounts a ON t.from_account = a.id 
    WHERE t.from_user_id = ? 
    ORDER BY t.date DESC
");
$stmt->execute([$user_id]);
$transactions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - Money Transfer</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-money-bill-wave"></i>
                <span>MoneyTransfer</span>
            </div>
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="contacts.php" class="nav-link">Contacts</a>
                <a href="transfer.php" class="nav-link">Transfer</a>
                <a href="transactions.php" class="nav-link active">Transactions</a>
                <a href="logout.php" class="nav-link logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>Transaction History</h1>
        </div>

        <div class="transactions-container">
            <?php if (empty($transactions)): ?>
                <div class="no-data">
                    <i class="fas fa-receipt"></i>
                    <p>No transactions yet. Make your first transfer!</p>
                </div>
            <?php else: ?>
                <div class="transactions-table">
                    <div class="table-header">
                        <div>Date</div>
                        <div>Description</div>
                        <div>From Account</div>
                        <div>To Account</div>
                        <div>Amount</div>
                    </div>
                    <?php foreach ($transactions as $transaction): ?>
                    <div class="table-row">
                        <div><?php echo date('M j, Y g:i A', strtotime($transaction['date'])); ?></div>
                        <div>
                            <strong><?php echo $transaction['to_name']; ?></strong>
                            <br><small><?php echo $transaction['description']; ?></small>
                        </div>
                        <div><?php echo $transaction['from_account_no']; ?></div>
                        <div><?php echo $transaction['to_account']; ?></div>
                        <div class="amount debit">-₹<?php echo number_format($transaction['amount'], 2); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>