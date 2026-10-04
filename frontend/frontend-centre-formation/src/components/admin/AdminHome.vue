<template>
  <div class="admin-home">
    <!-- En-tête -->
  <!-- Accès rapide (NOUVELLE SECTION) -->
    <div class="row mb-4">
     <div class="col-12 text-center">
  <h5 class="fs-2 fw-bold" style="color:#3B5EDB;">Vue d'ensemble </h5>
<hr class="w-25 mx-auto opacity-100 page-title-hr ">





</div>

      
      <!--*********box1**********-->
  <!-- Box 1 Formateurs -->
<div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden formateurs-box">
        <!-- Animation de la bordure qui tourne -->
        <div class="position-absolute top-0 start-0 w-100 h-100 border-animation"></div>
        
        <div class="card-body text-center d-flex flex-column justify-content-between p-4">
            <!-- Ovale avec le nombre -->
            <div class="d-flex flex-column align-items-center mb-3">
                <div class="oval-container d-flex align-items-center justify-content-center">
                    <div class="bg-primary bg-opacity-10 rounded-pill p-4 d-inline-block position-relative border-animation-inner">
                        <div class="h3 mb-0 fw-bold text-primary">{{ stats.formateurs || '0' }}</div>
                    </div>
                </div>
            </div>
            
            <!-- Texte "Formateurs" -->
            <div>
               <i class="bi bi-people text-primary fs-1"></i>
                <h5 class="text-primary fw-bold text-uppercase mb-0">Formateurs</h5>
            </div>
        </div>
    </div>
</div>

<!-- Box 2 Formations -->
<div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden formations-box">
        <!-- Animation de la bordure qui tourne -->
        <div class="position-absolute top-0 start-0 w-100 h-100 border-animation"></div>
        
        <div class="card-body text-center d-flex flex-column justify-content-between p-4">
            <!-- Ovale avec le nombre -->
            <div class="d-flex flex-column align-items-center mb-3">
                <div class="oval-container d-flex align-items-center justify-content-center">
                    <div class="bg-success bg-opacity-10 rounded-pill p-4 d-inline-block position-relative border-animation-inner">
                        <div class="h3 mb-0 fw-bold text-success">{{ stats.formations || '0' }}</div>
                    </div>
                </div>
            </div>
            
            <!-- Texte "Formations" -->
            <div>
               <i class="bi bi-journal-text text-success fs-1"></i>
                <h5 class="text-success fw-bold text-uppercase mb-0">Formations</h5>
            </div>
        </div>
    </div>
</div>

<!-- Box 3 Sessions -->
<div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden sessions-box">
        <!-- Animation de la bordure qui tourne -->
        <div class="position-absolute top-0 start-0 w-100 h-100 border-animation"></div>
        
        <div class="card-body text-center d-flex flex-column justify-content-between p-4">
            <!-- Ovale avec le nombre -->
            <div class="d-flex flex-column align-items-center mb-3">
                <div class="oval-container d-flex align-items-center justify-content-center">
                    <div class="bg-warning bg-opacity-10 rounded-pill p-4 d-inline-block position-relative border-animation-inner">
                        <div class="h3 mb-0 fw-bold text-warning">{{ stats.sessions || '0' }}</div>
                    </div>
                </div>
            </div>
            
            <!-- Texte "Sessions" -->
            <div>
                <i class="bi bi-calendar-check text-warning fs-1"></i>
                <h5 class="text-warning fw-bold text-uppercase mb-0">Sessions</h5>
            </div>
        </div>
    </div>
</div>

<!-- Box 4 Inscriptions -->
<div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden inscriptions-box">
        <!-- Animation de la bordure qui tourne -->
        <div class="position-absolute top-0 start-0 w-100 h-100 border-animation"></div>
        
        <div class="card-body text-center d-flex flex-column justify-content-between p-4">
            <!-- Ovale avec le nombre -->
            <div class="d-flex flex-column align-items-center mb-3">
                <div class="oval-container d-flex align-items-center justify-content-center">
                    <div class="bg-info bg-opacity-10 rounded-pill p-4 d-inline-block position-relative border-animation-inner">
                        <div class="h3 mb-0 fw-bold text-info">{{ stats.inscriptions || '0' }}</div>
                    </div>
                </div>
            </div>
            
            <!-- Texte "Inscriptions" -->
            <div>
                <i class="bi bi-clipboard-check text-info fs-1"></i>
                <h5 class="text-info fw-bold text-uppercase mb-0">Inscriptions</h5>
            </div>
        </div>
    </div>
