<template>
  <div class="formateur-profil">
    
    <!-- Chargement -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Chargement de votre profil...</p>
    </div>

    <!-- Contenu principal -->
    <div v-else class="profil-content">
      
      <!-- Carte principale - Bandeau de profil -->
      <div class="profile-banner">
        <div class="banner-background"></div>
        <div class="banner-content">
          
          <div class="profile-avatar">
            <div class="avatar-circle">
              <!-- Utilisation de votre composant LottieAnimation -->
              <LottieAnimation
                v-if="animationData"
                :animation-data="animationData"
                :width="130"
                :height="130"
                :loop="true"
                :autoplay="true"
                class="lottie-avatar"
              />
              <!-- Fallback -->
              <i v-else class="bi bi-person-fill avatar-fallback-icon"></i>
            </div>
            <div class="status-indicator online">
              <i class="bi bi-circle-fill" style="color: green;"></i>
            </div>
          </div>
          <div class="profile-identity">
            <h2 class="profile-name">{{ formateur.prenom }} {{ formateur.nom }}</h2>
            <div class="profile-badges">
              <span class="badge badge-role">
                <i class="bi bi-mortarboard-fill"></i>
                Formateur Certifié
              </span>
              <span class="badge badge-specialty" v-if="formateur.specialite">
                <i class="bi bi-star-fill"></i>
                {{ formateur.specialite }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Grille de contenu -->
      <div class="content-grid">
        
        <!-- Colonne gauche - Informations -->
        <div class="left-column">
          
          <!-- Carte Informations personnelles -->
          <div class="info-card">
            <div class="card-header">
              <h3>
                <i class="bi bi-person-lines-fill"></i>
                Informations Personnelles
              </h3>
            </div>
            <div class="card-body">
              <div class="info-row">
                <div class="info-label">
                  <i class="bi bi-person"></i>
                  <span>Nom complet</span>
                </div>
                <div class="info-value">{{ formateur.prenom }} {{ formateur.nom }}</div>
              </div>
              
              <div class="info-row">
                <div class="info-label">
                  <i class="bi bi-envelope"></i>
                  <span>Email professionnel</span>
                </div>
                <div class="info-value email-value">{{ formateur.email }}</div>
              </div>
              
              <div class="info-row">
                <div class="info-label">
                  <i class="bi bi-bookmark-star"></i>
                  <span>Spécialité</span>
                </div>
                <div class="info-value specialty-value">
                  <span class="specialty-tag">{{ formateur.specialite || 'Non spécifiée' }}</span>
                </div>
              </div>
              
              <!-- NOUVELLE LIGNE POUR LE CIN -->
              <div class="info-row">
                <div class="info-label">
                  <i class="bi bi-card-checklist"></i>
                  <span>CIN</span>
                </div>
                <div class="info-value">{{ formateur.cin || 'Non renseigné' }}</div>
              </div>
              
              <div class="info-row">
                <div class="info-label">
                  <i class="bi bi-shield-check"></i>
                  <span>Rôle</span>
                </div>
                <div class="info-value">
                  <span class="role-tag">{{ formateur.role }}</span>
                </div>
              </div>
              
              <div class="info-row">
                <div class="info-label">
                  <i class="bi bi-calendar-check"></i>
                  <span>Membre depuis</span>
                </div>
                <div class="info-value">{{ formatDate(formateur.date_inscription) }}</div>
              </div>
            </div>
          </div>

          <!-- Carte Contact & Support -->
          <div class="info-card support-card">
            <div class="card-header">
              <h3>
                <i class="bi bi-headset"></i>
                Besoin d'aide ?
              </h3>
            </div>
            <div class="card-body">
              <div class="support-content">
                <div class="support-icon">
                  <i class="bi bi-question-circle"></i>
                </div>
                <div class="support-text">
                  <p>Pour modifier vos informations personnelles ou votre spécialité, veuillez contacter un administrateur.</p>
                  <button class="btn-contact">
                    <i class="bi bi-envelope-at"></i>
                    Contacter l'administration
                  </button>
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- Colonne droite - Statistiques -->
        <div class="right-column">
          
          <!-- Carte Statistiques -->
          <div class="stats-card">
            <div class="card-header">
              <h3>
                <i class="bi bi-graph-up"></i>
                Mes Statistiques
              </h3>
            </div>
            <div class="card-body">
              
              <div v-if="stats && stats.stats" class="stats-grid">
                
                <!-- Total sessions -->
                <div class="stat-box stat-primary">
                  <div class="stat-icon">
                    <i class="bi bi-calendar3"></i>
                  </div>
                  <div class="stat-details">
                    <div class="stat-value">{{ stats.stats.total_sessions || 0 }}</div>
                    <div class="stat-label">Sessions Totales</div>
                  </div>
                </div>

                <!-- Sessions acceptées -->
                <div class="stat-box stat-success">
                  <div class="stat-icon">
                    <i class="bi bi-check-circle-fill"></i>
                  </div>
                  <div class="stat-details">
                    <div class="stat-value">{{ stats.stats.accepted_sessions || 0 }}</div>
                    <div class="stat-label">Sessions Acceptées</div>
                  </div>
                </div>

                <!-- En attente -->
                <div class="stat-box stat-warning">
                  <div class="stat-icon">
                    <i class="bi bi-clock-history"></i>
                  </div>
                  <div class="stat-details">
                    <div class="stat-value">{{ stats.stats.pending_sessions || 0 }}</div>
                    <div class="stat-label">En Attente</div>
                  </div>
                </div>

                <!-- Refusées -->
                <div class="stat-box stat-danger">
                  <div class="stat-icon">
                    <i class="bi bi-x-circle-fill"></i>
                  </div>
                  <div class="stat-details">
                    <div class="stat-value">{{ stats.stats.refused_sessions || 0 }}</div>
                    <div class="stat-label">Sessions Refusées</div>
                  </div>
                </div>

              </div>

              <div v-else class="no-stats">
                <i class="bi bi-inbox"></i>
                <p>Aucune statistique disponible</p>
                <p v-if="statsError" class="error-message">{{ statsError }}</p>
                <button @click="loadStats" class="btn-retry">
                  <i class="bi bi-arrow-clockwise"></i>
                  Réessayer
                </button>
              </div>

            </div>
          </div>

          <!-- Carte Taux de réussite -->
          <div class="success-card" v-if="stats && stats.stats && stats.stats.total_sessions > 0">
            <div class="card-header">
              <h3>
                <i class="bi bi-trophy"></i>
                Performance
              </h3>
            </div>
            <div class="card-body">
              <div class="performance-content">
                <div class="performance-circle">
                  <svg viewBox="0 0 120 120" class="progress-ring">
                    <circle
                      class="progress-ring-bg"
                      cx="60"
                      cy="60"
                      r="50"
                    />
                    <circle
                      class="progress-ring-fill"
                      cx="60"
                      cy="60"
                      r="50"
                      :stroke-dasharray="`${successRate * 3.14} 314`"
                    />
                  </svg>
                  <div class="performance-percentage">
                    <span class="percentage-value">{{ Math.round(successRate) }}%</span>
                    <span class="percentage-label">Taux d'acceptation</span>
                  </div>
                </div>
                <div class="performance-info">
                  <p>Excellent travail ! Vous avez accepté {{ stats.stats.accepted_sessions }} sessions sur {{ stats.stats.total_sessions }} proposées.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Carte Activité récente -->
          <div class="activity-card">
            <div class="card-header">
              <h3>
                <i class="bi bi-clock-history"></i>
                Activité Récente
              </h3>
            </div>
            <div class="card-body">
              <div v-if="recentSessions && recentSessions.length > 0" class="activity-timeline">
                <div class="timeline-item" v-for="session in recentSessions" :key="session.id">
                  <div class="timeline-marker" :class="getSessionStatusClass(session.statut_formateur)"></div>
                  <div class="timeline-content">
                    <div class="timeline-title">{{ session.formation_titre || 'Session' }}</div>
                    <div class="timeline-date">
                      {{ formatDateTime(session.date_debut) }}
                      <span class="timeline-status" :class="getSessionStatusClass(session.statut_formateur)">
                        {{ getSessionStatusText(session.statut_formateur) }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="no-activity">
                <i class="bi bi-calendar-x"></i>
                <p>Aucune activité récente</p>
              </div>
              
              <!-- Activité système -->
              <div class="system-activity">
                <div class="timeline-item">
                  <div class="timeline-marker info"></div>
                  <div class="timeline-content">
                    <div class="timeline-title">Profil consulté</div>
                    <div class="timeline-date">Aujourd'hui à {{ currentTime }}</div>
                  </div>
                </div>
                <div class="timeline-item">
                  <div class="timeline-marker info"></div>
                  <div class="timeline-content">
                    <div class="timeline-title">Compte créé</div>
                    <div class="timeline-date">{{ formatDate(formateur.date_inscription) }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>

      </div>

    </div>

  </div>
</template>

<script>
import formateurService from '@/services/formateur'
import authService from '@/services/auth'
import LottieAnimation from '@/components/LottieAnimation.vue'
import ConsultingAnimation from '@/assets/Consulting - Creative 3D Animation.json'

export default {
  name: 'FormateurProfil',
  components: {
    LottieAnimation
  },
  data() {
    return {
      formateur: {
        nom: '',
        prenom: '',
        email: '',
        role: '',
        date_inscription: '',
        specialite: '',
        cin: ''
      },
      stats: null,
      recentSessions: [],
      loading: true,
      animationData: null,
      statsError: null,
      currentTime: new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })
    }
  },
  computed: {
    successRate() {
      if (!this.stats || !this.stats.stats || this.stats.stats.total_sessions === 0) return 0
      return (this.stats.stats.accepted_sessions / this.stats.stats.total_sessions) * 100
    }
  },
  async created() {
    await this.loadProfilData()
    this.loadAnimationData()
    // Mettre à jour l'heure actuelle
    this.updateCurrentTime()
  },
  methods: {
    async loadProfilData() {
      this.loading = true
      console.log('=== CHARGEMENT PROFIL FORMATEUR ===')
      
      try {
        // 1. Charger les informations de base depuis l'API
        const profilResponse = await formateurService.getMyProfil()
        console.log('Réponse profil:', profilResponse.data)
        
        if (profilResponse.data && profilResponse.data.success) {
          this.formateur = {
            ...profilResponse.data.data,
            specialite: profilResponse.data.data.specialite || 'Non spécifiée',
            cin: profilResponse.data.data.cin || 'Non renseigné'
          }
          console.log('Formateur chargé:', this.formateur)
        } else {
          throw new Error(profilResponse.data?.message || 'Erreur de chargement du profil')
        }
        
        // 2. Charger les statistiques
        await this.loadStats()
        
        // 3. Charger les sessions récentes
        await this.loadRecentSessions()
        
      } catch (error) {
        console.error('Erreur chargement profil:', error)
        
        // Fallback: utiliser les données du localStorage
        const user = authService.getUser()
        if (user) {
          this.formateur = {
            nom: user.nom || '',
            prenom: user.prenom || '',
            email: user.email || '',
            role: user.role || 'FORMATEUR',
            date_inscription: user.date_inscription || new Date().toISOString(),
            specialite: 'Non spécifiée (mode fallback)',
            cin: user.cin || 'Non renseigné'
          }
        }
        
        this.statsError = 'Impossible de charger les statistiques'
      } finally {
        this.loading = false
      }
    },
    
    async loadStats() {
      try {
        console.log('Chargement des statistiques...')
        this.statsError = null
        
        const response = await formateurService.getStats()
        console.log('Réponse stats:', response.data)
        
        if (response.data && response.data.success) {
          this.stats = response.data.data
          console.log('Statistiques chargées:', this.stats)
        } else {
          this.statsError = 'Statistiques non disponibles'
          console.warn('Statistiques non disponibles:', response.data)
          
          // Essayer de calculer les stats depuis les sessions
          await this.calculateStatsFromSessions()
        }
      } catch (error) {
        console.error('Erreur chargement stats:', error)
        this.statsError = 'Erreur de chargement des statistiques'
        
        // Essayer de calculer les stats depuis les sessions
        await this.calculateStatsFromSessions()
      }
    },
    
    async calculateStatsFromSessions() {
      try {
        console.log('Calcul des stats depuis les sessions...')
        const response = await formateurService.getMySessions()
        
        if (response.data && response.data.success) {
          const sessions = response.data.data || []
          console.log(`${sessions.length} sessions trouvées pour calcul des stats`)
          
          const stats = {
            total_sessions: sessions.length,
            accepted_sessions: sessions.filter(s => s.statut_formateur === 'ACCEPTEE').length,
            pending_sessions: sessions.filter(s => s.statut_formateur === 'EN_ATTENTE').length,
            refused_sessions: sessions.filter(s => s.statut_formateur === 'REFUSEE').length
          }
          
          this.stats = {
            stats: stats,
            acceptance_rate: stats.total_sessions > 0 ? 
              Math.round((stats.accepted_sessions / stats.total_sessions) * 100) : 0
          }
          
          console.log('Stats calculées depuis sessions:', this.stats)
        }
      } catch (error) {
        console.error('Erreur calcul stats sessions:', error)
      }
    },
    
    async loadRecentSessions() {
      try {
        console.log('Chargement des sessions récentes...')
        const response = await formateurService.getMySessions({ limit: 3 })
        
        if (response.data && response.data.success) {
          this.recentSessions = response.data.data || []
          console.log(`${this.recentSessions.length} sessions récentes chargées`)
        }
      } catch (error) {
        console.error('Erreur chargement sessions récentes:', error)
      }
    },
    
    formatDate(dateString) {
      if (!dateString) return 'Non disponible'
      try {
        const date = new Date(dateString)
        return date.toLocaleDateString('fr-FR', {
          day: '2-digit',
          month: 'long',
          year: 'numeric'
        })
      } catch (e) {
        return dateString
      }
    },
    
    formatDateTime(dateString) {
      if (!dateString) return 'Non disponible'
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
    
    getSessionStatusClass(status) {
      switch(status) {
        case 'ACCEPTEE': return 'success'
        case 'EN_ATTENTE': return 'warning'
        case 'REFUSEE': return 'danger'
        default: return 'info'
      }
    },
    
    getSessionStatusText(status) {
      switch(status) {
        case 'ACCEPTEE': return 'Acceptée'
        case 'EN_ATTENTE': return 'En attente'
        case 'REFUSEE': return 'Refusée'
        default: return status || 'Inconnu'
      }
    },
    
    updateCurrentTime() {
      setInterval(() => {
        this.currentTime = new Date().toLocaleTimeString('fr-FR', { 
          hour: '2-digit', 
          minute: '2-digit' 
        })
      }, 60000) // Mettre à jour toutes les minutes
    },
    
    loadAnimationData() {
      try {
        this.animationData = ConsultingAnimation
        console.log('Animation chargée')
      } catch (error) {
        console.error('Erreur animation:', error)
      }
    }
  }
}
</script>

<style scoped>
/* ========================================
   VARIABLES THÈME BLEU (Cohérent avec sidebar)
   ======================================== */
:root {
  --primary-blue: #40a9ea;
  --primary-blue-dark: #2d8de8;
  --primary-blue-light: #5bb9f5;
  --text-dark: #031a31;
  --text-medium: #64748b;
  --text-light: #94a3b8;
  --bg-light: #f8fafc;
  --bg-white: #ffffff;
  --border-color: #e2e8f0;
  --success: #10b981;
  --warning: #f59e0b;
  --danger: #ef4444;
  --info: #3b82f6;
  --sidebar-width: 17%; /* Même largeur que la sidebar */
}

/* ========================================
   LAYOUT PRINCIPAL - DÉCALÉ POUR LA SIDEBAR
   ======================================== */
.formateur-profil {
  min-height: 100vh;
  background: var(--bg-light);
  margin-left: var(--sidebar-width); /* IMPORTANT: Décalage pour la sidebar */
  padding: 0;
}

/* En-tête de page - SIMPLIFIÉ */
.page-header {
  background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-dark) 100%);
  padding: 30px 40px;
  margin-bottom: 30px;
  box-shadow: 0 2px 8px rgba(64, 169, 234, 0.15);
}

