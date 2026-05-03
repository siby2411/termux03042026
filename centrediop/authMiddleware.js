const jwt = require('jsonwebtoken');

// Génération du token avec informations patient
const generateToken = (user, patientInfo = null) => {
  return jwt.sign(
    { 
      id: user.id,
      email: user.email,
      role: user.role,
      // Ajout des informations patient pour l'accueil
      patient: patientInfo ? {
        nom: patientInfo.nom,
        prenom: patientInfo.prenom,
        telephone: patientInfo.telephone,
        code_patient: patientInfo.code_patient
      } : null
    },
    process.env.JWT_SECRET,
    { expiresIn: '24h' }
  );
};

// Middleware de vérification du token
const verifyToken = (req, res, next) => {
  const token = req.headers['authorization']?.split(' ')[1];
  
  if (!token) {
    return res.status(403).json({ error: 'Token non fourni' });
  }

  try {
    const decoded = jwt.verify(token, process.env.JWT_SECRET);
    req.user = decoded;
    next();
  } catch (error) {
    return res.status(401).json({ error: 'Token invalide' });
  }
};

module.exports = { generateToken, verifyToken };
