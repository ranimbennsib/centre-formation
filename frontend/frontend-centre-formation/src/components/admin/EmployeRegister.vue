<template>
  <div class="employe-register">
    <div class="card shadow">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
          <i class="bi bi-person-plus me-2"></i>
          Créer un Nouvel Employé
        </h5>
      </div>
      
      <div class="card-body">
        <form @submit.prevent="registerEmploye">
          <!-- Informations personnelles -->
          <div class="row mb-4">
            <div class="col-12">
              <h6 class="border-bottom pb-2 mb-3">
                <i class="bi bi-person-vcard me-2"></i>
                Informations personnelles
              </h6>
            </div>
            
            <div class="col-md-6 mb-3">
              <label class="form-label">Nom *</label>
              <input type="text" class="form-control" v-model="formData.nom" required>
            </div>
            
            <div class="col-md-6 mb-3">
              <label class="form-label">Prénom *</label>
              <input type="text" class="form-control" v-model="formData.prenom" required>
            </div>
            
            <div class="col-md-6 mb-3">
              <label class="form-label">CIN (Carte d'Identité) *</label>
              <input type="text" class="form-control" v-model="formData.cin" required
                     placeholder="Ex: AA123456" maxlength="20">
              <small class="text-muted">Numéro unique d'identification</small>
            </div>
            
            <div class="col-md-6 mb-3">
              <label class="form-label">Email *</label>
              <input type="email" class="form-control" v-model="formData.email" required>
              <small class="text-muted">L'employé utilisera cet email pour se connecter</small>
            </div>
          </div>
          
          <!-- Informations professionnelles -->
          <div class="row mb-4">
            <div class="col-12">
              <h6 class="border-bottom pb-2 mb-3">
                <i class="bi bi-briefcase me-2"></i>
                Informations professionnelles
              </h6>
            </div>
            
            <div class="col-md-6 mb-3">
              <label class="form-label">Poste</label>
              <input type="text" class="form-control" v-model="formData.poste" 
                     placeholder="Ex: Développeur, Chef de projet...">
            </div>
            
            <div class="col-md-6 mb-3">
              <label class="form-label">Département</label>
              <input type="text" class="form-control" v-model="formData.departement" 
                     placeholder="Ex: IT, RH, Marketing...">
            </div>
            
            <div class="col-md-6 mb-3">
              <label class="form-label">Date d'embauche</label>
              <input type="date" class="form-control" v-model="formData.date_embauche">
            </div>
          </div>
          
          <!-- Mot de passe -->
          <div class="row mb-4">
            <div class="col-12">
              <h6 class="border-bottom pb-2 mb-3">
                <i class="bi bi-shield-lock me-2"></i>
                Sécurité
              </h6>
            </div>
            
            <div class="col-md-6 mb-3">
              <label class="form-label">Mot de passe *</label>
              <div class="input-group">
                <input :type="showPassword ? 'text' : 'password'" 
                       class="form-control" 
                       v-model="formData.mot_de_passe"
                       required
                       minlength="6"
                       @input="validatePassword">
                <button class="btn btn-outline-secondary" type="button"
                        @click="showPassword = !showPassword">
                  <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                </button>
              </div>
              <small class="text-muted">Minimum 6 caractères</small>
              <div v-if="passwordError" class="text-danger small mt-1">
                <i class="bi bi-exclamation-triangle me-1"></i>
                {{ passwordError }}
              </div>
            </div>
            
            <div class="col-md-6 mb-3">
              <label class="form-label">Confirmation *</label>
              <input type="password" class="form-control" 
                     v-model="formData.confirmation_mdp"
                     required
                     @input="validatePasswordConfirmation">
              <div v-if="confirmationError" class="text-danger small mt-1">
                <i class="bi bi-exclamation-triangle me-1"></i>
                {{ confirmationError }}
              </div>
            </div>
            
            <div class="col-12">
              <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Le mot de passe sera définitif.</strong> L'employé pourra le changer après sa première connexion.
              </div>
            </div>
          </div>
          
          <!-- Actions -->
          <div class="d-flex justify-content-between">
            <button type="button" class="btn btn-secondary" @click="resetForm">
              <i class="bi bi-x-circle me-1"></i>
              Réinitialiser
            </button>
            
            <button type="submit" class="btn btn-primary" :disabled="loading || !isFormValid">
              <span v-if="loading">
                <span class="spinner-border spinner-border-sm me-1"></span>
                Création...
              </span>
              <span v-else>
                <i class="bi bi-check-circle me-1"></i>
                Créer l'employé
              </span>
            </button>
          </div>
        </form>
      </div>
    </div>
    
    <!-- Message de succès -->
    <div v-if="showSuccess" class="modal fade show d-block" 
         style="background: rgba(0,0,0,0.5)" @click.self="closeSuccess">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title">
              <i class="bi bi-check-circle me-2"></i>
              Employé créé avec succès !
            </h5>
            <button type="button" class="btn-close btn-close-white" @click="closeSuccess"></button>
          </div>
          <div class="modal-body">
            <div class="alert alert-success">
              <h6><i class="bi bi-person-check me-2"></i>Employé créé</h6>
              <p class="mb-1"><strong>Nom :</strong> {{ successData.nom }} {{ successData.prenom }}</p>
              <p class="mb-1"><strong>Email :</strong> {{ successData.email }}</p>
              <p class="mb-1"><strong>CIN :</strong> {{ successData.cin }}</p>
              <p class="mb-1"><strong>Poste :</strong> {{ successData.poste || 'Non spécifié' }}</p>
              <p class="mb-0"><strong>Département :</strong> {{ successData.departement || 'Non spécifié' }}</p>
            </div>
            <div class="alert alert-warning">
              <i class="bi bi-exclamation-triangle me-2"></i>
              <strong>Important :</strong> Notez les identifiants de connexion :
              <ul class="mb-0 mt-2">
                <li><strong>Email :</strong> {{ successData.email }}</li>
                <li><strong>Mot de passe :</strong> Celui que vous venez de définir</li>
              </ul>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-success" @click="closeSuccess">
              <i class="bi bi-check me-1"></i>
              OK
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import adminService from '@/services/admin'

export default {
  name: 'EmployeRegister',
  
  setup() {
    const loading = ref(false)
    const showPassword = ref(false)
    const showSuccess = ref(false)
    
    const formData = ref({
      nom: '',
      prenom: '',
      cin: '',
      email: '',
      mot_de_passe: '',
      confirmation_mdp: '',
      poste: '',
      departement: '',
      date_embauche: new Date().toISOString().split('T')[0]
    })
    
    const passwordError = ref('')
    const confirmationError = ref('')
    const successData = ref({})
    
    const validatePassword = () => {
      const password = formData.value.mot_de_passe
      
      if (!password) {
        passwordError.value = 'Le mot de passe est requis'
        return
      }
      
      if (password.length < 6) {
        passwordError.value = 'Le mot de passe doit contenir au moins 6 caractères'
        return
      }
      
      passwordError.value = ''
      validatePasswordConfirmation()
    }
    
    const validatePasswordConfirmation = () => {
      const password = formData.value.mot_de_passe
      const confirmation = formData.value.confirmation_mdp
      
      if (!confirmation) {
        confirmationError.value = 'La confirmation est requise'
        return
      }
      
      if (password !== confirmation) {
        confirmationError.value = 'Les mots de passe ne correspondent pas'
        return
      }
      
      confirmationError.value = ''
    }
    
    const isFormValid = computed(() => {
      return formData.value.nom && 
             formData.value.prenom && 
             formData.value.cin &&
             formData.value.email && 
             formData.value.mot_de_passe && 
             formData.value.confirmation_mdp &&
             formData.value.mot_de_passe === formData.value.confirmation_mdp &&
             formData.value.mot_de_passe.length >= 6
    })
    
    const registerEmploye = async () => {
      if (!isFormValid.value) {
        alert('Veuillez corriger les erreurs dans le formulaire')
        return
      }
      
      loading.value = true
      try {
        console.log('📤 Création employé:', formData.value)
        
        const response = await adminService.createEmploye(formData.value)
        console.log('✅ Réponse création:', response)
        
        if (response.success) {
          successData.value = response.data
          showSuccess.value = true
          resetForm()
        } else {
          throw new Error(response.message)
        }
      } catch (error) {
        console.error('❌ Erreur création employé:', error)
        
        let errorMessage = error.response?.data?.message || 
                          error.message || 
                          'Erreur lors de la création'
        
        if (error.response?.data?.errors) {
          errorMessage += '\n\n' + error.response.data.errors.join('\n')
        }
        
        alert(`❌ ${errorMessage}`)
      } finally {
        loading.value = false
      }
    }
    
    const resetForm = () => {
      formData.value = {
        nom: '',
        prenom: '',
        cin: '',
        email: '',
        mot_de_passe: '',
        confirmation_mdp: '',
        poste: '',
        departement: '',
        date_embauche: new Date().toISOString().split('T')[0]
      }
      passwordError.value = ''
      confirmationError.value = ''
    }
    
    const closeSuccess = () => {
      showSuccess.value = false
    }
    
    return {
      loading,
      showPassword,
      showSuccess,
      formData,
      passwordError,
      confirmationError,
      successData,
      isFormValid,
      validatePassword,
      validatePasswordConfirmation,
      registerEmploye,
      resetForm,
      closeSuccess
    }
  }
}
</script>

<style scoped>
.employe-register {
  max-width: 800px;
  margin: 0 auto;
}

.card-header {
  background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
}

.modal {
  backdrop-filter: blur(3px);
}

.border-bottom {
  border-color: #dee2e6 !important;
}

.alert ul {
  padding-left: 20px;
}

@media (max-width: 768px) {
  .employe-register {
    padding: 0 15px;
  }
}
</style>