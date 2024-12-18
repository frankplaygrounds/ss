<?php
// Directory to store group assignments
$dataDir = 'data/';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0777, true);
}

// Include PHPExcel library
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Admin Page: Create a Secret Santa group
if (isset($_GET['admin'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $groupId = uniqid('group_');
        $names = array_map('trim', explode("\n", $_POST['names']));
        $names = array_filter($names);

        if (count($names) < 2) {
            die("Devi inserire almeno due nomi per creare un gruppo di Secret Santa.");
        }

        $givers = $names;
        $receivers = $names;

        // Shuffle receivers until no duplicates occur
        do {
            shuffle($receivers);
        } while (array_intersect_assoc($givers, $receivers));

        // Save assignments to a group file
        $assignments = [];
        foreach ($givers as $i => $giver) {
            $assignments[] = [$giver, $receivers[$i]];
        }

        $filePath = $dataDir . $groupId . '.csv';
        $handle = fopen($filePath, 'w');
        foreach ($assignments as $assignment) {
            fputcsv($handle, $assignment);
        }
        fclose($handle);

        // Generate Excel file with participant links
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Nome');
        $sheet->setCellValue('B1', 'Link');

        $baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
        $row = 2;
        foreach ($givers as $giver) {
            $link = $baseUrl . '?group=' . $groupId . '&name=' . urlencode($giver);
            $sheet->setCellValue("A$row", $giver);
            $sheet->setCellValue("B$row", $link);
            $row++;
        }

        $excelFile = $dataDir . $groupId . '_links.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($excelFile);

        // Provide download link for the Excel file
        echo "Gruppo creato con successo! Scarica i link per i partecipanti qui: ";
        echo "<a href='$excelFile' download>Scarica il file Excel</a><br>";
        exit;
    }
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Secret Santa</title>
</head>
<body>
    <h1>Admin - Crea un Gruppo di Secret Santa</h1>
    <form method="POST">
        <label for="names">Inserisci i nomi dei partecipanti (uno per riga):</label><br>
        <textarea id="names" name="names" rows="10" cols="30" required></textarea><br>
        <button type="submit">Crea Gruppo</button>
    </form>
</body>
</html>
<?php
    exit;
}

// Participant Page: View Secret Santa assignment
if (isset($_GET['group']) && isset($_GET['name'])) {
    $groupId = $_GET['group'];
    $name = $_GET['name'];
    $filePath = $dataDir . $groupId . '.csv';

    if (!file_exists($filePath)) {
        die("Il gruppo specificato non esiste.");
    }

    $handle = fopen($filePath, 'r');
    $receiver = null;
    while (($row = fgetcsv($handle)) !== false) {
        if ($row[0] === $name) {
            $receiver = $row[1];
            break;
        }
    }
    fclose($handle);

    if ($receiver) {
        echo "<h1>Ciao $name!</h1>";
        echo "<p>Il tuo destinatario di Secret Santa è: <strong>$receiver</strong></p>";
        echo "<p>Fai uno screenshot di questa schermata e tienila segreta!</p>";
        exit;
    } else {
        die("Il tuo nome non è stato trovato nel gruppo specificato.");
    }
}

// Default: Link to Admin Page
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secret Santa</title>
</head>
<body>
    <h1>Benvenuto in Secret Santa!</h1>
    <p>Sei un amministratore? <a href="admin.php">Gestisci gruppi</a></p>
</body>
</html>
