<?php
require_once 'config/database.php';

try {
    $pdo = getPDO();
    
    echo "Création des utilisateurs de base...\n";
    
    // Récupérer les services
    $services = $pdo->query("SELECT id, name FROM services")->fetchAll();
    $service_map = [];
    foreach ($services as $s) {
        $service_map[$s['name']] = $s['id'];
    }
    
    // Créer l'admin
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password, role, prenom, nom, service_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['admin', $admin_password, 'admin', 'Admin', 'System', $service_map['Accueil/Triage'] ?? 1]);
    echo "✅ Admin créé\n";
    
    // Créer caissier
    $stmt->execute(['caissier1', password_hash('caissier123', PASSWORD_DEFAULT), 'caissier', 'Oumar', 'Sow', $service_map['Caisse'] ?? 1]);
    echo "✅ Caissier créé\n";
    
    // Créer sage-femme
    $stmt->execute(['sagefemme1', password_hash('sagefemme123', PASSWORD_DEFAULT), 'sagefemme', 'Fatou', 'Ndiaye', $service_map['Gynécologie'] ?? 1]);
    echo "✅ Sage-femme créée\n";
    
    // Créer médecins
    $stmt->execute(['dr.fall', password_hash('pediatre123', PASSWORD_DEFAULT), 'medecin', 'Aminata', 'Fall', $service_map['Pédiatrie'] ?? 1]);
    $stmt->execute(['dr.diop', password_hash('medecin123', PASSWORD_DEFAULT), 'medecin', 'Moussa', 'Diop', $service_map['Odontologie'] ?? 1]);
    echo "✅ Médecins créés\n";
    
    echo "\n🔑 Informations de connexion:\n";
    echo "  Admin: admin / admin123\n";
    echo "  Caissier: caissier1 / caissier123\n";
    echo "  Sage-femme: sagefemme1 / sagefemme123\n";
    echo "  Dr. Fall: dr.fall / pediatre123\n";
    echo "  Dr. Diop: dr.diop / medecin123\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
