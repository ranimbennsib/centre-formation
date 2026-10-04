<template>
  <div class="formateur-home">
    <!-- En-tête -->
    <div class="page-header">
      <div class="header-content">
       <div class="welcome-section">
  <div class="title-container">
    <h1 class="page-title">
      
      Tableau de bord Formateur
    </h1>
    <div class="title-underline"></div>
  </div>
</div>
        <div class="current-info">
          <div class="info-item">
            <i class="bi bi-calendar3 me-2"></i>
            <span>{{ currentDate }}</span>
          </div>
          <div class="info-item">
            <i class="bi bi-clock me-2"></i>
            <span>{{ currentTime }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Cartes de statistiques -->
   <!-- Cartes de statistiques -->
<div class="stats-section">
 
  
  <div class="stats-grid">
    <!-- Box Sessions en attente -->
    <div class="stat-card pending-box" @click="$router.push('/formateur/sessions?statut=EN_ATTENTE')">
      <div class="position-absolute top-0 start-0 w-100 h-100 border-animation"></div>
      
      <div class="stat-card-inner">
        <!-- Ovale avec le nombre -->
        <div class="d-flex flex-column align-items-center mb-3">
          <div class="oval-container d-flex align-items-center justify-content-center">
            <div class="bg-warning bg-opacity-10 rounded-pill p-4 d-inline-block position-relative border-animation-inner">
              <div class="stat-value">{{ pendingSessionsCount }}</div>
            </div>
          </div>
        </div>
        
        <!-- Texte et icône -->
        <div class="text-center">
          <div class="stat-icon mb-2">
            <i class="bi bi-clock-history"></i>
          </div>
          <h3 class="stat-label mb-2">Sessions en attente</h3>
          <div class="stat-action">
            <span>Consulter</span>
            <i class="bi bi-arrow-right-circle ms-1"></i>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Box Sessions acceptées -->
    <div class="stat-card accepted-box" @click="$router.push('/formateur/sessions?statut=ACCEPTEE')">
      <div class="position-absolute top-0 start-0 w-100 h-100 border-animation"></div>
      
      <div class="stat-card-inner">
        <!-- Ovale avec le nombre -->
        <div class="d-flex flex-column align-items-center mb-3">
          <div class="oval-container d-flex align-items-center justify-content-center">
            <div class="bg-success bg-opacity-10 rounded-pill p-4 d-inline-block position-relative border-animation-inner">
              <div class="stat-value">{{ acceptedSessionsCount }}</div>
            </div>
          </div>
        </div>
        
        <!-- Texte et icône -->
        <div class="text-center">
          <div class="stat-icon mb-2">
            <i class="bi bi-check-circle"></i>
          </div>
          <h3 class="stat-label mb-2">Sessions acceptées</h3>
          <div class="stat-action">
            <span>Consulter</span>
            <i class="bi bi-arrow-right-circle ms-1"></i>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Box Prochaines sessions -->
    <div class="stat-card upcoming-box" @click="$router.push('/formateur/sessions?periode=futures')">
      <div class="position-absolute top-0 start-0 w-100 h-100 border-animation"></div>
      
      <div class="stat-card-inner">
        <!-- Ovale avec le nombre -->
        <div class="d-flex flex-column align-items-center mb-3">
          <div class="oval-container d-flex align-items-center justify-content-center">
            <div class="bg-info bg-opacity-10 rounded-pill p-4 d-inline-block position-relative border-animation-inner">
              <div class="stat-value">{{ upcomingSessionsCount }}</div>
            </div>
          </div>
        </div>
        
        <!-- Texte et icône -->
        <div class="text-center">
          <div class="stat-icon mb-2">
            <i class="bi bi-calendar2-week"></i>
          </div>
          <h3 class="stat-label mb-2">Prochaines sessions</h3>
          <div class="stat-action">
            <span>Planning</span>
            <i class="bi bi-arrow-right-circle ms-1"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

    <!-- Sessions urgentes -->
    <div class="urgent-section" v-if="pendingSessions.length > 0">
  <!-- Header amélioré -->
  <div class="section-header">
    <div class="section-title-wrapper">
      <div class="title-icon-wrapper">
        <i class="bi bi-exclamation-triangle-fill"></i>
      </div>
      <div>
        <h2 class="section-title">Action requise</h2>
        <p class="section-subtitle">Sessions nécessitant votre réponse immédiate</p>
      </div>
      <span class="urgent-badge">
        <span class="badge-dot"></span>
        {{ pendingSessions.length }} en attente
      </span>
    </div>
  </div>

  <div class="sessions-grid">
    <div v-for="session in pendingSessions.slice(0, 4)"
         :key="session.id"
         class="session-card"
         :style="{ animationDelay: `${pendingSessions.indexOf(session) * 0.12}s` }">

      <!-- Bande colorée en haut -->
      <div class="card-top-bar"></div>

      <div class="card-body-inner">
        <!-- Header de la carte -->
        <div class="session-card-header">
          <div class="session-info">
            <div class="session-id-badge">
              <i class="bi bi-hash"></i>{{ session.id }}
            </div>
            <h3 class="session-title">{{ session.formation_titre }}</h3>
          </div>
          <span class="status-badge status-pending">
            <i class="bi bi-hourglass-split me-1"></i>
            En attente
          </span>
        </div>

        <!-- Détails en grille -->
        <div class="session-details">
          <div class="detail-item">
            <div class="detail-icon-wrap">
              <i class="bi bi-calendar-check"></i>
            </div>
            <div>
              <span class="detail-label">Début</span>
              <span class="detail-value">{{ formatDate(session.date_debut) }}</span>
            </div>
          </div>

          <div class="detail-item">
            <div class="detail-icon-wrap">
              <i class="bi bi-calendar-x"></i>
            </div>
            <div>
              <span class="detail-label">Fin</span>
              <span class="detail-value">{{ formatDate(session.date_fin) }}</span>
            </div>
          </div>

          <div class="detail-item">
            <div class="detail-icon-wrap">
              <i class="bi bi-geo-alt-fill"></i>
            </div>
            <div>
              <span class="detail-label">Salle</span>
              <span class="detail-value">{{ session.salle }}</span>
            </div>
          </div>

          <div class="detail-item">
            <div class="detail-icon-wrap">
              <i class="bi bi-people-fill"></i>
            </div>
            <div>
              <span class="detail-label">Places</span>
              <span class="detail-value">
                <span class="places-highlight">{{ session.places_restantes || 'N/A' }}</span>
                / {{ session.places_max }}
              </span>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="session-actions">
          <button @click="acceptSession(session.id)" class="btn-action btn-accept">
            <i class="bi bi-check-lg"></i>
            <span>Accepter</span>
          </button>
          <button @click="refuseSession(session)" class="btn-action btn-refuse">
            <i class="bi bi-x-lg"></i>
            <span>Refuser</span>
          </button>
        <!--<router-link :to="`/formateur/sessions/${session.id}`" class="btn-action btn-details">
            <i class="bi bi-eye"></i>
            <span>Détails</span>
          </router-link>-->  
        </div>
      </div>
    </div>
  </div>

  <div v-if="pendingSessions.length > 4" class="view-all">
    <router-link to="/formateur/sessions" class="view-all-link">
      Voir toutes les sessions en attente
      <span class="view-all-count">{{ pendingSessions.length }}</span>
      <i class="bi bi-arrow-right-circle-fill ms-2"></i>
    </router-link>
  </div>
</div>

<!-- Aucune session en attente -->
<div v-else class="no-pending-section">
  <div class="empty-state">
    <div class="empty-icon" ref="lottieContainer"></div> <!-- CHANGÉ -->
    <div class="empty-content">
      <h3 class="empty-title">Aucune session en attente</h3>
      <p class="empty-text">Toutes vos sessions ont été traitées avec succès.</p>
      <router-link to="/formateur/sessions" class="btn-primary-elegant">
        <i class="bi bi-calendar2-week me-2"></i>
        Voir toutes mes sessions
      </router-link>
    </div>
  </div>
</div>

  </div>
</template>

<script>
import formateurService from '@/services/formateur'
import lottie from 'lottie-web' // AJOUTÉ
import SuccessAnimation from '@/assets/Success.json' // AJOUTÉ

export default {
  name: 'FormateurHome',
  data() {
    return {
      pendingSessions: [],
      pendingSessionsCount: 0,
      acceptedSessionsCount: 0,
      upcomingSessionsCount: 0,
      loading: false,
      currentDate: '',
      currentTime: '',
      lottieAnimation: null // AJOUTÉ
    }
  },
  async created() {
    await this.loadDashboardData()
    this.updateDateTime()
    setInterval(() => this.updateDateTime(), 60000) // Mise à jour chaque minute
  },
  mounted() { // AJOUTÉ
    this.$nextTick(() => {
      this.initLottie()
    })
  },
  beforeUnmount() { // AJOUTÉ
    if (this.lottieAnimation) {
      this.lottieAnimation.destroy()
    }
  },
  methods: {
    initLottie() { // AJOUTÉ
      if (this.$refs.lottieContainer) {
        this.lottieAnimation = lottie.loadAnimation({
          container: this.$refs.lottieContainer,
          renderer: 'svg',
          loop: 3 ,
          autoplay: true,
          animationData: SuccessAnimation
        })
      }
    },
    
    updateDateTime() {
      const now = new Date()
      
      // Format de la date
      this.currentDate = now.toLocaleDateString('fr-FR', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
      
      // Format de l'heure
      this.currentTime = now.toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit'
      })
    },
    
    async loadDashboardData() {
      this.loading = true
      try {
        const response = await formateurService.getMySessions()
        
        if (response && response.data && response.data.success) {
          const allSessions = response.data.data || []
          
          this.pendingSessions = allSessions.filter(s => s.statut_formateur === 'EN_ATTENTE')
          this.pendingSessionsCount = this.pendingSessions.length
          
          this.acceptedSessionsCount = allSessions.filter(s => s.statut_formateur === 'ACCEPTEE').length
          
          const now = new Date()
          this.upcomingSessionsCount = allSessions.filter(s => {
            if (!s.date_debut || s.statut_formateur !== 'ACCEPTEE') return false
            return new Date(s.date_debut) > now
          }).length
        } else {
          alert('Erreur: ' + (response.data?.message || 'Données non disponibles'))
        }
      } catch (error) {
        console.error('Erreur:', error)
        alert('Erreur chargement: ' + (error.response?.data?.message || error.message))
      } finally {
        this.loading = false
      }
    },
    
    formatDate(dateString) {
      if (!dateString) return 'Non définie'
      try {
        const date = new Date(dateString)
        return date.toLocaleDateString('fr-FR', {
          day: '2-digit',
          month: '2-digit',
          year: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        })
      } catch (e) {
        return dateString
      }
    },
    
    async acceptSession(sessionId) {
      if (confirm('Voulez-vous accepter cette session ?')) {
        try {
          await formateurService.respondToSession(sessionId, 'ACCEPTEE')
          alert('✅ Session acceptée !')
          await this.loadDashboardData()
        } catch (error) {
          console.error('Erreur:', error)
          alert('Erreur: ' + (error.response?.data?.message || error.message))
        }
      }
    },
    
    async refuseSession(session) {
      const commentaire = prompt('Raison du refus (optionnel) :', '')
      
      if (commentaire === null) {
        console.log('Refus annulé')
        return
      }
      
      if (!confirm(`Voulez-vous vraiment refuser cette session ?\n\n"${session.formation_titre}"`)) {
        return
      }
      
      try {
        await formateurService.respondToSession(session.id, 'REFUSEE', commentaire)
        alert('✅ Session refusée !')
        await this.loadDashboardData()
      } catch (error) {
        console.error('Erreur:', error)
        alert('Erreur: ' + (error.response?.data?.message || error.message))
      }
    }
  }
}
</script>

