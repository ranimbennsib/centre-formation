<template>
  <div class="formations-container">
    <!-- Header avec recherche -->
   <div class="formations-header-centered">
    <!-- Section du titre centré -->
    <div class="title-section-centered">
      <i class="bi bi-mortarboard-fill icon-title-centered"></i>
      <h2>Formations disponibles</h2>
      <p class="subtitle-centered">Développez vos compétences professionnelles</p>
    </div>
    
    <!-- Section de recherche en dessous -->
    <div class="search-section-below">
      <div class="search-wrapper-below">
        <i class="bi bi-search search-icon-below"></i>
        <input 
          type="text" 
          class="search-input-below" 
          placeholder="Rechercher une formation..." 
          v-model="search" 
          @input="loadFormations"
        >
      </div>
    </div>
  </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-container">
      <div class="spinner-container">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Chargement...</span>
        </div>
        <p class="loading-text">Chargement des formations...</p>
      </div>
    </div>

    <!-- Liste des formations avec boîtes modernes -->
    <div v-else-if="formations.length > 0" class="formations-grid-modern">
      <div v-for="formation in formations" :key="formation.id" class="formation-box-modern">
        <!-- Badge actif/inactif -->
        <div class="box-badge-modern" :class="formation.est_actif ? 'active' : 'inactive'">
          <div class="badge-dot-modern"></div>
          <span>{{ formation.est_actif ? 'Active' : 'Inactive' }}</span>
        </div>
        
        <!-- En-tête de la boîte -->
        <div class="box-header-modern">
          <div class="formation-header-modern">
            <div class="formation-icon-modern">
              <i class="bi bi-book-fill"></i>
            </div>
            <div class="formation-title-modern">
              <h3>{{ formation.titre }}</h3>
              <p class="formation-description-modern">
                {{ truncateDescription(formation.description) }}
              </p>
            </div>
          </div>
        </div>
        
        <!-- Corps de la boîte -->
        <div class="box-body-modern">
          <!-- Informations formation -->
          <div class="formation-info-modern">
            <div class="info-row-modern">
              <div class="info-item-modern" v-if="formation.duree_heures">
                <div class="info-icon-modern">
                  <i class="bi bi-clock-fill"></i>
                </div>
                <div class="info-content-modern">
                  <div class="info-label-modern">Durée</div>
                  <div class="info-value-modern">{{ formation.duree_heures }}h</div>
                </div>
              </div>
              
              <div class="info-item-modern" v-if="formatPrix(formation.prix)">
                <div class="info-icon-modern">
                  <i class="bi bi-currency-euro"></i>
                </div>
                <div class="info-content-modern">
                  <div class="info-label-modern">Prix</div>
                  <div class="info-value-modern">{{ formatPrix(formation.prix) }}</div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Compétences visées en tags -->
          <div v-if="formation.competences_visees" class="competences-section-modern">
            <div class="competences-header-modern">
              <i class="bi bi-lightbulb-fill"></i>
              <span class="competences-title-modern">Compétences visées</span>
            </div>
            <div class="tags-container-modern">
              <span class="skill-tag-modern" v-for="(competence, index) in getCompetencesArray(formation.competences_visees)" 
                    :key="index">
                {{ competence }}
              </span>
            </div>
          </div>
        </div>
        
        <!-- Bouton d'action -->
        <div class="box-footer-modern">
          <button class="action-btn-modern btn-view-sessions" @click="voirSessions(formation.id)">
            <i class="bi bi-calendar-week"></i>
            <span>Voir les sessions</span>
          </button>
        </div>
        
        <!-- Effet de bordure animé -->
        <div class="box-border-animation"></div>
      </div>
    </div>

    <!-- Aucune formation -->
    <div v-else class="no-formations-modern">
      <div class="empty-state-modern">
        <div class="empty-state-content-modern">
          <div class="empty-state-icon-modern">
            <i class="bi bi-bookmark-x"></i>
          </div>
          <div class="empty-state-text-modern">
            <h3>Aucune formation disponible</h3>
            <p>Aucune formation ne correspond à votre recherche.</p>
          </div>
          <button class="empty-state-btn-modern" @click="search = ''; loadFormations()">
            <i class="bi bi-arrow-clockwise"></i>
            Réinitialiser la recherche
          </button>
        </div>
      </div>
    </div>

    <!-- Modal des sessions -->
    <div v-if="showSessionsModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-container">
        <div class="modal-header">
          <h3>
            <i class="bi bi-calendar3 me-2"></i>
            Sessions disponibles
          </h3>
          <button class="btn-close-modal" @click="closeModal">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
        
        <div class="modal-body">
          <div v-if="sessionsLoading" class="loading-modal">
            <div class="spinner-border text-primary"></div>
            <p>Chargement des sessions...</p>
          </div>
          
          <div v-else-if="sessions.length > 0" class="sessions-list">
            <div v-for="session in sessions" :key="session.id" class="session-card">
              <div class="session-header">
                <div class="session-date">
                  <i class="bi bi-calendar-date"></i>
                  <span>{{ formatDate(session.date_debut) }}</span>
                </div>
                <div class="session-badge" :class="getPlacesClass(session.places_restantes, session.id)">
                  <i class="bi bi-people-fill"></i>
                  {{ session.places_restantes }} places
                </div>
              </div>
              
              <div class="session-details">
                <div class="detail-item">
                  <i class="bi bi-geo-alt"></i>
                  <span>Salle : {{ session.salle }}</span>
                </div>
                <div class="detail-item">
                  <i class="bi bi-clock-history"></i>
                  <span>{{ formatTime(session.date_debut) }}</span>
                </div>
                
                <!-- Formateur assigné -->
                <div class="detail-item" v-if="session.formateur_nom || session.formateur_prenom">
                  <i class="bi bi-person-badge"></i>
                  <span>Formateur : {{ getFormateurName(session) }}</span>
                </div>
                <div class="detail-item" v-else>
                  <i class="bi bi-person-x"></i>
                  <span>Formateur : Non assigné</span>
                </div>
                
                <!-- Statut inscription (seulement si l'utilisateur est inscrit) -->
                <div class="detail-item" v-if="getInscriptionStatus(session.id)">
                  <i class="bi bi-info-circle"></i>
                  <span>Statut : {{ getInscriptionStatusText(session.id) }}</span>
                </div>
              </div>
              
              <button 
                class="btn btn-register" 
                :class="getButtonClass(session.id, session.places_restantes)"
                @click="handleInscription(session.id)"
                :disabled="isButtonDisabled(session.id, session.places_restantes)"
              >
                <i class="bi me-2" :class="getButtonIcon(session.id, session.places_restantes)"></i>
                {{ getButtonText(session.id, session.places_restantes) }}
              </button>
            </div>
          </div>
          
          <div v-else class="no-sessions">
            <i class="bi bi-calendar-x-fill"></i>
            <h5>Aucune session disponible</h5>
            <p>Aucune session n'est prévue pour cette formation.</p>
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
  name: 'FormationsList',
  
  setup() {
    const loading = ref(false)
    const formations = ref([])
    const search = ref('')
    const showSessionsModal = ref(false)
    const sessions = ref([])
    const sessionsLoading = ref(false)
    const selectedFormationId = ref(null)
    const userInscriptions = ref([])
    const loadingInscriptions = ref(false)

    const loadFormations = async () => {
      loading.value = true
      try {
        const response = await employeService.getAvailableFormations({
          recherche: search.value
        })
        if (response.success) {
          formations.value = response.data
        }
      } catch (error) {
        console.error('Erreur:', error)
        alert('Erreur lors du chargement des formations')
      } finally {
        loading.value = false
      }
    }
    
    const getCompetencesArray = (competences) => {
      if (!competences) return []
      // Sépare les compétences par virgule ou point
      return competences.split(/[,.]/).map(c => c.trim()).filter(c => c.length > 0).slice(0, 3)
    }

    const loadUserInscriptions = async () => {
      loadingInscriptions.value = true
      try {
        const response = await employeService.getMyInscriptions()
        if (response.success) {
          userInscriptions.value = response.data
          console.log('Inscriptions chargées:', userInscriptions.value)
        }
      } catch (error) {
        console.error('Erreur chargement inscriptions:', error)
      } finally {
        loadingInscriptions.value = false
      }
    }

    const voirSessions = async (formationId) => {
      selectedFormationId.value = formationId
      sessionsLoading.value = true
      showSessionsModal.value = true
      
      try {
        const response = await employeService.getAvailableSessions({
          formation_id: formationId,
          periode: 'futures'
        })
        
        if (response.success) {
          sessions.value = response.data
          console.log('Sessions avec formateurs:', sessions.value)
          await loadUserInscriptions()
        }
      } catch (error) {
        console.error('Erreur sessions:', error)
      } finally {
        sessionsLoading.value = false
      }
    }

    // Fonction pour obtenir le nom du formateur
    const getFormateurName = (session) => {
      if (!session.formateur_nom && !session.formateur_prenom) {
        return 'Non assigné'
      }
      
      let nom = session.formateur_nom || ''
      let prenom = session.formateur_prenom || ''
      
      if (nom && prenom) {
        return `${prenom} ${nom.toUpperCase()}`
      } else if (nom) {
        return nom.toUpperCase()
      } else if (prenom) {
        return prenom
      }
      
      return 'Non assigné'
    }

    const findUserInscription = (sessionId) => {
      return userInscriptions.value.find(
        inscription => inscription.id_session == sessionId
      )
    }

    const getInscriptionStatus = (sessionId) => {
      const inscription = findUserInscription(sessionId)
      return inscription ? inscription.statut : null
    }

    const getInscriptionStatusText = (sessionId) => {
      const status = getInscriptionStatus(sessionId)
      if (!status) return null
      
      const statusTexts = {
        'EN_ATTENTE': 'En attente',
        'VALIDEE': 'Validée',
        'REFUSEE': 'Refusée',
        'ANNULEE': 'Annulée'
      }
      
      return statusTexts[status] || status
    }

    const handleInscription = async (sessionId) => {
      const inscription = findUserInscription(sessionId)
      const session = sessions.value.find(s => s.id === sessionId)
      
      if (inscription) {
        if (inscription.statut === 'EN_ATTENTE' || inscription.statut === 'VALIDEE') {
          if (!confirm('Voulez-vous annuler votre inscription à cette session ?')) return
          
          try {
            const response = await employeService.cancelInscription(inscription.id)
            if (response.success) {
              alert('✅ Inscription annulée !')
              await loadUserInscriptions()
              
              if (session) {
                session.places_restantes = (session.places_restantes || 0) + 1
              }
            } else {
              alert('❌ ' + response.message)
            }
          } catch (error) {
            alert('❌ Erreur: ' + error.message)
          }
        }
        else if (inscription.statut === 'ANNULEE' || inscription.statut === 'REFUSEE') {
          if (!confirm('Voulez-vous vous réinscrire à cette session ?')) return
          
          try {
            const response = await employeService.registerToSession(sessionId)
            if (response.success) {
              alert('✅ Réinscription effectuée !')
              await loadUserInscriptions()
              
              if (session && session.places_restantes > 0) {
                session.places_restantes = session.places_restantes - 1
              }
            } else {
              alert('❌ ' + response.message)
            }
          } catch (error) {
            alert('❌ Erreur: ' + error.message)
          }
        }
      } else {
        if (!confirm('Confirmez-vous votre inscription à cette session ?')) return
        
        try {
          const response = await employeService.registerToSession(sessionId)
          if (response.success) {
            alert('✅ Inscription confirmée !')
            await loadUserInscriptions()
            
            if (session && session.places_restantes > 0) {
              session.places_restantes = session.places_restantes - 1
            }
          } else {
            alert('❌ ' + response.message)
          }
        } catch (error) {
          alert('❌ Erreur: ' + error.message)
        }
      }
    }

    const isButtonDisabled = (sessionId, places) => {
      const inscription = findUserInscription(sessionId)
      
      if (places <= 0 && (!inscription || inscription.statut === 'ANNULEE' || inscription.statut === 'REFUSEE')) {
        return true
      }
      
      if (inscription && inscription.statut === 'REFUSEE') {
        return true
      }
      
      return false
    }

    const getPlacesClass = (places, sessionId) => {
      const inscription = findUserInscription(sessionId)
      
      if (inscription && (inscription.statut === 'EN_ATTENTE' || inscription.statut === 'VALIDEE')) {
        return 'registered'
      }
      
      if (places <= 0) return 'full'
      if (places <= 5) return 'limited'
      return 'available'
    }

    const getButtonClass = (sessionId, places) => {
      const inscription = findUserInscription(sessionId)
      
      if (inscription) {
        if (inscription.statut === 'EN_ATTENTE' || inscription.statut === 'VALIDEE') {
          return 'btn-warning'
        }
        if (inscription.statut === 'ANNULEE') {
          return 'btn-info'
        }
        if (inscription.statut === 'REFUSEE') {
          return 'btn-secondary'
        }
      }
      
      if (places <= 0) return 'btn-secondary'
      return 'btn-success'
    }

    const getButtonIcon = (sessionId, places) => {
      const inscription = findUserInscription(sessionId)
      
      if (inscription) {
        if (inscription.statut === 'EN_ATTENTE' || inscription.statut === 'VALIDEE') {
          return 'bi-x-circle-fill'
        }
        if (inscription.statut === 'ANNULEE') {
          return 'bi-arrow-clockwise'
        }
        if (inscription.statut === 'REFUSEE') {
          return 'bi-slash-circle'
        }
      }
      
      if (places <= 0) return 'bi-calendar-x'
      return 'bi-person-plus-fill'
    }

    const getButtonText = (sessionId, places) => {
      const inscription = findUserInscription(sessionId)
      
      if (inscription) {
        if (inscription.statut === 'EN_ATTENTE' || inscription.statut === 'VALIDEE') {
          return 'Annuler inscription'
        }
        if (inscription.statut === 'ANNULEE') {
          return 'Se réinscrire'
        }
        if (inscription.statut === 'REFUSEE') {
          return 'Refusé'
        }
      }
      
      if (places <= 0) return 'Complet'
      return "S'inscrire"
    }

    const closeModal = () => {
      showSessionsModal.value = false
      sessions.value = []
      userInscriptions.value = []
    }

    const truncateDescription = (text) => {
      if (!text) return 'Aucune description disponible'
      return text.length > 120 ? text.substring(0, 120) + '...' : text
    }

    const formatPrix = (prix) => {
      if (prix === null || prix === undefined || prix === '') return ''
      
      const prixNumber = typeof prix === 'string' ? parseFloat(prix) : prix
      
      if (isNaN(prixNumber) || prixNumber <= 0) return ''
      
      return prixNumber.toFixed(2) + '€'
    }

    const formatDate = (dateString) => {
      try {
        return new Date(dateString).toLocaleDateString('fr-FR', {
          weekday: 'long',
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        })
      } catch (e) {
        return dateString || 'Date non spécifiée'
      }
    }

    const formatTime = (dateString) => {
      try {
        return new Date(dateString).toLocaleTimeString('fr-FR', {
          hour: '2-digit',
          minute: '2-digit'
        })
      } catch (e) {
        return 'Heure non spécifiée'
      }
    }

    onMounted(() => {
      loadFormations()
    })

    return {
      loading,
      formations,
      search,
      showSessionsModal,
      sessions,
      sessionsLoading,
      loadFormations,
      voirSessions,
      handleInscription,
      closeModal,
      truncateDescription,
      formatPrix,
      formatDate,
      formatTime,
      getFormateurName,
      getPlacesClass,
      getButtonClass,
      getButtonIcon,
      getButtonText,
      getInscriptionStatus,
      getInscriptionStatusText,
      isButtonDisabled,
      getCompetencesArray
    }
  }
}
</script>

