import { createRouter, createWebHistory } from 'vue-router'
import LoginPage from '@/components/LoginPage.vue'
import AdminDashboard from '@/components/admin/AdminDashboard.vue'
import AdminHome from '@/components/admin/AdminHome.vue'
import FormateursList from '@/components/admin/FormateursList.vue'
import FormateurDashboard from '@/components/formateur/FormateurDashboard.vue'
import FormationsList from '@/components/admin/FormationsList.vue'
import SessionsList from '@/components/admin/SessionsList.vue'
import InscriptionsList from '@/components/admin/InscriptionsList.vue'
import StatistiquesList from '@/components/admin/StatistiquesList.vue'
import EmployeRegister from '@/components/admin/EmployeRegister.vue'
import EmployeDashboard from '@/components/employe/EmployeDashboard.vue'
import authService from '@/services/auth'
//emplye mangment
import EmployesManagement from '@/components/admin/EmployesManagement.vue'

// IMPORT DYNAMIQUE DES COMPOSANTS EMPLOYÉ
const EmployeHome = () => import('@/components/employe/EmployeHome.vue')
const FormationsListEmploye = () => import('@/components/employe/FormationsList.vue')
const MesInscriptions = () => import('@/components/employe/MesInscriptions.vue')
const EmployeProfil = () => import('@/components/employe/EmployeProfil.vue')

const routes = [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/login',
    name: 'Login',
    component: LoginPage,
    meta: { requiresGuest: true }
  },
  {
    path: '/admin',
    name: 'Admin',
    component: AdminDashboard,
    meta: { requiresAuth: true, role: 'admin' },
    children: [
      {
        path: '',
        name: 'AdminHome',
        component: AdminHome
      },
      {
        path: 'formateurs',
        name: 'FormateursList',
        component: FormateursList
      },
      {
        path: 'formations',
        name: 'FormationsList',
        component: FormationsList
      },
      {
        path: 'sessions',
        name: 'SessionsList',
        component: SessionsList
      },
      {
        path: 'inscriptions',
        name: 'InscriptionsList',
        component: InscriptionsList
      },
      {
        path: 'statistiques',
        name: 'StatistiquesList',
        component: StatistiquesList
      },
      {
        path: 'employes',
        name: 'EmployeRegister',
        component: EmployeRegister,
        meta: { requiresAuth: true, role: 'admin' }
      },
        {
        path: 'gestion-employes',
        name: 'EmployesManagement',
        component: EmployesManagement,
        meta: { requiresAuth: true, role: 'admin' }
      }
    ]
  },
  // Dans votre fichier index.js, modifiez la route /formateur
{
  path: '/formateur',
  name: 'Formateur',
  component: FormateurDashboard,
  meta: { requiresAuth: true, role: 'formateur' },
  children: [  // ← AJOUTEZ CES ENFANTS
    {
      path: '',
      name: 'FormateurHome',
      component: () => import('@/components/formateur/FormateurHome.vue')
    },
    {
      path: 'sessions',
      name: 'FormateurSessions',
      component: () => import('@/components/formateur/SessionsList.vue')
    },
    {
      path: 'sessions/:sessionId/inscriptions',
      name: 'FormateurInscriptions',
      component: () => import('@/components/formateur/InscriptionsList.vue'),
      props: true
    },
    {
  path: 'profil',
  name: 'FormateurProfil',
  component: () => import('@/components/formateur/FormateurProfil.vue')
},
  ]
},
  {
    path: '/employe',
    name: 'Employe',
    component: EmployeDashboard,
    meta: { requiresAuth: true, role: 'employe' },
    children: [  // ← AJOUTE CES ENFANTS ICI
      {
        path: '',
        name: 'EmployeHome',
        component: EmployeHome
      },
      {
        path: 'formations',
        name: 'EmployeFormations',
        component: FormationsListEmploye
      },
      {
        path: 'mes-inscriptions',
        name: 'MesInscriptions',
        component: MesInscriptions
      },
      {
        path: 'profil',
        name: 'EmployeProfil',
        component: EmployeProfil
      }
    ]
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/'
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Garde de navigation AVEC DEBUG
router.beforeEach((to, from, next) => {
  console.log(`🔄 Navigation: ${from.path} → ${to.path}`)
  console.log('🔐 État auth:', authService.isLoggedIn())
  console.log('👤 Rôle:', authService.getUserRole())
  
  const isAuthenticated = authService.isLoggedIn()
  const userRole = authService.getUserRole()
  
  // Routes nécessitant une authentification
  if (to.meta.requiresAuth && !isAuthenticated) {
    console.log('❌ Non authentifié, redirection login')
    next('/login')
    return
  }
  
  // Routes nécessitant un rôle spécifique
  if (to.meta.role) {
    console.log(`🎯 Route nécessite rôle: ${to.meta.role}, utilisateur a: ${userRole}`)
    
    // Normaliser les rôles pour la comparaison
    const normalizedUserRole = userRole ? userRole.toLowerCase() : ''
    const normalizedRequiredRole = to.meta.role.toLowerCase()
    
    if (normalizedUserRole !== normalizedRequiredRole) {
      console.log(`🚫 Accès refusé: ${normalizedUserRole} ≠ ${normalizedRequiredRole}`)
      
      // Rediriger vers le dashboard approprié
      if (authService.isAdmin()) {
        next('/admin')
      } else if (authService.isFormateur()) {
        next('/formateur')
      } else if (authService.isEmploye()) {
        next('/employe')
      } else {
        next('/login')
      }
      return
    }
  }
  
  // Si déjà connecté et essaie d'accéder à login
  if (to.meta.requiresGuest && isAuthenticated) {
    console.log('✅ Déjà connecté, redirection...')
    
    if (authService.isAdmin()) {
      next('/admin')
    } else if (authService.isFormateur()) {
      next('/formateur')
    } else if (authService.isEmploye()) {
      next('/employe')
    } else {
      next('/')
    }
    return
  }
  
  console.log('✅ Navigation autorisée')
  next()
})

export default router