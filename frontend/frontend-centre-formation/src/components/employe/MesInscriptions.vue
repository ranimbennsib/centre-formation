<template>
  <div class="mes-inscriptions-container">
    <!-- Header centré comme Formations -->
    <div class="formations-header-centered">
      <div class="title-section-centered">
        <i class="bi bi-calendar-check-fill icon-title-centered"></i>
        <h2>Mes inscriptions</h2>
        <p class="subtitle-centered">Suivez l'état de vos demandes d'inscription</p>
      </div>
    </div>

    <!-- Filtres juste en dessous du titre -->
    <div class="filters-section-modern">
      <div class="filter-header-modern">
        <i class="bi bi-funnel-fill"></i>
        <span class="filter-title-modern">Filtrer par statut</span>
      </div>
      <div class="status-filters-modern">
        <button 
          v-for="filter in statusFilters" 
          :key="filter.value"
          class="btn-filter-modern"
          :class="{ active: filterStatut === filter.value }"
          @click="applyStatusFilter(filter.value)"
        >
          <i class="bi" :class="filter.icon"></i>
          {{ filter.label }}
          <span class="badge-count-modern">{{ getCountByStatus(filter.value) }}</span>
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-container">
      <div class="spinner-container">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Chargement...</span>
        </div>
        <p class="loading-text">Chargement de vos inscriptions...</p>
      </div>
    </div>

    <!-- Liste des inscriptions dans des boîtes modernes -->
    <div v-else-if="filteredInscriptions.length > 0" class="inscriptions-grid-modern">
      <div v-for="inscription in filteredInscriptions" :key="inscription.id" class="inscription-box-modern">
        <!-- Header de la boîte -->
        <div class="box-header-modern">
          <div class="formation-header-modern">
            <div class="formation-icon-modern">
              <i class="bi bi-book-fill"></i>
            </div>
            <div class="formation-title-modern">
              <h3>{{ inscription.formation_titre }}</h3>
              <p class="formation-description-modern">
                {{ truncateText(inscription.formation_description, 70) }}
              </p>
            </div>
          </div>
          <div class="header-date-modern">
            <div class="date-badge-modern">
              <i class="bi bi-calendar-event"></i>
              {{ formatDate(inscription.date_debut) }}
            </div>
            <div class="inscription-date-modern">
              Inscrit le {{ formatDateTime(inscription.date_inscription) }}
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
                  <i class="bi bi-geo-alt-fill"></i>
                </div>
                <div class="info-content-modern">
                  <div class="info-label-modern">Salle</div>
                  <div class="info-value-modern">{{ inscription.salle }}</div>
                </div>
              </div>
              
              <div class="info-item-modern">
                <div class="info-icon-modern">
                  <i class="bi bi-clock-fill"></i>
                </div>
                <div class="info-content-modern">
                  <div class="info-label-modern">Horaire</div>
                  <div class="info-value-modern">{{ formatTime(inscription.date_debut) }}</div>
                </div>
              </div>
            </div>
            
            <div class="info-row-modern">
              <div class="info-item-modern" v-if="inscription.formateur_nom || inscription.formateur_prenom">
                <div class="info-icon-modern">
                  <i class="bi bi-person-badge-fill"></i>
                </div>
                <div class="info-content-modern">
                  <div class="info-label-modern">Formateur</div>
                  <div class="info-value-modern">
                    {{ getFormateurName(inscription) }}
                  </div>
                </div>
              </div>

              <div class="info-item-modern" v-if="inscription.places_max">
                <div class="info-icon-modern">
                  <i class="bi bi-people-fill"></i>
                </div>
                <div class="info-content-modern">
                  <div class="info-label-modern">Capacité</div>
                  <div class="info-value-modern">{{ inscription.places_max }} places</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Messages d'alerte -->
          <div v-if="inscription.statut === 'REFUSEE' && inscription.raison_refus" 
               class="alert-message-modern alert-refused-modern">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div class="alert-content-modern">
              <strong>Raison du refus :</strong>
              <p>{{ inscription.raison_refus }}</p>
            </div>
          </div>

          <div v-if="inscription.statut === 'ANNULEE' && inscription.raison_refus" 
               class="alert-message-modern alert-cancelled-modern">
            <i class="bi bi-info-circle-fill"></i>
            <div class="alert-content-modern">
              <strong>Raison de l'annulation :</strong>
              <p>{{ inscription.raison_refus }}</p>
            </div>
          </div>

          <!-- Actions et statut -->
          <div class="box-footer-modern">
            <div class="status-display-modern">
              <div class="status-indicator-modern" :class="getStatusClass(inscription.statut)">
                <div class="status-dot-modern"></div>
                <span class="status-text-modern">{{ getStatusText(inscription.statut) }}</span>
              </div>
            </div>
            
            <div class="actions-display-modern">
              <button 
                v-if="inscription.statut === 'EN_ATTENTE'"
                class="action-btn-modern btn-cancel-inscription"
                @click="annulerInscription(inscription.id)"
                :disabled="isSessionStarted(inscription.date_debut)"
                :title="isSessionStarted(inscription.date_debut) ? 'La session a déjà commencé' : 'Annuler cette inscription'"
              >
                <i class="bi bi-x-circle"></i>
                <span>Annuler</span>
              </button>
              
              <button 
                v-if="inscription.statut === 'ANNULEE' && !isSessionStarted(inscription.date_debut)"
                class="action-btn-modern btn-reinscrire-modern"
                @click="reinscrire(inscription)"
              >
                <i class="bi bi-arrow-clockwise"></i>
                <span>Se réinscrire</span>
              </button>
              
              <button 
                v-if="inscription.statut === 'VALIDEE'"
                class="action-btn-modern btn-validated"
                disabled
              >
                <i class="bi bi-check-circle"></i>
                <span>Validée</span>
              </button>
              
              <button 
                v-if="inscription.statut === 'REFUSEE'"
                class="action-btn-modern btn-refused"
                disabled
                :title="inscription.raison_refus ? `Refusé: ${inscription.raison_refus}` : 'Inscription refusée'"
              >
                <i class="bi bi-slash-circle"></i>
                <span>Refusée</span>
              </button>
            </div>
          </div>
        </div>
        
        <!-- Effet de bordure animé -->
        <div class="box-border-animation"></div>
      </div>
    </div>

    <!-- État vide amélioré -->
    <div v-else class="empty-state-modern">
      <div class="empty-state-content-modern">
        <div class="empty-state-icon-modern">
          <i class="bi bi-calendar-x"></i>
        </div>
        <div class="empty-state-text-modern">
          <h3>Aucune inscription trouvée</h3>
          <p v-if="filterStatut">
            Aucune inscription avec le statut "{{ getStatusText(filterStatut) }}" n'a été trouvée.
          </p>
          <p v-else>
            Vous n'avez pas encore d'inscriptions.<br>
            Parcourez les formations disponibles pour vous inscrire.
          </p>
        </div>
        <button class="empty-state-btn-modern" @click="filterStatut = ''; loadInscriptions()">
          <i class="bi bi-arrow-clockwise"></i>
          Réinitialiser les filtres
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import employeService from '@/services/employe'