<style scoped>
.formations-container {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.formations-header-centered {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 3rem;
  text-align: center;
}

.title-section-centered {
  margin-bottom: 2rem;
  max-width: 800px;
}

.title-section-centered .icon-title-centered {
  font-size: 2.8rem;
  color: #4361ee;
  margin-bottom: 1rem;
  display: inline-block;
}

.title-section-centered h2 {
  color: #2d3748;
  font-weight: 800;
  margin-bottom: 0.5rem;
  font-size: 2.2rem;
  letter-spacing: -0.5px;
}

.subtitle-centered {
  color: #718096;
  font-size: 1.2rem;
  line-height: 1.5;
  margin: 0;
}

.search-section-below {
  width: 100%;
  max-width: 600px;
  margin-top: 1rem;
}

.search-wrapper-below {
  position: relative;
  width: 100%;
}

.search-icon-below {
  position: absolute;
  left: 1.2rem;
  top: 50%;
  transform: translateY(-50%);
  color: #4A90E2;
  font-size: 1.2rem;
  z-index: 2;
}

.search-input-below {
  width: 100%;
  padding: 1rem 1rem 1rem 3.2rem;
  border: 2px solid #D4EBF2;
  border-radius: 14px;
  font-size: 1rem;
  transition: all 0.3s ease;
  background: #F8FBFD;
  color: #2C3E50;
  box-shadow: 0 4px 12px rgba(74, 144, 226, 0.08);
}

.search-input-below:focus {
  border-color: #4A90E2;
  background: #FFFFFF;
  box-shadow: 0 0 0 4px rgba(74, 144, 226, 0.15), 0 6px 20px rgba(74, 144, 226, 0.1);
  outline: none;
  transform: translateY(-2px);
}

.search-input-below::placeholder {
  color: #A5B4C6;
  font-size: 0.95rem;
}

/* Grille de formations moderne */
.formations-grid-modern {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 2rem;
  margin-bottom: 3rem;
}

.formation-box-modern {
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

.formation-box-modern:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 32px rgba(67, 97, 238, 0.15);
  border-color: #4361ee;
}

/* Animation d'entrée */
.formation-box-modern {
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

.formation-box-modern:nth-child(1) { animation-delay: 0.1s; }
.formation-box-modern:nth-child(2) { animation-delay: 0.2s; }
.formation-box-modern:nth-child(3) { animation-delay: 0.3s; }
.formation-box-modern:nth-child(4) { animation-delay: 0.4s; }
.formation-box-modern:nth-child(5) { animation-delay: 0.5s; }
.formation-box-modern:nth-child(6) { animation-delay: 0.6s; }

/* Badge actif/inactif */
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

/* Header de la boîte */
.box-header-modern {
  background: linear-gradient(135deg, #4361ee15 0%, #3a56d415 100%);
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
}

.formation-header-modern {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 0.5rem;
}

.formation-icon-modern {
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

.formation-box-modern:hover .formation-icon-modern {
  transform: rotate(10deg) scale(1.1);
}

.formation-title-modern {
  flex: 1;
}

.formation-title-modern h3 {
  color: #2d3748;
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0 0 0.5rem 0;
  line-height: 1.3;
}

.formation-description-modern {
  color: #718096;
  font-size: 0.9rem;
  line-height: 1.5;
  margin: 0;
  display: -webkit-box;
 
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Corps de la boîte */
.box-body-modern {
  padding: 1.5rem;
  flex: 1;
}

/* Informations formation */
.formation-info-modern {
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

/* Compétences en tags */
.competences-section-modern {
  margin-top: 1.5rem;
}

.competences-header-modern {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
}

.competences-header-modern i {
  color: #f59e0b;
  font-size: 0.95rem;
}

.competences-title-modern {
  color: #475569;
  font-weight: 600;
  font-size: 0.85rem;
}

.tags-container-modern {
  display: flex;
  flex-wrap: wrap;
  gap: 0.4rem;
}

.skill-tag-modern {
  padding: 0.3rem 0.7rem;
  background: #f0f9ff;
  color: #0369a1;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 500;
  border: 1px solid #e0f2fe;
  transition: all 0.2s ease;
}

.skill-tag-modern:hover {
  background: #e0f2fe;
  transform: translateY(-1px);
}

/* Footer de la boîte */
.box-footer-modern {
  padding: 1.5rem;
  padding-top: 0;
  border-top: 1px solid #e2e8f0;
  margin-top: auto;
}

.action-btn-modern {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  width: 100%;
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

.btn-view-sessions {
  background: linear-gradient(135deg, #4361ee, #3a56d4);
  color: white;
}

.btn-view-sessions:hover {
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

.action-btn-modern i {
  position: relative;
  z-index: 1;
}

.action-btn-modern span {
  position: relative;
  z-index: 1;
}

/* Effet de bordure animé */
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

.formation-box-modern:hover .box-border-animation {
  transform: translateX(100%);
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

/* État vide moderne */
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

/* Modal Styles (inchangé) */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(5px);
}

.modal-container {
  background: white;
  border-radius: 20px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-50px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.modal-header {
  padding: 1.5rem 2rem;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: linear-gradient(135deg, #4361ee, #3a56d4);
  color: white;
}

.modal-header h3 {
  margin: 0;
  font-weight: 700;
}

.btn-close-modal {
  background: none;
  border: none;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 5px;
  border-radius: 50%;
  width: 35px;
  height: 35px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.3s ease;
}

.btn-close-modal:hover {
  background: rgba(255, 255, 255, 0.2);
}

.modal-body {
  padding: 2rem;
}

.loading-modal {
  text-align: center;
  padding: 3rem;
}

.loading-modal p {
  margin-top: 1rem;
  color: #718096;
}

.sessions-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.session-card {
  background: #f8fafc;
  border-radius: 12px;
  padding: 1.5rem;
  border: 1px solid #e2e8f0;
  transition: all 0.3s ease;
}

.session-card:hover {
  background: #f1f5f9;
  border-color: #cbd5e0;
}

.session-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.session-date {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 600;
  color: #2d3748;
}

.session-badge {
  padding: 5px 12px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 5px;
}

.session-badge.available {
  background: #d1fae5;
  color: #065f46;
}

.session-badge.limited {
  background: #fef3c7;
  color: #92400e;
}

.session-badge.full {
  background: #fee2e2;
  color: #991b1b;
}

.session-badge.registered {
  background: #dbeafe;
  color: #1e40af;
}

.session-details {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
  margin-bottom: 1.5rem;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #4a5568;
  font-size: 0.95rem;
}

.detail-item i {
  color: #4361ee;
  min-width: 20px;
}

.btn-register {
  width: 100%;
  padding: 0.75rem;
  border-radius: 10px;
  font-weight: 600;
  transition: all 0.3s ease;
  border: none;
}

.btn-register:disabled {
  background: #cbd5e0;
  cursor: not-allowed;
}

.btn-warning {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  border: none;
  color: white;
}

.btn-warning:hover {
  background: linear-gradient(135deg, #d97706, #b45309);
  color: white;
}

.btn-info {
  background: linear-gradient(135deg, #0ea5e9, #0284c7);
  border: none;
  color: white;
}

.btn-info:hover {
  background: linear-gradient(135deg, #0284c7, #0369a1);
  color: white;
}

.btn-success {
  background: linear-gradient(135deg, #10b981, #059669);
  border: none;
  color: white;
}

.btn-success:hover {
  background: linear-gradient(135deg, #059669, #047857);
  color: white;
}

.btn-secondary {
  background: linear-gradient(135deg, #6b7280, #4b5563);
  border: none;
  color: white;
}

.no-sessions {
  text-align: center;
  padding: 3rem 2rem;
}

.no-sessions i {
  font-size: 3rem;
  color: #cbd5e0;
  margin-bottom: 1rem;
}

.no-sessions h5 {
  color: #4a5568;
  margin-bottom: 0.5rem;
}

.no-sessions p {
  color: #718096;
}

/* Responsive */
@media (max-width: 768px) {
  .formations-container {
    padding: 1rem;
  }
  
  .formations-header-centered {
    margin-bottom: 2rem;
  }
  
  .title-section-centered h2 {
    font-size: 1.8rem;
  }
  
  .subtitle-centered {
    font-size: 1.1rem;
  }
  
  .search-section-below {
    max-width: 100%;
    padding: 0 1rem;
  }
  
  .search-input-below {
    padding: 0.9rem 1rem 0.9rem 3rem;
  }
  
  .formations-grid-modern {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
  
  .info-row-modern {
    grid-template-columns: 1fr;
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
}

@media (max-width: 576px) {
  .title-section-centered h2 {
    font-size: 1.6rem;
  }
  
  .title-section-centered .icon-title-centered {
    font-size: 2.2rem;
  }
  
  .formation-header-modern {
    flex-direction: column;
    text-align: center;
  }
  
  .formation-icon-modern {
    align-self: center;
  }
  
  .empty-state-text-modern h3 {
    font-size: 1.5rem;
  }
  
  .empty-state-text-modern p {
    font-size: 1rem;
  }
}

@media (max-width: 480px) {
  .modal-container {
    width: 95%;
    margin: 1rem;
  }
  
  .session-details {
    flex-direction: column;
    gap: 0.75rem;
  }
}
</style>