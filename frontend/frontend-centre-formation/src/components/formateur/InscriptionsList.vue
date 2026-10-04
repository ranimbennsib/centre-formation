<template>
  <div class="inscriptions-modern">
    
    <!-- En-tête moderne -->
    <div class="modern-header">
      <div class="header-content">
        <div class="title-section-modern">
          <button @click="$router.back()" class="back-btn-modern">
            <i class="bi bi-arrow-left"></i>
            <span>Retour</span>
          </button>
          <h1 class="modern-title">
            <i class="bi bi-people-fill"></i>
            Inscriptions - Session #{{ sessionId }}
          </h1>
          <p class="modern-subtitle" v-if="sessionDetails">
            {{ sessionDetails.formation_titre }}
          </p>
        </div>
        
        <div class="header-actions">
          <div class="stats-badge-modern" v-if="!loading">
            <i class="bi bi-list-task"></i>
            <span>{{ stats.total }} inscription(s)</span>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Informations session modernes -->
    <div class="session-info-modern" v-if="sessionDetails">
      <div class="session-details-card">
        <div class="session-icon-modern">
          <i class="bi bi-calendar3-fill"></i>
        </div>
        <div class="session-info-content">
          <h3 class="session-title-modern">{{ sessionDetails.formation_titre }}</h3>
          <div class="session-meta-modern">
            <div class="meta-item-modern">
              <i class="bi bi-calendar-event-fill"></i>
              <span class="meta-label">Début</span>
              <span class="meta-value">{{ formatDate(sessionDetails.date_debut) }}</span>
            </div>
            <div class="meta-item-modern">
              <i class="bi bi-calendar-check-fill"></i>
              <span class="meta-label">Fin</span>
              <span class="meta-value">{{ formatDate(sessionDetails.date_fin) }}</span>
            </div>
            <div class="meta-item-modern">
              <i class="bi bi-geo-alt-fill"></i>
              <span class="meta-label">Salle</span>
              <span class="meta-value">{{ sessionDetails.salle }}</span>
            </div>
            <div class="meta-item-modern">
              <i class="bi bi-people-fill"></i>
              <span class="meta-label">Places</span>
              <span class="meta-value">{{ sessionDetails.places_restantes }} / {{ sessionDetails.places_max }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Statistiques modernes -->
    <div class="stats-section-modern">
      <div class="stats-grid-modern">
        <div class="stat-box-modern stat-total">
          <div class="stat-icon-modern">
            <i class="bi bi-people-fill"></i>
          </div>
          <div class="stat-content-modern">
            <span class="stat-value-modern">{{ stats.total }}</span>
            <span class="stat-label-modern">Total</span>
          </div>
        </div>
        
        <div class="stat-box-modern stat-validated">
          <div class="stat-icon-modern">
            <i class="bi bi-check-circle-fill"></i>
          </div>
          <div class="stat-content-modern">
            <span class="stat-value-modern">{{ stats.validated }}</span>
            <span class="stat-label-modern">Validées</span>
          </div>
        </div>
        
        <div class="stat-box-modern stat-pending">
          <div class="stat-icon-modern">
            <i class="bi bi-clock-history"></i>
          </div>
          <div class="stat-content-modern">
            <span class="stat-value-modern">{{ stats.pending }}</span>
            <span class="stat-label-modern">En attente</span>
          </div>
        </div>
        
        <div class="stat-box-modern stat-refused">
          <div class="stat-icon-modern">
            <i class="bi bi-x-circle-fill"></i>
          </div>
          <div class="stat-content-modern">
            <span class="stat-value-modern">{{ stats.refused }}</span>
            <span class="stat-label-modern">Refusées</span>
          </div>
        </div>
        
        <div class="stat-box-modern stat-cancelled">
          <div class="stat-icon-modern">
            <i class="bi bi-slash-circle-fill"></i>
          </div>
          <div class="stat-content-modern">
            <span class="stat-value-modern">{{ stats.cancelled }}</span>
            <span class="stat-label-modern">Annulées</span>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Filtres modernes -->
    <div class="filters-section-modern">
      <div class="search-bar-modern">
        <div class="search-input-wrapper">
          <i class="bi bi-search"></i>
          <input 
            v-model="searchTerm" 
            @input="onSearch" 
            placeholder="Rechercher par nom, prénom ou email..."
            class="modern-search-input"
          >
        </div>
      </div>
      
      <div class="filters-row-modern">
        <div class="filter-group-modern">
          <div class="filter-label-modern">
            <i class="bi bi-filter-circle"></i>
            Statut
          </div>
          <select v-model="selectedStatus" @change="loadInscriptions" class="modern-select">
            <option value="">Tous les statuts</option>
            <option value="EN_ATTENTE">En attente</option>
            <option value="VALIDEE">Validée</option>
            <option value="REFUSEE">Refusée</option>
            <option value="ANNULEE">Annulée</option>
          </select>
        </div>
        
        <button @click="resetFilters" class="reset-filters-btn">
          <i class="bi bi-arrow-clockwise"></i>
          Réinitialiser
        </button>
      </div>
    </div>
    
    <!-- Chargement -->
    <div v-if="loading" class="loading-container">
      <div class="spinner-container">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Chargement...</span>
        </div>
        <p class="loading-text">Chargement des inscriptions...</p>
      </div>
    </div>
    
    <!-- État vide moderne -->
    <div v-else-if="inscriptions.length === 0" class="empty-state-modern">
      <div class="empty-state-content-modern">
        <div class="empty-state-icon-modern">
          <i class="bi bi-people"></i>
        </div>
        <div class="empty-state-text-modern">
          <h3>Aucune inscription trouvée</h3>
          <p>Il n'y a pas d'inscriptions correspondant à vos critères.</p>
        </div>
        <button @click="resetFilters" class="empty-state-btn-modern">
          <i class="bi bi-arrow-clockwise"></i>
          Réinitialiser les filtres
        </button>
      </div>
    </div>
    
    <!-- Tableau moderne -->
    <div v-else class="table-section-modern">
      <div class="table-container-modern">
        <div class="table-header-modern">
          <h3><i class="bi bi-list-check"></i> Liste des inscriptions</h3>
          <div class="table-actions">
            <span class="table-count">{{ inscriptions.length }} inscription(s)</span>
          </div>
        </div>
        
        <div class="table-wrapper-modern">
          <table class="inscriptions-table-modern">
            <thead>
              <tr>
                <th class="th-name">
                  <i class="bi bi-person-badge"></i>
                  Nom & Prénom
                </th>
                <th class="th-email">
                  <i class="bi bi-envelope-fill"></i>
                  Email
                </th>
                <th class="th-date">
                  <i class="bi bi-calendar-check"></i>
                  Date inscription
                </th>
                <th class="th-status">
                  <i class="bi bi-flag-fill"></i>
                  Statut
                </th>
                <th class="th-actions">
                  <i class="bi bi-gear-fill"></i>
                  Actions
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="inscription in inscriptions" :key="inscription.id" class="table-row-modern">
                <td class="td-name">
                  <div class="user-info">
                    <div class="user-avatar">
                      {{ getUserInitials(inscription) }}
                    </div>
                    <div class="user-details">
                      <strong>{{ inscription.employe_prenom }} {{ inscription.employe_nom }}</strong>
                    </div>
                  </div>
                </td>
                <td class="td-email">
                  <a :href="`mailto:${inscription.employe_email}`" class="email-link">
                    <i class="bi bi-envelope"></i>
                    {{ inscription.employe_email }}
                  </a>
                </td>
                <td class="td-date">
                  <div class="date-badge">
                    <i class="bi bi-calendar-date"></i>
                    {{ formatDateShort(inscription.date_inscription) }}
                  </div>
                </td>
                <td class="td-status">
                  <span :class="`status-badge-modern status-${inscription.statut.toLowerCase()}`">
                    <i :class="getStatusIcon(inscription.statut)"></i>
                    {{ formatStatus(inscription.statut) }}
                  </span>
                  <span 
                    v-if="inscription.statut === 'REFUSEE' && inscription.raison_refus" 
                    class="refused-hint" 
                    @click="showRefusalReason(inscription)"
                    title="Voir la raison du refus"
                  >
                    <i class="bi bi-chat-left-text-fill"></i>
                  </span>
                </td>
                <td class="td-actions">
                  <div class="action-buttons">
                    <button @click="showDetails(inscription)" class="btn-details-modern">
                      <i class="bi bi-eye-fill"></i>
                      <span>Détails</span>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    
    <!-- Modal pour les détails -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-container">
        <div class="modal-header">
          <h3>
            <i class="bi bi-info-circle-fill"></i>
            Détails de l'inscription
          </h3>
          <button class="btn-close-modal" @click="closeModal">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
        
        <div class="modal-body">
          <div v-if="selectedInscription" class="inscription-details">
            <!-- En-tête de la modal -->
            <div class="detail-header">
              <div class="user-profile">
                <div class="profile-avatar-large">
                  {{ getUserInitials(selectedInscription) }}
                </div>
                <div class="profile-info">
                  <h4>{{ selectedInscription.employe_prenom }} {{ selectedInscription.employe_nom }}</h4>
                  <p class="user-email">
                    <i class="bi bi-envelope-fill"></i>
                    {{ selectedInscription.employe_email }}
                  </p>
                </div>
              </div>
              
              <div :class="`status-badge-large status-${selectedInscription.statut.toLowerCase()}`">
                <i :class="getStatusIcon(selectedInscription.statut)"></i>
                {{ formatStatus(selectedInscription.statut) }}
              </div>
            </div>
            
            <!-- Informations de l'inscription -->
            <div class="detail-content">
              <div class="info-section">
                <h5><i class="bi bi-calendar-event"></i> Informations de session</h5>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">Formation</div>
                    <div class="info-value">{{ selectedInscription.formation_titre }}</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Date début</div>
                    <div class="info-value">{{ formatDate(selectedInscription.date_debut) }}</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Date fin</div>
                    <div class="info-value">{{ formatDate(selectedInscription.date_fin) }}</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Salle</div>
                    <div class="info-value">{{ selectedInscription.salle }}</div>
                  </div>
                </div>
              </div>
              
              <!-- Informations de l'inscription -->
              <div class="info-section">
                <h5><i class="bi bi-file-text"></i> Détails de l'inscription</h5>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">Date d'inscription</div>
                    <div class="info-value">{{ formatDate(selectedInscription.date_inscription) }}</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Statut</div>
                    <div :class="`status-badge-inline status-${selectedInscription.statut.toLowerCase()}`">
                      {{ formatStatus(selectedInscription.statut) }}
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Raison du refus (si applicable) -->
              <div v-if="selectedInscription.statut === 'REFUSEE' && selectedInscription.raison_refus" 
                   class="info-section refused-reason">
                <h5><i class="bi bi-exclamation-triangle-fill"></i> Raison du refus</h5>
                <div class="reason-content">
                  <i class="bi bi-chat-left-quote-fill"></i>
                  <p>{{ selectedInscription.raison_refus }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="modal-footer">
          <button @click="closeModal" class="btn-close">
            <i class="bi bi-x-circle"></i>
            Fermer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import formateurService from '@/services/formateur'

export default {
  name: 'FormateurInscriptionsList',
  props: {
    sessionId: {
      type: [String, Number],
      required: true
    }
  },
  data() {
    return {
      inscriptions: [],
      sessionDetails: null,
      loading: true,
      searchTerm: '',
      selectedStatus: '',
      stats: {
        total: 0,
        validated: 0,
        refused: 0,
        cancelled: 0,
        pending: 0
      },
      showModal: false,
      selectedInscription: null
    }
  },
  created() {
    this.loadInscriptions()
    this.loadSessionDetails()
  },
  methods: {
    async loadInscriptions() {
      this.loading = true
      try {
        const params = {
          session_id: this.sessionId
        }
        
        if (this.selectedStatus) {
          params.statut = this.selectedStatus
        }
        
        if (this.searchTerm) {
          params.recherche = this.searchTerm
        }
        
        const response = await formateurService.getSessionInscriptions(params)
        this.inscriptions = response.data.data || []
        
        // Calculer les statistiques
        this.calculateStats()
        
      } catch (error) {
        console.error('Erreur chargement inscriptions:', error)
        this.$toast.error('Erreur lors du chargement des inscriptions')
      } finally {
        this.loading = false
      }
    },
    
    async loadSessionDetails() {
      try {
        const response = await formateurService.getMySessions()
        const sessions = response.data.data || []
        this.sessionDetails = sessions.find(s => s.id == this.sessionId)
      } catch (error) {
        console.error('Erreur chargement détails session:', error)
      }
    },
    
    calculateStats() {
      this.stats.total = this.inscriptions.length
      this.stats.validated = this.inscriptions.filter(i => i.statut === 'VALIDEE').length
      this.stats.refused = this.inscriptions.filter(i => i.statut === 'REFUSEE').length
      this.stats.cancelled = this.inscriptions.filter(i => i.statut === 'ANNULEE').length
      this.stats.pending = this.inscriptions.filter(i => i.statut === 'EN_ATTENTE').length
    },
    
    onSearch() {
      clearTimeout(this.searchTimeout)
      this.searchTimeout = setTimeout(() => {
        this.loadInscriptions()
      }, 500)
    },
    
    resetFilters() {
      this.searchTerm = ''
      this.selectedStatus = ''
      this.loadInscriptions()
    },
    
    formatDate(dateString) {
      if (!dateString) return ''
      try {
        const date = new Date(dateString)
        return date.toLocaleDateString('fr-FR', {
          day: '2-digit',
          month: 'long',
          year: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        })
      } catch (e) {
        return dateString
      }
    },
    
    formatDateShort(dateString) {
      if (!dateString) return ''
      try {
        const date = new Date(dateString)
        return date.toLocaleDateString('fr-FR', {
          day: '2-digit',
          month: '2-digit',
          year: 'numeric'
        })
      } catch (e) {
        return dateString
      }
    },
    
    formatStatus(status) {
      const statusMap = {
        'EN_ATTENTE': 'En attente',
        'VALIDEE': 'Validée',
        'REFUSEE': 'Refusée',
        'ANNULEE': 'Annulée'
      }
      return statusMap[status] || status
    },
    
    getStatusIcon(status) {
      const iconMap = {
        'EN_ATTENTE': 'bi bi-clock-history',
        'VALIDEE': 'bi bi-check-circle-fill',
        'REFUSEE': 'bi bi-x-circle-fill',
        'ANNULEE': 'bi bi-slash-circle-fill'
      }
      return iconMap[status] || 'bi bi-circle'
    },
    
    getUserInitials(inscription) {
      if (!inscription.employe_prenom || !inscription.employe_nom) return 'U'
      return `${inscription.employe_prenom.charAt(0)}${inscription.employe_nom.charAt(0)}`.toUpperCase()
    },
    
    showDetails(inscription) {
      this.selectedInscription = inscription
      this.showModal = true
    },
    
    showRefusalReason(inscription) {
      this.selectedInscription = inscription
      this.showModal = true
    },
    
    closeModal() {
      this.showModal = false
      this.selectedInscription = null
    }
  }
}
</script>

<style scoped>
/* ========================================
   LAYOUT PRINCIPAL
   ======================================== */
.inscriptions-modern {
  min-height: 100vh;
  background: #f8fafc;
  margin-left: 17%;
  padding: 0;
}

/* ========================================
   EN-TÊTE MODERNE
   ======================================== */
.modern-header {
  background: white;
  padding: 30px 40px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  margin-bottom: 30px;
  border-bottom: 1px solid #e2e8f0;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 25px;
  flex-wrap: wrap;
  gap: 20px;
}

.title-section-modern {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.back-btn-modern {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: #f1f5f9;
  color: #2d3748;
  padding: 0.65rem 1.5rem;
  border-radius: 10px;
  font-weight: 500;
  border: 1px solid #e2e8f0;
  cursor: pointer;
  transition: all 0.3s ease;
  align-self: flex-start;
  margin-bottom: 10px;
}

.back-btn-modern:hover {
  background: #e2e8f0;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.modern-title {
  font-size: 2em;
  font-weight: 700;
  color: #2d3748;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 12px;
}

.modern-title i {
  font-size: 1.1em;
  color: #4361ee;
}

.modern-subtitle {
  color: #718096;
  margin: 0;
  font-size: 1.05em;
  font-weight: 500;
}

.header-actions {
  display: flex;
  gap: 15px;
}

.stats-badge-modern {
  background: #f1f5f9;
  padding: 10px 20px;
  border-radius: 20px;
  display: flex;
  align-items: center;
  gap: 8px;
  color: #2d3748;
  font-weight: 600;
  border: 1px solid #e2e8f0;
}

.stats-badge-modern i {
  font-size: 1.2em;
  color: #4361ee;
}

/* ========================================
   INFORMATIONS SESSION MODERNES
   ======================================== */
.session-info-modern {
  max-width: 1400px;
  margin: 0 auto 30px;
  padding: 0 40px;
}

.session-details-card {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
  border: 1px solid #e2e8f0;
  display: flex;
  gap: 20px;
  padding: 25px;
  transition: all 0.3s ease;
}

.session-details-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
}

.session-icon-modern {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #4361ee, #3a56d4);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
}

.session-icon-modern i {
  font-size: 1.8em;
  color: white;
}

.session-info-content {
  flex: 1;
}

.session-title-modern {
  font-size: 1.3em;
  font-weight: 700;
  color: #2d3748;
  margin: 0 0 15px 0;
}

.session-meta-modern {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 15px;
}

.meta-item-modern {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px;
  background: #f8fafc;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.meta-item-modern:hover {
  background: #edf2f7;
  transform: translateX(3px);
}

.meta-item-modern i {
  color: #4361ee;
  font-size: 1.1rem;
  min-width: 20px;
}

.meta-label {
  font-size: 0.8rem;
  color: #718096;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
}

.meta-value {
  font-weight: 600;
  color: #2d3748;
  font-size: 0.95rem;
}

/* ========================================
   STATISTIQUES MODERNES
   ======================================== */
.stats-section-modern {
  max-width: 1400px;
  margin: 0 auto 30px;
  padding: 0 40px;
}

.stats-grid-modern {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
}

.stat-box-modern {
  background: white;
  padding: 20px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  gap: 15px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
  transition: all 0.3s ease;
  border: 1px solid #e2e8f0;
}

.stat-box-modern:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.stat-icon-modern {
  width: 55px;
  height: 55px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5em;
  flex-shrink: 0;
}

.stat-total .stat-icon-modern {
  background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
  color: #4361ee;
}

.stat-validated .stat-icon-modern {
  background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
  color: #10b981;
}

.stat-pending .stat-icon-modern {
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
  color: #f59e0b;
}

.stat-refused .stat-icon-modern {
  background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
  color: #ef4444;
}

.stat-cancelled .stat-icon-modern {
  background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
  color: #64748b;
}

.stat-content-modern {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.stat-value-modern {
  font-size: 1.8em;
  font-weight: 700;
  color: #2d3748;
  line-height: 1;
}

.stat-label-modern {
  font-size: 0.85em;
  color: #718096;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* ========================================
   FILTRES MODERNES
   ======================================== */
.filters-section-modern {
  max-width: 1400px;
  margin: 0 auto 30px;
  padding: 0 40px;
}

.search-bar-modern {
  margin-bottom: 20px;
}

.search-input-wrapper {
  position: relative;
  width: 100%;
}

.search-input-wrapper i {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #64748b;
  font-size: 1.2rem;
  z-index: 2;
}

.modern-search-input {
  width: 100%;
  padding: 0.875rem 1rem 0.875rem 3rem;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  background: white;
  color: #2d3748;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.modern-search-input:focus {
  border-color: #4361ee;
  background: white;
  box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1);
  outline: none;
}

.modern-search-input::placeholder {
  color: #94a3b8;
  font-size: 0.9rem;
}

.filters-row-modern {
  display: flex;
  gap: 15px;
  align-items: flex-end;
  flex-wrap: wrap;
}

.filter-group-modern {
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex: 1;
  min-width: 200px;
}

.filter-label-modern {
  color: #475569;
  font-weight: 600;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 6px;
}

.filter-label-modern i {
  font-size: 1em;
  color: #4361ee;
}

.modern-select {
  padding: 0.75rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  background: white;
  font-size: 0.95rem;
  color: #2d3748;
  cursor: pointer;
  transition: all 0.3s ease;
}

.modern-select:focus {
  outline: none;
  border-color: #4361ee;
  box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1);
}

.reset-filters-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 0.75rem 1.5rem;
  background: #64748b;
  color: white;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.reset-filters-btn:hover {
  background: #475569;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(100, 116, 139, 0.2);
}

.reset-filters-btn i {
  font-size: 1rem;
}

/* ========================================
   CHARGEMENT
   ======================================== */
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

/* ========================================
   ÉTAT VIDE MODERNE
   ======================================== */
.empty-state-modern {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 400px;
  padding: 3rem;
}

.empty-state-content-modern {
  text-align: center;
  max-width: 500px;
  animation: fadeIn 0.5s ease;
}

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

.empty-state-icon-modern {
  width: 120px;
  height: 120px;
  background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.5rem;
}

.empty-state-icon-modern i {
  font-size: 3.5rem;
  background: linear-gradient(135deg, #cbd5e0 0%, #94a3b8 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.empty-state-text-modern h3 {
  color: #2d3748;
  font-size: 1.75rem;
  font-weight: 700;
  margin-bottom: 1rem;
}

.empty-state-text-modern p {
  color: #718096;
  font-size: 1.1rem;
  line-height: 1.6;
  margin-bottom: 1.5rem;
}

.empty-state-btn-modern {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: linear-gradient(135deg, #4361ee, #3a56d4);
  color: white;
  border: none;
  padding: 0.75rem 2rem;
  border-radius: 12px;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 5px 15px rgba(67, 97, 238, 0.2);
}

.empty-state-btn-modern:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(67, 97, 238, 0.3);
}

.empty-state-btn-modern i {
  font-size: 1.1rem;
}

/* ========================================
   TABLEAU MODERNE
   ======================================== */
.table-section-modern {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 40px 40px;
}

.table-container-modern {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
  border: 1px solid #e2e8f0;
}

.table-header-modern {
  padding: 20px 25px;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.table-header-modern h3 {
  font-size: 1.1em;
  font-weight: 600;
  color: #2d3748;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.table-header-modern h3 i {
  color: #4361ee;
}

.table-actions {
  display: flex;
  gap: 15px;
  align-items: center;
}

.table-count {
  color: #718096;
  font-size: 0.9em;
  font-weight: 500;
}

.table-wrapper-modern {
  overflow-x: auto;
}

.inscriptions-table-modern {
  width: 100%;
  border-collapse: collapse;
}

.inscriptions-table-modern thead {
  background: #f1f5f9;
}

.inscriptions-table-modern th {
  padding: 18px 20px;
  text-align: left;
  font-weight: 600;
  color: #2d3748;
  font-size: 0.85em;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  border-bottom: 2px solid #e2e8f0;
  white-space: nowrap;
}

.inscriptions-table-modern th i {
  margin-right: 8px;
  color: #4361ee;
}

.inscriptions-table-modern td {
  padding: 16px 20px;
  border-bottom: 1px solid #e2e8f0;
}

.table-row-modern {
  transition: all 0.2s ease;
}

.table-row-modern:hover {
  background: #f8fafc;
}

/* Colonnes spécifiques */
.th-name, .td-name { min-width: 200px; }
.th-email, .td-email { min-width: 250px; }
.th-date, .td-date { min-width: 150px; }
.th-status, .td-status { min-width: 150px; }
.th-actions, .td-actions { min-width: 120px; }

/* Styles des cellules */
.td-name .user-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-avatar {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #4361ee, #3a56d4);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.9rem;
  flex-shrink: 0;
}

.user-details strong {
  color: #2d3748;
  font-weight: 600;
  font-size: 0.95rem;
  display: block;
  margin-bottom: 2px;
}

.email-link {
  color: #4361ee;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 500;
  transition: color 0.2s ease;
}

.email-link:hover {
  color: #3a56d4;
  text-decoration: underline;
}

.date-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  background: #f8fafc;
  border-radius: 6px;
  color: #64748b;
  font-size: 0.9rem;
  font-weight: 500;
}

/* Badges de statut modernes */
.status-badge-modern {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-en_attente {
  background: rgba(245, 158, 11, 0.12);
  color: #92400e;
}

.status-validee {
  background: rgba(16, 185, 129, 0.12);
  color: #065f46;
}

.status-refusee {
  background: rgba(239, 68, 68, 0.12);
  color: #991b1b;
}

.status-annulee {
  background: rgba(100, 116, 139, 0.12);
  color: #475569;
}

.refused-hint {
  margin-left: 8px;
  color: #ef4444;
  cursor: pointer;
  font-size: 1em;
  transition: transform 0.2s ease;
}

.refused-hint:hover {
  transform: scale(1.2);
}

/* Bouton détails */
.btn-details-modern {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  background: linear-gradient(135deg, #4361ee, #3a56d4);
  color: white;
  padding: 6px 12px;
  border-radius: 6px;
  font-weight: 500;
  font-size: 0.85rem;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-details-modern:hover {
  background: linear-gradient(135deg, #3a56d4, #2d46b8);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
}

/* ========================================
   MODAL MODERNE
   ======================================== */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
  backdrop-filter: blur(5px);
}

.modal-container {
  background: white;
  border-radius: 16px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  animation: modalSlideIn 0.3s ease;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-30px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.modal-header {
  padding: 20px 25px;
  background: linear-gradient(135deg, #4361ee, #3a56d4);
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-radius: 16px 16px 0 0;
}

.modal-header h3 {
  margin: 0;
  font-weight: 600;
  font-size: 1.3em;
  display: flex;
  align-items: center;
  gap: 10px;
}

.btn-close-modal {
  background: none;
  border: none;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 5px;
  border-radius: 50%;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.3s ease;
}

.btn-close-modal:hover {
  background: rgba(255, 255, 255, 0.2);
}

.modal-body {
  padding: 25px;
}

.inscription-details {
  display: flex;
  flex-direction: column;
  gap: 25px;
}

.detail-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 20px;
  margin-bottom: 10px;
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 15px;
}

.profile-avatar-large {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #4361ee, #3a56d4);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1.3rem;
  flex-shrink: 0;
}

.profile-info h4 {
  color: #2d3748;
  font-size: 1.4em;
  font-weight: 700;
  margin: 0 0 5px 0;
}

.user-email {
  color: #64748b;
  font-size: 0.95em;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.status-badge-large {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-badge-large i {
  font-size: 1.1rem;
}

.detail-content {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.info-section {
  padding: 20px;
  background: #f8fafc;
  border-radius: 12px;
  border-left: 4px solid #4361ee;
}

.info-section h5 {
  color: #2d3748;
  font-size: 1.1em;
  font-weight: 600;
  margin: 0 0 15px 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.info-section h5 i {
  color: #4361ee;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
}

.info-item {
  padding: 12px;
  background: white;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
}

.info-label {
  font-size: 0.75rem;
  color: #718096;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 4px;
  font-weight: 600;
}

.info-value {
  color: #2d3748;
  font-weight: 500;
  font-size: 0.95rem;
}

.status-badge-inline {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.refused-reason {
  border-left-color: #ef4444;
}

.refused-reason h5 i {
  color: #ef4444;
}

.reason-content {
  background: rgba(239, 68, 68, 0.05);
  border: 1px solid rgba(239, 68, 68, 0.2);
  border-radius: 8px;
  padding: 15px;
  display: flex;
  gap: 12px;
  align-items: flex-start;
}

.reason-content i {
  color: #ef4444;
  font-size: 1.2rem;
  flex-shrink: 0;
  margin-top: 2px;
}

.reason-content p {
  margin: 0;
  color: #991b1b;
  line-height: 1.5;
  font-size: 0.95rem;
}

.modal-footer {
  padding: 20px 25px;
  border-top: 1px solid #e2e8f0;
  display: flex;
  justify-content: flex-end;
}

.btn-close {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background: #64748b;
  color: white;
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-close:hover {
  background: #475569;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* ========================================
   RESPONSIVE
   ======================================== */
@media (max-width: 1400px) {
  .inscriptions-modern {
    margin-left: 20%;
  }
}

@media (max-width: 1200px) {
  .inscriptions-modern {
    margin-left: 17%;
  }
}

@media (max-width: 768px) {
  .inscriptions-modern {
    margin-left: 0;
  }
  
  .modern-header {
    padding: 20px;
  }
  
  .modern-title {
    font-size: 1.5em;
  }
  
  .session-info-modern,
  .stats-section-modern,
  .filters-section-modern,
  .table-section-modern {
    padding: 0 20px;
  }
  
  .session-details-card {
    flex-direction: column;
    padding: 20px;
  }
  
  .session-meta-modern {
    grid-template-columns: 1fr;
  }
  
  .stats-grid-modern {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .filters-row-modern {
    flex-direction: column;
  }
  
  .filter-group-modern {
    min-width: 100%;
  }
  
  .table-header-modern {
    flex-direction: column;
    gap: 10px;
    align-items: stretch;
  }
  
  .table-actions {
    justify-content: space-between;
  }
  
  .modal-container {
    width: 95%;
    margin: 0 10px;
  }
  
  .detail-header {
    flex-direction: column;
    align-items: stretch;
  }
  
  .info-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .stats-grid-modern {
    grid-template-columns: 1fr;
  }
  
  .user-info {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  
  .action-buttons {
    display: flex;
    justify-content: center;
  }
  
  .modal-body {
    padding: 20px;
  }
  
  .info-section {
    padding: 15px;
  }
}
</style>