<style scoped>
/* ========================================
   VARIABLES - THÈME PROFESSIONNEL
   ======================================== */
:root {
  /* Couleurs principales */
  --primary-blue: #8dc8f6;
  --primary-blue-dark: #6ba8d4;
  --accent-orange: #ff6b35;
  --accent-green: #2ecc71;
  --accent-red: #e74c3c;
  
  /* Backgrounds */
  --bg-main: #f8f9fa;
  --bg-card: #ffffff;
  --bg-light: #f1f3f4;
  
  /* Textes */
  --text-primary: #2c3e50;
  --text-secondary: #7f8c8d;
  --text-light: #95a5a6;
  
  /* Bordures */
  --border-light: #e9ecef;
  --border-medium: #dee2e6;
  
  /* Ombres */
  --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.04);
  --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.08);
  --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.12);
  
  /* Dimensions */
  --sidebar-width: 20%;
  --content-padding: 2rem;
}


/* ========================================
   LAYOUT GÉNÉRAL
   ======================================== */
.formateur-home {
  position: fixed; /* ou absolute selon vos besoins */
  top:0;
  left: 17%; /* Largeur du sidebar */
  right: 0;
  bottom: 0;
  padding: 0 !important;
  margin: 0 !important;
  min-height: 100vh;
  background-color: var(--bg-main);
  overflow-y: auto; /* Pour permettre le scroll si nécessaire */
  z-index: 1;
  width: calc(100% - 16%); /* 100% moins la largeur du sidebar */
 
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

/* ========================================
   EN-TÊTE
   ======================================== */
.page-header {
  margin-bottom: 2.5rem;
  animation: slideDown 0.6s ease-out;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-wrap: wrap;
  gap: 1.5rem;
}

.welcome-section {
  margin-top: 5%;
  margin-left: 100px;
  flex: 1;
  display: flex;
  align-items: flex-end; /* Aligne le contenu en bas */
  justify-content: center; /* Centre horizontalement */
  position: relative;
  padding-bottom: 1.5rem; /* Espace en bas */
}

.title-container {
  text-align: center;
  position: relative;
  padding-bottom: 0.5rem;
  margin-left: 30%;

}

.page-title {
  font-size: 2.4rem;
  font-weight: 700;
  color: #1159be; /* Bleu clair agréable   background: linear-gradient(135deg, #528dec 0%, #1159be 100%); */
  /*#414BB0*/
  margin-bottom: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  text-shadow: 0 2px 4px rgba(44, 139, 214, 0.1);
  letter-spacing: 0.5px;
  position: relative;
}

/* Version alternative avec dégradé bleu */
.page-title-alt {
  font-size: 2.4rem;
  font-weight: 700;
  background: linear-gradient(135deg, #528dec 0%, #1159be 100%);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
  margin-bottom: 0.75rem;
  text-align: center;
  text-shadow: 0 2px 8px rgba(44, 139, 214, 0.15);
  letter-spacing: 0.5px;
}



.title-underline {
  width: 80px;
  height: 4px;
  background: linear-gradient(90deg, #2c8bd6, #8dc8f6);
  margin: 0 auto;
  border-radius: 2px;
  animation: slideIn 1s ease-out;
}

@keyframes slideIn {
  from {
    width: 0;
    opacity: 0;
  }
  to {
    width: 80px;
    opacity: 1;
  }
}
.page-subtitle {
  color: var(--text-secondary);
  font-size: 1.1rem;
  margin: 0;
  font-weight: 400;
}

.current-info {
  display: flex;
  gap: 1.5rem;
  background: var(--bg-card);
  padding: 0.75rem 1.5rem;
  border-radius: 12px;
  box-shadow: var(--shadow-sm);
  margin-top: 0px;
}

.info-item {
  display: flex;
  align-items: center;
  color: var(--text-secondary);
  font-size: 0.95rem;
  font-weight: 500;
}

.info-item i {
  color: var(--primary-blue);
  font-size: 1.1rem;
}

/* ========================================
   CARTES DE STATISTIQUES
   ======================================== */
/* ========================================
   CARTES DE STATISTIQUES - TAILLE MOYENNE AVEC MARGES
   ======================================== */
.stats-section {
  margin-bottom: 2rem;
  animation: fadeIn 0.7s ease-out 0.2s both;
  padding: 0 1.5rem; /* MARGES À GAUCHE ET DROITE POUR TOUTE LA SECTION */
}

.section-title {
  font-size: 1.4rem;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 1.2rem;
  display: flex;
  align-items: center;
  padding: 0 0.5rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); /* TAILLE MOYENNE */
  gap: 1.2rem; /* ESPACE MOYEN ENTRE LES BOXES */
  margin: 0 0.5rem;
}

.stat-card {
  background: var(--bg-card);
  border-radius: 10px; /* TAILLE MOYENNE */
  cursor: pointer;
  transition: all 0.25s ease;
  position: relative;
  overflow: hidden;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
  border: none;
  height: 100%;
  min-height: 150px; /* TAILLE MOYENNE - NI TROP GRAND NI TROP PETIT */
  width: 100%;
}

.stat-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
}

.stat-card-inner {
  padding: 1.2rem; /* PADDING MOYEN */
  position: relative;
  z-index: 2;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  height: 100%;
}

/* ANIMATIONS - TAILLE MOYENNE */
.border-animation {
  border: 2px solid transparent; /* BORDURE MOYENNE */
  border-radius: 10px;
  animation: borderRotate 5s linear infinite;
  pointer-events: none;
  z-index: 1;
}

.border-animation-inner {
  border: 1.5px solid transparent; /* BORDURE MOYENNE */
  animation: borderRotateInner 2.5s linear infinite reverse;
}

/* Styles spécifiques pour chaque box */
.pending-box .border-animation {
  animation: borderRotatePending 5s linear infinite;
}

.pending-box .border-animation-inner {
  animation: borderRotateInnerPending 2.5s linear infinite reverse;
}

.accepted-box .border-animation {
  animation: borderRotateAccepted 5s linear infinite;
}

.accepted-box .border-animation-inner {
  animation: borderRotateInnerAccepted 2.5s linear infinite reverse;
}

.upcoming-box .border-animation {
  animation: borderRotateUpcoming 5s linear infinite;
}

.upcoming-box .border-animation-inner {
  animation: borderRotateInnerUpcoming 2.5s linear infinite reverse;
}

/* Animations pour la box Sessions en attente (Orange) */
@keyframes borderRotatePending {
  0% {
    border-top-color: #ff6b35;
    border-right-color: #ff6b35;
    border-bottom-color: transparent;
    border-left-color: transparent;
  }
  25% {
    border-top-color: transparent;
    border-right-color: #ff6b35;
    border-bottom-color: #ff6b35;
    border-left-color: transparent;
  }
  50% {
    border-top-color: transparent;
    border-right-color: transparent;
    border-bottom-color: #ff6b35;
    border-left-color: #ff6b35;
  }
  75% {
    border-top-color: #ff6b35;
    border-right-color: transparent;
    border-bottom-color: transparent;
    border-left-color: #ff6b35;
  }
  100% {
    border-top-color: #ff6b35;
    border-right-color: #ff6b35;
    border-bottom-color: transparent;
    border-left-color: transparent;
  }
}

@keyframes borderRotateInnerPending {
  0% {
    border-top-color: #ff6b35;
    border-right-color: #ff6b35;
    border-bottom-color: transparent;
    border-left-color: transparent;
  }
  25% {
    border-top-color: transparent;
    border-right-color: #ff6b35;
    border-bottom-color: #ff6b35;
    border-left-color: transparent;
  }
  50% {
    border-top-color: transparent;
    border-right-color: transparent;
    border-bottom-color: #ff6b35;
    border-left-color: #ff6b35;
  }
  75% {
    border-top-color: #ff6b35;
    border-right-color: transparent;
    border-bottom-color: transparent;
    border-left-color: #ff6b35;
  }
  100% {
    border-top-color: #ff6b35;
    border-right-color: #ff6b35;
    border-bottom-color: transparent;
    border-left-color: transparent;
  }
}

/* Animations pour la box Sessions acceptées (Vert) */
@keyframes borderRotateAccepted {
  0% {
    border-top-color: #2ecc71;
    border-right-color: #2ecc71;
    border-bottom-color: transparent;
    border-left-color: transparent;
  }
  25% {
    border-top-color: transparent;
    border-right-color: #2ecc71;
    border-bottom-color: #2ecc71;
    border-left-color: transparent;
  }
  50% {
    border-top-color: transparent;
    border-right-color: transparent;
    border-bottom-color: #2ecc71;
    border-left-color: #2ecc71;
  }
  75% {
    border-top-color: #2ecc71;
    border-right-color: transparent;
    border-bottom-color: transparent;
    border-left-color: #2ecc71;
  }
  100% {
    border-top-color: #2ecc71;
    border-right-color: #2ecc71;
    border-bottom-color: transparent;
    border-left-color: transparent;
  }
}

@keyframes borderRotateInnerAccepted {
  0% {
    border-top-color: #2ecc71;
    border-right-color: #2ecc71;
    border-bottom-color: transparent;
    border-left-color: transparent;
  }
  25% {
    border-top-color: transparent;
    border-right-color: #2ecc71;
    border-bottom-color: #2ecc71;
    border-left-color: transparent;
  }
  50% {
    border-top-color: transparent;
    border-right-color: transparent;
    border-bottom-color: #2ecc71;
    border-left-color: #2ecc71;
  }
  75% {
    border-top-color: #2ecc71;
    border-right-color: transparent;
    border-bottom-color: transparent;
    border-left-color: #2ecc71;
  }
  100% {
    border-top-color: #2ecc71;
    border-right-color: #2ecc71;
    border-bottom-color: transparent;
    border-left-color: transparent;
  }
}

/* Animations pour la box Prochaines sessions (Bleu) */
@keyframes borderRotateUpcoming {
  0% {
    border-top-color: #3498db;
    border-right-color: #3498db;
    border-bottom-color: transparent;
    border-left-color: transparent;
  }
  25% {
    border-top-color: transparent;
    border-right-color: #3498db;
    border-bottom-color: #3498db;
    border-left-color: transparent;
  }
  50% {
    border-top-color: transparent;
    border-right-color: transparent;
    border-bottom-color: #3498db;
    border-left-color: #3498db;
  }
  75% {
    border-top-color: #3498db;
    border-right-color: transparent;
    border-bottom-color: transparent;
    border-left-color: #3498db;
  }
  100% {
    border-top-color: #3498db;
    border-right-color: #3498db;
    border-bottom-color: transparent;
    border-left-color: transparent;
  }
}

@keyframes borderRotateInnerUpcoming {
  0% {
    border-top-color: #3498db;
    border-right-color: #3498db;
    border-bottom-color: transparent;
    border-left-color: transparent;
  }
  25% {
    border-top-color: transparent;
    border-right-color: #3498db;
    border-bottom-color: #3498db;
    border-left-color: transparent;
  }
  50% {
    border-top-color: transparent;
    border-right-color: transparent;
    border-bottom-color: #3498db;
    border-left-color: #3498db;
  }
  75% {
    border-top-color: #3498db;
    border-right-color: transparent;
    border-bottom-color: transparent;
    border-left-color: #3498db;
  }
  100% {
    border-top-color: #3498db;
    border-right-color: #3498db;
    border-bottom-color: transparent;
    border-left-color: transparent;
  }
}

/* OVALE - TAILLE MOYENNE */
.oval-container {
  width: 80px; /* TAILLE MOYENNE */
  height: 80px; /* TAILLE MOYENNE */
  margin: 0 auto 0.3rem;
}

/* ICÔNE - TAILLE MOYENNE */
.stat-icon {
  font-size: 1.6rem; /* TAILLE MOYENNE */
  margin-bottom: 0.3rem;
}

.pending-box .stat-icon {
  color: #ff6b35;
}

.accepted-box .stat-icon {
  color: #2ecc71;
}

.upcoming-box .stat-icon {
  color: #3498db;
}

/* TEXTE - TAILLE MOYENNE */
.stat-label {
  font-size: 0.9rem; /* TAILLE MOYENNE */
  color: var(--text-primary);
  margin-bottom: 0.3rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

/* VALEUR NUMÉRIQUE - TAILLE MOYENNE */
.stat-value {
  font-size: 2rem; /* TAILLE MOYENNE */
  font-weight: 700;
  line-height: 1;
  margin: 0;
}

.pending-box .stat-value {
  color: #ff6b35;
}

.accepted-box .stat-value {
  color: #2ecc71;
}

.upcoming-box .stat-value {
  color: #3498db;
}

/* ACTION - TAILLE MOYENNE */
.stat-action {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.3rem;
  color: var(--text-light);
  font-size: 0.85rem; /* TAILLE MOYENNE */
  font-weight: 500;
  transition: all 0.25s ease;
  margin-top: 0.3rem;
  opacity: 0.9;
}

.stat-card:hover .stat-action {
  color: var(--primary-blue);
  transform: translateX(3px);
}

/* RESPONSIVE POUR TAILLE MOYENNE */
@media (max-width: 768px) {
  .stats-section {
    padding: 0 1rem;
  }
  
  .stats-grid {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin: 0 0.3rem;
  }
  
  .stat-card {
    min-height: 140px;
  }
  
  .stat-card-inner {
    padding: 1rem;
  }
  
  .oval-container {
    width: 70px;
    height: 70px;
  }
  
  .stat-value {
    font-size: 1.8rem;
  }
  
  .stat-icon {
    font-size: 1.4rem;
  }
}

@media (max-width: 480px) {
  .stats-section {
    padding: 0 0.8rem;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
    gap: 0.8rem;
    margin: 0 0.2rem;
  }
  
  .stat-card {
    min-height: 130px;
  }
  
  .oval-container {
    width: 65px;
    height: 65px;
  }
}

/* POUR LES GRANDS ÉCRANS */
@media (min-width: 1200px) {
  .stats-section {
    padding: 0 2rem;
  }
  
  .stats-grid {
    margin: 0 0.8rem;
  }
}
/* ========================================
   SECTIONS URGENTES
   ======================================== */
/* ========================================
   SECTION URGENTE - REDESIGN
   ======================================== */
.urgent-section {
  animation: fadeIn 0.7s ease-out 0.4s both;
  padding: 0 1.5rem;
}

/* --- Header --- */
.section-header {
  margin-bottom: 2rem;
}

.section-title-wrapper {
  display: flex;
  align-items: center;
  gap: 1.2rem;
  flex-wrap: wrap;
}

.title-icon-wrapper {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  background: linear-gradient(135deg, #ff6b35, #ff9a3c);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.3rem;
  color: white;
  box-shadow: 0 4px 12px rgba(255, 107, 53, 0.35);
  flex-shrink: 0;
  animation: pulse-icon 3s ease-in-out infinite;
}

@keyframes pulse-icon {
  0%, 100% { box-shadow: 0 4px 12px rgba(255, 107, 53, 0.35); }
  50%       { box-shadow: 0 4px 22px rgba(255, 107, 53, 0.6); }
}

.section-title {
  font-size: 1.35rem;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0 0 0.2rem;
}

.section-subtitle {
  color: var(--text-secondary);
  font-size: 0.9rem;
  margin: 0;
}

.urgent-badge {
  margin-left: auto;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(255, 107, 53, 0.08);
  border: 1.5px solid rgba(255, 107, 53, 0.3);
  color: #ff6b35;
  padding: 0.45rem 1rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 700;
  letter-spacing: 0.3px;
}

.badge-dot {
  width: 8px;
  height: 8px;
  background: #ff6b35;
  border-radius: 50%;
  animation: blink 1.4s ease-in-out infinite;
}

@keyframes blink {
  0%, 100% { opacity: 1; transform: scale(1); }
  50%       { opacity: 0.3; transform: scale(0.7); }
}

/* --- Grille --- */
.sessions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

/* --- Carte --- */
.session-card {
  background: #fff;
  border-radius: 18px;
  overflow: hidden;
  box-shadow: 0 2px 12px rgba(0,0,0,0.06);
  border: 1px solid #eef0f4;
  transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
  animation: slideUp 0.5s ease-out both;
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(24px); }
  to   { opacity: 1; transform: translateY(0); }
}

.session-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 32px rgba(0,0,0,0.1);
  border-color: rgba(255, 107, 53, 0.25);
}

/* Barre colorée en haut de chaque carte */
.card-top-bar {
  height: 4px;
  background: linear-gradient(90deg, #ff6b35, #ff9a3c, #ffca6e);
  width: 100%;
}

.card-body-inner {
  padding: 1.4rem;
}

/* --- Header carte --- */
.session-card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 1.2rem;
}

.session-id-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.2rem;
  font-size: 0.72rem;
  font-weight: 600;
  color: var(--text-light);
  background: var(--bg-light);
  padding: 0.2rem 0.55rem;
  border-radius: 6px;
  margin-bottom: 0.45rem;
  letter-spacing: 0.3px;
}