</div>
</div>
    

    
    <!-- Accès rapide (NOUVELLE SECTION) -->
   
       
    
    <!-- Messages d'état -->
    <div v-if="apiError" class="alert alert-danger">
      <i class="bi bi-exclamation-triangle me-2"></i>
      Erreur de connexion à la base de données: {{ apiError }}
    </div>
    
    <div v-else-if="!loading && hasData" class="alert alert-success">
      <i class="bi bi-check-circle me-2"></i>
      Connecté à la base de données - {{ totalItems }} éléments chargés
    </div>
    
    <div v-else-if="!loading && !hasData" class="alert alert-warning">
      <i class="bi bi-info-circle me-2"></i>
      Aucune donnée trouvée dans la base de données
    </div>
    
    <!-- Sessions Récentes -->
<!-- Sessions Récentes -->
 <div class="row">
<div class="col-lg-8 mb-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">
                   <i class="bi bi-calendar-check text-primary  me-2"></i>
                    
                    Sessions Récentes
                </h5>
            </div>
            <div>
                <button @click="loadData" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>
        </div>
        
        <div class="card-body p-0">
            <!-- État de chargement -->
            <div v-if="loadingSessions" class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Chargement...</p>
            </div>
            
            <!-- État vide -->
            <div v-else-if="sessions.length === 0" class="text-center py-5">
                <i class="bi bi-calendar-x fs-1 text-muted mb-3"></i>
                <p class="text-muted">Aucune session disponible</p>
            </div>
            
            <!-- Tableau -->
            <div v-else class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Formation</th>
                            <th>Dates</th>
                            <th>Salle</th>
                            <th>Statut</th>
                          <!--<th class="d-none-mobile">Actions</th>-->  
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="session in sessions" :key="session.id">
                            <td>
                                <div class="fw-semibold">{{ session.formation_titre || 'Non spécifié' }}</div>
                                <small class="text-muted">{{ session.formateur || 'Formateur non assigné' }}</small>
                            </td>
                            <td>
                                <div>
                                    <small>{{ formatDate(session.date_debut) }}</small>
                                    <span class="mx-1">→</span>
                                    <small>{{ formatDate(session.date_fin) }}</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    {{ session.salle || 'Non défini' }}
                                </span>
                            </td>
                            <td>
                                <span :class="`badge bg-${getStatusBadge(session.statut_formateur)}`">
                                    {{ getStatusLabel(session.statut_formateur) }}
                                </span>
                            </td>
                           <!--
                            <td class="d-none-mobile">
                                <button class="btn btn-sm btn-outline-secondary" @click="viewDetails(session)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                           
                           --> 
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">{{ sessions.length }} session(s)</small>
               
                 <router-link to="/admin/sessions" class="btn btn-sm btn-link text-decoration-none">
                   Voir tout <i class="bi bi-arrow-right ms-1"></i>
                </router-link>
            </div>
        </div>
    </div>