.header-content {
  max-width: 1400px;
  margin: 0 auto;
}

.page-title {
  font-size: 1.8em;
  font-weight: 700;
  color: white;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 12px;
}

.page-title i {
  font-size: 1.1em;
}

/* Loading state */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 100px 20px;
  gap: 20px;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid var(--border-color);
  border-top-color: var(--primary-blue);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.loading-state p {
  color: var(--text-medium);
  font-size: 1.1em;
}

/* ========================================
   CONTENU PRINCIPAL
   ======================================== */
.profil-content {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 40px 40px;
}

/* ========================================
   BANDEAU DE PROFIL
   ======================================== */
.profile-banner {
  position: relative;
  background-color: #65aaead3;
  border-radius: 16px;
  overflow: hidden;
  margin-bottom: 30px;
  margin-left: 15%;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.banner-background {
  height: 140px;
  background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-dark) 100%);
}

.banner-content {
  display: flex;
  align-items: flex-end;
  gap: 25px;
  padding: 0 40px 30px;
  margin-top: -70px;
  position: relative;
}

.profile-avatar {
  position: relative;
  flex-shrink: 0;
}

.avatar-circle {
  width: 140px;
  height: 140px;
  background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-light) 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 6px solid white;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  overflow: hidden; /* Important pour contenir l'animation Lottie */
  position: relative;
}