.session-title {
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--text-primary);
  line-height: 1.35;
  margin: 0;
}

.status-badge {
  flex-shrink: 0;
  padding: 0.35rem 0.75rem;
  border-radius: 10px;
  font-size: 0.75rem;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  white-space: nowrap;
}

.status-pending {
  background: rgba(255, 107, 53, 0.1);
  color: #ff6b35;
  border: 1.5px solid rgba(255, 107, 53, 0.25);
}

/* --- Détails --- */
.session-details {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
  margin-bottom: 1.4rem;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 0.7rem;
  padding: 0.7rem 0.85rem;
  background: #f8f9fb;
  border-radius: 12px;
  transition: background 0.2s ease;
}

.detail-item:hover {
  background: rgba(141, 200, 246, 0.12);
}

.detail-icon-wrap {
  width: 32px;
  height: 32px;
  border-radius: 9px;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
  color: var(--primary-blue);
  box-shadow: 0 1px 4px rgba(0,0,0,0.07);
  flex-shrink: 0;
}

.detail-label {
  display: block;
  font-size: 0.7rem;
  color: var(--text-light);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  margin-bottom: 0.15rem;
}

.detail-value {
  display: block;
  font-size: 0.88rem;
  color: var(--text-primary);
  font-weight: 600;
  line-height: 1.2;
}

