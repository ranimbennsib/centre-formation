<template>
  <div class="inscriptions-list">
    <!-- En-tête avec gradient -->
    <div class="page-header">
      <div class="container-fluid">
        <div class="row align-items-center">
          <div class="col-lg-8">
            <div class="header-content">
              <div class="icon-wrapper">
                <i class="bi bi-person-check primary-text"></i>
              </div>
              <div>
                <h1 class="page-title primary-text mb-2">Gestion des Inscriptions</h1>
                <p class="page-subtitle mb-0">
                  <span class="badge badge-count primary-text">{{ inscriptions.length }}</span>
                  inscription(s) trouvée(s)
                </p>
              </div>
            </div>
          </div>
          <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <button @click="refreshData" class="btn btn-outline-secondary btn-refresh me-2" title="Rafraîchir">
              <i class="bi bi-arrow-clockwise"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="container-fluid mt-4">
      <div class="row mb-4 g-3">
        <div class="col-lg-3 col-md-6">
          <div class="stat-card stat-pending">
            <div class="stat-icon">
              <i class="bi bi-clock-history"></i>
            </div>
            <div class="stat-content">
              <h3 class="stat-value">{{ stats.en_attente || 0 }}</h3>
              <p class="stat-label">En attente</p>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="stat-card stat-accepted">
            <div class="stat-icon">
              <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-content">
              <h3 class="stat-value">{{ stats.validees || 0 }}</h3>
              <p class="stat-label">Validées</p>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="stat-card stat-refused">
            <div class="stat-icon">
              <i class="bi bi-x-circle"></i>
            </div>
            <div class="stat-content">
              <h3 class="stat-value">{{ stats.refusees || 0 }}</h3>
              <p class="stat-label">Refusées</p>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="stat-card stat-cancelled">
            <div class="stat-icon">
              <i class="bi bi-slash-circle"></i>
            </div>
            <div class="stat-content">
              <h3 class="stat-value">{{ stats.annulees || 0 }}</h3>
              <p class="stat-label">Annulées</p>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Filtres et recherche -->
      <div class="filters-card mb-4">
        <div class="card-body">
          <div class="row g-3">
            <div class="col-lg-5 col-md-6">
              <div class="search-box">
                <i class="bi bi-search search-icon"></i>
                <input 
                  type="text" 
                  class="form-control form-control-search" 
                  placeholder="Rechercher par employé, formation..."
                  v-model="search"
                  @input="handleSearch"
                />
              </div>
            </div>
            <div class="col-lg-2 col-md-3">
              <select class="form-select form-select-custom" v-model="filterStatus" @change="loadInscriptions">
                <option value="">Tous les statuts</option>
                <option value="EN_ATTENTE">En attente</option>
                <option value="VALIDEE">Validées</option>
                <option value="REFUSEE">Refusées</option>
                <option value="ANNULEE">Annulées</option>
              </select>
            </div>
            <div class="col-lg-3 col-md-3">
              <select class="form-select form-select-custom" v-model="filterSession" @change="loadInscriptions">
                <option value="">Toutes les sessions</option>
                <option v-for="session in sessions" :value="session.id" :key="session.id">
                  {{ session.formation_titre }} ({{ formatDate(session.date_debut) }})
                </option>
              </select>
            </div>
            <div class="col-lg-2 col-md-3">
              <select class="form-select form-select-custom" v-model="itemsPerPage" @change="loadInscriptions">
                <option value="10">10 par page</option>
                <option value="25">25 par page</option>
                <option value="50">50 par page</option>
              </select>
            </div>
          </div>
          
          <!-- Filtres avancés -->
          <transition name="slide-fade">
            <div class="row g-3 mt-2" v-if="showAdvancedFilters">
              <div class="col-md-4">
                <label class="form-label-custom">Formateur</label>
                <select class="form-select form-select-custom" v-model="filterFormateur" @change="loadInscriptions">
                  <option value="">Tous les formateurs</option>
                  <option v-for="formateur in formateurs" :value="formateur.id" :key="formateur.id">
                    {{ formateur.prenom }} {{ formateur.nom }}
                  </option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label-custom">Période d'inscription</label>
                <div class="date-range-group">
                  <input type="date" class="form-control form-control-custom" v-model="filterDateFrom" @change="loadInscriptions">
                  <span class="date-separator">à</span>
                  <input type="date" class="form-control form-control-custom" v-model="filterDateTo" @change="loadInscriptions">
                </div>
              </div>
              <div class="col-md-4">
                <label class="form-label-custom">Tri par</label>
                <select class="form-select form-select-custom" v-model="sortBy" @change="loadInscriptions">
                  <option value="date_desc">Date (récent)</option>
                  <option value="date_asc">Date (ancien)</option>
                  <option value="nom_asc">Nom A-Z</option>
                  <option value="nom_desc">Nom Z-A</option>
                </select>
              </div>
            </div>
          </transition>
          
          <div class="mt-3">
            <button class="btn btn-outline-primary btn-sm btn-filter-toggle" @click="toggleAdvancedFilters">
              <i class="bi bi-funnel me-2"></i>
              {{ showAdvancedFilters ? 'Masquer les filtres' : 'Plus de filtres' }}
            </button>
            <button class="btn btn-outline-danger btn-sm ms-2" @click="resetFilters">
              <i class="bi bi-x-circle me-2"></i> Réinitialiser
            </button>
          </div>
        </div>
      </div>
      
      <!-- Liste des inscriptions -->
      <div class="inscriptions-table-card">
        <div class="card-body p-0">
          <div v-if="loading" class="loading-state">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Chargement...</span>
            </div>
            <p class="mt-3 text-muted">Chargement des inscriptions...</p>
          </div>
          
          <div v-else-if="inscriptions.length === 0" class="empty-state">
            <div class="empty-icon">
              <i class="bi bi-person-x"></i>
            </div>
            <h5 class="empty-title">Aucune inscription trouvée</h5>
            <p v-if="hasActiveFilters" class="empty-text">
              Essayez de modifier vos filtres de recherche.
            </p>
            <p v-else class="empty-text">
              Aucune inscription n'est enregistrée pour le moment.
            </p>
          </div>
          
          <div v-else class="table-responsive">
            <table class="table table-custom mb-0">
              <thead>
                <tr>
                  <th>Employé</th>
                  <th>Formation & Session</th>
                  <th>Dates</th>
                  <th>Formateur</th>
                  <th>Inscription</th>
                  <th>Statut</th>
                  <th class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="inscription in inscriptions" :key="inscription.id" class="inscription-row">
                  <!-- Employé -->
                  <td>
                    <div class="employe-info">
                      <div class="employe-avatar">
                        <i class="bi bi-person-circle"></i>
                      </div>
                      <div>
                        <h6 class="employe-name">{{ inscription.employe_prenom }} {{ inscription.employe_nom }}</h6>
                        <span class="employe-email">{{ inscription.employe_email }}</span>
                        <span class="inscription-id">ID: {{ inscription.id }}</span>
                      </div>
                    </div>
                  </td>
                  
                  <!-- Formation & Session -->
                  <td>
                    <div class="formation-info">
                      <h6 class="formation-title">{{ inscription.formation_titre }}</h6>
                      <span class="session-location">
                        <i class="bi bi-geo-alt me-1"></i>{{ inscription.salle }}
                      </span>
                    </div>
                  </td>
                  
                  <!-- Dates -->
                  <td>
                    <div class="dates-info">
                      <div class="date-item">
                        <span class="date-label">Début</span>
                        <span class="date-value">{{ formatDate(inscription.date_debut) }}</span>
                      </div>
                      <div class="date-item">
                        <span class="date-label">Fin</span>
                        <span class="date-value">{{ formatDate(inscription.date_fin) }}</span>
                      </div>
                    </div>
                  </td>
                  
                  <!-- Formateur -->
                  <td>
                    <div v-if="inscription.formateur_nom" class="formateur-info">
                      <div class="formateur-avatar">
                        <i class="bi bi-person-badge"></i>
                      </div>
                      <span class="formateur-name">
                        {{ inscription.formateur_prenom }} {{ inscription.formateur_nom }}
                      </span>
                    </div>
                    <div v-else class="formateur-empty">
                      <i class="bi bi-person-x me-1"></i>
                      Non assigné
                    </div>
                  </td>
                  
                  <!-- Date inscription -->
                  <td>
                    <div class="inscription-date">
                      <div class="date-label">Inscrit le</div>
                      <div class="date-value">{{ formatDateTime(inscription.date_inscription) }}</div>
                    </div>
                  </td>
                  
                  <!-- Statut -->
                  <td>
                    <span class="badge badge-status" :class="getStatusClass(inscription.statut)">
                      <i class="bi me-1" :class="getStatusIconClass(inscription.statut)"></i>
                      {{ getStatusText(inscription.statut) }}
                    </span>
                    <div v-if="inscription.raison_refus && inscription.statut === 'REFUSEE'" 
                         class="refusal-reason">
                      <i class="bi bi-exclamation-circle me-1"></i>
                      {{ inscription.raison_refus }}
                    </div>
                  </td>
                  
                  <!-- Actions -->
                  <td>
                    <div class="action-buttons">
                      <button @click="viewDetails(inscription)" 
                              class="btn btn-action btn-view" 
                              title="Voir détails">
                        <i class="bi bi-eye"></i>
                      </button>
                      <button v-if="inscription.statut === 'EN_ATTENTE'"
                              @click="validateInscription(inscription)" 
                              class="btn btn-action btn-accept" 
                              title="Valider">
                        <i class="bi bi-check-circle"></i>
                      </button>
                      <button v-if="inscription.statut === 'EN_ATTENTE'"
                              @click="refuseInscription(inscription)" 
                              class="btn btn-action btn-refuse" 
                              title="Refuser">
                        <i class="bi bi-x-circle"></i>
                      </button>
                      <!-- Supprimer (optionnel) -->
                      <!--
                      <button v-if="['ANNULEE', 'REFUSEE'].includes(inscription.statut)"
                              @click="confirmDelete(inscription)" 
                              class="btn btn-action btn-delete" 
                              title="Supprimer">
                        <i class="bi bi-trash"></i>
                      </button>
                      -->
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        
        <!-- Pagination -->
        <div v-if="inscriptions.length > 0" class="card-footer pagination-footer">
          <div class="pagination-info">
            Affichage de <strong>{{ startItem }}</strong> à <strong>{{ endItem }}</strong> sur <strong>{{ totalItems }}</strong> inscriptions
          </div>
          <nav>
            <ul class="pagination pagination-custom mb-0">
              <li class="page-item" :class="{disabled: currentPage === 1}">
                <button class="page-link" @click="changePage(currentPage - 1)">
                  <i class="bi bi-chevron-left"></i>
                </button>
              </li>
              
              <li v-for="page in displayedPages" :key="page" 
                  class="page-item" :class="{active: page === currentPage}">
                <button class="page-link" @click="changePage(page)">
                  {{ page }}
                </button>
              </li>
              
              <li class="page-item" :class="{disabled: currentPage === totalPages}">
                <button class="page-link" @click="changePage(currentPage + 1)">
                  <i class="bi bi-chevron-right"></i>
                </button>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
    
    <!-- ========================================
       MODAL DÉTAILS - DESIGN PASTEL MODERNE
       ======================================== -->
    <div v-if="showDetailModal" class="modal fade show d-block modal-overlay" 
         @click.self="closeDetailModal">
      <div class="modal-dialog modal-lg modal-centered">
        <div class="modal-content modal-modern">
          <div class="modal-header modal-header-modern">
            <h5 class="modal-title-modern">
              <div class="modal-icon">
                <i class="bi bi-person-lines-fill"></i>
              </div>
              <span>Détails de l'Inscription</span>
            </h5>
            <button type="button" class="btn-close-modern" @click="closeDetailModal">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
          
          <!-- Chargement -->
          <div v-if="detailLoading" class="modal-body text-center py-5">
            <div class="spinner-border text-primary-pastel spinner-pastel" role="status">
              <span class="visually-hidden">Chargement...</span>
            </div>
            <p class="mt-3 text-muted-pastel">Chargement des détails...</p>
          </div>
          
          <!-- Erreur -->
          <div v-else-if="detailError" class="modal-body modal-body-modern">
            <div class="alert alert-danger-pastel">
              <i class="bi bi-exclamation-triangle me-2"></i>
              {{ detailError }}
            </div>
            <div class="text-center">
              <button @click="closeDetailModal" class="btn btn-secondary-pastel">Fermer</button>
            </div>
          </div>
          
          <!-- Contenu -->
          <div v-else-if="inscriptionDetails" class="modal-body modal-body-modern">
            <!-- En-tête avec titre et statut -->
            <div class="mb-4 detail-header">
              <h4 class="name-text-large mb-3">{{ inscriptionDetails.formation_titre }}</h4>
              <div class="d-flex align-items-center gap-3 flex-wrap">
                <span class="badge-status-large" :class="getStatusClass(inscriptionDetails.statut)">
                  <i class="bi me-1" :class="getStatusIconClass(inscriptionDetails.statut)"></i>
                  {{ getStatusText(inscriptionDetails.statut) }}
                </span>
                <small class="text-muted-pastel">
                  <i class="bi bi-hash me-1"></i>ID: {{ inscriptionDetails.id }}
                </small>
                <small class="text-muted-pastel">
                  <i class="bi bi-calendar me-1"></i>
                  Inscrit le {{ formatDateTime(inscriptionDetails.date_inscription) }}
                </small>
              </div>
            </div>
            
            <!-- Grille d'informations -->
            <div class="inscription-grid mb-4">
              <!-- Employé -->
              <div class="inscription-info-card">
                <div class="inscription-info-icon bg-primary-light-pastel">
                  <i class="bi bi-person text-primary-pastel"></i>
                </div>
                <div class="inscription-info-content">
                  <label class="inscription-info-label">Employé</label>
                  <p class="inscription-info-value mb-1">
                    {{ inscriptionDetails.employe_prenom }} {{ inscriptionDetails.employe_nom }}
                  </p>
                  <small class="text-muted-pastel">
                    <i class="bi bi-envelope me-1"></i>{{ inscriptionDetails.employe_email }}
                  </small>
                </div>
              </div>
              
              <!-- Dates de session -->
              <div class="inscription-info-card">
                <div class="inscription-info-icon bg-warning-light-pastel">
                  <i class="bi bi-calendar-event text-warning-pastel"></i>
                </div>
                <div class="inscription-info-content">
                  <label class="inscription-info-label">Dates de la session</label>
                  <p class="inscription-info-value mb-1">{{ formatDate(inscriptionDetails.date_debut) }}</p>
                  <small class="text-muted-pastel">au {{ formatDate(inscriptionDetails.date_fin) }}</small>
                </div>
              </div>
              
              <!-- Lieu -->
              <div class="inscription-info-card">
                <div class="inscription-info-icon bg-info-light-pastel">
                  <i class="bi bi-geo-alt text-info-pastel"></i>
                </div>
                <div class="inscription-info-content">
                  <label class="inscription-info-label">Lieu</label>
                  <p class="inscription-info-value">{{ inscriptionDetails.salle }}</p>
                </div>
              </div>
              
              <!-- Formateur -->
              <div class="inscription-info-card">
                <div class="inscription-info-icon bg-success-light-pastel">
                  <i class="bi bi-person-badge text-success-pastel"></i>
                </div>
                <div class="inscription-info-content">
                  <label class="inscription-info-label">Formateur</label>
                  <div v-if="inscriptionDetails.formateur_nom">
                    <p class="inscription-info-value mb-1">
                      {{ inscriptionDetails.formateur_prenom }} {{ inscriptionDetails.formateur_nom }}
                    </p>
                    <small v-if="inscriptionDetails.formateur_email" class="text-muted-pastel">
                      <i class="bi bi-envelope me-1"></i>{{ inscriptionDetails.formateur_email }}
                    </small>
                  </div>
                  <div v-else>
                    <p class="text-muted-pastel fst-italic">
                      <i class="bi bi-person-x me-1"></i>Non assigné
                    </p>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- État de la session -->
            <div class="mb-4">
              <h6 class="section-subtitle">
                <i class="bi bi-info-circle me-2"></i>
                État de la session
              </h6>
              <div class="session-status-card">
                <div class="row">
                  <div class="col-md-6">
                    <div class="status-item">
                      <label class="status-label">Statut de la session</label>
                      <span class="badge-status ms-2" :class="getSessionStatusClass(inscriptionDetails.statut_formateur)">
                        {{ getSessionStatusText(inscriptionDetails.statut_formateur) }}
                      </span>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="status-item">
                      <label class="status-label">Date de début</label>
                      <span :class="{'text-danger-pastel': isSessionPast(inscriptionDetails)}" class="fw-semibold">
                        {{ formatDate(inscriptionDetails.date_debut) }}
                      </span>
                      <small v-if="isSessionPast(inscriptionDetails)" class="text-danger-pastel ms-2">
                        <i class="bi bi-exclamation-triangle me-1"></i>Session passée
                      </small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Raison du refus -->
            <div v-if="inscriptionDetails.raison_refus && inscriptionDetails.statut === 'REFUSEE'" 
                 class="mb-4">
              <h6 class="section-subtitle">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Raison du refus
              </h6>
              <div class="alert alert-danger-pastel">
                <i class="bi bi-exclamation-triangle me-2"></i>
                {{ inscriptionDetails.raison_refus }}
              </div>
            </div>
            
            <!-- Description formation -->
            <div v-if="inscriptionDetails.formation_description" class="mb-4">
              <h6 class="section-subtitle">
                <i class="bi bi-text-paragraph me-2"></i>
                Description de la formation
              </h6>
              <div class="description-box">
                {{ inscriptionDetails.formation_description }}
              </div>
            </div>
          </div>
          
          <!-- Actions -->
          <div class="modal-footer modal-footer-pastel">
            <button type="button" class="btn btn-outline-secondary-pastel" 
                    @click="closeDetailModal">
              <i class="bi bi-x-circle me-1"></i>
              Fermer
            </button>
            <button v-if="inscriptionDetails && inscriptionDetails.statut === 'EN_ATTENTE'"
                    type="button" class="btn btn-outline-success-pastel"
                    @click="validateInscription(inscriptionDetails)">
              <i class="bi bi-check-circle me-1"></i>Valider
            </button>
            <button v-if="inscriptionDetails && inscriptionDetails.statut === 'EN_ATTENTE'"
                    type="button" class="btn btn-outline-danger-pastel"
                    @click="refuseInscription(inscriptionDetails)">
              <i class="bi bi-x-circle me-1"></i>Refuser
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- ========================================
       MODAL REFUS - DESIGN PASTEL MODERNE
       ======================================== -->
    <div v-if="showRefuseModal" class="modal fade show d-block modal-overlay" 
         @click.self="closeRefuseModal">
      <div class="modal-dialog modal-centered">
        <div class="modal-content modal-modern">
          <div class="modal-header modal-header-modern">
            <h5 class="modal-title-modern text-danger">
              <div class="modal-icon">
                <i class="bi bi-x-circle"></i>
              </div>
              <span>Refuser l'inscription</span>
            </h5>
            <button type="button" class="btn-close-modern" @click="closeRefuseModal">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
          <div class="modal-body modal-body-modern">
            <div class="alert alert-warning-pastel mb-4">
              <i class="bi bi-exclamation-triangle me-2"></i>
              <strong>Attention :</strong> Vous êtes sur le point de refuser une inscription.
            </div>
            
            <div class="inscription-preview mb-4">
              <div class="row">
                <div class="col-md-6">
                  <div class="preview-item">
                    <label class="preview-label">Employé</label>
                    <p class="preview-value">{{ inscriptionToRefuse?.employe_prenom }} {{ inscriptionToRefuse?.employe_nom }}</p>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="preview-item">
                    <label class="preview-label">Formation</label>
                    <p class="preview-value">{{ inscriptionToRefuse?.formation_titre }}</p>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="mb-3">
              <label class="form-label-modern">
                <i class="bi bi-chat-left-text me-1"></i> Raison du refus <span class="required">*</span>
              </label>
              <textarea class="form-control-modern" v-model="refuseReason" 
                        rows="4" placeholder="Expliquez la raison du refus..."
                        :class="{'is-invalid': !refuseReason && refuseTouched}"
                        @blur="refuseTouched = true"></textarea>
              <div v-if="!refuseReason && refuseTouched" class="invalid-feedback">
                La raison du refus est obligatoire
              </div>
              <small class="form-hint">
                Cette raison sera visible par l'employé concerné.
              </small>
            </div>
          </div>
          <div class="modal-footer modal-footer-pastel">
            <button type="button" class="btn btn-outline-secondary-pastel" 
                    @click="closeRefuseModal">
              <i class="bi bi-x-circle me-1"></i>
              Annuler
            </button>
            <button type="button" class="btn btn-outline-danger-pastel" 
                    @click="confirmRefuse" :disabled="!refuseReason || refusing">
              <span v-if="refusing">
                <span class="spinner-border spinner-border-sm me-1"></span>
                Traitement...
              </span>
              <span v-else>
                <i class="bi bi-x-circle me-1"></i>Confirmer le refus
              </span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import adminService from '@/services/admin'

