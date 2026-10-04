// formateur.js - version complète
import api from './api'

const formateurService = {
  // Sessions assignées au formateur
  getMySessions(params = {}) {
    return api.get('sessions/index.php', { params })
  },
  
  // Répondre à une session
  respondToSession(sessionId, reponse, commentaire = '') {
    return api.post(`sessions/response.php?session_id=${sessionId}`, {
      reponse,
      commentaire
    })
  },
  
  // Inscriptions à mes sessions
  getSessionInscriptions(params = {}) {
    return api.get('inscriptions/index.php', { params })
  },
  
  // Récupérer les détails d'une session spécifique
  getSessionDetails(sessionId) {
    return api.get(`sessions/details.php?session_id=${sessionId}`)
  },
  
 getMyProfil() {
  return api.get('formateurs/profil.php')
},

getStats() {
  return api.get('formateurs/stats.php')
}
}

export default formateurService