.places-highlight {
  color: #2ecc71;
  font-weight: 700;
}

/* --- Actions --- */
.session-actions {
  display: flex;
  gap: 0.6rem;
}

.btn-action {
  flex: 1;
  padding: 0.65rem 0.5rem;
  border: none;
  border-radius: 12px;
  font-size: 0.82rem;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.3rem;
  transition: all 0.25s ease;
  text-decoration: none;
  letter-spacing: 0.2px;
}

.btn-action i {
  font-size: 1.1rem;
}

.btn-accept {
  background: linear-gradient(135deg, #2ecc71, #27ae60);
  color: white;
  box-shadow: 0 3px 10px rgba(46, 204, 113, 0.25);
}

.btn-accept:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(46, 204, 113, 0.4);
  filter: brightness(1.05);
}

.btn-refuse {
  background: linear-gradient(135deg, #e74c3c, #c0392b);
  color: white;
  box-shadow: 0 3px 10px rgba(231, 76, 60, 0.25);
}

.btn-refuse:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(231, 76, 60, 0.4);
  filter: brightness(1.05);
}

.btn-details {
  background: #f0f4ff;
  color: #5a7bd6;
  border: 1.5px solid #dce6ff;
}

.btn-details:hover {
  background: #5a7bd6;
  color: white;
  border-color: #5a7bd6;
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(90, 123, 214, 0.3);
}

