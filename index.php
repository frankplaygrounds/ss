<?php
// File to store assignments
$file = 'assignments.csv';

// Generate Secret Santa assignments
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $names = array_map('trim', explode("\n", $_POST['names']));
    $names = array_filter($names); // Remove empty lines

    if (count($names) < 2) {
        die("Devi inserire almeno due nomi per generare gli abbinamenti.");
    }

    $givers = $names;
    $receivers = $names;

    // Shuffle receivers until no duplicates occur
    do {
        shuffle($receivers);
    } while (array_intersect_assoc($givers, $receivers));

    // Save assignments to a CSV file
    $assignments = [];
    foreach ($givers as $i => $giver) {
        $assignments[] = [$giver, $receivers[$i]];
    }

    $handle = fopen($file, 'w');
    foreach ($assignments as $assignment) {
        fputcsv($handle, $assignment);
    }
    fclose($handle);

    echo "Abbinamenti generati con successo! Condividi i link univoci.";
    exit;
}

// Serve individual Secret Santa page
if (isset($_GET['name'])) {
    $name = $_GET['name'];
    if (!file_exists($file)) {
        die("Nessun abbinamento trovato.");
    }

    $handle = fopen($file, 'r');
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
        die("Nome non trovato negli abbinamenti.");
    }
}

// Display input form
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secret Santa</title>
</head>
<body>
    <h1>Secret Santa</h1>
    <form method="POST">
        <label for="names">Inserisci i nomi dei partecipanti (uno per riga):</label><br>
        <textarea id="names" name="names" rows="10" cols="30" required></textarea><br>
        <button type="submit">Genera Abbinamenti</button>
    </form>
</body>
</html>
