<template>
  <div class="admin-dashboard">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light shadow-sm">
      <div class="container-fluid">
        <div class=" d-flex align-items-center">
          <img src="../../assets/logoStir.png"   alt="Logo STIR"  class="logo-img me-3">

         
            <div class="welcome-text scroll-container ">
              <h5 class=" fw-bold gradient-text scroll-text ">Bonjour  <span style="color:brown;">{{ user?.prenom }}</span> !Bienvenue dans votre espace administrateur</h5>
            </div>         
        </div>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdmin">
          <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarAdmin">
          <div class="navbar-nav ms-auto">
            <div class="nav-item dropdown">
              <a class="nav-link dropdown-toggle d-flex align-items-center profile-dropdown" href="#" 
                 role="button" data-bs-toggle="dropdown">
                <div class="me-2 text-end">
                  <div class="fw-bold admin-name">{{ user?.prenom }} {{ user?.nom }}</div>
                  <small class="admin-role">Administrateur</small>
                </div>
                <div class="avatar rounded-circle bg-gradient-primary d-flex align-items-center justify-content-center">
                  {{ getInitials() }}
                </div>
              </a>
              <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                <li><hr class="dropdown-divider"></li>
                <li>
                  <button class="dropdown-item text-danger" @click="logout">
                    <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                  </button>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </nav>
    
    <!-- Contenu principal -->
    <div class="container-fluid mt-4">
      <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-2 d-none d-lg-block">
          <div class="card shadow-sm border-0 sticky-top sidebar-card" style="top: 20px;">
            <div class="card-body">
              <h6 class="text-uppercase sidebar-title mb-3">Navigation</h6>
              <div class="list-group list-group-flush">
                <router-link to="/admin" class="list-group-item list-group-item-action border-0">
                  <i class="bi bi-grid-fill me-2"></i>Tableau de bord
                </router-link>
                <router-link to="/admin/formateurs" class="list-group-item list-group-item-action border-0">
                  <i class="bi bi-person-video3 me-2"></i>Formateurs
                </router-link>
               <router-link to="/admin/formations" class="list-group-item list-group-item-action border-0">
                  <i class="bi bi-book-half me-2"></i>Formations
                </router-link>
                <router-link to="/admin/sessions" class="list-group-item list-group-item-action border-0"
                 active-class="active">
                  <i class="bi bi-calendar-event me-2"></i>Sessions
                </router-link>
                 <router-link to="/admin/inscriptions" class="list-group-item list-group-item-action border-0">
                  <i class="bi bi-person-plus-fill me-2"></i>Inscriptions
                </router-link>
                 <router-link to="/admin/statistiques" class="list-group-item list-group-item-action border-0">
                  <i class="bi bi-bar-chart-fill me-2"></i>Statistiques
                </router-link>
                
                 <router-link to="/admin/gestion-employes" class="list-group-item list-group-item-action border-0">
                  <i class="bi bi-gear-fill me-2"></i>Gestion Employés
                </router-link>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-lg-10">
          <router-view></router-view>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import authService from '@/services/auth'

export default {
  name: 'AdminDashboard',
  
  setup() {
    const router = useRouter()
    const user = ref({})
    
    onMounted(() => {
      const currentUser = authService.getUser()
      if (!currentUser || currentUser.role !== 'admin') {
        router.push('/login')
      } else {
        user.value = currentUser
      }
    })
    
    const getInitials = () => {
      if (!user.value.nom || !user.value.prenom) return 'A'
      return `${user.value.prenom.charAt(0)}${user.value.nom.charAt(0)}`.toUpperCase()
    }
    
    const logout = async () => {
      await authService.logout()
      router.push('/login')
    }
    
    return {
      user,
      getInitials,
      logout
    }
  }
}
</script>

