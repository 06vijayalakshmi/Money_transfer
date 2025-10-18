<?php
require_once 'config.php';
if (!isLoggedIn()) {
    redirect('index.php');
}

$user_id = $_SESSION['user_id'];

// Get user contacts
$stmt = $pdo->prepare("SELECT * FROM contacts WHERE user_id = ? ORDER BY name");
$stmt->execute([$user_id]);
$contacts = $stmt->fetchAll();

// Add new contact
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_contact'])) {
    $name = $_POST['name'];
    $account_no = $_POST['account_no'];
    $bank_name = $_POST['bank_name'];
    $phone = $_POST['phone'];
    
    $stmt = $pdo->prepare("INSERT INTO contacts (user_id, name, account_no, bank_name, phone) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $name, $account_no, $bank_name, $phone]);
    
    header("Location: contacts.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacts - Money Transfer</title>
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
                <a href="contacts.php" class="nav-link active">Contacts</a>
                <a href="transfer.php" class="nav-link">Transfer</a>
                <a href="transactions.php" class="nav-link">Transactions</a>
                <a href="logout.php" class="nav-link logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>My Contacts</h1>
            <button class="btn btn-primary" onclick="openModal()">Add Contact</button>
        </div>

        <div class="contacts-grid">
            <?php if (empty($contacts)): ?>
                <div class="no-data">
                    <i class="fas fa-users"></i>
                    <p>No contacts yet. Add your first contact!</p>
                </div>
            <?php else: ?>
                <?php foreach ($contacts as $contact): ?>
                <div class="contact-card">
                    <div class="contact-avatar">
                        <?php echo strtoupper(substr($contact['name'], 0, 1)); ?>
                    </div>
                    <div class="contact-info">
                        <h3><?php echo $contact['name']; ?></h3>
                        <p><strong>Account:</strong> <?php echo $contact['account_no']; ?></p>
                        <p><strong>Bank:</strong> <?php echo $contact['bank_name']; ?></p>
                        <p><strong>Phone:</strong> <?php echo $contact['phone']; ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Contact Modal -->
    <div id="contactModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Contact</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST" class="modal-form">
                <input type="hidden" name="add_contact" value="1">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="account_no">Account Number</label>
                    <input type="text" id="account_no" name="account_no" required>
                </div>
                <div class="form-group">
                    <label for="bank_name">Bank Name</label>
                    <input type="text" id="bank_name" name="bank_name" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Contact</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('contactModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('contactModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('contactModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>