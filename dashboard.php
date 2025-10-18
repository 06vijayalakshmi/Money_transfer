<?php
require_once 'config.php';
if (!isLoggedIn()) {
    redirect('index.php');
}

$user_id = $_SESSION['user_id'];

// Get user accounts
$stmt = $pdo->prepare("SELECT * FROM accounts WHERE user_id = ?");
$stmt->execute([$user_id]);
$accounts = $stmt->fetchAll();

// Get recent transactions
$stmt = $pdo->prepare("
    SELECT t.*, a.account_no as from_account_no 
    FROM transactions t 
    JOIN accounts a ON t.from_account = a.id 
    WHERE t.from_user_id = ? 
    ORDER BY t.date DESC 
    LIMIT 5
");
$stmt->execute([$user_id]);
$transactions = $stmt->fetchAll();

$total_balance = 0;
foreach ($accounts as $account) {
    $total_balance += $account['balance'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Money Transfer</title>
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
                <a href="dashboard.php" class="nav-link active">Dashboard</a>
                <a href="contacts.php" class="nav-link">Contacts</a>
                <a href="transfer.php" class="nav-link">Transfer</a>
                <a href="transactions.php" class="nav-link">Transactions</a>
                <a href="logout.php" class="nav-link logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-section">
            <h1>Welcome, <?php echo $_SESSION['full_name']; ?>!</h1>
            <div class="balance-card">
                <h3>Total Balance</h3>
                <p class="balance-amount">₹<?php echo number_format($total_balance, 2); ?></p>
            </div>
        </div>

        <div class="grid-container">
            <div class="accounts-section">
                <h2>Your Accounts</h2>
                <div class="accounts-grid">
                    <?php foreach ($accounts as $account): ?>
                    <div class="account-card">
                        <div class="account-header">
                            <h3><?php echo $account['bank_name']; ?></h3>
                            <span class="account-type">Savings</span>
                        </div>
                        <p class="account-number"><?php echo $account['account_no']; ?></p>
                        <p class="account-balance">₹<?php echo number_format($account['balance'], 2); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="recent-transactions">
                <h2>Recent Transactions</h2>
                <div class="transactions-list">
                    <?php if (empty($transactions)): ?>
                        <p class="no-data">No transactions yet</p>
                    <?php else: ?>
                        <?php foreach ($transactions as $transaction): ?>
                        <div class="transaction-item">
                            <div class="transaction-info">
                                <h4><?php echo $transaction['to_name']; ?></h4>
                                <p><?php echo $transaction['description']; ?></p>
                                <small><?php echo $transaction['date']; ?></small>
                            </div>
                            <div class="transaction-amount debit">
                                -₹<?php echo number_format($transaction['amount'], 2); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <a href="transactions.php" class="btn btn-outline">View All Transactions</a>
            </div>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>