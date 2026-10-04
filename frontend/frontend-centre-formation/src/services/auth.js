import api from './api'

const authService = {
  async login(credentials) {
    try {
      console.log('🔐 Tentative connexion:', credentials)
      
      // Appel API CORRECT - notez l'URL
      const response = await api.post('auth/login.php', credentials)
      
      console.log('✅ Réponse login:', response.data)
      
      if (response.data.success && response.data.user) {
        // Normaliser le rôle
        const user = {
          ...response.data.user,
          role: (response.data.user.role || '').toLowerCase()
        }
        
        console.log('👤 Utilisateur connecté:', user)
        
        // Stocker
        localStorage.setItem('user', JSON.stringify(user))
        localStorage.setItem('token', response.data.session_id || 'temp')
        
        return {
          success: true,
          user: user,
          message: response.data.message
        }
      } else {
        throw new Error(response.data.message || 'Échec connexion')
      }
      
    } catch (error) {
      console.error('❌ Détails erreur login:', {
        message: error.message,
        response: error.response?.data,
        status: error.response?.status
      })
      
      let errorMessage = 'Erreur connexion'
      
      if (error.response) {
        // Erreur du serveur
        errorMessage = error.response.data?.message || `Erreur ${error.response.status}`
      } else if (error.request) {
        // Pas de réponse
        errorMessage = 'Serveur non accessible. Vérifiez WAMP et connexion.'
      } else {
        // Autre erreur
        errorMessage = error.message
      }
      
      throw new Error(errorMessage)
    }
  },

  logout() {
    localStorage.removeItem('user')
    localStorage.removeItem('token')
  },

  isLoggedIn() {
    return !!localStorage.getItem('user')
  },

  getUser() {
    const userStr = localStorage.getItem('user')
    try {
      return userStr ? JSON.parse(userStr) : null
    } catch {
      return null
    }
  },

  getUserRole() {
    const user = this.getUser()
    return user ? user.role : null
  },

  isAdmin() {
    return this.getUserRole() === 'admin'
  },

  isFormateur() {
    return this.getUserRole() === 'formateur'
  },

  isEmploye() {
    return this.getUserRole() === 'employe'
  }
}

export default authService