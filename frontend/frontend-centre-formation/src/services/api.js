import axios from 'axios'

const API_BASE_URL = 'http://localhost/centre-formation/backend/api'
console.log('🌐 API Base URL configurée:', API_BASE_URL)

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  timeout: 15000,
  withCredentials: false // ← TRÈS IMPORTANT: doit correspondre au backend
})

// Intercepteur pour envoyer l'ID utilisateur
api.interceptors.request.use(
  (config) => {
    // Récupérer l'utilisateur connecté
    try {
      const userStr = localStorage.getItem('user')
      if (userStr) {
        const user = JSON.parse(userStr)
        if (user && user.id) {
          config.headers['X-User-Id'] = user.id
          // Optionnel: ajouter le rôle aussi
          if (user.role) {
            config.headers['X-User-Role'] = user.role
          }
          console.log('👤 Headers utilisateur ajoutés:', { id: user.id, role: user.role })
        }
      }
    } catch (error) {
      console.warn('Erreur parsing user:', error)
    }
    
    console.log(`🚀 ${config.method.toUpperCase()} ${config.url}`)
    console.log('📤 Headers:', config.headers)
    return config
  },
  (error) => {
    console.error('❌ Erreur requête:', error)
    return Promise.reject(error)
  }
)

// Intercepteur pour gérer les réponses
api.interceptors.response.use(
  (response) => {
    console.log(`✅ ${response.status} de ${response.config.url}`)
    return response
  },
  (error) => {
    console.error('❌ Erreur API DÉTAILLÉE:')
    console.error('URL:', error.config?.url)
    console.error('Méthode:', error.config?.method)
    console.error('Status:', error.response?.status)
    console.error('Message:', error.message)
    console.error('Headers:', error.config?.headers)
    
    // Si erreur CORS
    if (error.message === 'Network Error' && !error.response) {
      console.error('🚨 Problème CORS détecté!')
      console.error('Assurez-vous que:')
      console.error('1. Le backend autorise l\'origine: http://localhost:8081')
      console.error('2. withCredentials: false (frontend) correspond à Allow-Credentials: false (backend)')
      console.error('3. Les headers personnalisés sont listés dans Access-Control-Allow-Headers')
    }
    
    return Promise.reject(error)
  }
)

export default api