<template>
  <div class="sessions-list">
    <!-- En-tête moderne sans background -->
    <div class="modern-header">
      <div class="header-content">
        <div class="title-section-modern">
          <h1 class="modern-title">
            <i class="bi bi-calendar3"></i>
            Mes Sessions
          </h1>
          <p class="modern-subtitle">Gérez vos sessions de formation</p>
        </div>
        
        <div class="header-actions">
          <div class="stats-badge" v-if="!loading">
            <i class="bi bi-list-task"></i>
            <span>{{ pagination.total }} session(s)</span>
          </div>
        </div>
      </div>
      
      <!-- Barre de recherche et filtres modernes -->
      <div class="search-filters-modern">
        <div class="search-bar-modern">
          <div class="search-input-wrapper">
            <i class="bi bi-search"></i>
            <input 
              v-model="searchTerm" 
              @input="applyFilters" 
              placeholder="Rechercher une formation..."
              class="modern-search-input"
            >
          </div>
        </div>
        
        <div class="filters-row-modern">
          <div class="filter-group-modern">
            <div class="filter-label">
              <i class="bi bi-check-circle"></i>
              Statut
            </div>
            <select v-model="selectedStatus" @change="applyFilters" class="modern-select">
              <option value="">Tous les statuts</option>
              <option value="EN_ATTENTE">En attente</option>
              <option value="ACCEPTEE">Acceptées</option>
              <option value="REFUSEE">Refusées</option>
            </select>
          </div>
          
          <div class="filter-group-modern">
            <div class="filter-label">
              <i class="bi bi-calendar-range"></i>
              Période
            </div>
            <select v-model="selectedPeriod" @change="applyFilters" class="modern-select">
              <option value="">Toutes périodes</option>
              <option value="futures">Futures</option>
              <option value="en_cours">En cours</option>
              <option value="passees">Passées</option>
            </select>
          </div>
          
          <button @click="resetFilters" class="reset-filters-btn">
            <i class="bi bi-arrow-clockwise"></i>
            Réinitialiser
          </button>
        </div>
      </div>
    </div>
    
    <!-- Chargement -->
    <div v-if="loading" class="loading-container">
      <div class="spinner-container">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Chargement...</span>
        </div>
        <p class="loading-text">Chargement des sessions...</p>
      </div>
    </div>
    
    <!-- État vide -->
    <div v-else-if="sessions.length === 0" class="no-formations-modern">
      <div class="empty-state-modern">
        <div class="empty-state-content-modern">
          <div class="empty-state-icon-modern">
            <i class="bi bi-calendar-x"></i>
          </div>
          <div class="empty-state-text-modern">
            <h3>Aucune session trouvée</h3>
            <p>Il n'y a pas de sessions correspondant à vos critères de recherche.</p>
          </div>
          <button @click="resetFilters" class="empty-state-btn-modern">
            <i class="bi bi-arrow-clockwise"></i>
            Réinitialiser les filtres
          </button>
        </div>
      </div>
    </div>
    
    <!-- Grille de sessions -->
    <div v-else class="sessions-container">
      <div class="sessions-grid-modern">
        <div v-for="session in sessions" :key="session.id" class="session-box-modern">
          <!-- Badge statut -->
          <div class="box-badge-modern" :class="getStatusClass(session.statut_formateur)">
            <div class="badge-dot-modern"></div>
            <span>{{ formatStatus(session.statut_formateur) }}</span>
          </div>
          
          <!-- En-tête de la boîte -->
          <div class="box-header-modern">
            <div class="session-header-modern">
              <div class="session-icon-modern">
                <i class="bi bi-calendar3-fill"></i>
              </div>
              <div class="session-title-modern">
                <h3>{{ session.formation_titre }}</h3>
                <p class="session-description-modern">
                  Session de formation
                </p>
              </div>
            </div>
          </div>
          
          <!-- Corps de la boîte -->
          <div class="box-body-modern">
            <!-- Informations session -->
            <div class="session-info-modern">
              <div class="info-row-modern">
                <div class="info-item-modern">
                  <div class="info-icon-modern">
                    <i class="bi bi-calendar-event-fill"></i>
                  </div>
                  <div class="info-content-modern">
                    <div class="info-label-modern">Début</div>
                    <div class="info-value-modern">{{ formatDate(session.date_debut) }}</div>
                  </div>
                </div>
                
                <div class="info-item-modern">
                  <div class="info-icon-modern">
                    <i class="bi bi-calendar-check-fill"></i>
                  </div>
                  <div class="info-content-modern">
                    <div class="info-label-modern">Fin</div>
                    <div class="info-value-modern">{{ formatDate(session.date_fin) }}</div>
                  </div>
                </div>
              </div>
              
              <div class="info-row-modern">
                <div class="info-item-modern">
                  <div class="info-icon-modern">
                    <i class="bi bi-geo-alt-fill"></i>
                  </div>
                  <div class="info-content-modern">
                    <div class="info-label-modern">Salle</div>
                    <div class="info-value-modern">{{ session.salle || 'Non spécifiée' }}</div>
                  </div>
                </div>
                
                <div class="info-item-modern">
                  <div class="info-icon-modern">
                    <i class="bi bi-people-fill"></i>
                  </div>
                  <div class="info-content-modern">
                    <div class="info-label-modern">Places</div>
                    <div class="info-value-modern">{{ session.places_restantes }} / {{ session.places_max }}</div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Commentaire formateur -->
            <div v-if="session.commentaire_formateur" class="comment-section-modern">
              <div class="comment-header-modern">
                <i class="bi bi-chat-left-text-fill"></i>
                <span class="comment-title-modern">Votre commentaire</span>
              </div>
              <p class="comment-text-modern">{{ session.commentaire_formateur }}</p>
            </div>
          </div>
          
          <!-- Pied de la boîte -->
          <div class="box-footer-modern">
            <!-- Boutons pour sessions en attente -->
            <div v-if="session.statut_formateur === 'EN_ATTENTE'" class="action-buttons-modern">
              <button @click="acceptSession(session)" class="action-btn-modern btn-accept-session">
                <i class="bi bi-check-circle-fill"></i>
                <span>Accepter</span>
              </button>
              <button @click="refuseSession(session)" class="action-btn-modern btn-refuse-session">
                <i class="bi bi-x-circle-fill"></i>
                <span>Refuser</span>
              </button>
            </div>
            
            <!-- Bouton pour sessions acceptées -->
            <div v-if="session.statut_formateur === 'ACCEPTEE'" class="action-buttons-modern">
              <button @click="viewInscriptions(session)" class="action-btn-modern btn-view-inscriptions">
                <i class="bi bi-people-fill"></i>
                <span>Voir les inscrits</span>
              </button>
            </div>
            
            <!-- Info pour sessions refusées -->
            <div v-if="session.statut_formateur === 'REFUSEE'" class="refused-info-modern">
              <i class="bi bi-info-circle-fill"></i>
              <span>Cette session a été refusée</span>
            </div>
          </div>
          
          <!-- Effet de bordure animé -->
          <div class="box-border-animation"></div>
        </div>
      </div>
    </div>
    
    <!-- Pagination -->
    <div v-if="pagination.pages > 1" class="pagination-modern">
      <button 
        @click="prevPage" 
        :disabled="pagination.page === 1"
        class="pagination-btn-modern btn-prev"
      >
        <i class="bi bi-chevron-left"></i>
        <span>Précédent</span>
      </button>
      
      <div class="page-info-modern">
        <span class="current-page-modern">{{ pagination.page }}</span>
        <span class="separator-modern">/</span>
        <span class="total-pages-modern">{{ pagination.pages }}</span>
      </div>
      
      <button 
        @click="nextPage" 
        :disabled="pagination.page === pagination.pages"
        class="pagination-btn-modern btn-next"
      >
        <span>Suivant</span>
        <i class="bi bi-chevron-right"></i>
      </button>
    </div>
  </div>
