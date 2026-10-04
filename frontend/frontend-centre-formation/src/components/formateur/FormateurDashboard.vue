<template>
  <div class="formateur-dashboard">

    <!-- SIDEBAR BLEU -->
    <aside class="sidebar-blue">

      <!-- Logo -->
      <div class="brand-section">
        <img src="@/assets/logoStir.png" class="brand-logo-img" alt="STIR Logo" />
        <span class="brand-title">Espace Formateur</span>
      </div>

      <!-- User mini profile -->
      <div class="user-mini-profile">
        <div class="avatar-mini">
          <i class="bi bi-person-fill"></i>
        </div>
        <div class="user-info-text">
          <strong>{{ userName }}</strong> <!-- Changé ici -->
          <span>Formateur certifié</span>
        </div>
        <span class="status-dot"></span>
      </div>

      <!-- Navigation -->
      <nav class="main-navigation">
        <ul class="nav-menu">

          <li class="nav-item">
            <router-link to="/formateur" exact-active-class="active" class="nav-link">
              <div class="nav-icon">
                <i class="bi bi-speedometer2"></i>
              </div>
              <span class="nav-text">Tableau de bord</span>
            </router-link>
          </li>
           <li class="nav-item">
            <router-link to="/formateur/sessions" active-class="active" class="nav-link">
              <div class="nav-icon">
                <i class="bi bi-calendar3"></i>
              </div>
              <span class="nav-text">Mes Sessions</span>
            </router-link>
          </li>

          <li class="nav-item">
            <router-link to="/formateur/profil" active-class="active" class="nav-link">
              <div class="nav-icon">
                <i class="bi bi-person-badge"></i>
              </div>
              <span class="nav-text">Mon Profil</span>
            </router-link>
          </li>

         

        </ul>
      </nav>

      <!-- Logout en bas -->
      <div class="sidebar-footer">
        <button @click="logout" class="logout-btn">
          <i class="bi bi-box-arrow-left"></i>
          <span>Déconnexion</span>
        </button>
      </div>

    </aside>

    <!-- MAIN -->
    <main class="main-content-area">
      <div class="content-container">
        <router-view></router-view>
      </div>
    </main>

  </div>
</template>

<script>
import authService from '@/services/auth'

export default {
  name: 'FormateurDashboard',
  data() {
    return {
      userName: 'Imen Ben Ali' // Changé ici
    }
  },
  created() {
    // Si vous avez les données utilisateur dans votre authService
    const user = authService.getUser()
    
    // Option 1: Si vous avez nom et prénom séparés
    if (user?.nom && user?.prenom) {
      this.userName = `${user.prenom} ${user.nom}`
    }
    // Option 2: Si vous avez un champ fullName
    else if (user?.fullName) {
      this.userName = user.fullName
    }
    // Option 3: Si vous avez seulement l'email, extrayez le nom
    else if (user?.email) {
      // Extrait le nom de l'email (ex: "imen.benali@gmail.com" -> "Imen Benali")
      const emailName = user.email.split('@')[0]
      const formattedName = emailName
        .split('.')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ')
      this.userName = formattedName
    }
    // Option 4: Valeur par défaut
    else {
      this.userName = 'Imen Ben Ali'
    }
  },
  methods: {
    logout() {
      authService.logout()
      this.$router.push('/login')
    }
  }
}
</script>

<style scoped>
/* ========================================
   VARIABLES - THÈME BLEU
   ======================================== */
:root {
  /* Couleurs bleues */
  --sidebar-bg: #031a31; /* Bleu principal */
  --sidebar-bg-dark: #155a9c; /* Bleu foncé */
  --sidebar-bg-light: #2d8de8; /* Bleu clair */
  --sidebar-border: #3a7bbf; /* Bordure bleue */
  
  /* Texte sur fond bleu */
  --text-on-blue: #ffffff;
  --text-on-blue-light: #e3f2fd;
  --text-on-blue-secondary: #b3d4fc;
  
  /* Accents */
  --primary-blue: #ffffff; /* Blanc pour contraste */
  --primary-blue-light: #e3f2fd;
  --bg-hover: rgba(255, 255, 255, 0.1);
  --bg-active: rgba(255, 255, 255, 0.2);
  
  /* Dimensions */
  --sidebar-width: 30%;
  --avatar-size: 40px;
}

/* ========================================
   LAYOUT
   ======================================== */
.formateur-dashboard {
  display: flex;
  min-height: 100vh;
  background: #f8fafc;
  font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
}

/* ========================================
   SIDEBAR BLEU
   ======================================== */