export default {
  name: 'InscriptionsList',
  
  setup() {
    // États principaux
    const loading = ref(true)
    const inscriptions = ref([])
    const sessions = ref([])
    const formateurs = ref([])
    
    // Filtres
    const search = ref('')
    const searchTimeout = ref(null)
    const filterStatus = ref('')
    const filterSession = ref('')
    const filterPeriod = ref('')
    const filterFormateur = ref('')
    const filterDateFrom = ref('')
    const filterDateTo = ref('')
    const sortBy = ref('date_desc')
    const showAdvancedFilters = ref(false)
    
    // Pagination
    const currentPage = ref(1)
    const itemsPerPage = ref(10)
    const totalItems = ref(0)
    const totalPages = ref(1)
    
    // Modals et actions
    const showDetailModal = ref(false)
    const showRefuseModal = ref(false)
    const detailLoading = ref(false)
    const detailError = ref(null)
    const inscriptionDetails = ref(null)
    const inscriptionToRefuse = ref(null)
    const refuseReason = ref('')
    const refuseTouched = ref(false)
    const refusing = ref(false)
    
    // Statistiques
    const stats = ref({
      en_attente: 0,
      validees: 0,
      refusees: 0,
      annulees: 0
    })
    //methode ajoutée
    const getStatusIconClass = (status) => {
  const iconMap = {
    'EN_ATTENTE': 'bi-clock',
    'VALIDEE': 'bi-check-circle-fill',
    'REFUSEE': 'bi-x-circle-fill',
    'ANNULEE': 'bi-slash-circle-fill'
  }
  return iconMap[status] || 'bi-question-circle'
}
    
    // Computed
    const startItem = computed(() => {
      return (currentPage.value - 1) * itemsPerPage.value + 1
    })
    
    const endItem = computed(() => {
      const end = currentPage.value * itemsPerPage.value
      return end > totalItems.value ? totalItems.value : end
    })
    
    const displayedPages = computed(() => {
      const pages = []
      const maxVisible = 5
      let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2))
      let end = Math.min(totalPages.value, start + maxVisible - 1)
      
      if (end - start + 1 < maxVisible) {
        start = Math.max(1, end - maxVisible + 1)
      }
      
      for (let i = start; i <= end; i++) {
        pages.push(i)
      }
      
      return pages
    })
    
    const hasActiveFilters = computed(() => {
      return filterStatus.value || filterSession.value || filterPeriod.value || 
             filterFormateur.value || filterDateFrom.value || filterDateTo.value ||
             search.value.trim() !== ''
    })
    
    // Méthodes utilitaires
    const formatDate = (dateString) => {
      if (!dateString) return ''
      try {
        const date = new Date(dateString)
        return date.toLocaleDateString('fr-FR', {
          weekday: 'short',
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        })
      } catch (error) {
        return dateString
      }
    }
    
    const formatDateTime = (dateString) => {
      if (!dateString) return ''
      try {
        const date = new Date(dateString)
        return date.toLocaleDateString('fr-FR', {
          year: 'numeric',
          month: 'short',
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        })
      } catch (error) {
        return dateString
      }
    }
    
    const getStatusText = (status) => {
      const statusMap = {
        'EN_ATTENTE': 'En attente',
        'VALIDEE': 'Validée',
        'REFUSEE': 'Refusée',
        'ANNULEE': 'Annulée'
      }
      return statusMap[status] || status
    }
    
    const getStatusClass = (status) => {
      const classMap = {
        'EN_ATTENTE': 'bg-warning bg-opacity-10 text-warning border border-warning',
        'VALIDEE': 'bg-success bg-opacity-10 text-success border border-success',
        'REFUSEE': 'bg-danger bg-opacity-10 text-danger border border-danger',
        'ANNULEE': 'bg-secondary bg-opacity-10 text-secondary border border-secondary'
      }
      return classMap[status] || 'bg-light'
    }
    
    const getSessionStatusText = (status) => {
      const statusMap = {
        'EN_ATTENTE': 'En attente',
        'ACCEPTEE': 'Acceptée',
        'REFUSEE': 'Refusée',
        'ANNULEE': 'Annulée',
        null: 'Non assigné'
      }
      return statusMap[status] || status
    }
    
    const getSessionStatusClass = (status) => {
      const classMap = {
        'EN_ATTENTE': 'bg-warning',
        'ACCEPTEE': 'bg-success',
        'REFUSEE': 'bg-danger',
        'ANNULEE': 'bg-secondary',
        null: 'bg-light'
      }
      return classMap[status] || 'bg-light'
    }
    
    const isSessionPast = (inscription) => {
      if (!inscription.date_debut) return false
      return new Date(inscription.date_debut) < new Date()
    }
    
    const calculateStats = () => {
      stats.value = {
        en_attente: 0,
        validees: 0,
        refusees: 0,
        annulees: 0
      }
      
      inscriptions.value.forEach(inscription => {
        switch (inscription.statut) {
          case 'EN_ATTENTE':
            stats.value.en_attente++
            break
          case 'VALIDEE':
            stats.value.validees++
            break
          case 'REFUSEE':
            stats.value.refusees++
            break
          case 'ANNULEE':
            stats.value.annulees++
            break
        }
      })
    }
    
    // Méthodes principales
    const loadInscriptions = async () => {
      loading.value = true
      try {
        console.log('🔄 Chargement des inscriptions...')
        
        const params = {
          limit: itemsPerPage.value,
          page: currentPage.value
        }
        
        // Filtres
        if (search.value.trim()) {
          params.recherche = search.value.trim()
        }
        
        if (filterStatus.value) {
          params.statut = filterStatus.value
        }
        
        if (filterSession.value) {
          params.session_id = filterSession.value
        }
        
        if (filterFormateur.value) {
          params.formateur_id = filterFormateur.value
        }
        
        if (filterDateFrom.value) {
          params.date_from = filterDateFrom.value
        }
        
        if (filterDateTo.value) {
          params.date_to = filterDateTo.value
        }
        
        if (filterPeriod.value) {
          params.periode = filterPeriod.value
        }
        
        if (sortBy.value) {
          params.sort = sortBy.value
        }
        
        console.log('🔍 Paramètres inscriptions:', params)
        
        const response = await adminService.getInscriptions(params)
        console.log('📊 Réponse inscriptions:', response)
        
        if (response.success) {
          inscriptions.value = response.data || []
          totalItems.value = response.count || inscriptions.value.length
          totalPages.value = Math.ceil(totalItems.value / itemsPerPage.value)
          
          calculateStats()
          
          console.log(`✅ ${inscriptions.value.length} inscriptions chargées`)
        } else {
          throw new Error(response.message || 'Erreur lors du chargement')
        }
      } catch (error) {
        console.error('❌ Erreur chargement inscriptions:', error)
        alert(error.message || 'Erreur lors du chargement des inscriptions')
      } finally {
        loading.value = false
      }
    }
    
    const loadSessions = async () => {
      try {
        const response = await adminService.getSessions()
        if (response.success) {
          sessions.value = response.data || []
        }
      } catch (error) {
        console.error('Erreur chargement sessions:', error)
      }
    }
    
    const loadFormateurs = async () => {
      try {
        const response = await adminService.getFormateurs()
        if (response.success) {
          formateurs.value = response.data || []
        }
      } catch (error) {
        console.error('Erreur chargement formateurs:', error)
      }
    }
    
    const handleSearch = () => {
      clearTimeout(searchTimeout.value)
      searchTimeout.value = setTimeout(() => {
        currentPage.value = 1
        loadInscriptions()
      }, 500)
    }
    
    const toggleAdvancedFilters = () => {
      showAdvancedFilters.value = !showAdvancedFilters.value
    }
    
    const resetFilters = () => {
      search.value = ''
      filterStatus.value = ''
      filterSession.value = ''
      filterPeriod.value = ''
      filterFormateur.value = ''
      filterDateFrom.value = ''
      filterDateTo.value = ''
      sortBy.value = 'date_desc'
      currentPage.value = 1
      loadInscriptions()
    }
    
    const refreshData = () => {
      loadInscriptions()
    }
    
    const exportData = () => {
      // TODO: Implémenter l'export CSV/Excel
      alert('Fonctionnalité d\'export à implémenter')
    }
    
    const viewDetails = async (inscription) => {
      showDetailModal.value = true
      detailLoading.value = true
      detailError.value = null
      inscriptionDetails.value = null
      
      try {
        const response = await adminService.getInscription(inscription.id)
        if (response.success && response.data) {
          inscriptionDetails.value = response.data.inscription
        } else {
          // Fallback sur les données de base
          inscriptionDetails.value = inscription
        }
      } catch (error) {
        console.error('Erreur chargement détails:', error)
        detailError.value = error.message || 'Impossible de charger les détails'
        inscriptionDetails.value = inscription
      } finally {
        detailLoading.value = false
      }
    }
    
    const validateInscription = async (inscription) => {
      if (!confirm(`Voulez-vous valider l'inscription de ${inscription.employe_prenom} ${inscription.employe_nom} ?`)) {
        return
      }
      
      try {
        await adminService.validateInscription(inscription.id)
        alert('✅ Inscription validée avec succès')
        loadInscriptions()
        if (showDetailModal.value) {
          closeDetailModal()
        }
      } catch (error) {
        console.error('Erreur validation:', error)
        const errorMessage = error.response?.data?.message || 
                           error.message || 
                           'Erreur lors de la validation'
        alert(errorMessage)
      }
    }
    
    const refuseInscription = (inscription) => {
      inscriptionToRefuse.value = inscription
      refuseReason.value = ''
      refuseTouched.value = false
      showRefuseModal.value = true
    }
    
    const confirmRefuse = async () => {
      if (!refuseReason.value.trim()) {
        refuseTouched.value = true
        return
      }
      
      refusing.value = true
      try {
        await adminService.refuseInscription(inscriptionToRefuse.value.id, refuseReason.value)
        alert('✅ Inscription refusée avec succès')
        closeRefuseModal()
        loadInscriptions()
        if (showDetailModal.value) {
          closeDetailModal()
        }
      } catch (error) {
        console.error('Erreur refus:', error)
        const errorMessage = error.response?.data?.message || 
                           error.message || 
                           'Erreur lors du refus'
        alert(errorMessage)
      } finally {
        refusing.value = false
      }
    }
    
    const confirmDelete = async (inscription) => {
      const message = `Êtes-vous sûr de vouloir supprimer cette inscription ?\n\n`
        + `Employé: ${inscription.employe_prenom} ${inscription.employe_nom}\n`
        + `Formation: ${inscription.formation_titre}\n`
        + `Statut: ${getStatusText(inscription.statut)}\n\n`
        + '⚠️ Cette action est irréversible.'
        
      if (confirm(message)) {
        try {
          await adminService.deleteInscription(inscription.id)
          alert('✅ Inscription supprimée avec succès')
          loadInscriptions()
        } catch (error) {
          console.error('Erreur suppression:', error)
          const errorMessage = error.response?.data?.message || 
                             error.message || 
                             'Erreur lors de la suppression'
          alert(errorMessage)
        }
      }
    }
    
    const changePage = (page) => {
      if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page
        loadInscriptions()
      }
    }
    
    const closeDetailModal = () => {
      showDetailModal.value = false
      inscriptionDetails.value = null
      detailError.value = null
    }
    
    const closeRefuseModal = () => {
      showRefuseModal.value = false
      inscriptionToRefuse.value = null
      refuseReason.value = ''
      refuseTouched.value = false
    }
    
    // Lifecycle
    onMounted(async () => {
      console.log('🚀 Initialisation InscriptionsList...')
      await Promise.all([
        loadInscriptions(),
        loadSessions(),
        loadFormateurs()
      ])
      console.log('✅ InscriptionsList initialisé')
    })
    
    return {
      // États
      loading,
      inscriptions,
      sessions,
      formateurs,
      
      // Filtres
      search,
      filterStatus,
      filterSession,
      filterPeriod,
      filterFormateur,
      filterDateFrom,
      filterDateTo,
      sortBy,
      showAdvancedFilters,
      
      // Pagination
      currentPage,
      itemsPerPage,
      totalItems,
      totalPages,
      
      // Modals
      showDetailModal,
      showRefuseModal,
      detailLoading,
      detailError,
      inscriptionDetails,
      inscriptionToRefuse,
      refuseReason,
      refuseTouched,
      refusing,
      
      // Statistiques
      stats,
      
      // Computed
      startItem,
      endItem,
      displayedPages,
      hasActiveFilters,
      
      // Méthodes
      formatDate,
      formatDateTime,
      getStatusText,
      getStatusClass,
      getSessionStatusText,
      getSessionStatusClass,
      isSessionPast,
      handleSearch,
      toggleAdvancedFilters,
      resetFilters,
      refreshData,
      exportData,
      viewDetails,
      validateInscription,
      refuseInscription,
      confirmRefuse,
      confirmDelete,
      changePage,
      closeDetailModal,
      closeRefuseModal,
      loadInscriptions,
       getStatusIconClass
    }
  }
}
</script>