</div>
      
      <!-- Inscriptions en attente -->
      <div class="col-lg-4 mb-4">
        <div class="card shadow border-0 h-100">
          <div class="card-header bg-white border-0 py-3">
            <h5 class="card-title mb-0 text-info">
              <i class="bi bi-clock-history text-info me-2"></i>
              Inscriptions en attente
            </h5>
          </div>
          <div class="card-body">
            <div v-if="loadingInscriptions" class="text-center py-5">
              <div class="spinner-border text-warning" role="status"></div>
            </div>
            
            <div v-else-if="inscriptions.length === 0" class="text-center py-5 text-muted">
              <i class="bi bi-check-circle fs-1 mb-3"></i>
              <p>Aucune inscription en attente</p>
            </div>
            
            <div v-else>
              <div v-for="inscription in inscriptions" :key="inscription.id" 
                   class="d-flex align-items-center mb-3 p-3 border rounded">
                <div class="flex-grow-1">
                  <h6 class="mb-1">
                    {{ inscription.employe_nom || 'Nom' }} {{ inscription.employe_prenom || 'Prénom' }}
                  </h6>
                  <p class="mb-0 text-muted small">
                    {{ inscription.formation_titre || 'Formation' }}
                  </p>
                  <small class="text-muted">
                    Inscrit le {{ formatDate(inscription.date_inscription) }}
                  </small>
                </div>
                <button @click="showInscriptionDetails(inscription)" 
                        class="btn btn-sm btn-outline-info">
                  <i class="bi bi-info-circle"></i>
                </button>
              </div>
              <div class="text-center mt-3">
                <small class="text-muted">
                  {{ inscriptions.length }} inscription(s) en attente
                </small>
              </div>
            </div>
          </div>
        </div>
      </div>
 </div>

    </div>
    
    <!-- Bouton debug (optionnel, visible seulement en dev) -->
    <div v-if="showDebug" class="text-center mt-4">
      <button @click="toggleDebug" class="btn btn-sm btn-outline-info">
        <i class="bi bi-bug me-1"></i>
        {{ debugVisible ? 'Masquer' : 'Afficher' }} les infos debug
      </button>
    </div>
    
    <!-- Section debug (optionnelle) -->
    <div v-if="debugVisible && showDebug" class="card mt-4 border-info">
      <div class="card-header bg-info text-white">
        <i class="bi bi-bug me-2"></i>Informations de debug
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <h6>État API:</h6>
            <pre class="bg-light p-2 small">{{ JSON.stringify(apiStatus, null, 2) }}</pre>
          </div>
          <div class="col-md-6">
            <h6>Données brutes:</h6>
            <pre class="bg-light p-2 small">{{ JSON.stringify(rawData, null, 2) }}</pre>
          </div>
        </div>
      </div>
    </div>
  
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import authService from '@/services/auth'
import adminService from '@/services/admin'

