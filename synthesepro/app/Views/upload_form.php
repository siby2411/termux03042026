<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SynthesePro UEMOA - Import du Journal</title>
    <link rel="stylesheet" href="/css/style.css"> </head>
<body>
    <header>
        <h1>Importation du Journal Comptable (Phase A)</h1>
        <p>Veuillez télécharger votre fichier d'écritures pour la société sélectionnée.</p>
    </header>

    <main>
        <form action="/index.php?action=upload" method="post" enctype="multipart/form-data">
            
            <label for="societe_id">Sélectionner l'entreprise :</label>
            <select name="societe_id" id="societe_id" required>
                <option value="1">SENECOMM (Exercice 2025)</option>
                </select>
            <br><br>

            <label for="journal_file">Fichier du Journal (CSV ou Excel) :</label>
            <input type="file" name="journal_file" id="journal_file" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" required>
            <p class="hint">Format attendu : Date, Libellé, Compte Débit, Compte Crédit, Montant</p>
            <br>

            <button type="submit" name="submit_upload">Importer et Organiser les Données</button>

            <input type="hidden" name="MAX_FILE_SIZE" value="10000000" /> 
        </form>
    </main>
    <footer>
        <p>&copy; SynthesePro UEMOA</p>
    </footer>
</body>
</html>