</template>

<script>
import formateurService from '@/services/formateur'

export default {
  name: 'FormateurSessionsList',
  data() {
    return {
      sessions: [],
      loading: true,
      searchTerm: '',
      selectedStatus: '',
      selectedPeriod: '',
      pagination: {
        page: 1,
        limit: 10,
        pages: 1,
        total: 0
      }
    }
  },
  created() {
    const query = this.$route.query
    if (query.statut) this.selectedStatus = query.statut
    if (query.periode) this.selectedPeriod = query.periode
    if (query.search) this.searchTerm = query.search
    
    this.loadSessions()
  },
  methods: {
    async loadSessions() {
      this.loading = true
      try {
        const params = {
          page: this.pagination.page,
          limit: this.pagination.limit
        }
        
        if (this.searchTerm) params.recherche = this.searchTerm
        if (this.selectedStatus) params.statut = this.selectedStatus
        if (this.selectedPeriod) params.periode = this.selectedPeriod
        
        const response = await formateurService.getMySessions(params)
        
        this.sessions = response.data.data
        this.pagination = {
          page: response.data.pagination.page,
          limit: response.data.pagination.limit,
          pages: response.data.pagination.pages,
          total: response.data.pagination.total
        }
        
      } catch (error) {
        console.error('Erreur chargement sessions:', error)
        this.$toast.error('Erreur lors du chargement des sessions')
      } finally {
        this.loading = false
      }
    },
    
    applyFilters() {
      this.pagination.page = 1
      this.loadSessions()
      
      const query = {}
      if (this.searchTerm) query.search = this.searchTerm
      if (this.selectedStatus) query.statut = this.selectedStatus
      if (this.selectedPeriod) query.periode = this.selectedPeriod
      
      this.$router.replace({ query })
    },
    
    resetFilters() {
      this.searchTerm = ''
      this.selectedStatus = ''
      this.selectedPeriod = ''
      this.applyFilters()
    },
    
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    },
    
    formatStatus(status) {
      const statusMap = {
        'EN_ATTENTE': 'En attente',
        'ACCEPTEE': 'Acceptée',
        'REFUSEE': 'Refusée'
      }
      return statusMap[status] || status
    },
    
    getStatusClass(status) {
      return status.toLowerCase() === 'en_attente' ? 'inactive' : 
             status.toLowerCase() === 'acceptee' ? 'active' : 'inactive'
    },
    
    async acceptSession(session) {
      if (confirm(`Accepter la session "${session.formation_titre}" ?`)) {
        try {
          await formateurService.respondToSession(session.id, 'ACCEPTEE')
          this.$toast.success('Session acceptée')
          this.loadSessions()
        } catch (error) {
          console.error('Erreur:', error)
          this.$toast.error('Erreur lors de l\'acceptation')
        }
      }
    },
    
    async refuseSession(session) {
      const comment = prompt('Raison du refus (optionnel) :')
      try {
        await formateurService.respondToSession(session.id, 'REFUSEE', comment || '')
        this.$toast.success('Session refusée')
        this.loadSessions()
      } catch (error) {
        console.error('Erreur:', error)
        this.$toast.error('Erreur lors du refus')
      }
    },
    
    viewInscriptions(session) {
      this.$router.push(`/formateur/sessions/${session.id}/inscriptions`)
    },
    
    prevPage() {
      if (this.pagination.page > 1) {
        this.pagination.page--
        this.loadSessions()
      }
    },
    
    nextPage() {
      if (this.pagination.page < this.pagination.pages) {
        this.pagination.page++
        this.loadSessions()
      }
    }
  }
}
</script>