/* --- Voir tout --- */
.view-all {
  text-align: center;
  padding-top: 1.5rem;
  border-top: 1px solid var(--border-light);
}

.view-all-link {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: #5a7bd6;
  text-decoration: none;
  font-weight: 700;
  font-size: 0.95rem;
  padding: 0.75rem 2rem;
  border-radius: 12px;
  background: #f0f4ff;
  border: 1.5px solid #dce6ff;
  transition: all 0.25s ease;
}

.view-all-link:hover {
  background: linear-gradient(135deg, #5a7bd6, #3f5bbf);
  color: white;
  border-color: transparent;
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(90, 123, 214, 0.3);
}

.view-all-count {
  background: rgba(255, 107, 53, 0.15);
  color: #ff6b35;
  padding: 0.15rem 0.55rem;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 800;
}

/* --- Responsive --- */
@media (max-width: 768px) {
  .urgent-section { padding: 0 0.75rem; }
  .sessions-grid { grid-template-columns: 1fr; }
  .session-details { grid-template-columns: 1fr; }
  .section-title-wrapper { gap: 0.8rem; }
  .urgent-badge { margin-left: 0; }
}

/* ========================================
   ÉTAT VIDE
   ======================================== */
.no-pending-section {
  animation: fadeIn 0.7s ease-out 0.4s both;
}

.empty-state {
  background: var(--bg-card);
  border-radius: 20px;
  padding: 4rem 2rem;
  text-align: center;
  box-shadow: var(--shadow-md);
  max-width: 500px;
  margin: 0 auto;
}

.empty-icon {
  width: 80px; /* Réduit de 120px */
  height: 80px; /* Réduit de 120px */
  margin: 0 auto 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Pour que l'animation s'adapte */
.empty-icon svg {
  max-width: 100%;
  max-height: 100%;
}

.empty-content {
  max-width: 300px;
  margin: 0 auto;
}

.empty-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 0.5rem;
}

.empty-text {
  color: var(--text-secondary);
  margin-bottom: 1.5rem;
  line-height: 1.5;
}

/* ========================================
   BOUTONS AMÉLIORÉS
   ======================================== */

/* ========================================
   BOUTON AMÉLIORÉ - VERSION FINE
   ======================================== */

/* Style élégant et fin pour "Voir toutes mes sessions" */
.btn-primary-elegant {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  background: linear-gradient(135deg, #8dc8f6 0%, #6ba8d4 100%);
  color: #FFFFFF;
  padding: 0.65rem 1.75rem;
  border-radius: 10px;
  font-weight: 500;
  text-decoration: none;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  border: none;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(141, 200, 246, 0.3);
  letter-spacing: 0.3px;
  font-size: 0.95rem;
  height: 40px;
  line-height: 1;
}

.btn-primary-elegant:hover {
  background: linear-gradient(135deg, #6ba8d4 0%, #4a8db9 100%);
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(141, 200, 246, 0.4);
  color: #FFFFFF;
}

.btn-primary-elegant:active {
  transform: translateY(0);
  box-shadow: 0 1px 4px rgba(141, 200, 246, 0.2);
}

.btn-primary-elegant i {
  font-size: 1rem;
  transition: transform 0.25s ease;
}

.btn-primary-elegant:hover i {
  transform: translateX(2px);
}

/* ========================================
   RESPONSIVE
   ======================================== */
@media (max-width: 1200px) {
  .formateur-home {
    margin-left: 0;
  }
}

@media (max-width: 992px) {
  .header-content {
    flex-direction: column;
  }
  
  .current-info {
    align-self: stretch;
    justify-content: center;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .sessions-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .formateur-home {
    padding: 1.5rem;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .detail-row {
    grid-template-columns: 1fr;
  }
  
  .session-actions {
    flex-direction: column;
  }
  
  .page-title {
    font-size: 1.8rem;
  }
}

@media (max-width: 576px) {
  .current-info {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .empty-state {
    padding: 3rem 1.5rem;
  }
}
</style>