export default {
  name: 'AdminHome',
  
  setup() {
    // États
    const user = ref({})
    const loading = ref(true)
    const loadingSessions = ref(true)
    const loadingInscriptions = ref(true)
    const apiError = ref('')
    const debugVisible = ref(false)
    
    const stats = ref({
      formateurs: 0,
      formations: 0,
      sessions: 0,
      inscriptions: 0
    })
    
    const sessions = ref([])
    const inscriptions = ref([])
    
    // Pour le debug
    const apiStatus = ref({})
    const rawData = ref({})
    
    // Computed
    const hasData = computed(() => {
      return sessions.value.length > 0 || inscriptions.value.length > 0
    })
    
    const totalItems = computed(() => {
      return sessions.value.length + inscriptions.value.length
    })
    
    // Pour savoir si on est en mode développement
    const showDebug = ref(import.meta.env.MODE === 'development')
    
    // Méthodes
    const loadData = async () => {
      console.log('🔄 Chargement des données depuis la base de données...')
      loading.value = true
      apiError.value = ''
      
      try {
        // 1. Charger les formateurs
        console.log('📤 Chargement formateurs...')
        const formateursRes = await adminService.getFormateurs()
        apiStatus.value.formateurs = {
          success: formateursRes.success,
          count: formateursRes.data?.length || 0
        }
        
        if (formateursRes.success) {
          stats.value.formateurs = formateursRes.count || formateursRes.data?.length || 0
          rawData.value.formateurs = formateursRes.data?.slice(0, 2)
          console.log(`✅ ${stats.value.formateurs} formateur(s) trouvé(s)`)
        }
        
        // 2. Charger les formations
        console.log('📤 Chargement formations...')
        const formationsRes = await adminService.getFormations()
        apiStatus.value.formations = {
          success: formationsRes.success,
          count: formationsRes.data?.length || 0
        }
        
        if (formationsRes.success) {
          stats.value.formations = formationsRes.count || formationsRes.data?.length || 0
          rawData.value.formations = formationsRes.data?.slice(0, 2)
        }
        
        // 3. Charger les sessions
        await loadSessions()
        
        // 4. Charger les inscriptions
        await loadInscriptions()
        
        console.log('✅ Données chargées avec succès')
        console.log(`📊 Résumé: ${stats.value.formateurs} formateurs, ${stats.value.formations} formations`)
        
      } catch (error) {
        console.error('❌ Erreur chargement données:', error)
        apiError.value = error.message || 'Erreur de connexion à la base de données'
      } finally {
        loading.value = false
      }
    }
    
    const loadSessions = async () => {
      loadingSessions.value = true
      try {
        console.log('📤 Chargement sessions...')
        const sessionsRes = await adminService.getSessions()
        apiStatus.value.sessions = {
          success: sessionsRes.success,
          count: sessionsRes.data?.length || 0
        }
        
        if (sessionsRes.success && sessionsRes.data) {
          sessions.value = sessionsRes.data.slice(0, 5)
          stats.value.sessions = sessionsRes.data.length
          rawData.value.sessions = sessionsRes.data.slice(0, 2)
        } else {
          sessions.value = []
        }
      } catch (error) {
        console.error('Erreur chargement sessions:', error)
        sessions.value = []
      } finally {
        loadingSessions.value = false
      }
    }
    
    const loadInscriptions = async () => {
      loadingInscriptions.value = true
      try {
        console.log('📤 Chargement inscriptions...')
        const inscriptionsRes = await adminService.getInscriptions({ 
          statut: 'EN_ATTENTE' 
        })
        apiStatus.value.inscriptions = {
          success: inscriptionsRes.success,
          count: inscriptionsRes.data?.length || 0
        }
        
        if (inscriptionsRes.success && inscriptionsRes.data) {
          inscriptions.value = inscriptionsRes.data.slice(0, 3)
          stats.value.inscriptions = inscriptionsRes.data.length
          rawData.value.inscriptions = inscriptionsRes.data.slice(0, 2)
        } else {
          inscriptions.value = []
        }
      } catch (error) {
        console.error('Erreur chargement inscriptions:', error)
        inscriptions.value = []
      } finally {
        loadingInscriptions.value = false
      }
    }
    
    const formatDate = (dateString) => {
      if (!dateString) return 'Non défini'
      try {
        const date = new Date(dateString)
        return date.toLocaleDateString('fr-FR')
      } catch (error) {
        return dateString
      }
    }
    
    const getStatusLabel = (status) => {
      const statusMap = {
        'EN_ATTENTE': 'En attente',
        'ACCEPTEE': 'Acceptée',
        'REFUSEE': 'Refusée',
        'ANNULEE': 'Annulée'
      }
      return statusMap[status] || status || 'Non défini'
    }
    
    const getStatusBadge = (status) => {
      const badgeMap = {
        'EN_ATTENTE': 'warning',
        'ACCEPTEE': 'success',
        'REFUSEE': 'danger',
        'ANNULEE': 'secondary'
      }
      return badgeMap[status] || 'primary'
    }
    
    const showInscriptionDetails = (inscription) => {
      console.log('Détails inscription:', inscription)
      alert(`Détails inscription #${inscription.id}\n\nEmployé: ${inscription.employe_nom} ${inscription.employe_prenom}\nFormation: ${inscription.formation_titre}\nDate: ${formatDate(inscription.date_inscription)}`)
    }
    
    const toggleDebug = () => {
      debugVisible.value = !debugVisible.value
    }
    
    // Fonction de test pour vérifier les formateurs
    const testFormateursAPI = async () => {
      console.log('🧪 Test API Formateurs...')
      try {
        const response = await adminService.getFormateurs()
        console.log('🧪 Résultat test:', {
          success: response.success,
          count: response.count,
          dataLength: response.data?.length,
          data: response.data
        })
        
        if (response.data) {
          response.data.forEach((formateur, index) => {
            console.log(`🧪 Formateur ${index + 1}:`, {
              id: formateur.id,
              nom: formateur.nom,
              prenom: formateur.prenom,
              email: formateur.email
            })
          })
        }
      } catch (error) {
        console.error('🧪 Erreur test:', error)
      }
    }
    
    // Lifecycle
    onMounted(async () => {
      user.value = authService.getUser() || {}
      console.log('👤 Utilisateur connecté:', user.value)
      console.log('🔗 Mode:', import.meta.env.MODE)
      await loadData()
      await testFormateursAPI()
    })
    

// Formater la date au format court (JJ/MM)
const formatDateShort = (dateString) => {
    if (!dateString) return '--/--'
    try {
        const date = new Date(dateString)
        return date.toLocaleDateString('fr-FR', { 
            day: '2-digit', 
            month: '2-digit' 
        })
    } catch (error) {
        return '--/--'
    }
}

// Obtenir la couleur du badge selon le statut (pour les sessions)
const getSessionStatusBadge = (status) => {
    // Adaptez cette fonction selon les statuts de vos sessions
    const badgeMap = {
        'planifie': 'bg-info',
        'en_cours': 'bg-success',
        'termine': 'bg-secondary',
        'annule': 'bg-danger',
        // Ajoutez vos statuts spécifiques ici
        'PLANIFIE': 'bg-info',
        'EN_COURS': 'bg-success',
        'TERMINE': 'bg-secondary',
        'ANNULE': 'bg-danger'
    }
    return badgeMap[status] || 'bg-secondary'
}

// Obtenir le libellé du statut (pour les sessions)
const getSessionStatusLabel = (status) => {
    const labelMap = {
        'planifie': 'Planifié',
        'en_cours': 'En cours',
        'termine': 'Terminé',
        'annule': 'Annulé',
        // Ajoutez vos statuts spécifiques ici
        'PLANIFIE': 'Planifié',
        'EN_COURS': 'En cours',
        'TERMINE': 'Terminé',
        'ANNULE': 'Annulé'
    }
    return labelMap[status] || status || 'Inconnu'
}

// Voir les détails d'une session
const viewSessionDetails = (session) => {
    console.log('Voir détails session:', session)
    // À adapter selon votre logique
    alert(`Détails session #${session.id}\n\nFormation: ${session.formation_titre}\nFormateur: ${session.formateur_nom || 'Non assigné'}\nDates: ${formatDateShort(session.date_debut)} → ${formatDateShort(session.date_fin)}`)
}

// Fonction pour obtenir l'icône de la salle
const getRoomIcon = (salle) => {
    return salle ? 'bi-geo-alt' : 'bi-question-circle'
}
    
    return {
      // États
      user,
      loading,
      loadingSessions,
      loadingInscriptions,
      apiError,
      stats,
      sessions,
      inscriptions,
      debugVisible,
      apiStatus,
      rawData,
      showDebug,
      
      // Computed
      hasData,
      totalItems,
      
      // Méthodes
      loadData,
      formatDate,
      getStatusLabel,
      getStatusBadge,
      showInscriptionDetails,
      toggleDebug,
       formatDateShort,
    getSessionStatusBadge,
    getSessionStatusLabel,
    viewSessionDetails,
    getRoomIcon
    }
  }
  
}
</script>