/* Styles pour l'animation Lottie */
.lottie-avatar {
  width: 100%;
  height: 100%;
  position: absolute;
  top: 0;
  left: 0;
  z-index: 1;
}

/* Icône de fallback */
.avatar-fallback-icon {
  font-size: 4em;
  color: white;
  z-index: 2;
  position: relative;
}

.status-indicator {
  position: absolute;
  bottom: 10px;
  right: 10px;
  width: 26px;
  height: 26px;
  background: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
  z-index: 10; /* Assure que l'indicateur est au-dessus de l'animation */
}

.status-indicator.online i {
  color: var(--success);
  font-size: 16px;
}

.profile-identity {
  flex: 1;
  padding-bottom: 10px;
}

.profile-name {
  font-size: 2.2em;
  font-weight: 700;
  color: var(--text-dark);
  margin: 0 0 15px 0;
}

.profile-badges {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 18px;
  border-radius: 20px;
  font-size: 0.95em;
  font-weight: 600;
}

.badge-role {
  background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-dark) 100%);
  color: white;
}

.badge-specialty {
  background: #fef3c7;
  color: #92400e;
}

/* ========================================
   GRILLE DE CONTENU
   ======================================== */
.content-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 30px;
  margin-left: 15%;
}

/* ========================================
   CARTES
   ======================================== */
