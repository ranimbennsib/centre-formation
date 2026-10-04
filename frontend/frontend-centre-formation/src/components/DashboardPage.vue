<template>
  <div class="dashboard-page">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
      <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="#">
          <img src="https://via.placeholder.com/40x40/ffffff/0056A3?text=S" 
               alt="Logo" 
               class="rounded-circle me-2">
          <div>
            <h5 class="mb-0 fw-bold">STIR Bizerte</h5>
            <small class="opacity-75">Centre de Formation</small>
          </div>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav me-auto">
            <li class="nav-item">
              <router-link to="/dashboard" class="nav-link active">
                <i class="bi bi-speedometer2 me-1"></i>Dashboard
              </router-link>
            </li>
            <li v-if="isAdmin" class="nav-item">
              <a class="nav-link" href="#">
                <i class="bi bi-journal-text me-1"></i>Formations
              </a>
            </li>
            <li v-if="isFormateur" class="nav-item">
              <a class="nav-link" href="#">
                <i class="bi bi-calendar-check me-1"></i>Mes Sessions
              </a>
            </li>
            <li v-if="isEmploye" class="nav-item">
              <a class="nav-link" href="#">
                <i class="bi bi-clipboard-check me-1"></i>Inscriptions
              </a>
            </li>
          </ul>
          
          <div class="navbar-nav align-items-center">
            <div class="nav-item dropdown">
              <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                <div class="me-2 text-end">
                  <div class="fw-bold">{{ user.nom }} {{ user.prenom }}</div>
                  <small class="text-light opacity-75">{{ getRoleName(user.role) }}</small>
                </div>
                <div class="avatar rounded-circle bg-light d-flex align-items-center justify-content-center" 
                     :class="roleBadgeClass">
                  {{ getInitials(user.nom, user.prenom) }}
                </div>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Mon Profil</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Paramètres</a></li>
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
        <!-- Sidebar (optionnel) -->
        <div class="col-lg-2 d-none d-lg-block">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <h6 class="text-uppercase text-muted mb-3">Navigation</h6>
              <div class="list-group list-group-flush">
                <router-link to="/dashboard" class="list-group-item list-group-item-action border-0 active">
                  <i class="bi bi-house-door me-2"></i>Tableau de bord
                </router-link>
                <a href="#" class="list-group-item list-group-item-action border-0">
                  <i class="bi bi-bell me-2"></i>Notifications
                  <span class="badge bg-primary float-end">3</span>
                </a>
                <a href="#" class="list-group-item list-group-item-action border-0">
                  <i class="bi bi-envelope me-2"></i>Messages
                  <span class="badge bg-success float-end">5</span>
                </a>
                <a href="#" class="list-group-item list-group-item-action border-0">
                  <i class="bi bi-calendar-week me-2"></i>Calendrier
                </a>
                <a href="#" class="list-group-item list-group-item-action border-0">
                  <i class="bi bi-file-earmark-text me-2"></i>Documents
                </a>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Contenu principal -->
        <div class="col-lg-10">
          <!-- En-tête -->
          <div class="row mb-4">
            <div class="col-12">
              <div class="card bg-primary text-white border-0 shadow">
                <div class="card-body py-4">
                  <div class="row align-items-center">
                    <div class="col-md-8">
                      <h1 class="display-6 fw-bold mb-2">Bienvenue, {{ user.prenom }} !</h1>
                      <p class="lead mb-0 opacity-75">
                        Plateforme de gestion du Centre de Formation STIR Bizerte
                      </p>
                    </div>
                    <div class="col-md-4 text-end">
                      <div class="role-display">
                        <span class="badge bg-light text-primary fs-6 p-3">
                          <i :class="roleIcon" class="me-2"></i>{{ getRoleName(user.role) }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Statistiques -->
          <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card border-start border-primary border-4 shadow h-100">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col">
                      <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                        Formations Actives
                      </div>
                      <div class="h5 mb-0 fw-bold">12</div>
                    </div>
                    <div class="col-auto">
                      <i class="bi bi-journal-text text-primary fs-1"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card border-start border-success border-4 shadow h-100">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col">
                      <div class="text-xs fw-bold text-success text-uppercase mb-1">
                        Sessions en cours
                      </div>
                      <div class="h5 mb-0 fw-bold">8</div>
                    </div>
                    <div class="col-auto">
                      <i class="bi bi-calendar-check text-success fs-1"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card border-start border-warning border-4 shadow h-100">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col">
                      <div class="text-xs fw-bold text-warning text-uppercase mb-1">
                        Participants
                      </div>
                      <div class="h5 mb-0 fw-bold">156</div>
                    </div>
                    <div class="col-auto">
                      <i class="bi bi-people text-warning fs-1"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card border-start border-info border-4 shadow h-100">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col">
                      <div class="text-xs fw-bold text-info text-uppercase mb-1">
                        Taux de satisfaction
                      </div>
                      <div class="h5 mb-0 fw-bold">94%</div>
                    </div>
                    <div class="col-auto">
                      <i class="bi bi-graph-up text-info fs-1"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Dashboard par rôle -->
          <div class="row">
            <!-- Admin Dashboard -->
            <div v-if="isAdmin" class="col-12">
              <div class="card shadow border-0 mb-4">
                <div class="card-header bg-white border-0 py-3">
                  <h5 class="card-title mb-0">
                    <i class="bi bi-shield-check text-primary me-2"></i>
                    Tableau de Bord Administrateur
                  </h5>
                </div>
                <div class="card-body">
                  <div class="row g-4">
                    <div class="col-md-6 col-lg-3">
                      <div class="card border-0 bg-light h-100">
                        <div class="card-body text-center">
                          <i class="bi bi-journal-plus text-primary fs-1 mb-3"></i>
                          <h6 class="card-title">Nouvelle Formation</h6>
                          <button class="btn btn-primary mt-2">Créer</button>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                      <div class="card border-0 bg-light h-100">
                        <div class="card-body text-center">
                          <i class="bi bi-person-plus text-success fs-1 mb-3"></i>
                          <h6 class="card-title">Ajouter Formateur</h6>
                          <button class="btn btn-success mt-2">Ajouter</button>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                      <div class="card border-0 bg-light h-100">
                        <div class="card-body text-center">
                          <i class="bi bi-calendar-plus text-warning fs-1 mb-3"></i>
                          <h6 class="card-title">Planifier Session</h6>
                          <button class="btn btn-warning mt-2">Planifier</button>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                      <div class="card border-0 bg-light h-100">
                        <div class="card-body text-center">
                          <i class="bi bi-graph-up text-info fs-1 mb-3"></i>
                          <h6 class="card-title">Rapports</h6>
                          <button class="btn btn-info mt-2">Générer</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Formateur Dashboard -->
            <div v-else-if="isFormateur" class="col-12">
              <div class="card shadow border-0 mb-4">
                <div class="card-header bg-white border-0 py-3">
                  <h5 class="card-title mb-0">
                    <i class="bi bi-person-badge text-success me-2"></i>
                    Tableau de Bord Formateur
                  </h5>
                </div>
                <div class="card-body">
                  <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    Vous avez 3 sessions prévues cette semaine
                  </div>
                  <!-- Contenu formateur -->
                </div>
              </div>
            </div>
            
            <!-- Employé Dashboard -->
            <div v-else-if="isEmploye" class="col-12">
              <div class="card shadow border-0 mb-4">
                <div class="card-header bg-white border-0 py-3">
                  <h5 class="card-title mb-0">
                    <i class="bi bi-briefcase text-warning me-2"></i>
                    Tableau de Bord Employé
                  </h5>
                </div>
                <div class="card-body">
                  <div class="alert alert-warning">
                    <i class="bi bi-clock me-2"></i>
                    5 inscriptions en attente de validation
                  </div>
                  <!-- Contenu employé -->
                </div>
              </div>
            </div>
          </div>
          
          <!-- Dernières activités -->
          <div class="row">
            <div class="col-12">
              <div class="card shadow border-0">
                <div class="card-header bg-white border-0 py-3">
                  <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history text-secondary me-2"></i>
                    Activités Récentes
                  </h5>
                </div>
                <div class="card-body">
                  <div class="list-group list-group-flush">
                    <div class="list-group-item border-0 d-flex">
                      <div class="me-3">
                        <div class="avatar-sm rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center">
                          <i class="bi bi-person-add"></i>
                        </div>
                      </div>
                      <div class="flex-grow-1">
                        <div class="d-flex justify-content-between">
                          <h6 class="mb-1">Nouvelle inscription</h6>
                          <small class="text-muted">Il y a 5 min</small>
                        </div>
                        <p class="mb-0 text-muted">Mohamed Ali s'est inscrit à la formation "Gestion de Projet"</p>
                      </div>
                    </div>
                    <div class="list-group-item border-0 d-flex">
                      <div class="me-3">
                        <div class="avatar-sm rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center">
                          <i class="bi bi-check-circle"></i>
                        </div>
                      </div>
                      <div class="flex-grow-1">
                        <div class="d-flex justify-content-between">
                          <h6 class="mb-1">Session terminée</h6>
                          <small class="text-muted">Il y a 2 heures</small>
                        </div>
                        <p class="mb-0 text-muted">Session "Excel Avancé" marquée comme terminée</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Footer -->
    <footer class="mt-5 py-3 bg-light border-top">
      <div class="container-fluid">
        <div class="row align-items-center">
          <div class="col-md-6">
            <span class="text-muted">
              © 2024 STIR Bizerte - Centre de Formation. Tous droits réservés.
            </span>
          </div>
          <div class="col-md-6 text-end">
            <span class="text-muted me-3">
              <i class="bi bi-envelope me-1"></i>contact@stir-bizerte.tn
            </span>
            <span class="text-muted">
              <i class="bi bi-telephone me-1"></i>+216 XX XXX XXX
            </span>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import authService from '@/services/auth'

export default {
  name: 'DashboardPage',
  
  setup() {
    const router = useRouter()
    const user = ref({})
    
    onMounted(() => {
      user.value = authService.getUser() || {}
    })
    
    // Computed properties
    const isAdmin = computed(() => authService.isAdmin())
    const isFormateur = computed(() => authService.isFormateur())
    const isEmploye = computed(() => authService.isEmploye())
    
    const roleBadgeClass = computed(() => {
      if (isAdmin.value) return 'admin-avatar'
      if (isFormateur.value) return 'formateur-avatar'
      if (isEmploye.value) return 'employe-avatar'
      return ''
    })
    
    const roleIcon = computed(() => {
      if (isAdmin.value) return 'bi bi-shield-check'
      if (isFormateur.value) return 'bi bi-person-badge'
      if (isEmploye.value) return 'bi bi-briefcase'
      return 'bi bi-person'
    })
    
    // Méthodes
    const getRoleName = (role) => {
      const roles = {
        'admin': 'Administrateur',
        'formateur': 'Formateur',
        'employe': 'Employé'
      }
      return roles[role] || role
    }
    
    const getInitials = (nom, prenom) => {
      if (!nom || !prenom) return 'U'
      return `${prenom.charAt(0)}${nom.charAt(0)}`.toUpperCase()
    }
    
    const logout = () => {
      authService.logout()
      router.push('/login')
    }
    
    return {
      user,
      isAdmin,
      isFormateur,
      isEmploye,
      roleBadgeClass,
      roleIcon,
      getRoleName,
      getInitials,
      logout
    }
  }
}
</script>

<style scoped>
.dashboard-page {
  min-height: 100vh;
  background: #f8f9fa;
}

.navbar {
  background: linear-gradient(135deg, #0056A3 0%, #003366 100%) !important;
  padding: 0.75rem 0;
}

.avatar {
  width: 40px;
  height: 40px;
  font-weight: bold;
  font-size: 14px;
}

.admin-avatar {
  background: linear-gradient(135deg, #f5222d, #fa541c) !important;
  color: white;
}

.formateur-avatar {
  background: linear-gradient(135deg, #52c41a, #73d13d) !important;
  color: white;
}

.employe-avatar {
  background: linear-gradient(135deg, #fa8c16, #faad14) !important;
  color: white;
}

.card {
  border-radius: 12px;
  transition: transform 0.3s;
}

.card:hover {
  transform: translateY(-5px);
}

.border-start {
  border-left-width: 4px !important;
}

.avatar-sm {
  width: 36px;
  height: 36px;
}

.list-group-item.active {
  background: linear-gradient(135deg, #0056A3 0%, #003366 100%);
  border-color: #0056A3;
}

.btn {
  border-radius: 8px;
  font-weight: 500;
}

.badge {
  border-radius: 20px;
  padding: 0.5em 1em;
}

/* Responsive */
@media (max-width: 768px) {
  .display-6 {
    font-size: 1.5rem;
  }
  
  .card-body {
    padding: 1rem;
  }
}
</style>