<style scoped>
/* ========================================
   LAYOUT PRINCIPAL
   ======================================== */
.sessions-list {
  min-height: 100vh;
  background: #f8fafc;
  margin-left: 17%;
  padding: 0;
}

/* ========================================
   EN-TÊTE MODERNE SANS BACKGROUND
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
}

.modern-title {
  font-size: 2em;
  font-weight: 700;
  color: #2d3748;
  margin: 0 0 8px 0;
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

.stats-badge {
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

.stats-badge i {
  font-size: 1.2em;
  color: #4361ee;
}

/* ========================================
   RECHERCHE ET FILTRES MODERNES
   ======================================== */
.search-filters-modern {
  background: #f8fafc;
  padding: 20px;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
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

.filter-label {
  color: #475569;
  font-weight: 600;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 6px;
}

.filter-label i {
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
   CHARGEMENT - STYLE DES FORMATIONS
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
   ÉTAT VIDE - EXACTEMENT LE MÊME STYLE QUE LES FORMATIONS
   ======================================== */
.no-formations-modern {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 400px;
  padding: 3rem;
}

.empty-state-modern {
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

.empty-state-content-modern {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.5rem;
}

.empty-state-icon-modern {
  width: 120px;
  height: 120px;
  background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
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
  margin-bottom: 0.5rem;
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
   CONTENEUR SESSIONS
   ======================================== */
.sessions-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 40px 40px;
}

/* ========================================
   GRILLE DE BOÎTES MODERNE - EXACTEMENT LE MÊME STYLE
   ======================================== */
.sessions-grid-modern {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 2rem;
  margin-bottom: 3rem;
}

.session-box-modern {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
  border: 1px solid #e2e8f0;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  display: flex;
  flex-direction: column;
}

.session-box-modern:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 32px rgba(67, 97, 238, 0.15);
  border-color: #4361ee;
}

/* Animation d'entrée */
.session-box-modern {
  opacity: 0;
  transform: translateY(20px);
  animation: boxEntrance 0.6s ease forwards;
}

@keyframes boxEntrance {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.session-box-modern:nth-child(1) { animation-delay: 0.1s; }
.session-box-modern:nth-child(2) { animation-delay: 0.2s; }
.session-box-modern:nth-child(3) { animation-delay: 0.3s; }
.session-box-modern:nth-child(4) { animation-delay: 0.4s; }
.session-box-modern:nth-child(5) { animation-delay: 0.5s; }
.session-box-modern:nth-child(6) { animation-delay: 0.6s; }

/* Badge statut - MÊME STYLE QUE FORMATIONS */
.box-badge-modern {
  position: absolute;
  top: 1rem;
  right: 1rem;
  display: flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.4rem 0.8rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  z-index: 2;
  backdrop-filter: blur(8px);
}

.box-badge-modern.active {
  background: rgba(16, 185, 129, 0.12);
  color: #065f46;
}

.box-badge-modern.inactive {
  background: rgba(239, 68, 68, 0.12);
  color: #991b1b;
}

.badge-dot-modern {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  animation: pulse 2s infinite;
}

.box-badge-modern.active .badge-dot-modern {
  background: #10b981;
}

.box-badge-modern.inactive .badge-dot-modern {
  background: #ef4444;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

/* Header de la boîte - MÊME STYLE QUE FORMATIONS */
.box-header-modern {
  background: linear-gradient(135deg, #4361ee15 0%, #3a56d415 100%);
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
}

.session-header-modern {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 0.5rem;
}

.session-icon-modern {
  width: 56px;
  height: 56px;
  background: linear-gradient(135deg, #4361ee, #3a56d4);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.4rem;
  flex-shrink: 0;
  transition: transform 0.3s ease;
}

.session-box-modern:hover .session-icon-modern {
  transform: rotate(10deg) scale(1.1);
}

.session-title-modern {
  flex: 1;
}

.session-title-modern h3 {
  color: #2d3748;
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0 0 0.5rem 0;
  line-height: 1.3;
}

.session-description-modern {
  color: #718096;
  font-size: 0.9rem;
  line-height: 1.5;
  margin: 0;
}

/* Corps de la boîte - MÊME STYLE QUE FORMATIONS */
.box-body-modern {
  padding: 1.5rem;
  flex: 1;
}

/* Informations session - MÊME STYLE QUE FORMATIONS */
.session-info-modern {
  margin-bottom: 1.5rem;
}

.info-row-modern {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  margin-bottom: 1rem;
}

.info-item-modern {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background: #f8fafc;
  border-radius: 10px;
  transition: all 0.3s ease;
}

.info-item-modern:hover {
  background: #edf2f7;
  transform: translateX(3px);
}

.info-icon-modern {
  width: 40px;
  height: 40px;
  background: white;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #4361ee;
  font-size: 1.1rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.info-content-modern {
  flex: 1;
}

.info-label-modern {
  font-size: 0.75rem;
  color: #718096;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.25rem;
}

.info-value-modern {
  font-weight: 600;
  color: #2d3748;
  font-size: 0.95rem;
}

/* Commentaire */
.comment-section-modern {
  margin-top: 1.5rem;
}

.comment-header-modern {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
}

.comment-header-modern i {
  color: #f59e0b;
  font-size: 0.95rem;
}

.comment-title-modern {
  color: #475569;
  font-weight: 600;
  font-size: 0.85rem;
}

.comment-text-modern {
  color: #78350f;
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
  padding: 0.75rem;
  border-radius: 8px;
  font-size: 0.85rem;
  line-height: 1.5;
  margin: 0;
  border-left: 3px solid #f59e0b;
}

/* Footer de la boîte - MÊME STYLE QUE FORMATIONS */
.box-footer-modern {
  padding: 1.5rem;
  padding-top: 0;
  border-top: 1px solid #e2e8f0;
  margin-top: auto;
}

.action-buttons-modern {
  display: flex;
  gap: 0.75rem;
}

.action-btn-modern {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  flex: 1;
  padding: 0.7rem 1rem;
  border-radius: 8px;
  border: none;
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.btn-accept-session {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
}

.btn-accept-session:hover {
  background: linear-gradient(135deg, #059669, #047857);
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
}

.btn-refuse-session {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
}

.btn-refuse-session:hover {
  background: linear-gradient(135deg, #dc2626, #b91c1c);
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
}

.btn-view-inscriptions {
  background: linear-gradient(135deg, #4361ee, #3a56d4);
  color: white;
}

.btn-view-inscriptions:hover {
  background: linear-gradient(135deg, #3a56d4, #2d46b8);
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
}

.action-btn-modern::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  transform: translate(-50%, -50%);
  transition: width 0.6s, height 0.6s;
}

.action-btn-modern:hover::before {
  width: 300px;
  height: 300px;
}

.action-btn-modern i,
.action-btn-modern span {
  position: relative;
  z-index: 1;
}

.refused-info-modern {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem;
  background: rgba(239, 68, 68, 0.1);
  border-radius: 10px;
  color: #ef4444;
  font-weight: 600;
  font-size: 0.9rem;
}

.refused-info-modern i {
  font-size: 1rem;
}

/* Effet de bordure animé - MÊME STYLE QUE FORMATIONS */
.box-border-animation {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: linear-gradient(90deg, transparent, #4361ee, transparent);
  transform: translateX(-100%);
  transition: transform 0.6s ease;
}

.session-box-modern:hover .box-border-animation {
  transform: translateX(100%);
}

/* ========================================
   PAGINATION MODERNE
   ======================================== */
.pagination-modern {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 2rem;
  margin: 40px 0 20px;
  padding: 0 40px;
}

.pagination-btn-modern {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border-radius: 12px;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
  color: white;
}

.btn-prev {
  background: linear-gradient(135deg, #94a3b8, #64748b);
}

.btn-next {
  background: linear-gradient(135deg, #4361ee, #3a56d4);
}

.pagination-btn-modern:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.pagination-btn-modern:active:not(:disabled) {
  transform: translateY(0);
}

.pagination-btn-modern:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none !important;
  box-shadow: none !important;
}

.page-info-modern {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
  color: #2d3748;
  font-size: 1.1em;
}

.current-page-modern {
  color: #4361ee;
  font-size: 1.3em;
}

.separator-modern {
  color: #a0aec0;
}

.total-pages-modern {
  color: #718096;
}

/* ========================================
   RESPONSIVE
   ======================================== */
@media (max-width: 1400px) {
  .sessions-list {
    margin-left: 20%;
  }
}

@media (max-width: 1200px) {
  .sessions-list {
    margin-left: 17%;
  }
  
  .sessions-grid-modern {
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
  }
}

@media (max-width: 768px) {
  .sessions-list {
    margin-left: 0;
  }
  
  .modern-header {
    padding: 20px;
  }
  
  .modern-title {
    font-size: 1.6em;
  }
  
  .sessions-container {
    padding: 0 20px 20px;
  }
  
  .search-filters-modern {
    padding: 15px;
  }
  
  .filters-row-modern {
    flex-direction: column;
    align-items: stretch;
  }
  
  .filter-group-modern {
    min-width: 100%;
  }
  
  .reset-filters-btn {
    width: 100%;
    justify-content: center;
  }
  
  .sessions-grid-modern {
    grid-template-columns: 1fr;
  }
  
  .info-row-modern {
    grid-template-columns: 1fr;
  }
  
  .action-buttons-modern {
    flex-direction: column;
  }
  
  .empty-state-modern {
    padding: 2rem 1rem;
    min-height: 300px;
  }
  
  .empty-state-icon-modern {
    width: 100px;
    height: 100px;
  }
  
  .empty-state-icon-modern i {
    font-size: 2.5rem;
  }
  
  .pagination-modern {
    flex-direction: column;
    gap: 1rem;
    padding: 0 20px;
  }
}

@media (max-width: 480px) {
  .session-header-modern {
    flex-direction: column;
    text-align: center;
  }
  
  .session-icon-modern {
    align-self: center;
  }
  
  .empty-state-text-modern h3 {
    font-size: 1.5rem;
  }
  
  .empty-state-text-modern p {
    font-size: 1rem;
  }
}
</style>