.info-card,
.stats-card,
.success-card,
.activity-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  overflow: hidden;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.info-card:hover,
.stats-card:hover,
.success-card:hover,
.activity-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.card-header {
  padding: 25px 30px;
  border-bottom: 1px solid var(--border-color);
  background: linear-gradient(to bottom, #f8fafc 0%, white 100%);
}

.card-header h3 {
  margin: 0;
  font-size: 1.25em;
  font-weight: 700;
  color: var(--text-dark);
  display: flex;
  align-items: center;
  gap: 10px;
}

.card-header h3 i {
  color: var(--primary-blue);
  font-size: 1.1em;
}

.card-body {
  padding: 30px;
}

/* ========================================
   INFORMATIONS PERSONNELLES
   ======================================== */
.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 0;
  border-bottom: 1px solid var(--border-color);
}

.info-row:last-child {
  border-bottom: none;
}

.info-label {
  display: flex;
  align-items: center;
  gap: 12px;
  color: var(--text-medium);
  font-weight: 500;
  font-size: 0.95em;
}

.info-label i {
  color: var(--primary-blue);
  font-size: 1.2em;
  width: 24px;
  text-align: center;
}

.info-value {
  color: var(--text-dark);
  font-weight: 600;
  font-size: 1em;
  text-align: right;
}

