<template>
  <div class="employe-dashboard">
    <!-- Navbar améliorée -->
    <nav class="navbar navbar-expand-lg navbar-employe">
      <div class="container-fluid">
        <!-- Logo et marque -->
        <div class="navbar-brand-container">
          <img src="@/assets/logoStir.png" alt="STIR Logo" class="logo-img me-3">
          <div class="brand-text">
            <span class="brand-title">Espace Employé</span>
            <small class="brand-subtitle">Formation & Développement</small>
          </div>
        </div>

        <!-- Bouton toggle pour mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarEmploye">
          <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Contenu navbar -->
        <div class="collapse navbar-collapse" id="navbarEmploye">
          <!-- Navigation centrale - ESPACEMENT AJOUTÉ -->
          <ul class="navbar-nav mx-auto nav-spaced">
            <li class="nav-item mx-2">
              <router-link class="nav-link" to="/employe" exact-active-class="active">
                <i class="bi bi-house-door me-2"></i>
                <span class="nav-text">Accueil</span>
              </router-link>
            </li>
            <li class="nav-item mx-2">
              <router-link class="nav-link" to="/employe/formations" active-class="active">
                <i class="bi bi-book me-2"></i>
                <span class="nav-text">Formations</span>
              </router-link>
            </li>
            <li class="nav-item mx-2">
              <router-link class="nav-link" to="/employe/mes-inscriptions" active-class="active">
                <i class="bi bi-list-check me-2"></i>
                <span class="nav-text">Mes inscriptions</span>
              </router-link>
            </li>
          </ul>

          <!-- Section utilisateur à droite -->
          <div class="navbar-user-section">
            <!-- Profil utilisateur -->
            <div class="user-dropdown">
              <button class="user-profile-btn" @click="toggleUserMenu">
                <div class="user-avatar">
                  {{ userInitials }}
                </div>
                <div class="user-info">
                  <span class="user-name">{{ userName }}</span>
                  <span class="user-role">Employé</span>
                </div>
                <i class="bi bi-chevron-down"></i>
              </button>
              <div v-if="showUserMenu" class="user-menu">
                <div class="user-menu-header">
                  <div class="user-avatar-large">{{ userInitials }}</div>
                  <div>
                    <h6>{{ userName }}</h6>
                    <p class="text-muted mb-0">{{ userEmail }}</p>
                  </div>
                </div>
                <div class="user-menu-items">
                  <router-link to="/employe/profil" class="menu-item" @click="showUserMenu = false">
                    <i class="bi bi-person-circle"></i>
                    <span>Mon profil</span>
                  </router-link>
                  <div class="dropdown-divider"></div>
                  <button class="menu-item logout-item" @click="logout">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Déconnexion</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Contenu principal -->
    <main class="main-content">
      <router-view></router-view>
    </main>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import authService from '@/services/auth'

export default {
  name: 'EmployeDashboard',
  
  setup() {
    const router = useRouter()
    const user = ref({})
    const showUserMenu = ref(false)
    
    onMounted(() => {
      const currentUser = authService.getUser()
      if (!currentUser || currentUser.role !== 'employee') {
        router.push('/login')
      } else {
        user.value = currentUser
      }
    })
    
    const userName = computed(() => {
      if (user.value.prenom && user.value.nom) {
        return `${user.value.prenom} ${user.value.nom}`
      }
      return user.value.email ? user.value.email.split('@')[0] : 'Employé'
    })
    
    const userEmail = computed(() => {
      return user.value.email || 'employe@stir.com'
    })
    
    const userInitials = computed(() => {
      if (!user.value.nom || !user.value.prenom) return 'E'
      return `${user.value.prenom.charAt(0)}${user.value.nom.charAt(0)}`.toUpperCase()
    })
    
    const toggleUserMenu = () => {
      showUserMenu.value = !showUserMenu.value
    }
    
    const logout = () => {
      authService.logout()
      router.push('/login')
    }
    
    // Fermer le dropdown quand on clique ailleurs
    const handleClickOutside = (event) => {
      if (!event.target.closest('.user-dropdown')) {
        showUserMenu.value = false
      }
    }
    
    onMounted(() => {
      document.addEventListener('click', handleClickOutside)
    })
    
    return {
      user,
      userName,
      userEmail,
      userInitials,
      showUserMenu,
      toggleUserMenu,
      logout
    }
  }
}
</script>