<style scoped>
/* Variables et base - Mêmes que pour sessions */
:root {
  --primary-color: #4165f7;
  --primary-dark: #4338ca;
  --primary-light: #818cf8;
  --success-color: #10b981;
  --warning-color: #f59e0b;
  --danger-color: #ef4444;
  --info-color: #3b82f6;
  --gray-50: #f9fafb;
  --gray-100: #f3f4f6;
  --gray-200: #e5e7eb;
  --gray-300: #d1d5db;
  --gray-400: #9ca3af;
  --gray-500: #6b7280;
  --gray-600: #4b5563;
  --gray-700: #374151;
  --gray-800: #1f2937;
  --gray-900: #111827;
  --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
  --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
  --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
  --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
  --border-radius: 12px;
  --border-radius-sm: 8px;
  --border-radius-lg: 16px;
  
  /* COULEURS PASTEL */
  --pastel-blue-primary: #A8D5E2;
  --pastel-blue-light: #D4EBF2;
  --pastel-blue-dark: #7BB8CC;
  --pastel-blue-bg: #F0F8FA;
  --pastel-success: #B8E0D2;
  --pastel-success-light: #D6F0E7;
  --pastel-warning: #FFE5B4;
  --pastel-warning-light: #FFF4E0;
  --pastel-danger: #FFB3BA;
  --pastel-danger-light: #FFD9DC;
  --pastel-info: #B4D7FF;
  --pastel-info-light: #E0EEFF;
  --pastel-secondary: #D4D4D4;
  --pastel-secondary-light: #EEEEEE;
  --pastel-text-primary: #2C3E50;
  --pastel-text-muted: #7D8DA6;
  --pastel-text-light: #A5B4C6;
}

