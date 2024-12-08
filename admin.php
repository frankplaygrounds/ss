<?php
// Directory where group data is stored
$dataDir = 'data/';

// Admin password
$adminPassword = 'ss24m';

// Handle login
session_start();
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
        if ($_POST['password'] === $adminPassword) {
            $_SESSION['logged_in'] = true;
            header('Location: admin.php');
            exit;
        } else {
            $error = "Password errata.";
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login</title>
    </head>
    <body>
        <h1>Login Admin</h1>
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="POST">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Accedi</button>
        </form>
    </body>
    </html>
    <?php
    exit;
}

// Handle group deletion
if (isset($_GET['delete']) && isset($_GET['group'])) {
    $groupFile = $dataDir . basename($_GET['group']) . '.csv';
    $excelFile = $dataDir . basename($_GET['group']) . '_links.xlsx';
    if (file_exists($groupFile)) unlink($groupFile);
    if (file_exists($excelFile)) unlink($excelFile);
    header('Location: admin.php');
    exit;
}

// Handle group viewing
if (isset($_GET['view']) && isset($_GET['group'])) {
    $groupFile = $dataDir . basename($_GET['group']) . '.csv';
    if (file_exists($groupFile)) {
        $groupContent = file_get_contents($groupFile);
        $lines = explode("\n", trim($groupContent));
    } else {
        $groupContent = "Il gruppo selezionato non esiste.";
        $lines = [];
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Gruppi - Admin</title>
</head>
<body>
    <h1>Gestione Gruppi di Secret Santa</h1>
    
    <?php if (isset($lines)): ?>
        <h2>Dettagli del Gruppo: <?= htmlspecialchars($_GET['group']) ?></h2>
        <ul>
            <?php foreach ($lines as $line): ?>
                <?php
                $pair = explode(",", trim($line));
                if (count($pair) === 2) {
                    echo "<li><strong>" . htmlspecialchars($pair[0]) . "</strong> → " . htmlspecialchars($pair[1]) . "</li>";
                }
                ?>
            <?php endforeach; ?>
        </ul>
        <a href="admin.php">Torna alla gestione gruppi</a>
    <?php else: ?>
        <h2>Gruppi Esistenti:</h2>
        <ul>
            <?php
            $groups = array_filter(scandir($dataDir), function($file) {
                return preg_match('/^group_.*\.csv$/', $file);
            });
            if (empty($groups)) {
                echo "<li>Nessun gruppo trovato.</li>";
            } else {
                foreach ($groups as $groupFile) {
                    $groupId = basename($groupFile, '.csv');
                    echo "<li>
                        <strong>ID Gruppo:</strong> $groupId 
                        - <a href='admin.php?view=1&group=$groupId'>Visualizza</a>
                        - <a href='admin.php?delete=1&group=$groupId' onclick='return confirm(\"Sei sicuro di voler eliminare questo gruppo?\");'>Elimina</a>
                    </li>";
                }
            }
            ?>
        </ul>
    <?php endif; ?>
    <a href="index.php">Torna alla Home</a>
    <a href="index.php?admin">Aggiungi gruppo</a>
</body>
</html>