.email-value {
  color: var(--primary-blue);
}

.specialty-tag {
  background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
  color: var(--primary-blue-dark);
  padding: 6px 14px;
  border-radius: 16px;
  font-size: 0.9em;
  display: inline-block;
}

.role-tag {
  background: var(--primary-blue);
  color: white;
  padding: 5px 12px;
  border-radius: 12px;
  font-size: 0.85em;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* ========================================
   CARTE SUPPORT
   ======================================== */
.support-card {
  margin-top: 20px;
}

.support-content {
  display: flex;
  gap: 20px;
  align-items: flex-start;
}

.support-icon {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.support-icon i {
  font-size: 1.8em;
  color: var(--primary-blue);
}

.support-text {
  flex: 1;
}

.support-text p {
  margin: 0 0 15px 0;
  color: var(--text-medium);
  line-height: 1.6;
}

.btn-contact {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-dark) 100%);
  color: white;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.95em;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-contact:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(64, 169, 234, 0.3);
}

/* ========================================
   STATISTIQUES
   ======================================== */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
}

.stat-box {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 20px;
  border-radius: 12px;
  transition: all 0.3s ease;
}

.stat-box:hover {
  transform: scale(1.05);
}

.stat-primary {
  background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
}

.stat-success {
  background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
}

.stat-warning {
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
}

.stat-danger {
  background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
}

.stat-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5em;
  background: white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.stat-primary .stat-icon {
  color: var(--primary-blue);
}

.stat-success .stat-icon {
  color: var(--success);
}

.stat-warning .stat-icon {
  color: var(--warning);
}

.stat-danger .stat-icon {
  color: var(--danger);
}

.stat-details {
  flex: 1;
}

.stat-value {
  font-size: 2em;
  font-weight: 700;
  color: var(--text-dark);
  line-height: 1;
  margin-bottom: 5px;
}