.inscriptions-list {
  min-height: calc(100vh - 200px);
  background: linear-gradient(135deg, #f5f7fa 0%, #e4e9f2 100%);
  padding-bottom: 3rem;
}

/* ========================================
   EN-TÊTE - Même style que sessions
   ======================================== */
.page-header {
  background: white;
  padding: 2rem 0;
  margin-bottom: 2rem;
  border-bottom: 1px solid var(--gray-200);
  box-shadow: var(--shadow-sm);
}

.header-content {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.icon-wrapper {
  width: 70px;
  height: 70px;
  background: linear-gradient(135deg, var(--pastel-blue-primary) 0%, var(--pastel-blue-dark) 100%);
  border-radius: var(--border-radius-lg);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: var(--shadow-md);
}

.icon-wrapper i {
  font-size: 2rem;
  color: white;
}

.page-title {
  color: var(--pastel-text-primary);
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  letter-spacing: -0.5px;
}

.primary-text {
  color: var(--pastel-text-primary) !important;
}

.page-subtitle {
  color: var(--pastel-text-muted);
  font-size: 1rem;
  font-weight: 500;
}

.badge-count {
  background: var(--pastel-blue-dark);
  color: white;
  padding: 0.375rem 0.875rem;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.875rem;
  margin-left: 0.5rem;
}

/* ========================================
   BOUTONS D'ACTION
   ======================================== */
.btn-refresh {
  border: 2px solid var(--pastel-blue-primary);
  color: var(--pastel-blue-dark);
  border-radius: 10px;
  padding: 0.75rem;
  transition: all 0.3s ease;
}

.btn-refresh:hover {
  background: var(--pastel-blue-light);
  transform: rotate(180deg);
}

.btn-export {
  background: linear-gradient(135deg, #4A90E2 0%, #357ABD 100%);
  border: none;
  color: #FFFFFF;
  font-weight: 600;
  padding: 0.875rem 1.5rem;
  border-radius: 12px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(74, 144, 226, 0.4);
}

.btn-export:hover {
  background: linear-gradient(135deg, #357ABD 0%, #2868A8 100%);
  transform: translateY(-3px);
  box-shadow: 0 6px 25px rgba(74, 144, 226, 0.5);
}

/* ========================================
   CARTES DE STATISTIQUES
   ======================================== */
.stat-card {
  background: white;
  border-radius: var(--border-radius);
  padding: 1.5rem;
  box-shadow: var(--shadow-md);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
  border: 1px solid var(--gray-200);
  height: 100%;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-xl);
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
}

.stat-pending::before {
  background: var(--warning-color);
}

.stat-accepted::before {
  background: var(--success-color);
}

.stat-refused::before {
  background: var(--danger-color);
}

.stat-cancelled::before {
  background: var(--pastel-secondary);
}

.stat-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1rem;
  font-size: 1.5rem;
}

.stat-pending .stat-icon {
  background: rgba(245, 158, 11, 0.1);
  color: var(--warning-color);
}

.stat-accepted .stat-icon {
  background: rgba(16, 185, 129, 0.1);
  color: var(--success-color);
}

.stat-refused .stat-icon {
  background: rgba(239, 68, 68, 0.1);
  color: var(--danger-color);
}

.stat-cancelled .stat-icon {
  background: rgba(212, 212, 212, 0.1);
  color: var(--pastel-secondary);
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  color: var(--gray-900);
  margin-bottom: 0.25rem;
}

.stat-label {
  font-size: 0.875rem;
  color: var(--gray-600);
  margin: 0;
  font-weight: 500;
}

/* ========================================
   FILTRES - Même style que sessions
   ======================================== */
.filters-card {
  border: none;
  border-radius: 16px;
  background: #FFFFFF;
  box-shadow: 0 2px 15px rgba(74, 144, 226, 0.1);
  margin-bottom: 2rem;
}

.filters-card .card-body {
  padding: 1.5rem;
}

.search-box {
  position: relative;
}

.search-icon {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #4A90E2;
  font-size: 1.1rem;
  z-index: 10;
  pointer-events: none;
}

.form-control-search {
  padding: 0.875rem 1rem 0.875rem 3rem;
  border: 2px solid #D4EBF2;
  border-radius: 12px;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  background: #F8FBFD;
  color: #2C3E50;
  width: 100%;
}

.form-control-search:focus {
  border-color: #4A90E2;
  background: #FFFFFF;
  box-shadow: 0 0 0 4px rgba(74, 144, 226, 0.1);
  outline: none;
}

.form-select-custom {
  padding: 0.875rem 1rem;
  border: 2px solid #D4EBF2;
  border-radius: 12px;
  font-size: 0.95rem;
  font-weight: 500;
  transition: all 0.3s ease;
  background: #F8FBFD;
  color: #2C3E50;
  cursor: pointer;
  width: 100%;
}

.form-select-custom:focus {
  border-color: #4A90E2;
  background: #FFFFFF;
  box-shadow: 0 0 0 4px rgba(74, 144, 226, 0.1);
  outline: none;
}

.form-label-custom {
  font-weight: 600;
  color: var(--gray-700);
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

.date-range-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.form-control-custom {
  padding: 0.875rem 1rem;
  border: 2px solid #D4EBF2;
  border-radius: 12px;
  font-size: 0.95rem;
  font-weight: 500;
  transition: all 0.3s ease;
  background: #F8FBFD;
  color: #2C3E50;
  flex: 1;
}

.form-control-custom:focus {
  border-color: #4A90E2;
  background: #FFFFFF;
  box-shadow: 0 0 0 4px rgba(74, 144, 226, 0.1);
  outline: none;
}

.date-separator {
  color: #4A90E2;
  font-weight: 600;
  font-size: 0.875rem;
}

.btn-filter-toggle {
  background: transparent;
  border: 2px solid #4A90E2;
  color: #4A90E2;
  font-weight: 600;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-size: 0.875rem;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
}

.btn-filter-toggle:hover {
  background: #4A90E2;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(74, 144, 226, 0.2);
}

/* Animation slide-fade */
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.3s ease;
}

.slide-fade-enter-from {
  transform: translateY(-10px);
  opacity: 0;
}

.slide-fade-leave-to {
  transform: translateY(-10px);
  opacity: 0;
}

/* ========================================
   TABLEAU DES INSCRIPTIONS
   ======================================== */
.inscriptions-table-card {
  background: white;
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-md);
  overflow: hidden;
  border: 1px solid var(--gray-200);
}

.loading-state,
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
}