export default {
  name: 'MesInscriptions',
  
  setup() {
    const loading = ref(false)
    const inscriptions = ref([])
    const filterStatut = ref('')

    const statusFilters = [
      { value: '', label: 'Toutes', icon: 'bi-filter' },
      { value: 'EN_ATTENTE', label: 'En attente', icon: 'bi-clock-history' },
      { value: 'VALIDEE', label: 'Validées', icon: 'bi-check-circle' },
      { value: 'ANNULEE', label: 'Annulées', icon: 'bi-x-circle' },
      { value: 'REFUSEE', label: 'Refusées', icon: 'bi-slash-circle' }
    ]

    const loadInscriptions = async () => {
      loading.value = true
      try {
        const params = {}
        if (filterStatut.value) {
          params.statut = filterStatut.value
        }
        
        console.log('Chargement inscriptions avec params:', params)
        const response = await employeService.getMyInscriptions(params)
        
        if (response.success) {
          inscriptions.value = response.data
          console.log('Inscriptions chargées:', inscriptions.value)
        } else {
          alert('Erreur: ' + response.message)
        }
      } catch (error) {
        console.error('Erreur:', error)
        alert('Erreur lors du chargement des inscriptions')
      } finally {
        loading.value = false
      }
    }

    const annulerInscription = async (id) => {
      if (!confirm('Voulez-vous vraiment annuler cette inscription ?')) return
      
      try {
        const response = await employeService.cancelInscription(id)
        if (response.success) {
          alert('✅ Inscription annulée avec succès')
          loadInscriptions()
        } else {
          alert('❌ ' + response.message)
        }
      } catch (error) {
        alert('❌ Erreur: ' + error.message)
      }
    }

    const reinscrire = async (inscription) => {
      if (!confirm('Voulez-vous vous réinscrire à cette session ?')) return
      
      try {
        const response = await employeService.registerToSession(inscription.id_session)
        if (response.success) {
          alert('✅ Réinscription effectuée avec succès')
          loadInscriptions()
        } else {
          alert('❌ ' + response.message)
        }
      } catch (error) {
        alert('❌ Erreur: ' + error.message)
      }
    }

    // Computed pour filtrer les inscriptions
    const filteredInscriptions = computed(() => {
      if (!filterStatut.value) return inscriptions.value
      return inscriptions.value.filter(ins => ins.statut === filterStatut.value)
    })

    // Compter les inscriptions par statut
    const getCountByStatus = (statut) => {
      if (!statut) return inscriptions.value.length
      return inscriptions.value.filter(ins => ins.statut === statut).length
    }

    const applyStatusFilter = (statut) => {
      filterStatut.value = statut
    }

    const truncateText = (text, length) => {
      if (!text) return 'Pas de description'
      return text.length > length ? text.substring(0, length) + '...' : text
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
        return 'Date invalide'
      }
    }

    const formatDateTime = (dateString) => {
      try {
        return new Date(dateString).toLocaleString('fr-FR', {
          day: '2-digit',
          month: '2-digit',
          year: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        })
      } catch (e) {
        return 'Date invalide'
      }
    }

    const formatTime = (dateString) => {
      try {
        return new Date(dateString).toLocaleTimeString('fr-FR', {
          hour: '2-digit',
          minute: '2-digit'
        })
      } catch (e) {
        return 'Heure invalide'
      }
    }

    const getFormateurName = (inscription) => {
      if (!inscription.formateur_nom && !inscription.formateur_prenom) {
        return 'Non assigné'
      }
      
      let nom = inscription.formateur_nom || ''
      let prenom = inscription.formateur_prenom || ''
      
      if (nom && prenom) {
        return `${prenom} ${nom.toUpperCase()}`
      } else if (nom) {
        return nom.toUpperCase()
      } else if (prenom) {
        return prenom
      }
      
      return 'Non assigné'
    }

    const getStatusText = (statut) => {
      const texts = {
        'EN_ATTENTE': 'En attente',
        'VALIDEE': 'Validée',
        'REFUSEE': 'Refusée',
        'ANNULEE': 'Annulée'
      }
      return texts[statut] || statut
    }

    const getStatusClass = (statut) => {
      const classes = {
        'EN_ATTENTE': 'status-waiting-modern',
        'VALIDEE': 'status-validated-modern',
        'REFUSEE': 'status-refused-modern',
        'ANNULEE': 'status-cancelled-modern'
      }
      return classes[statut] || 'status-default-modern'
    }

    const isSessionStarted = (dateString) => {
      try {
        const sessionDate = new Date(dateString)
        const now = new Date()
        return sessionDate <= now
      } catch (e) {
        return false
      }
    }

    onMounted(() => {
      loadInscriptions()
    })

    return {
      loading,
      inscriptions,
      filterStatut,
      statusFilters,
      filteredInscriptions,
      loadInscriptions,
      annulerInscription,
      reinscrire,
      getCountByStatus,
      applyStatusFilter,
      truncateText,
      formatDate,
      formatDateTime,
      formatTime,
      getFormateurName,
      getStatusText,
      getStatusClass,
      isSessionStarted
    }
  }
}
</script>

