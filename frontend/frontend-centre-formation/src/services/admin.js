import api from './api'

const adminService = {
  // ============ FORMATEURS ============
  async getFormateurs(params = {}) {
    console.log('📤 Appel API formateurs avec params:', params)
    try {
      const response = await api.get('formateurs/index.php', { params })
      console.log('📊 Réponse API formateurs:', response.data)
      return response.data
    } catch (error) {
      console.error('❌ Erreur getFormateurs:', error)
      // Retourner des données de test si l'API échoue
      return {
        success: false,
        message: error.message,
        data: []
      }
    }
  },
  
  async getFormateur(id) {
    console.log('📤 Appel API formateur ID:', id)
    try {
      const response = await api.get(`formateurs/detail.php?id=${id}`)
      console.log('👤 Réponse API formateur:', response.data)
      return response.data
    } catch (error) {
      console.error('❌ Erreur getFormateur:', error)
      throw error
    }
  },
  
  async createFormateur(data) {
    console.log('📤 Création formateur:', data)
    try {
      const response = await api.post('formateurs/create.php', data)
      console.log('✅ Réponse création:', response.data)
      return response.data
    } catch (error) {
      console.error('❌ Erreur createFormateur:', error)
      throw error
    }
  },
  
  async updateFormateur(id, data) {
    console.log('📤 Mise à jour formateur ID:', id, 'Data:', data)
    try {
      const response = await api.put(`formateurs/update.php?id=${id}`, data)
      console.log('✅ Réponse mise à jour:', response.data)
      return response.data
    } catch (error) {
      console.error('❌ Erreur updateFormateur:', error)
      throw error
    }
  },
  
 deleteFormateur: async (id, action = 'soft_delete') => {
  try {
    console.log(`🗑️ Suppression formateur ID: ${id}, action: ${action}`)
    
    const url = `formateurs/delete.php?id=${id}`
    
    // AJOUTEZ CE BLOC DE CODE :
    const response = await api.delete(url, {
      data: { action: action } // ← TRÈS IMPORTANT !
    })
    
    console.log('✅ Réponse suppression:', response.data)
    return response.data
  } catch (error) {
    console.error('❌ Erreur service suppression:', error)
    throw error
  }
},
//---------create employée-------------------
// services/admin.js - AJOUTEZ CETTE FONCTION


  
  // ============ FORMATIONS ============
// Dans admin.js, ajoutez ces méthodes :

// ============ FORMATIONS ============
async getFormations(params = {}) {
  console.log('📤 Appel API formations avec params:', params)
  try {
    const response = await api.get('formations/index.php', { params })
    console.log('📚 Réponse API formations:', response.data)
    return response.data
  } catch (error) {
    console.error('❌ Erreur getFormations:', error)
    return {
      success: false,
      message: error.message,
      data: []
    }
  }
},

async getFormation(id) {
  console.log('📤 Appel API formation ID:', id)
  try {
    const response = await api.get(`formations/detail.php?id=${id}`)
    console.log('📖 Réponse API formation:', response.data)
    return response.data
  } catch (error) {
    console.error('❌ Erreur getFormation:', error)
    throw error
  }
},

async createFormation(data) {
  console.log('📤 Création formation:', data)
  try {
    const response = await api.post('formations/create.php', data)
    console.log('✅ Réponse création:', response.data)
    return response.data
  } catch (error) {
    console.error('❌ Erreur createFormation:', error)
    throw error
  }
},

async updateFormation(id, data) {
  console.log('📤 Mise à jour formation ID:', id, 'Data:', data)
  try {
    const response = await api.put(`formations/update.php?id=${id}`, data)
    console.log('✅ Réponse mise à jour:', response.data)
    return response.data
  } catch (error) {
    console.error('❌ Erreur updateFormation:', error)
    throw error
  }
},

async deleteFormation(id) {
  console.log('📤 Suppression formation ID:', id)
  try {
    const response = await api.delete(`formations/delete.php?id=${id}`)
    console.log('✅ Réponse suppression:', response.data)
    return response.data
  } catch (error) {
    console.error('❌ Erreur deleteFormation:', error)
    throw error
  }
},
  // ============ SESSIONS ============
  // Dans admin.js, ajoutez ces méthodes :

// ============ SESSIONS ============
async getSessions(params = {}) {
    console.log('📤 Appel API sessions avec params:', params)
    try {
      const response = await api.get('sessions/index.php', { params })
      console.log('📅 Réponse API sessions:', response.data)
      return response.data
    } catch (error) {
      console.error('❌ Erreur getSessions:', error)
      return {
        success: false,
        message: error.message,
        data: []
      }
    }
  },

  async getSession(id) {
    console.log('📤 Appel API session ID:', id)
    try {
      const response = await api.get(`sessions/detail.php?id=${id}`)
      console.log('📊 Réponse API session:', response.data)
      return response.data
    } catch (error) {
      console.error('❌ Erreur getSession:', error)
      throw error
    }
  },

  async createSession(data) {
    console.log('📤 Création session:', data)
    try {
      const response = await api.post('sessions/create.php', data)
      console.log('✅ Réponse création:', response.data)
      return response.data
    } catch (error) {
      console.error('❌ Erreur createSession:', error)
      throw error
    }
  },

  async updateSession(id, data) {
    console.log('📤 Mise à jour session ID:', id, 'Data:', data)
    try {
      const response = await api.put(`sessions/update.php?id=${id}`, data)
      console.log('✅ Réponse mise à jour:', response.data)
      return response.data
    } catch (error) {
      console.error('❌ Erreur updateSession:', error)
      throw error
    }
  },

  async deleteSession(id) {
    console.log('📤 Suppression session ID:', id)
    try {
      const response = await api.delete(`sessions/delete.php?id=${id}`)
      console.log('✅ Réponse suppression:', response.data)
      return response.data
    } catch (error) {
      console.error('❌ Erreur deleteSession:', error)
      throw error
    }
  },

  async assignFormateurToSession(sessionId, formateurId) {
    console.log('📤 Assignation formateur:', { sessionId, formateurId })
    try {
      const response = await api.post(`sessions/assign.php?session_id=${sessionId}`, {
        id_formateur: formateurId
      })
      console.log('✅ Réponse assignation:', response.data)
      return response.data
    } catch (error) {
      console.error('❌ Erreur assignFormateurToSession:', error)
      throw error
    }
  },

 // Dans admin.js, ajoutez ces méthodes à l'objet adminService :

// ============ INSCRIPTIONS ============
async getInscriptions(params = {}) {
  console.log('📤 Appel API inscriptions avec params:', params)
  try {
    const response = await api.get('inscriptions/index.php', { params })
    console.log('📝 Réponse API inscriptions:', response.data)
    return response.data
  } catch (error) {
    console.error('❌ Erreur getInscriptions:', error)
    return {
      success: false,
      message: error.message,
      data: []
    }
  }
},

async getInscription(id) {
  console.log('📤 Appel API inscription ID:', id)
  try {
    const response = await api.get(`inscriptions/detail.php?id=${id}`)
    console.log('📊 Réponse API inscription:', response.data)
    return response.data
  } catch (error) {
    console.error('❌ Erreur getInscription:', error)
    throw error
  }
},

async validateInscription(id) {
  console.log('✅ Validation inscription ID:', id)
  try {
    const response = await api.post(`inscriptions/validate.php?id=${id}`)
    console.log('✅ Réponse validation:', response.data)
    return response.data
  } catch (error) {
    console.error('❌ Erreur validateInscription:', error)
    throw error
  }
},

async refuseInscription(id, raison) {
  console.log('❌ Refus inscription ID:', id, 'Raison:', raison)
  try {
    const response = await api.post(`inscriptions/refuse.php?id=${id}`, { raison })
    console.log('✅ Réponse refus:', response.data)
    return response.data
  } catch (error) {
    console.error('❌ Erreur refuseInscription:', error)
    throw error
  }
},

async deleteInscription(id) {
  console.log('🗑️ Suppression inscription ID:', id)
  try {
    const response = await api.delete(`inscriptions/delete.php?id=${id}`)
    console.log('✅ Réponse suppression:', response.data)
    return response.data
  } catch (error) {
    console.error('❌ Erreur deleteInscription:', error)
    throw error
  }
},
  
 // ============ STATISTIQUES ============
async getStats(params = {}) {
  console.log('📊 Appel API statistiques avec params:', params)
  try {
    const response = await api.get('inscriptions/statistiques.php', { params })
    console.log('📈 Réponse API statistiques:', response.data)
    return response.data
  } catch (error) {
    console.error('❌ Erreur getStats:', error)
    return {
      success: false,
      message: error.message,
      data: null
    }
  }
},
/************admin/employe=> crud *********** */
// ============ EMPLOYÉS ============
  async getEmployes(params = {}) {
    console.log('📤 Appel API employés avec params:', params)
    try {
      // Note: Créer cet endpoint backend
      const response = await api.get('admin/employes/index.php', { params })
      console.log('👥 Réponse API employés:', response.data)
      return response.data
    } catch (error) {
      console.error('❌ Erreur getEmployes:', error)
      return {
        success: true,
        data: [],
        message: 'Endpoint non implémenté'
      }
    }
  },

  async getEmploye(id) {
    console.log('📤 Appel API employé ID:', id)
    try {
      // Note: Créer cet endpoint backend
      const response = await api.get(`admin/employes/detail.php?id=${id}`)
      console.log('👤 Réponse API employé:', response.data)
      return response.data
    } catch (error) {
      console.error('❌ Erreur getEmploye:', error)
      throw error
    }
  },

  async createEmploye(data) {
    try {
      console.log('👤 Création employé:', data)
      
      const dataToSend = { ...data }
      delete dataToSend.confirmation_mdp
      delete dataToSend.confirmation_nouveau_mdp
      
      // Note: Utilisez votre endpoint existant
      const response = await api.post('auth/register.php', dataToSend)
      
      console.log('✅ Employé créé:', response.data)
      return response.data
    } catch (error) {
      console.error('❌ Erreur création employé:', error)
      throw error
    }
  },

 // Dans admin.js, modifiez la fonction updateEmploye :
async updateEmploye(id, data) {
  console.log('📤 Mise à jour employé ID:', id, 'Data:', data)
  try {
    // AJOUTEZ CETTE LIGNE POUR DEBUG
    console.log('🔗 BaseURL d\'axios:', api.defaults.baseURL)
    console.log('🔗 URL complète devrait être:', 
      api.defaults.baseURL + `admin/employes/update.php?id=${id}`
    )
    
    // Option 1: Utilisez l'URL avec le slash initial
    // const response = await api.put(`/admin/employes/update.php?id=${id}`, data)
    
    // Option 2: Telle qu'elle est
    const response = await api.put(`admin/employes/update.php?id=${id}`, data)
    
    console.log('✅ Réponse mise à jour:', response.data)
    return response.data
  } catch (error) {
    console.error('❌ Erreur updateEmploye DÉTAILLÉE:')
    console.error('- URL tentée:', error.config?.url)
    console.error('- BaseURL:', api.defaults.baseURL)
    console.error('- URL complète:', api.defaults.baseURL + error.config?.url)
    console.error('- Méthode:', error.config?.method)
    console.error('- Status:', error.response?.status)
    console.error('- Response data:', error.response?.data)
    console.error('- Headers envoyés:', error.config?.headers)
    
    throw error
  }
},

  async deleteEmploye(id, action = 'soft_delete') {
    try {
      console.log(`🗑️ Suppression employé ID: ${id}, action: ${action}`)
      
      // Note: Créer cet endpoint backend
      const response = await api.delete(`admin/employes/delete.php?id=${id}`, {
        data: { action: action }
      })
      
      console.log('✅ Réponse suppression:', response.data)
      return response.data
    } catch (error) {
      console.error('❌ Erreur deleteEmploye:', error)
      throw error
    }
  },

  async restoreEmploye(id) {
    try {
      console.log(`🔄 Restauration employé ID: ${id}`)
      
      // Note: Créer cet endpoint backend
      const response = await api.post(`admin/employes/restore.php?id=${id}`)
      
      console.log('✅ Réponse restauration:', response.data)
      return response.data
    } catch (error) {
      console.error('❌ Erreur restoreEmploye:', error)
      throw error
    }
  },
}

export default adminService