.empty-icon {
  width: 100px;
  height: 100px;
  margin: 0 auto 1.5rem;
  background: linear-gradient(135deg, var(--gray-100) 0%, var(--gray-200) 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.empty-icon i {
  font-size: 3rem;
  color: var(--gray-400);
}

.empty-title {
  color: var(--gray-700);
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.empty-text {
  color: var(--gray-500);
}

/* Tableau personnalisé */
.table-custom {
  margin: 0;
}

.table-custom thead {
  background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
}

.table-custom th {
  padding: 1rem 1.5rem;
  font-weight: 700;
  color: var(--gray-700);
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.05em;
  border-bottom: 2px solid var(--gray-200);
}

.table-custom td {
  padding: 1.25rem 1.5rem;
  vertical-align: middle;
  border-bottom: 1px solid var(--gray-100);
}

.inscription-row {
  transition: all 0.2s ease;
}

.inscription-row:hover {
  background: var(--gray-50);
}

/* Info employé */
.employe-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.employe-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--pastel-info-light) 0%, var(--pastel-info) 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  flex-shrink: 0;
}

.employe-name {
  font-weight: 600;
  color: var(--gray-900);
  margin-bottom: 0.25rem;
  font-size: 0.95rem;
}

.employe-email {
  font-size: 0.8rem;
  color: var(--gray-600);
  display: block;
  margin-bottom: 0.25rem;
}

.inscription-id {
  font-size: 0.75rem;
  color: var(--gray-500);
  background: var(--gray-100);
  padding: 0.125rem 0.5rem;
  border-radius: 4px;
}

/* Info formation */
.formation-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.formation-title {
  font-weight: 600;
  color: var(--gray-900);
  margin: 0;
  font-size: 0.95rem;
}

.session-location {
  font-size: 0.8rem;
  color: var(--gray-600);
  display: flex;
  align-items: center;
}

/* Info dates */
.dates-info {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.date-item {
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

.date-label {
  font-size: 0.7rem;
  color: var(--gray-500);
  text-transform: uppercase;
  font-weight: 600;
  letter-spacing: 0.05em;
}

.date-value {
  font-size: 0.875rem;
  color: var(--gray-900);
  font-weight: 500;
}

/* Formateur */
.formateur-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.formateur-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--pastel-success-light) 0%, var(--pastel-success) 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  flex-shrink: 0;
}

.formateur-name {
  font-weight: 600;
  color: var(--gray-900);
  font-size: 0.875rem;
}

.formateur-empty {
  color: var(--gray-500);
  font-style: italic;
  font-size: 0.875rem;
}

/* Date inscription */
.inscription-date {
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

/* Badge status */
.badge-status {
  padding: 0.5rem 1rem;
  border-radius: var(--border-radius-sm);
  font-weight: 600;
  font-size: 0.8125rem;
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
}

.status-pending {
  background: rgba(245, 158, 11, 0.1);
  color: #d97706;
  border: 1px solid rgba(245, 158, 11, 0.3);
}

.status-accepted {
  background: rgba(16, 185, 129, 0.1);
  color: #059669;
  border: 1px solid rgba(16, 185, 129, 0.3);
}

.status-refused {
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.status-cancelled {
  background: var(--gray-100);
  color: var(--gray-600);
  border: 1px solid var(--gray-300);
}

.refusal-reason {
  margin-top: 0.5rem;
  font-size: 0.75rem;
  color: var(--danger-color);
  font-style: italic;
  display: flex;
  align-items: flex-start;
}

/* Boutons d'action */
.action-buttons {
  display: flex;
  gap: 0.375rem;
  justify-content: center;
}

.btn-action {
  width: 36px;
  height: 36px;
  padding: 0;
  border-radius: var(--border-radius-sm);
  border: 1px solid;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  font-size: 0.875rem;
}

.btn-view {
  background: rgba(59, 130, 246, 0.05);
  border-color: rgba(59, 130, 246, 0.3);
  color: var(--info-color);
}

.btn-view:hover {
  background: var(--info-color);
  color: white;
  transform: translateY(-2px);
  box-shadow: var(--shadow-sm);
}

.btn-accept {
  background: rgba(16, 185, 129, 0.05);
  border-color: rgba(16, 185, 129, 0.3);
  color: var(--success-color);
}

.btn-accept:hover:not(:disabled) {
  background: var(--success-color);
  color: white;
  transform: translateY(-2px);
  box-shadow: var(--shadow-sm);
}

.btn-refuse {
  background: rgba(239, 68, 68, 0.05);
  border-color: rgba(239, 68, 68, 0.3);
  color: var(--danger-color);
}

.btn-refuse:hover:not(:disabled) {
  background: var(--danger-color);
  color: white;
  transform: translateY(-2px);
  box-shadow: var(--shadow-sm);
}

.btn-delete {
  background: rgba(239, 68, 68, 0.05);
  border-color: rgba(239, 68, 68, 0.3);
  color: var(--danger-color);
}

.btn-delete:hover:not(:disabled) {
  background: var(--danger-color);
  color: white;
  transform: translateY(-2px);
  box-shadow: var(--shadow-sm);
}

.btn-action:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

/* ========================================
   PAGINATION - Même style que sessions
   ======================================== */
.pagination-footer {
  background: var(--gray-50);
  padding: 1.25rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-top: 2px solid var(--gray-200);
}

.pagination-info {
  color: var(--gray-600);
  font-size: 0.875rem;
}

.pagination-custom .page-link {
  border: 1px solid var(--gray-300);
  color: var(--gray-700);
  padding: 0.5rem 0.75rem;
  margin: 0 0.125rem;
  border-radius: var(--border-radius-sm);
  font-weight: 500;
  transition: all 0.2s ease;
}

.pagination-custom .page-link:hover {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
  transform: translateY(-1px);
}

.pagination-custom .page-item.active .page-link {
  background: var(--primary-color);
  border-color: var(--primary-color);
  color: white;
  font-weight: 700;
}

.pagination-custom .page-item.disabled .page-link {
  opacity: 0.4;
  cursor: not-allowed;
}

/* ========================================
   MODALS PASTEL MODERNES
   ======================================== */
.modal-overlay {
  background: rgba(44, 62, 80, 0.6) !important;
  backdrop-filter: blur(8px);
  animation: fadeIn 0.3s ease;
}

.modal-centered {
  margin: 2rem auto;
  max-width: 900px;
}

.modal-modern {
  border: none;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(44, 62, 80, 0.3);
  overflow: hidden;
  animation: slideUp 0.4s ease;
}

.modal-header-modern {
  background: linear-gradient(135deg, #4A90E2 0%, #357ABD 100%);
  border: none;
  padding: 2rem 2.5rem;
  position: relative;
}

.modal-title-modern {
  color: #FFFFFF;
  font-weight: 700;
  font-size: 1.5rem;
  display: flex;
  align-items: center;
  margin: 0;
}

.modal-icon {
  width: 50px;
  height: 50px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 1rem;
  backdrop-filter: blur(10px);
}

.modal-icon i {
  font-size: 1.5rem;
  color: #FFFFFF;
}

.btn-close-modern {
  position: absolute;
  right: 1.5rem;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(255, 255, 255, 0.2);
  border: 2px solid rgba(255, 255, 255, 0.3);
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  color: #FFFFFF;
  font-size: 1.2rem;
}

.btn-close-modern:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-50%) rotate(90deg);
}

.modal-body-modern {
  padding: 2.5rem;
  background: #FAFBFC;
  max-height: calc(100vh - 250px);
  overflow-y: auto;
}

/* ========================================
   CARTES D'INFORMATION POUR INSCRIPTIONS
   ======================================== */
.inscription-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.inscription-info-card {
  background: #FFFFFF;
  border: 2px solid #E5F2F8;
  border-radius: 12px;
  padding: 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: all 0.3s ease;
  height: 100%;
}

.inscription-info-card:hover {
  border-color: #D4EBF2;
  box-shadow: 0 4px 12px rgba(74, 144, 226, 0.1);
  transform: translateY(-2px);
}

.inscription-info-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.inscription-info-content {
  flex: 1;
}

.inscription-info-label {
  color: var(--pastel-text-muted);
  font-size: 0.85rem;
  font-weight: 500;
  margin-bottom: 0.25rem;
}

.inscription-info-value {
  color: var(--pastel-text-primary);
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0;
  line-height: 1.4;
}

/* Classes de fond pastel */
.bg-primary-light-pastel {
  background: linear-gradient(135deg, var(--pastel-blue-light) 0%, var(--pastel-blue-bg) 100%);
}

.bg-warning-light-pastel {
  background: linear-gradient(135deg, var(--pastel-warning-light) 0%, #FFFFFF 100%);
}

.bg-info-light-pastel {
  background: linear-gradient(135deg, var(--pastel-info-light) 0%, #FFFFFF 100%);
}

.bg-success-light-pastel {
  background: linear-gradient(135deg, var(--pastel-success-light) 0%, #FFFFFF 100%);
}

/* ========================================
   ÉLÉMENTS SPÉCIFIQUES AUX MODALS
   ======================================== */
.detail-header {
  background: linear-gradient(135deg, var(--pastel-blue-bg) 0%, #FFFFFF 100%);
  padding: 1.5rem;
  border-radius: 12px;
  border: 2px solid var(--pastel-blue-light);
  margin-bottom: 2rem;
}

.name-text-large {
  color: var(--pastel-text-primary);
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.section-subtitle {
  color: var(--pastel-text-primary);
  font-weight: 600;
  font-size: 1rem;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid var(--pastel-blue-light);
}

.description-box {
  background: #F8FBFD;
  border: 2px solid #E5F2F8;
  border-left: 5px solid #4A90E2;
  border-radius: 12px;
  padding: 1.25rem 1.5rem;
  color: var(--pastel-text-primary);
  line-height: 1.7;
  font-size: 0.95rem;
  margin: 1.5rem 0;
}

/* Badge de statut amélioré */
.badge-status-large {
  display: inline-flex;
  align-items: center;
  padding: 0.75rem 1.5rem;
  border-radius: 25px;
  font-weight: 700;
  font-size: 1rem;
  margin-right: 1rem;
}

/* Alerte pastel */
.alert-danger-pastel {
  background: linear-gradient(135deg, var(--pastel-danger-light) 0%, #FFFFFF 100%);
  border: 2px solid var(--pastel-danger);
  border-left: 5px solid var(--pastel-danger);
  color: #E6757E;
  border-radius: 12px;
  padding: 1rem 1.25rem;
  margin: 1rem 0;
}

.alert-warning-pastel {
  background: linear-gradient(135deg, var(--pastel-warning-light) 0%, #FFFFFF 100%);
  border: 2px solid var(--pastel-warning);
  border-left: 5px solid #F4B860;
  color: #F4B860;
  border-radius: 12px;
  padding: 1rem 1.25rem;
  margin: 1rem 0;
}

/* Pied de page modal */
.modal-footer-pastel {
  background: linear-gradient(180deg, #FFFFFF 0%, #F8FBFD 100%);
  border-top: 2px solid #E5F2F8;
  padding: 1.5rem 2rem;
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
}

/* Boutons pastel */
.btn-outline-secondary-pastel {
  border: 2px solid var(--pastel-secondary);
  color: #9B9B9B;
  background: transparent;
  transition: all 0.3s ease;
  border-radius: 8px;
  padding: 0.625rem 1.5rem;
  font-weight: 600;
  font-size: 0.95rem;
}

.btn-outline-secondary-pastel:hover {
  background: var(--pastel-secondary-light);
  border-color: #BEBEBE;
  color: #7D7D7D;
  transform: translateY(-2px);
}

.btn-outline-success-pastel {
  border: 2px solid var(--pastel-success);
  color: #6BB896;
  background: transparent;
  transition: all 0.3s ease;
  border-radius: 8px;
  padding: 0.625rem 1.5rem;
  font-weight: 600;
  font-size: 0.95rem;
}

.btn-outline-success-pastel:hover {
  background: var(--pastel-success-light);
  border-color: #6BB896;
  color: #5FA583;
  transform: translateY(-2px);
}

.btn-outline-danger-pastel {
  border: 2px solid var(--pastel-danger);
  color: #FF8B94;
  background: transparent;
  transition: all 0.3s ease;
  border-radius: 8px;
  padding: 0.625rem 1.5rem;
  font-weight: 600;
  font-size: 0.95rem;
}

.btn-outline-danger-pastel:hover {
  background: var(--pastel-danger-light);
  border-color: #FF8B94;
  color: #E6757E;
  transform: translateY(-2px);
}

/* Spinner pastel */
.spinner-pastel {
  border-color: var(--pastel-blue-light);
  border-right-color: var(--pastel-blue-primary);
  width: 3rem;
  height: 3rem;
}

/* Couleurs pastel pour texte */
.text-primary-pastel {
  color: var(--pastel-blue-dark) !important;
}

.text-success-pastel {
  color: #6BB896 !important;
}

.text-warning-pastel {
  color: #F4B860 !important;
}

.text-danger-pastel {
  color: #E6757E !important;
}

.text-info-pastel {
  color: #7BA7D9 !important;
}

.text-muted-pastel {
  color: var(--pastel-text-muted) !important;
}

/* ========================================
   FORMULAIRES MODERNES
   ======================================== */
.form-label-modern {
  color: var(--pastel-text-primary);
  font-weight: 600;
  font-size: 0.95rem;
  margin-bottom: 0.625rem;
  display: flex;
  align-items: center;
}

.form-label-modern i {
  color: #4A90E2;
  font-size: 1rem;
}

.required {
  color: #E74C3C;
  font-weight: 700;
  margin-left: 4px;
}

.form-control-modern {
  border: 2px solid #E5F2F8;
  border-radius: 12px;
  padding: 0.875rem 1.125rem;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  background: #F8FBFD;
  color: var(--pastel-text-primary);
  font-weight: 500;
}

.form-control-modern:focus {
  border-color: #4A90E2;
  background: #FFFFFF;
  box-shadow: 0 0 0 4px rgba(74, 144, 226, 0.12);
  outline: none;
}

.form-control-modern.is-invalid {
  border-color: var(--pastel-danger);
}

.invalid-feedback {
  color: #E6757E;
  font-size: 0.85rem;
  margin-top: 0.25rem;
}

.form-hint {
  color: var(--pastel-text-muted);
  font-size: 0.85rem;
  margin-top: 0.5rem;
  display: block;
  font-style: italic;
}

/* ========================================
   PRÉVISUALISATION INSCRIPTION
   ======================================== */
.inscription-preview {
  background: linear-gradient(135deg, var(--pastel-blue-bg) 0%, #FFFFFF 100%);
  border: 2px solid var(--pastel-blue-light);
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
}

.preview-item {
  margin-bottom: 1rem;
}

.preview-label {
  color: var(--pastel-text-muted);
  font-size: 0.85rem;
  font-weight: 500;
  margin-bottom: 0.25rem;
}

.preview-value {
  color: var(--pastel-text-primary);
  font-size: 1.1rem;
  font-weight: 600;
  margin: 0;
}

/* ========================================
   ÉTAT SESSION
   ======================================== */
.session-status-card {
  background: #FFFFFF;
  border: 2px solid #E5F2F8;
  border-radius: 12px;
  padding: 1.5rem;
}

.status-item {
  display: flex;
  align-items: center;
  margin-bottom: 1rem;
}

.status-label {
  color: var(--pastel-text-muted);
  font-size: 0.9rem;
  font-weight: 500;
  min-width: 150px;
}

/* ========================================
   RESPONSIVE
   ======================================== */
@media (max-width: 768px) {
  .page-header {
    padding: 1.5rem 0;
  }
  
  .header-content {
    flex-direction: column;
    text-align: center;
  }
  
  .page-title {
    font-size: 1.5rem;
  }
  
  .stat-card {
    margin-bottom: 1rem;
  }
  
  .action-buttons {
    flex-wrap: wrap;
  }
  
  .btn-action {
    width: 32px;
    height: 32px;
    font-size: 0.75rem;
  }
  
  .table-custom {
    font-size: 0.875rem;
  }
  
  .table-custom th,
  .table-custom td {
    padding: 0.75rem 0.5rem;
  }
  
  .pagination-footer {
    flex-direction: column;
    gap: 1rem;
  }
  
  .inscription-grid {
    grid-template-columns: 1fr;
  }
  
  .modal-footer-pastel {
    padding: 1.25rem 1.5rem;
    flex-wrap: wrap;
  }
  
  .modal-footer-pastel .btn {
    flex: 1;
    min-width: 120px;
    margin-bottom: 0.5rem;
  }
  
  .modal-centered {
    margin: 1rem;
    max-width: calc(100% - 2rem);
  }
  
  .modal-header-modern {
    padding: 1.5rem 1.25rem;
  }
  
  .modal-title-modern {
    font-size: 1.25rem;
  }
  
  .modal-icon {
    width: 45px;
    height: 45px;
  }
  
  .btn-close-modern {
    width: 36px;
    height: 36px;
    right: 1.25rem;
  }
  
  .modal-body-modern {
    padding: 1.5rem 1.25rem;
  }
}

@media (max-width: 576px) {
  .icon-wrapper {
    width: 50px;
    height: 50px;
  }
  
  .icon-wrapper i {
    font-size: 1.5rem;
  }
  
  .date-range-group {
    flex-direction: column;
  }
  
  .date-separator {
    display: none;
  }
  
  .employe-info {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .formateur-info {
    flex-direction: column;
    align-items: flex-start;
  }
}

/* ========================================
   ANIMATIONS
   ======================================== */
@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes slideUp {
  from {
    transform: translateY(30px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

/* ========================================
   SCROLLBAR PERSONNALISÉE
   ======================================== */
.modal-body-modern::-webkit-scrollbar {
  width: 8px;
}

.modal-body-modern::-webkit-scrollbar-track {
  background: var(--pastel-blue-bg);
  border-radius: 10px;
}

.modal-body-modern::-webkit-scrollbar-thumb {
  background: var(--pastel-blue-primary);
  border-radius: 10px;
}

.modal-body-modern::-webkit-scrollbar-thumb:hover {
  background: var(--pastel-blue-dark);
}
</style>