<style scoped>
.mes-inscriptions-container {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

/* Header centré comme Formations */
.formations-header-centered {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 2rem;
  text-align: center;
}

.title-section-centered {
  margin-bottom: 1.5rem;
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

/* Filtres modernes sous le titre */
.filters-section-modern {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 3rem;
  box-shadow: 0 4px 6px rgba(67, 97, 238, 0.08);
  border: 1px solid #e2e8f0;
  max-width: 1200px;
  margin-left: auto;
  margin-right: auto;
}

.filter-header-modern {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.25rem;
}

.filter-header-modern i {
  color: #4361ee;
  font-size: 1.25rem;
}

.filter-title-modern {
  font-size: 1.1rem;
  font-weight: 600;
  color: #2d3748;
}

.status-filters-modern {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.btn-filter-modern {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1.2rem;
  border-radius: 8px;
  border: 2px solid #e2e8f0;
  background: white;
  color: #4a5568;
  font-weight: 500;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
  font-size: 0.95rem;
}

.btn-filter-modern:hover:not(.active) {
  border-color: #cbd5e0;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.btn-filter-modern.active {
  border-color: #4361ee;
  background: #4361ee;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(67, 97, 238, 0.2);
}

.btn-filter-modern i {
  font-size: 1rem;
}

.badge-count-modern {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  padding: 0.15rem 0.6rem;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 600;
  margin-left: 0.25rem;
}

.btn-filter-modern:not(.active) .badge-count-modern {
  background: #e2e8f0;
  color: #4a5568;
}

/* Grille d'inscriptions moderne */
.inscriptions-grid-modern {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 2rem;
  margin-bottom: 3rem;
}

.inscription-box-modern {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
  border: 1px solid #e2e8f0;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
}

.inscription-box-modern:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 32px rgba(67, 97, 238, 0.15);
  border-color: #4361ee;
}

/* Animation d'entrée */
.inscription-box-modern {
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

.inscription-box-modern:nth-child(1) { animation-delay: 0.1s; }
.inscription-box-modern:nth-child(2) { animation-delay: 0.2s; }
.inscription-box-modern:nth-child(3) { animation-delay: 0.3s; }
.inscription-box-modern:nth-child(4) { animation-delay: 0.4s; }
.inscription-box-modern:nth-child(5) { animation-delay: 0.5s; }
.inscription-box-modern:nth-child(6) { animation-delay: 0.6s; }

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
  margin-bottom: 1.25rem;
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

.inscription-box-modern:hover .formation-icon-modern {
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

.header-date-modern {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.date-badge-modern {
  background: white;
  border: 2px solid #4361ee;
  color: #4361ee;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.9rem;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.inscription-date-modern {
  color: #a0aec0;
  font-size: 0.85rem;
  font-weight: 500;
}

/* Corps de la boîte */
.box-body-modern {
  padding: 1.5rem;
}

/* Informations session */
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

/* Alertes modernes */
.alert-message-modern {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1rem;
  border-radius: 12px;
  margin-bottom: 1.5rem;
  animation: slideIn 0.3s ease;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.alert-refused-modern {
  background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
  border-left: 4px solid #dc2626;
}

.alert-cancelled-modern {
  background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
  border-left: 4px solid #6b7280;
}

.alert-message-modern i {
  font-size: 1.2rem;
  margin-top: 2px;
}

.alert-refused-modern i {
  color: #dc2626;
}

.alert-cancelled-modern i {
  color: #6b7280;
}

.alert-content-modern {
  flex: 1;
}

.alert-content-modern strong {
  display: block;
  margin-bottom: 0.5rem;
  font-size: 0.95rem;
  color: #4b5563;
}

.alert-content-modern p {
  margin: 0;
  font-size: 0.9rem;
  line-height: 1.5;
  color: #6b7280;
}

/* Footer de la boîte */
.box-footer-modern {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 1.5rem;
  border-top: 1px solid #e2e8f0;
  flex-wrap: wrap;
  gap: 1rem;
}

.status-display-modern {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.status-indicator-modern {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.9rem;
}

.status-dot-modern {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

/* Couleurs des statuts - Garder le vert pour "Validée" */
.status-waiting-modern {
  background: #fef3c7;
  color: #92400e;
}

.status-waiting-modern .status-dot-modern {
  background: #f59e0b;
}

.status-validated-modern {
  background: #d1fae5; /* Vert */
  color: #065f46;
}

.status-validated-modern .status-dot-modern {
  background: #10b981; /* Vert */
}

.status-refused-modern {
  background: #fee2e2;
  color: #991b1b;
}

.status-refused-modern .status-dot-modern {
  background: #ef4444;
}

.status-cancelled-modern {
  background: #f1f5f9;
  color: #4b5563;
}

.status-cancelled-modern .status-dot-modern {
  background: #6b7280;
}

.actions-display-modern {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.action-btn-modern {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1.25rem;
  border-radius: 8px;
  border: none;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 120px;
  justify-content: center;
}

.btn-cancel-inscription {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: white;
}

.btn-cancel-inscription:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(245, 158, 11, 0.3);
}

.btn-cancel-inscription:disabled {
  background: #cbd5e0;
  cursor: not-allowed;
  transform: none;
}

.btn-reinscrire-modern {
  background: linear-gradient(135deg, #0ea5e9, #0284c7);
  color: white;
}

.btn-reinscrire-modern:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(14, 165, 233, 0.3);
}

.btn-validated {
  background: linear-gradient(135deg, #10b981, #059669); /* Vert */
  color: white;
  cursor: default;
}

.btn-refused {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
  cursor: default;
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

.inscription-box-modern:hover .box-border-animation {
  transform: translateX(100%);
}

/* État vide amélioré */
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
  margin: 0 auto 2rem;
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
  margin-bottom: 2rem;
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
@media (max-width: 1200px) {
  .inscriptions-grid-modern {
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  }
}

@media (max-width: 768px) {
  .mes-inscriptions-container {
    padding: 1rem;
  }
  
  .formations-header-centered {
    margin-bottom: 1.5rem;
  }
  
  .title-section-centered h2 {
    font-size: 1.8rem;
  }
  
  .subtitle-centered {
    font-size: 1.1rem;
  }
  
  .filters-section-modern {
    padding: 1.25rem;
    margin-bottom: 2rem;
  }
  
  .status-filters-modern {
    gap: 0.5rem;
  }
  
  .btn-filter-modern {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
  }
  
  .inscriptions-grid-modern {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
  
  .info-row-modern {
    grid-template-columns: 1fr;
  }
  
  .box-footer-modern {
    flex-direction: column;
    align-items: stretch;
  }
  
  .actions-display-modern {
    justify-content: center;
  }
  
  .action-btn-modern {
    min-width: 100%;
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

@media (max-width: 480px) {
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
  
  .header-date-modern {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .empty-state-text-modern h3 {
    font-size: 1.5rem;
  }
  
  .empty-state-text-modern p {
    font-size: 1rem;
  }
}
</style>