<template>
  <div class="formateur-detail">
    <!-- En-tête avec bouton retour -->
    <div class="d-flex align-items-center mb-4">
      <button @click="goBack" class="btn btn-outline-secondary me-3">
        <i class="bi bi-arrow-left"></i> Retour
      </button>
      <div>
        <h2 class="mb-1">
          <i class="bi bi-person-badge text-primary me-2"></i>
          Détails du Formateur
        </h2>
        <p class="text-muted mb-0">
          ID: {{ formateurId }} | {{ formateurData?.prenom }} {{ formateurData?.nom }}
        </p>
      </div>
    </div>

    <!-- Chargement -->
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Chargement...</span>
      </div>
      <p class="mt-2 text-muted">Chargement des détails...</p>
    </div>

    <!-- Erreur -->
    <div v-else-if="error" class="alert alert-danger">
      <i class="bi bi-exclamation-triangle me-2"></i>
      {{ error }}
    </div>

    <!-- Contenu -->
    <div v-else-if="formateurData" class="row">
      <!-- Colonne gauche: Informations personnelles -->
      <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
          <div class="card-header bg-white">
            <h5 class="mb-0">
              <i class="bi bi-info-circle text-primary me-2"></i>
              Informations personnelles
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label text-muted">Nom</label>
                <p class="fw-semibold">{{ formateurData.nom }}</p>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label text-muted">Prénom</label>
                <p class="fw-semibold">{{ formateurData.prenom }}</p>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label text-muted">Email</label>
                <p class="fw-semibold">
                  <a :href="`mailto:${formateurData.email}`" class="text-decoration-none">
                    {{ formateurData.email }}
                  </a>
                </p>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label text-muted">Spécialité</label>
                <p class="fw-semibold">
                  <span class="badge bg-info bg-opacity-10 text-info border border-info">
                    {{ formateurData.specialite || 'Non spécifié' }}
                  </span>
                </p>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label text-muted">Date d'inscription</label>
                <p class="fw-semibold">{{ formatDate(formateurData.date_inscription) }}</p>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label text-muted">ID Utilisateur</label>
                <p class="fw-semibold">{{ formateurData.utilisateur_id }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Sessions futures -->
        <div v-if="sessionsFutures.length > 0" class="card shadow-sm mb-4">
          <div class="card-header bg-white">
            <h5 class="mb-0">
              <i class="bi bi-calendar-event text-success me-2"></i>
              Sessions futures
            </h5>
          </div>
          <div class="card-body">
            <div v-for="session in sessionsFutures" :key="session.id" class="border-bottom pb-3 mb-3">
              <h6>{{ session.formation_titre }}</h6>
              <div class="d-flex text-muted small">
                <div class="me-4">
                  <i class="bi bi-calendar me-1"></i>
                  {{ formatDate(session.date_debut) }}
                </div>
                <div class="me-4">
                  <i class="bi bi-clock me-1"></i>
                  {{ session.heure_debut }}
                </div>
                <div>
                  <i class="bi bi-people me-1"></i>
                  {{ session.places_prises }}/{{ session.places_max }} places
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Colonne droite: Statistiques -->
      <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
          <div class="card-header bg-white">
            <h5 class="mb-0">
              <i class="bi bi-bar-chart text-primary me-2"></i>
              Statistiques
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-6 mb-3">
                <div class="text-center">
                  <div class="display-6 fw-bold text-primary">
                    {{ statistiques?.sessions_total || 0 }}
                  </div>
                  <small class="text-muted">Sessions totales</small>
                </div>
              </div>
              <div class="col-6 mb-3">
                <div class="text-center">
                  <div class="display-6 fw-bold text-success">
                    {{ statistiques?.inscriptions_validees || 0 }}
                  </div>
                  <small class="text-muted">Participants validés</small>
                </div>
              </div>
              <div class="col-6 mb-3">
                <div class="text-center">
                  <div class="display-6 fw-bold text-warning">
                    {{ statistiques?.sessions_attente || 0 }}
                  </div>
                  <small class="text-muted">Sessions en attente</small>
                </div>
              </div>
              <div class="col-6 mb-3">
                <div class="text-center">
                  <div class="display-6 fw-bold text-info">
                    {{ Math.round(statistiques?.places_moyennes || 0) }}
                  </div>
                  <small class="text-muted">Places moyennes</small>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="card shadow-sm">
          <div class="card-header bg-white">
            <h5 class="mb-0">
              <i class="bi bi-lightning-charge text-warning me-2"></i>
              Actions rapides
            </h5>
          </div>
          <div class="card-body">
            <div class="d-grid gap-2">
              <button @click="editFormateur" class="btn btn-outline-success">
                <i class="bi bi-pencil me-2"></i>Modifier
              </button>
              <button @click="sendEmail" class="btn btn-outline-primary">
                <i class="bi bi-envelope me-2"></i>Envoyer un email
              </button>
              <button @click="viewSessions" class="btn btn-outline-info">
                <i class="bi bi-calendar-week me-2"></i>Voir toutes les sessions
              </button>
              <button @click="confirmDelete" class="btn btn-outline-danger">
                <i class="bi bi-trash me-2"></i>Supprimer
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import adminService from '@/services/admin'

export default {
  name: 'FormateurDetail',
  
  setup() {
    const route = useRoute()
    const router = useRouter()
    
    const loading = ref(true)
    const error = ref(null)
    
    const formateurId = ref(route.params.id)
    const formateurData = ref(null)
    const statistiques = ref(null)
    const sessionsFutures = ref([])
    const sessionsPassees = ref([])
    
    const loadFormateurDetails = async () => {
      loading.value = true
      try {
        console.log('📤 Chargement détail formateur ID:', formateurId.value)
        
        // Si vous avez un endpoint dédié
        const response = await adminService.getFormateur(formateurId.value)
        
        if (response.success) {
          formateurData.value = response.data?.formateur || response.data
          statistiques.value = response.data?.statistiques
          sessionsFutures.value = response.data?.sessions_futures || []
          sessionsPassees.value = response.data?.sessions_passees || []
          
          console.log('✅ Détails chargés:', formateurData.value)
        } else {
          throw new Error(response.message || 'Erreur lors du chargement')
        }
      } catch (err) {
        console.error('❌ Erreur chargement détails:', err)
        error.value = err.message || 'Impossible de charger les détails'
      } finally {
        loading.value = false
      }
    }
    
    const goBack = () => {
      router.push('/admin/formateurs')
    }
    
    const editFormateur = () => {
      router.push(`/admin/formateurs?edit=${formateurId.value}`)
    }
    
    const sendEmail = () => {
      if (formateurData.value?.email) {
        window.location.href = `mailto:${formateurData.value.email}`
      }
    }
    
    const viewSessions = () => {
      // Rediriger vers la liste des sessions filtrée
      router.push(`/admin/sessions?formateur=${formateurId.value}`)
    }
    
    const confirmDelete = () => {
      const name = formateurData.value ? `${formateurData.value.prenom} ${formateurData.value.nom}` : 'ce formateur'
      if (confirm(`Êtes-vous sûr de vouloir supprimer "${name}" ?\n\nCette action est irréversible.`)) {
        deleteFormateur()
      }
    }
    
    const deleteFormateur = async () => {
      try {
        await adminService.deleteFormateur(formateurId.value)
        alert('✅ Formateur supprimé avec succès')
        router.push('/admin/formateurs')
      } catch (err) {
        console.error('❌ Erreur suppression:', err)
        alert(err.response?.data?.message || 'Erreur lors de la suppression')
      }
    }
    
    const formatDate = (dateString) => {
      if (!dateString) return 'N/A'
      try {
        const date = new Date(dateString)
        return date.toLocaleDateString('fr-FR', {
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        })
      } catch {
        return dateString
      }
    }
    
    onMounted(() => {
      loadFormateurDetails()
    })
    
    return {
      loading,
      error,
      formateurId,
      formateurData,
      statistiques,
      sessionsFutures,
      sessionsPassees,
      goBack,
      editFormateur,
      sendEmail,
      viewSessions,
      confirmDelete,
      formatDate
    }
  }
}
</script>

<style scoped>
.formateur-detail {
  min-height: calc(100vh - 200px);
}

.card {
  border: none;
}

.card-header {
  border-bottom: 1px solid rgba(0,0,0,.125);
}

.badge {
  font-size: 0.9em;
  padding: 0.5em 0.8em;
}
</style>