<template>
  <div class="login-container">
    <div class="row g-0 min-vh-100">
      <!-- Colonne gauche - Illustration -->
      <div class="col-md-6 d-none d-md-flex login-left">
        <div class="login-left-content d-flex flex-column justify-content-center align-items-center p-5">
          <div class="logo-container mb-5">
            <img src="../assets/logoStir.png" 
                 alt="STIR Bizerte Logo" 
                 class="logo-img ">
          </div>
          <h1 class="text-white display-4 fw-bold mb-3 text-center">STIR BIZERTE</h1>
          <h2 class="text-white mb-4 text-center">Centre de Formation</h2>
          <div class="features-list">
            <div class="feature-item">
              <i class="bi bi-shield-check text-white fs-4"></i>
              <span class="text-white ms-3">Plateforme sécurisée</span>
            </div>
            <div class="feature-item">
              <i class="bi bi-graph-up text-white fs-4"></i>
              <span class="text-white ms-3">Suivi pédagogique</span>
            </div>
            <div class="feature-item">
              <i class="bi bi-people text-white fs-4"></i>
              <span class="text-white ms-3">Gestion multi-utilisateurs</span>
            </div>
          </div>
          <div class="mt-5">
            <p class="text-light text-center">© 2024 STIR Bizerte. Tous droits réservés.</p>
          </div>
        </div>
      </div>
      
      <!-- Colonne droite - Formulaire -->
      <div class="col-md-6 d-flex align-items-center login-right">
        <div class="login-form-container w-100 p-4 p-md-5">
          <!-- En-tête -->
          <div class="text-center mb-5">
            <h2 class="fw-bold text-primary">CONNEXION</h2>
            <p class="text-muted">Accédez à votre espace de formation</p>
          </div>
          
          <!-- Formulaire -->
          <form @submit.prevent="login" class="needs-validation" novalidate>
            <!-- Email -->
            <div class="mb-4">
              <label for="email" class="form-label fw-medium">
                <i class="bi bi-envelope me-2"></i>Adresse Email
              </label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0">
                  <i class="bi bi-person text-muted"></i>
                </span>
                <input 
                  type="email" 
                  id="email"
                  v-model="email"
                  class="form-control py-3"
                  :class="{'is-invalid': errors.email}"
                  placeholder="exemple@stir-formation.tn"
                  required
                  :disabled="loading"
                />
              </div>
              <div v-if="errors.email" class="invalid-feedback d-block">
                {{ errors.email }}
              </div>
            </div>
            
            <!-- Mot de passe -->
            <div class="mb-4">
              <label for="password" class="form-label fw-medium">
                <i class="bi bi-key me-2"></i>Mot de passe
              </label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0">
                  <i class="bi bi-lock text-muted"></i>
                </span>
                <input 
                  :type="showPassword ? 'text' : 'password'"
                  id="password"
                  v-model="password"
                  class="form-control py-3 border-start-0"
                  :class="{'is-invalid': errors.password}"
                  placeholder="Votre mot de passe"
                  required
                  :disabled="loading"
                />
                <button 
                  type="button" 
                  class="input-group-text bg-light"
                  @click="showPassword = !showPassword"
                >
                  <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                </button>
              </div>
              <div v-if="errors.password" class="invalid-feedback d-block">
                {{ errors.password }}
              </div>
            </div>
            
            <!-- Bouton de connexion -->
            <button 
              type="submit" 
              class="btn btn-primary btn-lg w-100 py-3 fw-bold"
              :disabled="loading"
            >
              <span v-if="!loading">
                <i class="bi bi-box-arrow-in-right me-2"></i>Se Connecter
              </span>
              <span v-else>
                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                Connexion en cours...
              </span>
            </button>
            
            <!-- Messages d'erreur -->
            <div v-if="errorMessage" class="alert alert-danger alert-dismissible fade show mt-4" role="alert">
              <i class="bi bi-exclamation-triangle-fill me-2"></i>
              {{ errorMessage }}
              <button type="button" class="btn-close" @click="errorMessage = ''"></button>
            </div>
          </form>
          
         
          
          <!-- Footer -->
          <div class="mt-5 text-center">
            <p class="text-muted">
              <small>
                Version 1.0.0 | 
                <i class="bi bi-telephone ms-2 me-1"></i>Support: +216 72 592 744 |
                <i class="bi bi-envelope ms-2 me-1"></i>centre.formation@stir.com.tn
              </small>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import authService from '@/services/auth'