.sidebar-blue {
  width:17%;
  background: linear-gradient(135deg, #528dec 0%, #1159be 100%);
  border-right: 1px solid var(--sidebar-border);
  display: flex;
  flex-direction: column;
  position: fixed;
  height: 100vh;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
}

/* Logo */
.brand-section {
  padding: 24px 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  text-align: center;
}

.brand-logo-img {
  width: 150px;
  height: 80px;
  object-fit: contain;
  margin-bottom: 5px;
  padding: 8px;
  animation: logoAnim 2s ease-in-out 0s infinite alternate forwards;
  transition: transform 0.1s ease;
}

@keyframes logoAnim {
  0% {
    opacity: 0.7;
    transform: scale(1);
  }
  100% {
    opacity: 1;
    transform: scale(1.25);
  }
}

.brand-title {
  display: block;
  font-size: 15px;
  font-weight: 600;
  color: var(--text-on-blue);
  letter-spacing: 0.3px;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

/* User Profile */
.user-mini-profile {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 18px 20px;
  margin: 16px;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 14px;
  backdrop-filter: blur(10px);
  transition: all 0.2s ease;
}

.user-mini-profile:hover {
  background: rgba(255, 255, 255, 0.15);
  border-color: rgba(255, 255, 255, 0.3);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.avatar-mini {
  width: var(--avatar-size);
  height: var(--avatar-size);
  background: linear-gradient(135deg, #ffffff 0%, #e3f2fd 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--sidebar-bg);
  font-size: 18px;
  flex-shrink: 0;
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.user-info-text {
  flex: 1;
  min-width: 0;
}

.user-info-text strong {
  display: block;
  font-size: 14px;
  font-weight: 600;
  color: var(--text-on-blue);
  margin-bottom: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
}

.user-info-text span {
  font-size: 12px;
  color: var(--text-on-blue-secondary);
  font-weight: 500;
}

.status-dot {
  width: 10px;
  height: 10px;
  background: #4ade80; /* Vert vif */
  border-radius: 50%;
  flex-shrink: 0;
  position: relative;
  box-shadow: 0 0 8px rgba(74, 222, 128, 0.5);
}

.status-dot::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 4px;
  height: 4px;
  background: white;
  border-radius: 50%;
}

/* Navigation */
.main-navigation {
  flex: 1;
  padding: 20px 16px;
  overflow-y: auto;
}

.nav-menu {
  list-style: none;
  padding: 0;
  margin: 0;
}

.nav-item {
  margin-bottom: 8px;
}

.nav-link {
  display: flex;
  align-items: center;
  padding: 14px 16px;
  color: var(--text-on-blue-secondary);
  text-decoration: none;
  border-radius: 12px;
  transition: all 0.3s ease;
  position: relative;
}

.nav-link:hover {
  background: var(--bg-hover);
  color: var(--text-on-blue);
  transform: translateX(4px);
}

.nav-link.active {
  background: var(--bg-active);
  color: var(--text-on-blue);
  font-weight: 600;
  border-left: 4px solid #ffffff;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.nav-icon {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.15);
  color: var(--text-on-blue-secondary);
  font-size: 18px; /* Plus grand */
  margin-right: 14px;
  transition: all 0.3s ease;
}

/* Icônes plus claires au hover et actif */
.nav-link:hover .nav-icon {
  background: rgba(255, 255, 255, 0.25);
  color: var(--text-on-blue);
  transform: scale(1.1); /* Effet de zoom */
}

.nav-link.active .nav-icon {
  background: #ffffff;
  color: var(--sidebar-bg);
  transform: scale(1.1);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.nav-text {
  font-size: 15px;
  font-weight: 500;
  flex: 1;
  letter-spacing: 0.3px;
}

/* Logout en bas */
.sidebar-footer {
  padding: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  margin-top: auto;
  background: rgba(0, 0, 0, 0.1);
}

.logout-btn {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 14px 20px;
  background: rgba(239, 68, 68, 0.9);
  border: 1px solid rgba(239, 68, 68, 0.5);
  border-radius: 12px;
  color: white;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

.logout-btn:hover {
  background: #ef4444;
  border-color: #ef4444;
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(239, 68, 68, 0.3);
}

.logout-btn i {
  font-size: 16px;
}

/* ========================================
   MAIN CONTENT
   ======================================== */
.main-content-area {
  flex: 1;
  margin-left: var(--sidebar-width);
  display: flex;
  flex-direction: column;
}

.content-container {
  flex: 1;
  padding: 30px;
  max-width: 1400px;
  margin: 0 auto;
  width: 100%;
}

/* ========================================
   SCROLLBAR STYLÉE
   ======================================== */
.main-navigation::-webkit-scrollbar {
  width: 5px;
}

.main-navigation::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 10px;
}

.main-navigation::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 10px;
}

.main-navigation::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.3);
}

/* ========================================
   RESPONSIVE
   ======================================== */
@media (max-width: 768px) {
  :root {
    --sidebar-width: 240px;
  }
  
  .sidebar-blue {
    transform: translateX(-100%);
    transition: transform 0.3s ease;
    z-index: 1000;
  }
  
  .sidebar-blue.mobile-open {
    transform: translateX(0);
  }
  
  .main-content-area {
    margin-left: 0;
  }
  
  .content-container {
    padding: 20px;
  }
}
</style>