import api from './api'

const employeService = {
  // ============ FORMATIONS DISPONIBLES ============
  async getAvailableFormations(params = {}) {
    try {
      console.log('📚 Chargement formations disponibles...')
      // ENLÈVE "api/" DU DÉBUT, car la base URL l'inclut déjà
      const response = await api.get('formations/index.php', { 
        params: {
          est_actif: 1,  // Seulement formations actives
          ...params
        }
      })
      console.log('✅ Formations chargées:', response.data)
      return response.data
    } catch (error) {
      console.error('❌ Erreur getAvailableFormations:', error)
      throw error
    }
  },

  // ============ SESSIONS DISPONIBLES ============
  async getAvailableSessions(params = {}) {
    try {
      console.log('📅 Chargement sessions disponibles...')
      // ENLÈVE "api/" ici aussi
      const response = await api.get('sessions/index.php', { 
        params: {
          periode: 'futures',  // Seulement sessions futures
          ...params
        }
      })
      console.log('✅ Sessions chargées:', response.data)
      return response.data
    } catch (error) {
      console.error('❌ Erreur getAvailableSessions:', error)
      throw error
    }
  },

  // ============ S'INSCRIRE À UNE SESSION ============
  async registerToSession(sessionId) {
    try {
      console.log('📝 Inscription à la session:', sessionId)
      // ENLÈVE "api/" ici aussi
      const response = await api.post('inscriptions/create.php', {
        id_session: sessionId
      })
      console.log('✅ Inscription réussie:', response.data)
      return response.data
    } catch (error) {
      console.error('❌ Erreur registerToSession:', error)
      throw error
    }
  },

  // ============ MES INSCRIPTIONS ============
async getMyInscriptions(params = {}) {
  try {
    console.log('📋 Chargement mes inscriptions...')
    // Utilisez la nouvelle API dédiée aux employés
    const response = await api.get('inscriptions/employe.php', { params })
    console.log('✅ Inscriptions chargées:', response.data)
    return response.data
  } catch (error) {
    console.error('❌ Erreur getMyInscriptions:', error)
    throw error
  }
},

  // ============ ANNULER UNE INSCRIPTION ============
  async cancelInscription(id) {
    try {
      console.log('🗑️ Annulation inscription:', id)
      // ENLÈVE "api/" ici aussi
      const response = await api.post(`inscriptions/cancel.php?id=${id}`)
      console.log('✅ Annulation réussie:', response.data)
      return response.data
    } catch (error) {
      console.error('❌ Erreur cancelInscription:', error)
      throw error
    }
  },

  // ============ MON PROFIL ============
// ============ MON PROFIL ============
// employe.js - version corrigée
async getMonProfil() {
  try {
    console.log('👤 Chargement profil employé...')
    const response = await api.get('employe/profil/mon-profil.php')
    console.log('✅ Réponse brute de l\'API:', response)
    
    // Retourner uniquement la réponse de l'API
    // NE PAS AJOUTER de données par défaut
    return response.data
      
  } catch (error) {
    console.error('❌ Erreur getMonProfil:', error)
    
    // Ne retournez PAS de données par défaut
    // Lancez simplement l'erreur
    throw {
      message: 'Impossible de charger le profil',
      error: error
    }
  }
}
}

export default employeService