.stat-label {
  font-size: 0.9em;
  color: var(--text-medium);
  font-weight: 500;
}

.no-stats {
  text-align: center;
  padding: 40px 20px;
  color: var(--text-light);
}

.no-stats i {
  font-size: 3em;
  margin-bottom: 15px;
  display: block;
}

.error-message {
  color: var(--danger);
  font-size: 0.9em;
  margin: 10px 0;
}

.btn-retry {
  margin-top: 15px;
  padding: 8px 16px;
  background: var(--primary-blue);
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 0.9em;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  transition: background 0.3s;
}

.btn-retry:hover {
  background: var(--primary-blue-dark);
}

/* ========================================
   CARTE PERFORMANCE
   ======================================== */
.success-card {
  margin-top: 20px;
}

.performance-content {
  text-align: center;
}

.performance-circle {
  position: relative;
  width: 180px;
  height: 180px;
  margin: 0 auto 20px;
}

.progress-ring {
  transform: rotate(-90deg);
}

.progress-ring-bg {
  fill: none;
  stroke: #e2e8f0;
  stroke-width: 8;
}

.progress-ring-fill {
  fill: none;
  stroke: var(--primary-blue);
  stroke-width: 8;
  stroke-linecap: round;
  transition: stroke-dasharray 1s ease;
}

.performance-percentage {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
}

.percentage-value {
  display: block;
  font-size: 2.5em;
  font-weight: 700;
  color: var(--primary-blue);
  line-height: 1;
}

.percentage-label {
  display: block;
  font-size: 0.8em;
  color: var(--text-medium);
  margin-top: 5px;
}

.performance-info p {
  color: var(--text-medium);
  line-height: 1.6;
  margin: 0;
}

/* ========================================
   ACTIVITÉ RÉCENTE
   ======================================== */
.activity-card {
  margin-top: 20px;
}

.activity-timeline {
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin-bottom: 20px;
}

.no-activity {
  text-align: center;
  padding: 30px 20px;
  color: var(--text-light);
  margin-bottom: 20px;
}

.no-activity i {
  font-size: 2.5em;
  margin-bottom: 10px;
  display: block;
}

.system-activity {
  border-top: 1px solid var(--border-color);
  padding-top: 20px;
}

.timeline-item {
  display: flex;
  gap: 15px;
  align-items: flex-start;
}

.timeline-marker {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  flex-shrink: 0;
  margin-top: 5px;
  box-shadow: 0 0 0 4px rgba(64, 169, 234, 0.1);
}

.timeline-marker.success {
  background: var(--success);
}

.timeline-marker.warning {
  background: var(--warning);
}

.timeline-marker.danger {
  background: var(--danger);
}

.timeline-marker.info {
  background: var(--info);
}

.timeline-content {
  flex: 1;
}

.timeline-title {
  font-weight: 600;
  color: var(--text-dark);
  margin-bottom: 4px;
}

.timeline-date {
  font-size: 0.85em;
  color: var(--text-light);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.timeline-status {
  font-size: 0.8em;
  padding: 2px 8px;
  border-radius: 10px;
  font-weight: 500;
}

.timeline-status.success {
  background: #d1fae5;
  color: var(--success);
}

.timeline-status.warning {
  background: #fef3c7;
  color: var(--warning);
}

.timeline-status.danger {
  background: #fee2e2;
  color: var(--danger);
}

/* ========================================
   RESPONSIVE
   ======================================== */
@media (max-width: 1400px) {
  .formateur-profil {
    margin-left: 20%; /* Ajuster selon votre sidebar responsive */
  }
}

@media (max-width: 1200px) {
  .content-grid {
    grid-template-columns: 1fr;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .formateur-profil {
    margin-left: 0; /* Pas de décalage sur mobile */
  }
  
  .page-header {
    padding: 25px 20px;
  }
  
  .page-title {
    font-size: 1.5em;
  }
  
  .profil-content {
    padding: 0 20px 20px;
  }
  
  .banner-content {
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 0 20px 20px;
    margin-top: -70px;
  }
  
  .profile-name {
    font-size: 1.6em;
  }
  
  .avatar-circle {
    width: 120px;
    height: 120px;
  }
  
  .avatar-fallback-icon {
    font-size: 3.5em;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .card-body {
    padding: 20px;
  }
  
  .info-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 5px;
  }
  
  .info-value {
    text-align: left;
  }
}
</style>