<style scoped>
.admin-home {
  min-height: calc(100vh - 200px);
}
.page-title-hr {
      width: 80px;
  height: 4px;
  background: linear-gradient(90deg, #2c8bd6, #8dc8f6);
  margin: 0 auto;
  border-radius: 2px;
  animation: slideIn 1s ease-out;
  margin-bottom: 2%;
}
.card {
  border-radius: 12px;
}

.border-start {
  border-left-width: 4px !important;
}

.table th {
  background-color: #f8f9fa;
  border-top: none;
}

.badge {
  font-size: 0.8em;
  padding: 5px 10px;
}

pre {
  max-height: 200px;
  overflow-y: auto;
  font-size: 12px;
}

/* Style pour la section Accès rapide */
.card-body.text-center {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.card-text {
  flex-grow: 1;
}

.btn {
  margin-top: auto;
}
/* Animation pour le bord extérieur de la box */
/* Animation pour le bord extérieur de la box */
.border-animation {
    border: 3px solid transparent;
    border-radius: 12px;
    animation: borderRotate 5s linear infinite;
    pointer-events: none;
    z-index: 1;
}

/* Animation pour le bord de l'ovale intérieur */
.border-animation-inner {
    border: 2px solid transparent;
    animation: borderRotateInner 2.5s linear infinite reverse;
}

/* Animation du bord extérieur pour la box Formateurs (Bleu) */
.formateurs-box .border-animation {
    animation: borderRotateFormateurs 5s linear infinite;
}

/* Animation du bord intérieur pour la box Formateurs */
.formateurs-box .border-animation-inner {
    animation: borderRotateInnerFormateurs 2.5s linear infinite reverse;
}

/* Animation du bord extérieur pour la box Formations (Vert) */
.formations-box .border-animation {
    animation: borderRotateFormations 5s linear infinite;
}

/* Animation du bord intérieur pour la box Formations */
.formations-box .border-animation-inner {
    animation: borderRotateInnerFormations 2.5s linear infinite reverse;
}

/* Animation du bord extérieur pour la box Sessions (Orange) */
.sessions-box .border-animation {
    animation: borderRotateSessions 5s linear infinite;
}

/* Animation du bord intérieur pour la box Sessions */
.sessions-box .border-animation-inner {
    animation: borderRotateInnerSessions 2.5s linear infinite reverse;
}

/* Animation du bord extérieur pour la box Inscriptions (Cyan) */
.inscriptions-box .border-animation {
    animation: borderRotateInscriptions 5s linear infinite;
}

/* Animation du bord intérieur pour la box Inscriptions */
.inscriptions-box .border-animation-inner {
    animation: borderRotateInnerInscriptions 2.5s linear infinite reverse;
}

/* Animations pour la box Formateurs (Bleu) */
@keyframes borderRotateFormateurs {
    0% {
        border-top-color: #0d6efd;
        border-right-color: #0d6efd;
        border-bottom-color: transparent;
        border-left-color: transparent;
    }
    25% {
        border-top-color: transparent;
        border-right-color: #0d6efd;
        border-bottom-color: #0d6efd;
        border-left-color: transparent;
    }
    50% {
        border-top-color: transparent;
        border-right-color: transparent;
        border-bottom-color: #0d6efd;
        border-left-color: #0d6efd;
    }
    75% {
        border-top-color: #0d6efd;
        border-right-color: transparent;
        border-bottom-color: transparent;
        border-left-color: #0d6efd;
    }
    100% {
        border-top-color: #0d6efd;
        border-right-color: #0d6efd;
        border-bottom-color: transparent;
        border-left-color: transparent;
    }
}

@keyframes borderRotateInnerFormateurs {
    0% {
        border-top-color: #0d6efd;
        border-right-color: #0d6efd;
        border-bottom-color: transparent;
        border-left-color: transparent;
    }
    25% {
        border-top-color: transparent;
        border-right-color: #0d6efd;
        border-bottom-color: #0d6efd;
        border-left-color: transparent;
    }
    50% {
        border-top-color: transparent;
        border-right-color: transparent;
        border-bottom-color: #0d6efd;
        border-left-color: #0d6efd;
    }
    75% {
        border-top-color: #0d6efd;
        border-right-color: transparent;
        border-bottom-color: transparent;
        border-left-color: #0d6efd;
    }
    100% {
        border-top-color: #0d6efd;
        border-right-color: #0d6efd;
        border-bottom-color: transparent;
        border-left-color: transparent;
    }
}

/* Animations pour la box Formations (Vert) */
@keyframes borderRotateFormations {
    0% {
        border-top-color: #198754;
        border-right-color: #198754;
        border-bottom-color: transparent;
        border-left-color: transparent;
    }
    25% {
        border-top-color: transparent;
        border-right-color: #198754;
        border-bottom-color: #198754;
        border-left-color: transparent;
    }
    50% {
        border-top-color: transparent;
        border-right-color: transparent;
        border-bottom-color: #198754;
        border-left-color: #198754;
    }
    75% {
        border-top-color: #198754;
        border-right-color: transparent;
        border-bottom-color: transparent;
        border-left-color: #198754;
    }
    100% {
        border-top-color: #198754;
        border-right-color: #198754;
        border-bottom-color: transparent;
        border-left-color: transparent;
    }
}

@keyframes borderRotateInnerFormations {
    0% {
        border-top-color: #198754;
        border-right-color: #198754;
        border-bottom-color: transparent;
        border-left-color: transparent;
    }
    25% {
        border-top-color: transparent;
        border-right-color: #198754;
        border-bottom-color: #198754;
        border-left-color: transparent;
    }
    50% {
        border-top-color: transparent;
        border-right-color: transparent;
        border-bottom-color: #198754;
        border-left-color: #198754;
    }
    75% {
        border-top-color: #198754;
        border-right-color: transparent;
        border-bottom-color: transparent;
        border-left-color: #198754;
    }
    100% {
        border-top-color: #198754;
        border-right-color: #198754;
        border-bottom-color: transparent;
        border-left-color: transparent;
    }
}

/* Animations pour la box Sessions (Orange) */
@keyframes borderRotateSessions {
    0% {
        border-top-color: #ffc107;
        border-right-color: #ffc107;
        border-bottom-color: transparent;
        border-left-color: transparent;
    }
    25% {
        border-top-color: transparent;
        border-right-color: #ffc107;
        border-bottom-color: #ffc107;
        border-left-color: transparent;
    }
    50% {
        border-top-color: transparent;
        border-right-color: transparent;
        border-bottom-color: #ffc107;
        border-left-color: #ffc107;
    }
    75% {
        border-top-color: #ffc107;
        border-right-color: transparent;
        border-bottom-color: transparent;
        border-left-color: #ffc107;
    }
    100% {
        border-top-color: #ffc107;
        border-right-color: #ffc107;
        border-bottom-color: transparent;
        border-left-color: transparent;
    }
}

@keyframes borderRotateInnerSessions {
    0% {
        border-top-color: #ffc107;
        border-right-color: #ffc107;
        border-bottom-color: transparent;
        border-left-color: transparent;
    }
    25% {
        border-top-color: transparent;
        border-right-color: #ffc107;
        border-bottom-color: #ffc107;
        border-left-color: transparent;
    }
    50% {
        border-top-color: transparent;
        border-right-color: transparent;
        border-bottom-color: #ffc107;
        border-left-color: #ffc107;
    }
    75% {
        border-top-color: #ffc107;
        border-right-color: transparent;
        border-bottom-color: transparent;
        border-left-color: #ffc107;
    }
    100% {
        border-top-color: #ffc107;
        border-right-color: #ffc107;
        border-bottom-color: transparent;
        border-left-color: transparent;
    }
}

/* Animations pour la box Inscriptions (Cyan) */
@keyframes borderRotateInscriptions {
    0% {
        border-top-color: #0dcaf0;
        border-right-color: #0dcaf0;
        border-bottom-color: transparent;
        border-left-color: transparent;
    }
    25% {
        border-top-color: transparent;
        border-right-color: #0dcaf0;
        border-bottom-color: #0dcaf0;
        border-left-color: transparent;
    }
    50% {
        border-top-color: transparent;
        border-right-color: transparent;
        border-bottom-color: #0dcaf0;
        border-left-color: #0dcaf0;
    }
    75% {
        border-top-color: #0dcaf0;
        border-right-color: transparent;
        border-bottom-color: transparent;
        border-left-color: #0dcaf0;
    }
    100% {
        border-top-color: #0dcaf0;
        border-right-color: #0dcaf0;
        border-bottom-color: transparent;
        border-left-color: transparent;
    }
}

@keyframes borderRotateInnerInscriptions {
    0% {
        border-top-color: #0dcaf0;
        border-right-color: #0dcaf0;
        border-bottom-color: transparent;
        border-left-color: transparent;
    }
    25% {
        border-top-color: transparent;
        border-right-color: #0dcaf0;
        border-bottom-color: #0dcaf0;
        border-left-color: transparent;
    }
    50% {
        border-top-color: transparent;
        border-right-color: transparent;
        border-bottom-color: #0dcaf0;
        border-left-color: #0dcaf0;
    }
    75% {
        border-top-color: #0dcaf0;
        border-right-color: transparent;
        border-bottom-color: transparent;
        border-left-color: #0dcaf0;
    }
    100% {
        border-top-color: #0dcaf0;
        border-right-color: #0dcaf0;
        border-bottom-color: transparent;
        border-left-color: transparent;
    }
}

/* Style pour l'ovale */
.oval-container {
    width: 90px;
    height: 90px;
    margin: 0 auto 3px;
}

/* Pour que le texte soit au-dessus de l'animation */
.card-body {
    position: relative;
    z-index: 2;
    background-color: white;
}

/* Ajustement responsive pour mobiles */
@media (max-width: 768px) {
    .border-animation {
        border-width: 2px;
    }
    
    .oval-container {
        width: 80px;
        height: 80px;
    }
}
/************************************** */ 
/* Styles pour le tableau des sessions - Version épurée */
.card {
    border-radius: 10px;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.card-header {
    background: white;
    border-bottom: 1px solid #eee;
    padding: 1rem 1.5rem;
}

.table th {
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    color: #6c757d;
    border-bottom: 2px solid #dee2e6;
    padding: 0.75rem;
}

.table td {
    padding: 1rem 0.75rem;
    vertical-align: middle;
}

.badge {
    font-weight: 500;
    padding: 0.35em 0.65em;
    font-size: 0.75em;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

/* Masquer sur mobile */
@media (max-width: 768px) {
    .d-none-mobile {
        display: none !important;
    }
}
</style>