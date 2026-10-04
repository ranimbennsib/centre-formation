<template>
  <div class="profil-employe-container">
    <!-- Loading -->
    <div v-if="loading" class="loading-container">
      <div class="spinner-container">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Chargement...</span>
        </div>
        <p class="loading-text">Chargement de votre profil...</p>
      </div>
    </div>

    <!-- Profil chargé -->
    <div v-else>
      <!-- Header -->
      <div class="profil-header">
        <div class="header-content">
          <div class="avatar-section">
            <div class="avatar-wrapper">
              <div class="avatar-placeholder">
                <i class="bi bi-person-circle"></i>
              </div>
            </div>
            <div class="user-info">
              <h1>{{ employe.prenom }} {{ employe.nom }}</h1>
              <p class="user-role">{{ getRoleText(employe.role) }}</p>
              <div class="user-stats">
                <div class="stat-item">
                  <i class="bi bi-bookmark-check-fill"></i>
                  <span>{{ stats.formations_total || 0 }} formations</span>
                </div>
                <div class="stat-item">
                  <i class="bi bi-calendar-check-fill"></i>
                  <span>{{ stats.inscriptions_validees || 0 }} validées</span>
                </div>
                <div class="stat-item">
                  <i class="bi bi-clock-history"></i>
                  <span>{{ stats.inscriptions_en_attente || 0 }} en attente</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Navigation Tabs -->
      <div class="profil-nav">
        <div class="nav-tabs">
          <button 
            v-for="tab in tabs" 
            :key="tab.id"
            class="nav-tab"
            :class="{ active: activeTab === tab.id }"
            @click="activeTab = tab.id"
          >
            <i class="bi" :class="tab.icon"></i>
            {{ tab.label }}
            <span class="tab-badge" v-if="tab.badge">{{ tab.badge }}</span>
          </button>
        </div>
      </div>

      <!-- Content -->
      <div class="profil-content">
        <!-- Tab 1: Informations personnelles -->
        <div v-if="activeTab === 'infos'" class="tab-content">
          <div class="info-grid">
            <div class="info-card">
              <div class="card-header">
                <i class="bi bi-person-badge-fill"></i>
                <h3>Informations personnelles</h3>
              </div>
              <div class="card-body">
                <div class="info-row">
                  <div class="info-label">Email</div>
                  <div class="info-value">{{ employe.email }}</div>
                </div>
                <div class="info-row">
                  <div class="info-label">Nom</div>
                  <div class="info-value">{{ employe.nom }}</div>
                </div>
                <div class="info-row">
                  <div class="info-label">Prénom</div>
                  <div class="info-value">{{ employe.prenom }}</div>
                </div>
                <div class="info-row">
                  <div class="info-label">CIN</div>
                  <div class="info-value">{{ employe.cin || 'Non renseigné' }}</div>
                </div>
                <div class="info-row">
                  <div class="info-label">Date d'inscription</div>
                  <div class="info-value">{{ formatDate(employe.date_inscription) }}</div>
                </div>
              </div>
            </div>

            <div class="info-card">
              <div class="card-header">
                <i class="bi bi-briefcase-fill"></i>
                <h3>Informations professionnelles</h3>
              </div>
              <div class="card-body">
                <div class="info-row">
                  <div class="info-label">Poste</div>
                  <div class="info-value">{{ employe.poste || 'Non renseigné' }}</div>
                </div>
                <div class="info-row">
                  <div class="info-label">Département</div>
                  <div class="info-value">{{ employe.departement || 'Non renseigné' }}</div>
                </div>
                <div class="info-row">
                  <div class="info-label">Date d'embauche</div>
                  <div class="info-value">{{ formatDate(employe.date_embauche) }}</div>
                </div>
                <div class="info-row">
                  <div class="info-label">Rôle</div>
                  <div class="info-value">{{ getRoleText(employe.role) }}</div>
                </div>
              </div>
            </div>

            <div class="info-card">
              <div class="card-header">
                <i class="bi bi-bar-chart-fill"></i>
                <h3>Statistiques</h3>
              </div>
              <div class="card-body">
                <div class="stats-grid">
                  <div class="stat-item-card">
                    <div class="stat-number">{{ stats.inscriptions_total || 0 }}</div>
                    <div class="stat-label">Total inscriptions</div>
                  </div>
                  <div class="stat-item-card">
                    <div class="stat-number">{{ stats.inscriptions_validees || 0 }}</div>
                    <div class="stat-label">Validées</div>
                  </div>
                  <div class="stat-item-card">
                    <div class="stat-number">{{ stats.inscriptions_en_attente || 0 }}</div>
                    <div class="stat-label">En attente</div>
                  </div>
                  <div class="stat-item-card">
                    <div class="stat-number">{{ stats.inscriptions_annulees || 0 }}</div>
                    <div class="stat-label">Annulées</div>
                  </div>
                  <div class="stat-item-card">
                    <div class="stat-number">{{ stats.inscriptions_refusees || 0 }}</div>
                    <div class="stat-label">Refusées</div>
                  </div>
                  <div class="stat-item-card">
                    <div class="stat-number">{{ stats.formations_total || 0 }}</div>
                    <div class="stat-label">Formations</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tab 2: Formations -->
        <div v-else-if="activeTab === 'formations'" class="tab-content">
          <div class="formations-section">
            <div class="section-header">
              <h3>Mes formations</h3>
              <div class="section-filters">
                <select class="filter-select" v-model="formationFilter">
                  <option value="all">Toutes les formations</option>
                  <option value="completed">Terminées</option>
                  <option value="in_progress">En cours</option>
                  <option value="upcoming">À venir</option>
                </select>
              </div>
            </div>
            
            <div v-if="getFilteredFormations().length > 0" class="formations-list">
              <div v-for="formation in getFilteredFormations()" :key="formation.id" class="formation-item">
                <div class="formation-header">
                  <div class="formation-title">
                    <h4>{{ formation.titre }}</h4>
                    <div class="formation-status" :class="formation.statut">
                      {{ getFormationStatusText(formation.statut) }}
                    </div>
                  </div>
                  <div class="formation-date">{{ formatDate(formation.date_debut) }}</div>
                </div>
                
                <div class="formation-details">
                  <div class="detail-item">
                    <i class="bi bi-clock"></i>
                    {{ formation.duree_heures }} heures
                  </div>
                  <div class="detail-item">
                    <i class="bi bi-person-badge"></i>
                    {{ formation.formateur || 'Formateur non assigné' }}
                  </div>
                  <div class="detail-item">
                    <i class="bi bi-geo-alt"></i>
                    {{ formation.salle || 'Salle non définie' }}
                  </div>
                </div>
                
                <p class="formation-description">{{ formation.description }}</p>
                
                <div class="formation-period">
                  <i class="bi bi-calendar-range"></i>
                  Du {{ formatDateTime(formation.date_debut) }} au {{ formatDateTime(formation.date_fin) }}
                </div>
                
                <div class="formation-actions">
                  <button class="btn-view-details" v-if="formation.statut === 'completed'">
                    <i class="bi bi-file-earmark-pdf-fill"></i>
                    Certificat
                  </button>
                  <div class="inscription-status">
                    <span class="status-badge" :class="formation.inscription_statut">
                      {{ getInscriptionStatusText(formation.inscription_statut) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="empty-formations">
              <i class="bi bi-bookmark-x"></i>
              <p v-if="formationFilter === 'all'">Aucune formation suivie</p>
              <p v-else>Aucune formation {{ getFilterText(formationFilter) }}</p>
            </div>
          </div>
        </div>

        <!-- Tab 3: Inscriptions -->
        <div v-else-if="activeTab === 'inscriptions'" class="tab-content">
          <div class="inscriptions-section">
            <div class="section-header">
              <h3>Historique des inscriptions</h3>
            </div>
            
            <div v-if="inscriptions.length > 0" class="inscriptions-list">
              <div v-for="inscription in inscriptions" :key="inscription.inscription_id" class="inscription-item">
                <div class="inscription-header">
                  <div class="inscription-title">
                    <h4>{{ inscription.formation_titre }}</h4>
                    <div class="inscription-status" :class="inscription.inscription_statut">
                      {{ getInscriptionStatusText(inscription.inscription_statut) }}
                    </div>
                  </div>
                  <div class="inscription-date">
                    Inscrit le {{ formatDateTime(inscription.date_inscription) }}
                  </div>
                </div>
                
                <div class="inscription-details">
                  <div class="detail-row">
                    <div class="detail-item">
                      <i class="bi bi-calendar-event"></i>
                      Session : {{ formatDate(inscription.date_debut) }}
                    </div>
                    <div class="detail-item">
                      <i class="bi bi-clock"></i>
                      {{ inscription.duree_heures }} heures
                    </div>
                    <div class="detail-item">
                      <i class="bi bi-person-badge"></i>
                      {{ inscription.formateur_nom ? inscription.formateur_nom + ' ' + inscription.formateur_prenom : 'Formateur non assigné' }}
                    </div>
                  </div>
                </div>
                
                <div v-if="inscription.raison_refus" class="inscription-alert">
                  <i class="bi bi-exclamation-triangle-fill"></i>
                  <div class="alert-content">
                    <strong>Raison :</strong> {{ inscription.raison_refus }}
                  </div>
                </div>
                
                <div class="inscription-actions">
                  <button class="btn-view-details">
                    <i class="bi bi-eye-fill"></i>
                    Voir la formation
                  </button>
                </div>
              </div>
            </div>
            <div v-else class="empty-inscriptions">
              <i class="bi bi-calendar-x"></i>
              <p>Aucune inscription trouvée</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import employeService from '@/services/employe'

export default {
  name: 'EmployeProfil',
  
  setup() {
    const loading = ref(true)
    const activeTab = ref('infos')
    const formationFilter = ref('all')
    
    // Données du profil
    const employe = ref({})
    const stats = ref({})
    const formations = ref({
      completed: [],
      in_progress: [],
      upcoming: []
    })
    const inscriptions = ref([])

    const tabs = ref([
      { id: 'infos', label: 'Informations', icon: 'bi-person-fill', badge: null },
      { id: 'formations', label: 'Formations', icon: 'bi-book-fill', badge: null },
      { id: 'inscriptions', label: 'Inscriptions', icon: 'bi-calendar-check-fill', badge: null }
    ])

    const getRoleText = (role) => {
      const roles = {
        'EMPLOYE': 'Employé',
        'FORMATEUR': 'Formateur',
        'ADMIN': 'Administrateur'
      }
      return roles[role] || role
    }

    const getFormationStatusText = (statut) => {
      const status = {
        'completed': 'Terminée',
        'in_progress': 'En cours',
        'upcoming': 'À venir'
      }
      return status[statut] || statut
    }

    const getInscriptionStatusText = (statut) => {
      const status = {
        'EN_ATTENTE': 'En attente',
        'VALIDEE': 'Validée',
        'REFUSEE': 'Refusée',
        'ANNULEE': 'Annulée'
      }
      return status[statut] || statut
    }

    const getFilterText = (filter) => {
      const filters = {
        'completed': 'terminées',
        'in_progress': 'en cours',
        'upcoming': 'à venir'
      }
      return filters[filter] || filter
    }

    const getFilteredFormations = () => {
      if (formationFilter.value === 'all') {
        return [
          ...formations.value.completed,
          ...formations.value.in_progress,
          ...formations.value.upcoming
        ]
      }
      return formations.value[formationFilter.value] || []
    }

    const formatDate = (dateString) => {
      if (!dateString) return 'Non renseignée'
      try {
        return new Date(dateString).toLocaleDateString('fr-FR', {
          day: '2-digit',
          month: '2-digit',
          year: 'numeric'
        })
      } catch (e) {
        return dateString
      }
    }

    const formatDateTime = (dateString) => {
      if (!dateString) return 'Non renseignée'
      try {
        return new Date(dateString).toLocaleString('fr-FR', {
          day: '2-digit',
          month: '2-digit',
          year: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        })
      } catch (e) {
        return dateString
      }
    }

   const loadProfil = async () => {
  try {
    loading.value = true
    
    // Réinitialiser les données avant de charger
    employe.value = {}
    stats.value = {}
    formations.value = {
      completed: [],
      in_progress: [],
      upcoming: []
    }
    inscriptions.value = []
    
    const response = await employeService.getMonProfil()
    
    console.log('📊 Réponse API complète:', response)
    
    // Vérifier si la réponse existe
    if (response) {
      // Assurez-vous que les données sont bien assignées
      employe.value = response.profil || {}
      stats.value = response.stats || {}
      formations.value = response.formations || {
        completed: [],
        in_progress: [],
        upcoming: []
      }
      inscriptions.value = response.inscriptions || []
      
      // Mettre à jour les badges des tabs
      tabs.value[1].badge = stats.value.formations_total || 0
      tabs.value[2].badge = stats.value.inscriptions_total || 0
      
      console.log('✅ Données mises à jour depuis API')
      console.log('👤 Profil:', employe.value)
      console.log('📊 Stats:', stats.value)
      console.log('📚 Formations:', formations.value)
      console.log('📋 Inscriptions:', inscriptions.value)
    } else {
      console.error('❌ API a retourné une réponse vide')
    }
  } catch (error) {
    console.error('❌ Erreur lors du chargement du profil:', error)
    // Vous pouvez afficher un message d'erreur à l'utilisateur ici
  } finally {
    loading.value = false
  }
}
    onMounted(() => {
      loadProfil()
    })

    return {
      loading,
      activeTab,
      formationFilter,
      employe,
      stats,
      formations,
      inscriptions,
      tabs,
      getRoleText,
      getFormationStatusText,
      getInscriptionStatusText,
      getFilterText,
      getFilteredFormations,
      formatDate,
      formatDateTime
    }
  }
}
</script>

<style scoped>
/* Styles de base */
.profil-employe-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

/* Header */
.profil-header {
  background: linear-gradient(135deg, #4361ee, #3a56d4);
  border-radius: 20px;
  margin: 2rem 0;
  padding: 2.5rem 3rem;
  color: white;
  position: relative;
  overflow: hidden;
}

.profil-header::before {
  content: '';
  position: absolute;
  top: 0;
  right: 0;
  width: 300px;
  height: 300px;
  background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
  border-radius: 50%;
}

.header-content {
  position: relative;
  z-index: 1;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 2rem;
}

.avatar-section {
  display: flex;
  align-items: center;
  gap: 2rem;
  flex: 1;
}

.avatar-wrapper {
  position: relative;
}

.avatar-placeholder {
  width: 120px;
  height: 120px;
  background: rgba(255, 255, 255, 0.9);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #4361ee;
  font-size: 4rem;
  border: 4px solid white;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
}

.user-info h1 {
  font-size: 2.5rem;
  font-weight: 800;
  margin: 0 0 0.5rem 0;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.user-role {
  font-size: 1.1rem;
  opacity: 0.9;
  margin-bottom: 1.5rem;
}

.user-stats {
  display: flex;
  gap: 2rem;
  flex-wrap: wrap;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(255, 255, 255, 0.15);
  padding: 0.5rem 1rem;
  border-radius: 20px;
  backdrop-filter: blur(10px);
}

.stat-item i {
  color: #f59e0b;
}

/* Navigation Tabs */
.profil-nav {
  margin-bottom: 2rem;
}

.nav-tabs {
  display: flex;
  gap: 0.5rem;
  border-bottom: 2px solid #e2e8f0;
  padding: 0 0.5rem;
}

.nav-tab {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem 1.5rem;
  background: none;
  border: none;
  border-bottom: 3px solid transparent;
  color: #718096;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
}

.nav-tab:hover {
  color: #4361ee;
}

.nav-tab.active {
  color: #4361ee;
  border-bottom-color: #4361ee;
  background: #f0f4ff;
}

.tab-badge {
  background: #ef4444;
  color: white;
  padding: 0.15rem 0.5rem;
  border-radius: 10px;
  font-size: 0.75rem;
  font-weight: 600;
  margin-left: 0.25rem;
}

.nav-tab.active .tab-badge {
  background: #dc2626;
}

/* Content */
.profil-content {
  margin-bottom: 3rem;
}

.tab-content {
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Informations personnelles */
.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 1.5rem;
}

.info-card {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
  border: 1px solid #e2e8f0;
  transition: all 0.3s ease;
}

.info-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
}

.card-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  background: linear-gradient(135deg, #4361ee15 0%, #3a56d415 100%);
  border-bottom: 1px solid #e2e8f0;
}

.card-header i {
  color: #4361ee;
  font-size: 1.5rem;
}

.card-header h3 {
  margin: 0;
  color: #2d3748;
  font-size: 1.25rem;
}

.card-body {
  padding: 1.5rem;
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
  border-bottom: 1px solid #f1f5f9;
}

.info-row:last-child {
  border-bottom: none;
}

.info-label {
  color: #718096;
  font-weight: 500;
  font-size: 0.9rem;
}

.info-value {
  color: #2d3748;
  font-weight: 600;
  text-align: right;
}

/* Statistiques */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}

.stat-item-card {
  background: #f8fafc;
  border-radius: 12px;
  padding: 1.5rem;
  text-align: center;
  border: 1px solid #e2e8f0;
  transition: all 0.3s ease;
}

.stat-item-card:hover {
  border-color: #4361ee;
  background: #f0f4ff;
}

.stat-number {
  font-size: 2rem;
  font-weight: 800;
  color: #4361ee;
  margin-bottom: 0.5rem;
}

.stat-label {
  color: #718096;
  font-size: 0.9rem;
  font-weight: 500;
}

/* Formations */
.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.section-header h3 {
  color: #2d3748;
  font-size: 1.5rem;
  margin: 0;
}

.filter-select {
  padding: 0.5rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  background: white;
  color: #4a5568;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.filter-select:focus {
  border-color: #4361ee;
  outline: none;
}

.formations-list, .inscriptions-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.formation-item, .inscription-item {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  border: 1px solid #e2e8f0;
  transition: all 0.3s ease;
}

.formation-item:hover, .inscription-item:hover {
  border-color: #4361ee;
  box-shadow: 0 4px 15px rgba(67, 97, 238, 0.1);
}

.formation-header, .inscription-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.formation-title, .inscription-title {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex: 1;
}

.formation-title h4, .inscription-title h4 {
  margin: 0;
  color: #2d3748;
  font-size: 1.1rem;
}

.formation-status, .inscription-status {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
}

.formation-status.completed, .inscription-status.VALIDEE {
  background: #d1fae5;
  color: #065f46;
}

.formation-status.in_progress, .inscription-status.EN_ATTENTE {
  background: #fef3c7;
  color: #92400e;
}

.formation-status.upcoming {
  background: #dbeafe;
  color: #1e40af;
}

.inscription-status.REFUSEE, .inscription-status.ANNULEE {
  background: #fee2e2;
  color: #991b1b;
}

.formation-date, .inscription-date {
  color: #718096;
  font-size: 0.9rem;
}

.formation-details, .inscription-details {
  display: flex;
  gap: 1.5rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.detail-row {
  display: flex;
  gap: 1.5rem;
  width: 100%;
  flex-wrap: wrap;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #4a5568;
  font-size: 0.9rem;
}

.detail-item i {
  color: #4361ee;
}

.formation-description {
  color: #718096;
  line-height: 1.5;
  margin-bottom: 1rem;
  font-size: 0.95rem;
}

.formation-period {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #4a5568;
  font-size: 0.9rem;
  margin-bottom: 1rem;
}

.formation-actions, .inscription-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.btn-view-details {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: #4361ee;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-weight: 500;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-view-details:hover {
  background: #3a56d4;
  transform: translateY(-1px);
}

.inscription-alert {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  background: #fef2f2;
  border-left: 4px solid #dc2626;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.inscription-alert i {
  color: #dc2626;
  font-size: 1.1rem;
  margin-top: 2px;
}

.alert-content {
  flex: 1;
  color: #991b1b;
  font-size: 0.9rem;
}

/* États vides */
.empty-formations, .empty-inscriptions {
  text-align: center;
  padding: 4rem 2rem;
  color: #94a3b8;
}

.empty-formations i, .empty-inscriptions i {
  font-size: 4rem;
  margin-bottom: 1.5rem;
  display: block;
}

.empty-formations p, .empty-inscriptions p {
  font-size: 1.1rem;
}

/* Loading */
.loading-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 400px;
}

.spinner-container {
  text-align: center;
}

.spinner-border {
  width: 3rem;
  height: 3rem;
  margin-bottom: 1rem;
}

.loading-text {
  color: #718096;
  font-size: 1.1rem;
}

/* Responsive */
@media (max-width: 768px) {
  .profil-employe-container {
    padding: 0 1rem;
  }
  
  .profil-header {
    padding: 1.5rem;
    margin: 1rem 0;
  }
  
  .header-content {
    flex-direction: column;
    gap: 1.5rem;
  }
  
  .avatar-section {
    flex-direction: column;
    text-align: center;
  }
  
  .user-info h1 {
    font-size: 2rem;
  }
  
  .user-stats {
    justify-content: center;
  }
  
  .nav-tabs {
    overflow-x: auto;
    padding-bottom: 0.5rem;
  }
  
  .nav-tab {
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
    white-space: nowrap;
  }
  
  .info-grid {
    grid-template-columns: 1fr;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  
  .formation-header, .inscription-header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .formation-actions, .inscription-actions {
    flex-direction: column;
    align-items: stretch;
  }
  
  .btn-view-details {
    width: 100%;
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .user-stats {
    flex-direction: column;
    align-items: center;
  }
  
  .stat-item {
    width: 100%;
    justify-content: center;
  }
  
  .detail-row {
    flex-direction: column;
    gap: 0.75rem;
  }
}
</style>