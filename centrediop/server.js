const express = require('express');
const path = require('path');
const cors = require('cors');
const dotenv = require('dotenv');
const fs = require('fs');

// Charger les variables d'environnement
dotenv.config();

// Initialisation de l'application
const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static(path.join(__dirname, 'public')));

// Créer les dossiers nécessaires
const dirs = ['temp', 'uploads', 'logs'];
dirs.forEach(dir => {
    const dirPath = path.join(__dirname, dir);
    if (!fs.existsSync(dirPath)) {
        fs.mkdirSync(dirPath, { recursive: true });
        console.log(`📁 Dossier créé: ${dir}`);
    }
});

// Routes
app.use('/', require('./routes/dossierRoutes'));

// Route d'accueil
app.get('/', (req, res) => {
    res.json({
        name: 'Omega Informatique - Dossier Médical',
        version: '2.0.0',
        status: 'online',
        endpoints: {
            formulaire: '/edition-dossier',
            api: '/api/patient/dossier/:code_patient'
        }
    });
});

// Gestion des erreurs 404
app.use((req, res) => {
    res.status(404).json({ error: 'Route non trouvée' });
});

// Gestion des erreurs globales
app.use((err, req, res, next) => {
    console.error('❌ Erreur:', err.stack);
    res.status(500).json({ 
        error: 'Erreur interne du serveur',
        message: err.message 
    });
});

// Démarrage du serveur
app.listen(PORT, () => {
    console.log(`
    🚀 Serveur démarré sur http://localhost:${PORT}
    📋 Formulaire d'édition: http://localhost:${PORT}/edition-dossier
    🔧 Environnement: ${process.env.NODE_ENV}
    `);
});

module.exports = app;
