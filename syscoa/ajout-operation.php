<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saisie Comptable Simplifiée OHADA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f4f7f9; }
        .card { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1); }
        .autocomplete-list { max-height: 200px; overflow-y: auto; z-index: 10; }
    </style>
</head>
<body class="p-4 sm:p-8">

<div class="max-w-4xl mx-auto bg-white p-6 md:p-10 rounded-xl card">
    <h1 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-2">Saisie d'Écriture OHADA Automatisée</h1>
    <p class="text-sm text-gray-600 mb-6">Utilisez la recherche pour trouver rapidement le compte sans connaître le numéro exact.</p>

    <!-- FORMULAIRE DE SAISIE -->
    <form id="saisieForm" action="insert_operation.php" method="POST" class="space-y-6">

        <!-- L'environnement étant mono-utilisateur pour le moment, le compte se simplifie ici -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="date_ecriture" class="block text-sm font-medium text-gray-700">Date de l'écriture</label>
                <input type="date" id="date_ecriture" name="date_ecriture" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label for="journal_code" class="block text-sm font-medium text-gray-700">Type de Journal (Code)</label>
                <select id="journal_code" name="journal_code" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="VTE">Ventes</option>
                    <option value="ACH">Achats</option>
                    <option value="CA">Caisse</option>
                    <option value="BQ">Banque</option>
                    <option value="OD">Opérations Diverses</option>
                </select>
            </div>
        </div>

        <div>
            <label for="libelle_ecriture" class="block text-sm font-medium text-gray-700">Libellé de l'opération</label>
            <input type="text" id="libelle_ecriture" name="libelle_ecriture" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <!-- Section Compte (avec Autocomplétion) -->
        <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-200">
            <h3 class="text-lg font-semibold text-indigo-800 mb-4">Saisie Débit/Crédit</h3>
            
            <!-- Compte 1 (Débit) -->
            <div class="mb-6 relative">
                <label for="search_compte_debit" class="block text-sm font-medium text-gray-700">Compte Débit (Recherche)</label>
                <input type="text" id="search_compte_debit" placeholder="Entrez numéro ou intitulé (ex: Vente, 701, Fournisseur)" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:ring-indigo-500 focus:border-indigo-500">
                <!-- Champ caché pour le numéro de compte réel -->
                <input type="hidden" id="compte_debit" name="compte_debit" required>
                <!-- Liste d'autocomplétion -->
                <div id="autocomplete_debit" class="autocomplete-list absolute w-full bg-white border border-gray-300 mt-1 rounded-md shadow-lg hidden"></div>
                
                <label for="montant_debit" class="block text-sm font-medium text-gray-700 mt-4">Montant Débit (XOF)</label>
                <input type="number" step="0.01" id="montant_debit" name="montant_debit" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Compte 2 (Crédit) -->
            <div class="relative">
                <label for="search_compte_credit" class="block text-sm font-medium text-gray-700">Compte Crédit (Recherche)</label>
                <input type="text" id="search_compte_credit" placeholder="Entrez numéro ou intitulé" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:ring-indigo-500 focus:border-indigo-500">
                <!-- Champ caché pour le numéro de compte réel -->
                <input type="hidden" id="compte_credit" name="compte_credit" required>
                <!-- Liste d'autocomplétion -->
                <div id="autocomplete_credit" class="autocomplete-list absolute w-full bg-white border border-gray-300 mt-1 rounded-md shadow-lg hidden"></div>
                
                <label for="montant_credit" class="block text-sm font-medium text-gray-700 mt-4">Montant Crédit (XOF)</label>
                <input type="number" step="0.01" id="montant_credit" name="montant_credit" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>
        
        <!-- Bouton de Soumission -->
        <button type="submit" class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150 ease-in-out">
            Enregistrer l'Écriture Comptable
        </button>

        <div id="messageBox" class="p-3 mt-4 text-center rounded-md hidden" role="alert"></div>

    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const debitInput = document.getElementById('search_compte_debit');
        const creditInput = document.getElementById('search_compte_credit');
        const debitAC = document.getElementById('autocomplete_debit');
        const creditAC = document.getElementById('autocomplete_credit');
        const hiddenDebit = document.getElementById('compte_debit');
        const hiddenCredit = document.getElementById('compte_credit');
        const form = document.getElementById('saisieForm');
        const msgBox = document.getElementById('messageBox');

        // Fonction générique pour la recherche par autocomplétion
        const setupAutocomplete = (inputElement, autocompleteElement, hiddenInputElement) => {
            let timeout = null;

            inputElement.addEventListener('input', () => {
                clearTimeout(timeout);
                const query = inputElement.value.trim();

                if (query.length < 2) {
                    autocompleteElement.innerHTML = '';
                    autocompleteElement.classList.add('hidden');
                    return;
                }

                timeout = setTimeout(() => {
                    fetch('get_compte_ohada.php?q=' + encodeURIComponent(query))
                        .then(response => response.json())
                        .then(data => {
                            autocompleteElement.innerHTML = '';
                            if (data.length > 0) {
                                data.forEach(item => {
                                    const div = document.createElement('div');
                                    div.textContent = item.text;
                                    div.className = 'p-2 cursor-pointer hover:bg-indigo-100 transition duration-100';
                                    div.onclick = () => {
                                        inputElement.value = item.text;
                                        hiddenInputElement.value = item.id;
                                        autocompleteElement.classList.add('hidden');
                                    };
                                    autocompleteElement.appendChild(div);
                                });
                                autocompleteElement.classList.remove('hidden');
                            } else {
                                autocompleteElement.classList.add('hidden');
                            }
                        })
                        .catch(error => console.error('Erreur Fetch:', error));
                }, 300); // Délai de 300ms avant la recherche
            });

            // Cacher la liste si l'utilisateur clique en dehors
            document.addEventListener('click', (e) => {
                if (!inputElement.contains(e.target) && !autocompleteElement.contains(e.target)) {
                    autocompleteElement.classList.add('hidden');
                }
            });
        };

        // Initialisation de l'autocomplétion pour les deux champs
        setupAutocomplete(debitInput, debitAC, hiddenDebit);
        setupAutocomplete(creditInput, creditAC, hiddenCredit);

        // Validation du formulaire avant soumission (équilibre Débit/Crédit)
        form.addEventListener('submit', (e) => {
            const debit = parseFloat(document.getElementById('montant_debit').value) || 0;
            const credit = parseFloat(document.getElementById('montant_credit').value) || 0;

            // Vérification de l'existence des comptes
            if (hiddenDebit.value === '' || hiddenCredit.value === '') {
                e.preventDefault();
                showMessage('Veuillez sélectionner un compte Débit et un compte Crédit valides à partir de la recherche.', 'bg-red-100 text-red-700 border border-red-400');
                return;
            }

            // Vérification de l'équilibre
            if (debit !== credit || debit === 0 || credit === 0) {
                e.preventDefault();
                showMessage('L\'écriture doit être équilibrée (Débit = Crédit) et le montant doit être supérieur à zéro.', 'bg-red-100 text-red-700 border border-red-400');
                return;
            }

            // Soumission normale si tout est valide
            showMessage('Soumission en cours...', 'bg-yellow-100 text-yellow-700 border border-yellow-400');
            // La logique pour la soumission réelle et la gestion de la réponse se fera dans insert_operation.php
        });

        function showMessage(text, className) {
            msgBox.textContent = text;
            msgBox.className = `p-3 mt-4 text-center rounded-md ${className}`;
            msgBox.classList.remove('hidden');
            setTimeout(() => {
                msgBox.classList.add('hidden');
            }, 5000);
        }
    });
</script>
</body>
</html>