export default {
  name: 'LoginPage',
  
  setup() {
    const router = useRouter()
    
    // États réactifs
    const email = ref('')
    const password = ref('')
    const showPassword = ref(false)
    const loading = ref(false)
    const errorMessage = ref('')
    const errors = ref({})
    
    // Remplir automatiquement les champs pour test
    const fillCredentials = (type) => {
      switch(type) {
        case 'admin':
          email.value = 'admin@test.com'
          password.value = 'password'
          break
        case 'formateur':
          email.value = 'formateur@test.com'
          password.value = 'password'
          break
        case 'employe':
          email.value = 'employe@test.com'
          password.value = 'password'
          break
      }
      // Fermer l'accordéon
      const accordion = document.getElementById('accountsCollapse')
      if (accordion) {
        accordion.classList.remove('show')
      }
    }
    
    // Validation
    const validateForm = () => {
      errors.value = {}
      let isValid = true
      
      if (!email.value) {
        errors.value.email = 'L\'email est requis'
        isValid = false
      } else if (!/\S+@\S+\.\S+/.test(email.value)) {
        errors.value.email = 'Format d\'email invalide'
        isValid = false
      }
      
      if (!password.value) {
        errors.value.password = 'Le mot de passe est requis'
        isValid = false
      }
      
      return isValid
    }
    
    // Login
    const login = async () => {
      if (!validateForm()) return
      
      loading.value = true
      errorMessage.value = ''
      
      try {
        console.log('Tentative de connexion avec:', email.value)
        
        const response = await authService.login({
          email: email.value,
          password: password.value
        })
        
        console.log('Réponse login:', response)
        
        if (response.success) {
          const role = response.user.role
          console.log('Rôle détecté:', role)
          
          // Redirection IMMÉDIATE selon le rôle
          switch (role) {
            case 'admin':
              router.push('/admin')
              break
            case 'formateur':
              router.push('/formateur')
              break
            case 'employe':
              router.push('/employe')
              break
            default:
              router.push('/dashboard')
          }
        }
        
      } catch (err) {
        console.error('Erreur détaillée:', err)
        errorMessage.value = err.message || 'Erreur lors de la connexion'
      } finally {
        loading.value = false
      }
    }
    
    return {
      email,
      password,
      showPassword,
      loading,
      errorMessage,
      errors,
      fillCredentials,
      login
    }
  }
}
</script>

<style scoped>
.login-container {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: #f8f9fa;
}

.login-left {
  background: linear-gradient(135deg, #0056A3 0%, #003366 100%);
  position: relative;
  overflow: hidden;
}

.login-left::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -50%;
  width: 100%;
  height: 200%;
  background: rgba(255, 255, 255, 0.05);
  transform: rotate(30deg);
}

.logo-img {
  width: 150px;
  height: 150px;
  object-fit: cover;
  
  padding: 5px;
}

.features-list {
  max-width: 400px;
}

.feature-item {
  display: flex;
  align-items: center;
  padding: 15px 20px;
  margin: 10px 0;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  transition: all 0.3s;
}

.feature-item:hover {
  background: rgba(255, 255, 255, 0.15);
  transform: translateX(10px);
}

.login-right {
  background: #ffffff;
}

.login-form-container {
  max-width: 500px;
  margin: 0 auto;
}

.btn-primary {
  background: linear-gradient(135deg, #0056A3 0%, #003366 100%);
  border: none;
  transition: all 0.3s;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0, 86, 163, 0.4);
}

.btn-primary:disabled {
  background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
  transform: none;
  box-shadow: none;
}

.input-group-text {
  transition: all 0.3s;
}

.input-group:focus-within .input-group-text {
  background-color: #e3f2fd;
  border-color: #0056A3;
}

.form-control:focus {
  border-color: #0056A3;
  box-shadow: 0 0 0 0.25rem rgba(0, 86, 163, 0.25);
}

.alert {
  border-radius: 10px;
  border: none;
}

.card {
  border-radius: 10px;
  transition: transform 0.3s;
}

.card:hover {
  transform: translateY(-5px);
}

/* Responsive */
@media (max-width: 768px) {
  .login-left {
    display: none !important;
  }
  
  .login-form-container {
    padding: 2rem !important;
  }
  
  .accordion-body .row {
    display: block;
  }
  
  .accordion-body .col-md-4 {
    margin-bottom: 1rem;
  }
}

/* Animation pour le chargement */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.login-form-container {
  animation: fadeIn 0.5s ease-out;
}

/* Styles pour Bootstrap Icons */
.bi {
  vertical-align: -0.125em;
}
</style>