<style scoped>
.admin-dashboard {
  min-height: 100vh;
  background: linear-gradient(135deg, #f0f4ff 0%, #e6f0ff 100%);
}

/* Navbar avec couleur pastel douce */
.navbar {
  
  background: linear-gradient(135deg, #b8d4f1 0%, #d4e5f7 100%) !important;
  height:7%;
}

/* Logo */
.logo-img {
   animation: logoAnim 2s ease-in-out 0s infinite alternate forwards;
  transition: transform 0.3s ease;
  height: 80px;
  width: 150px;
}
@keyframes logoAnim {
  0% {
    opacity: 0.7;
    transform: scale(1);
  }
  100% {
    opacity: 1;
    transform: scale(1.15);
  }
}

.logo-img:hover {
  transform: scale(1.05);
}

/* Animation de bienvenue */
.welcome-animation {
  display: flex;
  align-items: center;
  gap: 12px;
}





.welcome-text h5 {
  color: #152EA3;
  font-size: 1.3rem;
  letter-spacing: 0.5px;
  font-family:Cambria, Cochin, Georgia, Times, 'Times New Roman', serif

}

/*.gradient-text {
  background: linear-gradient(135deg, #003366 0%, #0056A3 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}*/
.scroll-container {
           
  overflow: hidden;    /* cache le débordement */
  white-space: nowrap;
  
}

.scroll-text {
  display: inline-block;
  padding-left: 50%;  /* commence hors écran */
  animation: scroll-left 12s linear infinite;
}

@keyframes scroll-left {
  from {
    transform: translateX(0);
  }
  to {
    transform: translateX(-100%);
  }
}




/* Profile dropdown */
.profile-dropdown {
  padding: 8px 16px;
  border-radius: 12px;
  transition: all 0.3s ease;
}

.profile-dropdown:hover {
  background: rgba(0, 51, 102, 0.08);
}

.admin-name {
  color: #003366;
  font-size: 0.95rem;
}

.admin-role {
  color: #6b8caf;
  font-size: 0.75rem;
}

.avatar {
  width: 42px;
  height: 42px;
  font-weight: bold;
  font-size: 15px;
  color: white;
  background: linear-gradient(135deg, #003366 0%, #0056A3 100%);
  box-shadow: 0 3px 10px rgba(0, 51, 102, 0.2);
}

/* Sidebar Card */
.sidebar-card {
  background: white;
  border-radius: 16px !important;
  border: 1px solid #e0eaf5 !important;
}

.sidebar-title {
  color: #003366;
  font-weight: 700;
  font-size: 0.75rem;
  letter-spacing: 1.2px;
  padding-bottom: 8px;
  border-bottom: 2px solid #b8d4f1;
}

.list-group-item {
  border-radius: 10px !important;
  margin-bottom: 6px;
  transition: all 0.3s ease;
  color: #4a5568;
  font-weight: 500;
  padding: 12px 16px;
  border: none !important;
}

.list-group-item i {
  font-size: 1.1rem;
  color: #6b8caf;
  transition: all 0.3s ease;
}

.list-group-item:hover {
  background: linear-gradient(135deg, #e6f0ff 0%, #d4e5f7 100%);
  transform: translateX(5px);
  color: #003366;
}

.list-group-item:hover i {
  color: #003366;
  transform: scale(1.1);
}

.list-group-item.router-link-active,
.list-group-item.active {
  background: linear-gradient(135deg, #003366 0%, #0056A3 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(0, 51, 102, 0.25);
  transform: translateX(5px);
}

.list-group-item.router-link-active i,
.list-group-item.active i {
  color: white;
}

/* Dropdown menu */
.dropdown-menu {
  border-radius: 12px;
  padding: 8px;
  margin-top: 8px;
}

.dropdown-item {
  border-radius: 8px;
  padding: 10px 16px;
  transition: all 0.2s ease;
}

.dropdown-item:hover {
  background: #fee;
  transform: translateX(4px);
}

/* Responsive */
@media (max-width: 991px) {
  .welcome-animation {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .wave-hand {
    font-size: 24px;
  }
}
</style>