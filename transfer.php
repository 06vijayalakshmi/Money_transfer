<?php
require_once 'config.php';
if (!isLoggedIn()) {
    redirect('index.php');
}

$user_id = $_SESSION['user_id'];
$message = '';

// Get user accounts and contacts
$accounts_stmt = $pdo->prepare("SELECT * FROM accounts WHERE user_id = ?");
$accounts_stmt->execute([$user_id]);
$accounts = $accounts_stmt->fetchAll();

$contacts_stmt = $pdo->prepare("SELECT * FROM contacts WHERE user_id = ? ORDER BY name");
$contacts_stmt->execute([$user_id]);
$contacts = $contacts_stmt->fetchAll();

// Process transfer
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['transfer'])) {
    $from_account = $_POST['from_account'];
    $to_account = $_POST['to_account'];
    $to_name = $_POST['to_name'];
    $to_bank = $_POST['to_bank'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    
    // Check balance
    $balance_stmt = $pdo->prepare("SELECT balance FROM accounts WHERE id = ?");
    $balance_stmt->execute([$from_account]);
    $current_balance = $balance_stmt->fetchColumn();
    
    if ($current_balance >= $amount) {
        try {
            $pdo->beginTransaction();
            
            // Deduct from sender
            $update_sender = $pdo->prepare("UPDATE accounts SET balance = balance - ? WHERE id = ?");
            $update_sender->execute([$amount, $from_account]);
            
            // Add to receiver (if account exists in our system)
            $update_receiver = $pdo->prepare("UPDATE accounts SET balance = balance + ? WHERE account_no = ?");
            $update_receiver->execute([$amount, $to_account]);
            
            // Record transaction
            $insert_transaction = $pdo->prepare("INSERT INTO transactions (from_account, from_user_id, to_account, to_name, to_bank, amount, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insert_transaction->execute([$from_account, $user_id, $to_account, $to_name, $to_bank, $amount, $description]);
            
            $pdo->commit();
            $message = "Transfer successful!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "Transfer failed: " . $e->getMessage();
        }
    } else {
        $message = "Insufficient balance!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Money - Money Transfer</title>
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
                <a href="transfer.php" class="nav-link active">Transfer</a>
                <a href="transactions.php" class="nav-link">Transactions</a>
                <a href="logout.php" class="nav-link logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>Transfer Money</h1>
        </div>

        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, 'successful') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="transfer-form-container">
            <form method="POST" class="transfer-form">
                <input type="hidden" name="transfer" value="1">
                
                <div class="form-group">
                    <label for="from_account">From Account</label>
                    <select id="from_account" name="from_account" required onchange="updateBalance()">
                        <option value="">Select Account</option>
                        <?php foreach ($accounts as $account): ?>
                        <option value="<?php echo $account['id']; ?>" data-balance="<?php echo $account['balance']; ?>">
                            <?php echo $account['bank_name'] . ' - ' . $account['account_no'] . ' (₹' . number_format($account['balance'], 2) . ')'; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="balance-display" class="balance-display"></div>
                </div>

                <div class="form-group">
                    <label for="recipient_type">Send To</label>
                    <div class="recipient-options">
                        <button type="button" class="tab-btn active" onclick="showTab('contact')">Contact</button>
                        <button type="button" class="tab-btn" onclick="showTab('manual')">Manual Entry</button>
                    </div>
                </div>

                <div id="contact-tab" class="tab-content active">
                    <div class="form-group">
                        <label for="contact_select">Select Contact</label>
                        <select id="contact_select" onchange="fillContactDetails()">
                            <option value="">Select Contact</option>
                            <?php foreach ($contacts as $contact): ?>
                            <option value="<?php echo $contact['id']; ?>" 
                                    data-account="<?php echo $contact['account_no']; ?>"
                                    data-name="<?php echo $contact['name']; ?>"
                                    data-bank="<?php echo $contact['bank_name']; ?>">
                                <?php echo $contact['name'] . ' - ' . $contact['account_no']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div id="manual-tab" class="tab-content">
                    <div class="form-group">
                        <label for="to_name">Recipient Name</label>
                        <input type="text" id="to_name" name="to_name" required>
                    </div>
                    <div class="form-group">
                        <label for="to_account">Account Number</label>
                        <input type="text" id="to_account" name="to_account" required>
                    </div>
                    <div class="form-group">
                        <label for="to_bank">Bank Name</label>
                        <input type="text" id="to_bank" name="to_bank" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="amount">Amount (₹)</label>
                    <input type="number" id="amount" name="amount" min="1" step="0.01" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" placeholder="Add a note for this transfer"></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-large">Transfer Money</button>
            </form>
        </div>
    </div>

    <script>
        function updateBalance() {
            const select = document.getElementById('from_account');
            const balanceDisplay = document.getElementById('balance-display');
            const selectedOption = select.options[select.selectedIndex];
            
            if (selectedOption.value) {
                const balance = selectedOption.getAttribute('data-balance');
                balanceDisplay.innerHTML = `Available Balance: ₹${parseFloat(balance).toLocaleString('en-IN', {minimumFractionDigits: 2})}`;
            } else {
                balanceDisplay.innerHTML = '';
            }
        }
        
        function showTab(tabName) {
            // Update buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Update content
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById(tabName + '-tab').classList.add('active');
        }
        
        function fillContactDetails() {
            const select = document.getElementById('contact_select');
            const selectedOption = select.options[select.selectedIndex];
            
            if (selectedOption.value) {
                document.getElementById('to_name').value = selectedOption.getAttribute('data-name');
                document.getElementById('to_account').value = selectedOption.getAttribute('data-account');
                document.getElementById('to_bank').value = selectedOption.getAttribute('data-bank');
            }
        }
    </script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>