<style scoped>
.employe-dashboard {
  min-height: 100vh;
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
}

/* Navbar améliorée */
.navbar-employe {
  background: linear-gradient(135deg, #528dec 0%, #064fb7 100%);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  padding: 0.8rem 0;
}

.navbar-brand-container {
  display: flex;
  align-items: center;
}

.logo-img {
  height: 40px;
  animation: logoAnim 2s ease-in-out infinite alternate;
}

@keyframes logoAnim {
  0% { opacity: 0.8; transform: scale(1); }
  100% { opacity: 1; transform: scale(1.05); }
}

.brand-text {
  display: flex;
  flex-direction: column;
}

.brand-title {
  color: white;
  font-weight: 600;
  font-size: 1.2rem;
}

.brand-subtitle {
  color: #cbd5e1;
  font-size: 0.75rem;
}

/* Navigation items - ESPACEMENT AJOUTÉ */
.nav-spaced .nav-link {
  color: #e2e8f0 !important;
  padding: 0.6rem 1.5rem; /* Padding augmenté */
  border-radius: 8px;
  margin: 0 8px; /* Marge augmentée */
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  position: relative;
}

.nav-spaced .nav-link:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white !important;
  transform: translateY(-2px);
}

.nav-spaced .nav-link.active {
  background: #3b82f6;
  color: white !important;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.nav-text {
  margin-left: 0.5rem;
}

/* Section utilisateur */
.navbar-user-section {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-left: 1rem; /* Espacement ajouté */
}

/* Profil utilisateur */
.user-dropdown {
  position: relative;
}

.user-profile-btn {
  background: none;
  border: none;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem;
  border-radius: 12px;
  transition: all 0.3s ease;
}

.user-profile-btn:hover {
  background: rgba(255, 255, 255, 0.1);
}

.user-avatar {
  width: 36px;
  height: 36px;
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.9rem;
}

.user-info {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.user-name {
  color: white;
  font-weight: 600;
  font-size: 0.9rem;
}

.user-role {
  color: #cbd5e1;
  font-size: 0.75rem;
}

.user-menu {
  position: absolute;
  top: 100%;
  right: 0;
  width: 280px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  z-index: 1000;
  margin-top: 10px;
}

.user-menu-header {
  padding: 1.5rem;
  background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
  border-radius: 12px 12px 0 0;
  display: flex;
  align-items: center;
  gap: 1rem;
  color: white;
}

.user-avatar-large {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 1.2rem;
}

.user-menu-items {
  padding: 0.5rem;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  color: #475569;
  text-decoration: none;
  border-radius: 8px;
  transition: all 0.3s ease;
  width: 100%;
  border: none;
  background: none;
  text-align: left;
}

.menu-item:hover {
  background: #f1f5f9;
  color: #1e293b;
}

.menu-item.logout-item {
  color: #ef4444;
}

.menu-item.logout-item:hover {
  background: #fee2e2;
}

/* Contenu principal */
.main-content {
  min-height: calc(100vh - 140px);
}

/* Responsive */
@media (max-width: 992px) {
  .nav-spaced .nav-link {
    margin: 0.25rem 0; /* Espacement vertical sur mobile */
    padding: 0.75rem 1rem;
  }
  
  .navbar-user-section {
    margin-top: 1rem;
    width: 100%;
    justify-content: flex-end;
    margin-left: 0;
  }
  
  .user-menu {
    position: fixed;
    top: 70px;
    right: 20px;
    width: calc(100% - 40px);
    max-width: 320px;
  }
}

@media (max-width: 768px) {
  .brand-text {
    display: none;
  }
  
  .nav-spaced .nav-link {
    padding: 0.6rem 1rem;
    margin: 0.2rem 0;
  }
}
</style>