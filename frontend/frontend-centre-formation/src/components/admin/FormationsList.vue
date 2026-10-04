<template>
  <div class="formations-list">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="mb-1 page-title">
          <i class="bi bi-book-fill text-primary-pastel me-2"></i>
          Gestion des Formations
        </h2>
        <p class="text-muted-pastel mb-0">
          {{ formations.length }} formation(s) trouvée(s)
        </p>
      </div>
      <button @click="openAddModal" class="btn btn-add-new">
        <i class="bi bi-plus-circle-fill me-2"></i>Nouvelle Formation
      </button>
    </div>
    
    <!-- Filtres et recherche -->
    <div class="card card-filters mb-4">
      <div class="card-body p-4">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="filter-label mb-2">
              <i class="bi bi-search me-1"></i> Rechercher
            </label>
            <div class="search-wrapper">
              <span class="search-icon">
                <i class="bi bi-search"></i>
              </span>
              <input 
                type="text" 
                class="form-control search-input" 
                placeholder="Rechercher par titre ou description..."
                v-model="search"
                @input="handleSearch"
              />
            </div>
          </div>
          <div class="col-md-3">
            <label class="filter-label mb-2">
              <i class="bi bi-funnel me-1"></i> Statut
            </label>
            <select class="form-select select-items" v-model="filterStatus" @change="loadFormations">
              <option value="">Tous les statuts</option>
              <option value="actif">Actives seulement</option>
              <option value="inactif">Inactives seulement</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="filter-label mb-2">
              <i class="bi bi-list-ul me-1"></i> Affichage
            </label>
            <select class="form-select select-items" v-model="itemsPerPage" @change="loadFormations">
              <option value="10">10 par page</option>
              <option value="25">25 par page</option>
              <option value="50">50 par page</option>
            </select>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Liste des formations -->
    <div class="card card-pastel shadow-pastel">
      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary-pastel spinner-pastel" role="status">
            <span class="visually-hidden">Chargement...</span>
          </div>
          <p class="mt-3 text-muted-pastel">Chargement des formations...</p>
        </div>
        
        <div v-else-if="formations.length === 0" class="text-center py-5 empty-state">
          <i class="bi bi-book fs-1 text-muted-pastel mb-3"></i>
          <h5 class="text-muted-pastel">Aucune formation trouvée</h5>
          <p class="text-muted-pastel">Cliquez sur "Nouvelle Formation" pour en ajouter une.</p>
        </div>
        
        <div v-else class="table-responsive">
          <table class="table table-hover table-pastel mb-0">
            <thead class="table-light-pastel">
              <tr>
                <th style="width: 25%">Titre</th>
                <th style="width: 30%">Description</th>
                <th style="width: 15%">Informations</th>
                <th style="width: 10%">Statut</th>
                <th style="width: 20%" class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="formation in formations" :key="formation.id" class="table-row-pastel">
                <td>
                  <div>
                    <h6 class="mb-1 formation-title">{{ formation.titre }}</h6>
                    <small class="text-muted-pastel">
                      <i class="bi bi-hash me-1"></i>ID: {{ formation.id }}
                    </small>
                  </div>
                </td>
                <td>
                  <div class="formation-description">
                    {{ truncateText(formation.description, 100) }}
                  </div>
                  <div v-if="formation.competences_visees" class="mt-2">
                    <small class="competences-badge">
                      <i class="bi bi-check-circle-fill me-1"></i>
                      {{ truncateText(formation.competences_visees, 80) }}
                    </small>
                  </div>
                </td>
                <td>
                  <div class="info-container">
                    <div class="info-item">
                      <i class="bi bi-clock-fill text-primary-pastel"></i>
                      <span>{{ formation.duree_heures }}h</span>
                    </div>
                    <div class="info-item">
                      <i class="bi bi-currency-euro text-success-pastel"></i>
                      <span>{{ formatPrice(formation.prix) }}</span>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="badge-status" :class="getStatusClass(formation.est_actif)">
                    <i :class="formation.est_actif ? 'bi bi-check-circle-fill' : 'bi bi-x-circle-fill'" class="me-1"></i>
                    {{ formation.est_actif ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="text-center">
                  <div class="btn-group btn-group-sm btn-group-pastel">
                    <button @click="viewDetails(formation)" 
                            class="btn btn-outline-primary-pastel" title="Voir détails">
                      <i class="bi bi-eye"></i>
                    </button>
                    <button @click="editFormation(formation)" 
                            class="btn btn-outline-success-pastel" title="Modifier">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button @click="confirmDelete(formation)" 
                            class="btn btn-outline-danger-pastel" title="Supprimer">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      
      <!-- Pagination -->
      <div v-if="formations.length > 0" class="card-footer pagination-footer">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
          <div class="pagination-info">
            <i class="bi bi-info-circle me-2"></i>
            Affichage de <strong>{{ startItem }}</strong> à <strong>{{ endItem }}</strong> sur <strong>{{ totalItems }}</strong> formations
          </div>
          <nav>
            <ul class="pagination pagination-modern mb-0">
              <li class="page-item" :class="{disabled: currentPage === 1}">
                <button class="page-link page-link-modern" @click="changePage(currentPage - 1)">
                  <i class="bi bi-chevron-left"></i>
                  <span class="d-none d-sm-inline ms-1">Précédent</span>
                </button>
              </li>
              
              <li v-for="page in displayedPages" :key="page" 
                  class="page-item" :class="{active: page === currentPage}">
                <button class="page-link page-link-modern" @click="changePage(page)">
                  {{ page }}
                </button>
              </li>
              
              <li class="page-item" :class="{disabled: currentPage === totalPages}">
                <button class="page-link page-link-modern" @click="changePage(currentPage + 1)">
                  <span class="d-none d-sm-inline me-1">Suivant</span>
                  <i class="bi bi-chevron-right"></i>
                </button>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
    
    <!-- Modal Ajout/Modification -->
    <div v-if="showModal" class="modal fade show d-block modal-overlay" 
         @click.self="closeModal">
      <div class="modal-dialog modal-lg modal-centered">
        <div class="modal-content modal-modern">
          <div class="modal-header modal-header-modern">
            <h5 class="modal-title-modern">
              <div class="modal-icon">
                <i class="bi bi-book-half"></i>
              </div>
              <span>{{ isEditing ? 'Modifier la formation' : 'Nouvelle Formation' }}</span>
            </h5>
            <button type="button" class="btn-close-modern" @click="closeModal">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
          <div class="modal-body modal-body-modern">
            <form @submit.prevent="saveFormation">
              <!-- Section Informations Générales -->
              <div class="form-section">
                <h6 class="form-section-title">
                  <i class="bi bi-info-circle-fill me-2"></i>
                  Informations Générales
                </h6>
                <div class="form-group-modern mb-4">
                  <label class="form-label-modern">
                    <i class="bi bi-type me-1"></i> Titre de la formation <span class="required">*</span>
                  </label>
                  <input type="text" class="form-control-modern" v-model="formData.titre" 
                         required :disabled="saving" maxlength="200" 
                         placeholder="Ex: Formation avancée en développement web">
                </div>
                
                <div class="form-group-modern">
                  <label class="form-label-modern">
                    <i class="bi bi-text-paragraph me-1"></i> Description <span class="required">*</span>
                  </label>
                  <textarea class="form-control-modern textarea-modern" v-model="formData.description" 
                            rows="5" required :disabled="saving"
                            placeholder="Décrivez les objectifs et le contenu de cette formation..."></textarea>
                  <small class="form-hint">Décrivez clairement les objectifs et le contenu de la formation</small>
                </div>
              </div>

              <!-- Section Durée et Tarification -->
              <div class="form-section">
                <h6 class="form-section-title">
                  <i class="bi bi-clock-history me-2"></i>
                  Durée et Tarification
                </h6>
                <div class="row g-4">
                  <div class="col-md-6">
                    <div class="form-group-modern">
                      <label class="form-label-modern">
                        <i class="bi bi-hourglass-split me-1"></i> Durée (heures) <span class="required">*</span>
                      </label>
                      <input type="number" class="form-control-modern" v-model="formData.duree_heures" 
                             required :disabled="saving" min="1" max="500"
                             placeholder="Ex: 40">
                      <small class="form-hint">Durée totale de la formation en heures</small>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <div class="form-group-modern">
                      <label class="form-label-modern">
                        <i class="bi bi-currency-euro me-1"></i> Prix (€)
                      </label>
                      <input type="number" class="form-control-modern" v-model="formData.prix" 
                             :disabled="saving" min="0" step="0.01"
                             placeholder="0.00">
                      <small class="form-hint">Laisser à 0 pour une formation gratuite</small>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Section Compétences -->
              <div class="form-section">
                <h6 class="form-section-title">
                  <i class="bi bi-award-fill me-2"></i>
                  Compétences et Objectifs
                </h6>
                <div class="form-group-modern">
                  <label class="form-label-modern">
                    <i class="bi bi-lightbulb me-1"></i> Compétences visées
                  </label>
                  <textarea class="form-control-modern textarea-modern" v-model="formData.competences_visees" 
                            rows="4" :disabled="saving" 
                            placeholder="Exemple: Maîtriser JavaScript ES6+, Développer des applications React, Comprendre les API REST..."></textarea>
                  <small class="form-hint">Listez les compétences que les participants vont acquérir</small>
                </div>
              </div>
              
              <!-- Section Statut (pour modification uniquement) -->
              <div class="form-section form-section-status" v-if="isEditing">
                <h6 class="form-section-title">
                  <i class="bi bi-toggles me-2"></i>
                  Statut de la Formation
                </h6>
                <div class="form-check-modern">
                  <input class="form-check-input-modern" type="checkbox" role="switch" 
                         id="est_actif" v-model="formData.est_actif" :disabled="saving">
                  <label class="form-check-label-modern" for="est_actif">
                    <div class="d-flex align-items-center">
                      <i :class="formData.est_actif ? 'bi bi-check-circle-fill text-success-pastel' : 'bi bi-x-circle-fill text-danger-pastel'" class="me-2 fs-5"></i>
                      <div>
                        <div class="fw-semibold">{{ formData.est_actif ? 'Formation Active' : 'Formation Inactive' }}</div>
                        <small class="text-muted-pastel">
                          {{ formData.est_actif ? 'Visible et accessible aux utilisateurs' : 'Cachée des listes publiques' }}
                        </small>
                      </div>
                    </div>
                  </label>
                </div>
              </div>
              
              <div class="modal-footer-modern">
                <button type="button" class="btn btn-cancel-modern" 
                        @click="closeModal" :disabled="saving">
                  <i class="bi bi-x-circle me-1"></i>
                  Annuler
                </button>
                <button type="submit" class="btn btn-submit-modern" :disabled="saving">
                  <span v-if="saving">
                    <span class="spinner-border spinner-border-sm me-2"></span>
                    {{ isEditing ? 'Modification...' : 'Création...' }}
                  </span>
                  <span v-else>
                    <i :class="isEditing ? 'bi bi-check-circle-fill' : 'bi bi-plus-circle-fill'" class="me-1"></i>
                    {{ isEditing ? 'Enregistrer' : 'Créer la formation' }}
                  </span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Modal Détails de la Formation -->
    <div v-if="showDetailModal" class="modal fade show d-block modal-overlay" 
         @click.self="closeDetailModal">
      <div class="modal-dialog modal-lg modal-centered">
        <div class="modal-content modal-modern">
          <div class="modal-header modal-header-modern">
            <h5 class="modal-title-modern">
              <div class="modal-icon">
                <i class="bi bi-book-half"></i>
              </div>
              <span>Détails de la Formation</span>
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
          <div v-else-if="formationDetails" class="modal-body modal-body-modern">
            <!-- En-tête avec titre et statut -->
            <div class="mb-4 detail-header">
              <h4 class="name-text-large mb-3">{{ formationDetails.titre }}</h4>
              <div class="d-flex align-items-center gap-3 flex-wrap">
                <span class="badge-status-large" :class="getStatusClass(formationDetails.est_actif)">
                  <i :class="formationDetails.est_actif ? 'bi bi-check-circle-fill' : 'bi bi-x-circle-fill'" class="me-1"></i>
                  {{ formationDetails.est_actif ? 'Active' : 'Inactive' }}
                </span>
                <small class="text-muted-pastel">
                  <i class="bi bi-hash me-1"></i>ID: {{ formationDetails.id }}
                </small>
              </div>
            </div>
            
            <!-- Description -->
            <div class="mb-4">
              <h6 class="section-subtitle">
                <i class="bi bi-text-paragraph me-2"></i>
                Description
              </h6>
              <div class="description-box">
                {{ formationDetails.description }}
              </div>
            </div>
            
            <!-- Informations détaillées -->
            <div class="row mb-4">
              <div class="col-md-6 mb-3">
                <div class="info-card">
                  <div class="info-card-icon bg-primary-light-pastel">
                    <i class="bi bi-clock-fill text-primary-pastel"></i>
                  </div>
                  <div class="info-card-content">
                    <label class="info-card-label">Durée</label>
                    <p class="info-card-value">{{ formationDetails.duree_heures }} heures</p>
                  </div>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <div class="info-card">
                  <div class="info-card-icon bg-success-light-pastel">
                    <i class="bi bi-currency-euro text-success-pastel"></i>
                  </div>
                  <div class="info-card-content">
                    <label class="info-card-label">Prix</label>
                    <p class="info-card-value">{{ formatPrice(formationDetails.prix) }}</p>
                  </div>
                </div>
              </div>
              
              <div v-if="formationDetails.competences_visees" class="col-12 mb-3">
                <h6 class="section-subtitle">
                  <i class="bi bi-award-fill me-2"></i>
                  Compétences visées
                </h6>
                <div class="competences-box">
                  <i class="bi bi-lightbulb-fill text-warning-pastel me-2"></i>
                  {{ formationDetails.competences_visees }}
                </div>
              </div>
              
              <!-- Statistiques (pour admin) -->
              <div v-if="formationDetails.statistiques" class="col-12">
                <div class="card card-stats-pastel">
                  <div class="card-header card-header-stats-pastel">
                    <h6 class="mb-0">
                      <i class="bi bi-bar-chart-fill me-2"></i>
                      Statistiques
                    </h6>
                  </div>
                  <div class="card-body">
                    <div class="row text-center">
                      <div class="col-4 mb-3">
                        <div class="display-6 fw-bold text-primary-pastel">
                          {{ formationDetails.statistiques.total_sessions || 0 }}
                        </div>
                        <small class="text-muted-pastel">Sessions totales</small>
                      </div>
                      <div class="col-4 mb-3">
                        <div class="display-6 fw-bold text-success-pastel">
                          {{ formationDetails.statistiques.total_places || 0 }}
                        </div>
                        <small class="text-muted-pastel">Places totales</small>
                      </div>
                      <div class="col-4 mb-3">
                        <div class="display-6 fw-bold text-warning-pastel">
                          {{ formationDetails.statistiques.places_restantes || 0 }}
                        </div>
                        <small class="text-muted-pastel">Places restantes</small>
                      </div>
                    </div>
                  </div>
                </div>
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
            <button v-if="formationDetails" type="button" class="btn btn-outline-primary-pastel"
                    @click="editFromDetail">
              <i class="bi bi-pencil me-1"></i>Modifier
            </button>
            <button v-if="formationDetails" type="button" class="btn btn-outline-danger-pastel"
                    @click="deleteFromDetail">
              <i class="bi bi-trash me-1"></i>Supprimer
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
  name: 'FormationsList',
  
  setup() {
    // États principaux
    const loading = ref(true)
    const saving = ref(false)
    const showModal = ref(false)
    const isEditing = ref(false)
    
    const search = ref('')
    const searchTimeout = ref(null)
    const filterStatus = ref('')
    
    const formations = ref([])
    const currentPage = ref(1)
    const itemsPerPage = ref(10)
    const totalItems = ref(0)
    const totalPages = ref(1)
    
    const formData = ref({
      titre: '',
      description: '',
      duree_heures: '',
      competences_visees: '',
      prix: 0,
      est_actif: true
    })
    
    const currentFormationId = ref(null)
    
    // États pour le modal de détails
    const showDetailModal = ref(false)
    const detailLoading = ref(false)
    const detailError = ref(null)
    const formationDetails = ref(null)
    
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
    
    // Méthodes utilitaires
    const truncateText = (text, maxLength) => {
      if (!text) return ''
      if (text.length <= maxLength) return text
      return text.substring(0, maxLength) + '...'
    }
    
    const formatPrice = (price) => {
      if (!price || price === 0) return 'Gratuit'
      return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
      }).format(price)
    }
    
    const getStatusClass = (isActive) => {
      return isActive ? 'badge-status-active' : 'badge-status-inactive'
    }
    
    // Méthodes principales
    const loadFormations = async () => {
      loading.value = true
      try {
        console.log('🔄 Chargement des formations...')
        
        const params = {
          limit: itemsPerPage.value,
          page: currentPage.value
        }
        
        if (search.value.trim()) {
          params.recherche = search.value.trim()
        }
        
        if (filterStatus.value === 'actif') {
          params.est_actif = 1
        } else if (filterStatus.value === 'inactif') {
          params.est_actif = 0
        }
        
        // Note: Vous devez ajouter getFormations à adminService
        const response = await adminService.getFormations(params)
        
        console.log('📚 Réponse API formations:', response)
        
        if (response.success) {
          formations.value = response.data || []
          totalItems.value = response.pagination?.total || response.count || formations.value.length
          totalPages.value = response.pagination?.pages || 
                            Math.ceil(totalItems.value / itemsPerPage.value)
          
          console.log(`✅ ${formations.value.length} formations chargées`)
        } else {
          throw new Error(response.message || 'Erreur lors du chargement')
        }
      } catch (error) {
        console.error('❌ Erreur chargement formations:', error)
        alert(error.message || 'Erreur lors du chargement des formations')
      } finally {
        loading.value = false
      }
    }
    
    const handleSearch = () => {
      clearTimeout(searchTimeout.value)
      searchTimeout.value = setTimeout(() => {
        currentPage.value = 1
        loadFormations()
      }, 500)
    }
    
    const openAddModal = () => {
      isEditing.value = false
      formData.value = {
        titre: '',
        description: '',
        duree_heures: '',
        competences_visees: '',
        prix: 0,
        est_actif: true
      }
      showModal.value = true
    }
    
    const viewDetails = async (formation) => {
      console.log('👁️ Affichage détails pour:', formation)
      
      showDetailModal.value = true
      detailLoading.value = true
      detailError.value = null
      formationDetails.value = null
      
      try {
        // Charger les détails depuis l'API
        const response = await adminService.getFormation(formation.id)
        console.log('📊 Réponse détails formation:', response)
        
        if (response.success && response.data) {
          formationDetails.value = response.data
        } else {
          // Si l'API échoue, afficher les données de base
          formationDetails.value = formation
        }
      } catch (error) {
        console.error('❌ Erreur chargement détails:', error)
        detailError.value = error.message || 'Impossible de charger les détails'
        formationDetails.value = formation
      } finally {
        detailLoading.value = false
      }
    }
    
    const editFormation = (formation) => {
      isEditing.value = true
      currentFormationId.value = formation.id
      formData.value = {
        titre: formation.titre || '',
        description: formation.description || '',
        duree_heures: formation.duree_heures || '',
        competences_visees: formation.competences_visees || '',
        prix: formation.prix || 0,
        est_actif: formation.est_actif ?? true
      }
      showModal.value = true
      
      if (showDetailModal.value) {
        closeDetailModal()
      }
    }
    
    const editFromDetail = () => {
      if (formationDetails.value) {
        editFormation(formationDetails.value)
      }
    }
    
    const deleteFromDetail = () => {
      if (formationDetails.value) {
        confirmDelete(formationDetails.value)
      }
    }
    
    const saveFormation = async () => {
      saving.value = true
      try {
        console.log('💾 Sauvegarde formation:', formData.value)
        
        // Validation
        if (!formData.value.titre.trim()) {
          throw new Error('Le titre est obligatoire')
        }
        if (!formData.value.description.trim()) {
          throw new Error('La description est obligatoire')
        }
        if (!formData.value.duree_heures || formData.value.duree_heures < 1) {
          throw new Error('La durée doit être d\'au moins 1 heure')
        }
        
        if (isEditing.value) {
          // Modification
          await adminService.updateFormation(currentFormationId.value, formData.value)
          alert('✅ Formation modifiée avec succès')
        } else {
          // Création
          await adminService.createFormation(formData.value)
          alert('✅ Formation créée avec succès')
        }
        
        closeModal()
        loadFormations()
        
      } catch (error) {
        console.error('❌ Erreur sauvegarde:', error)
        const errorMessage = error.response?.data?.message || 
                           error.message || 
                           'Erreur lors de la sauvegarde'
        alert(errorMessage)
      } finally {
        saving.value = false
      }
    }
    
    const confirmDelete = (formation) => {
      const message = `Êtes-vous sûr de vouloir supprimer "${formation.titre}" ?\n\n`
        + `ID: ${formation.id}\n`
        + `Durée: ${formation.duree_heures}h\n\n`
        + '⚠️ Cette action est irréversible.'
        
      if (confirm(message)) {
        deleteFormation(formation.id)
      }
    }
    
    const deleteFormation = async (id) => {
      try {
        // Note: Vous devez ajouter deleteFormation à adminService
        await adminService.deleteFormation(id)
        alert('✅ Formation supprimée avec succès')
        loadFormations()
        
        if (showDetailModal.value) {
          closeDetailModal()
        }
      } catch (error) {
        console.error('❌ Erreur suppression:', error)
        const errorMessage = error.response?.data?.message || 
                           error.message || 
                           'Erreur lors de la suppression'
        alert(errorMessage)
      }
    }
    
    const changePage = (page) => {
      if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page
        loadFormations()
      }
    }
    
    const closeModal = () => {
      showModal.value = false
      isEditing.value = false
      formData.value = {
        titre: '',
        description: '',
        duree_heures: '',
        competences_visees: '',
        prix: 0,
        est_actif: true
      }
      currentFormationId.value = null
    }
    
    const closeDetailModal = () => {
      showDetailModal.value = false
      formationDetails.value = null
      detailError.value = null
    }
    
    // Lifecycle
    onMounted(() => {
      loadFormations()
    })
    
    return {
      // États
      loading,
      saving,
      showModal,
      isEditing,
      search,
      filterStatus,
      formations,
      currentPage,
      itemsPerPage,
      totalItems,
      totalPages,
      formData,
      showDetailModal,
      detailLoading,
      detailError,
      formationDetails,
      
      // Computed
      startItem,
      endItem,
      displayedPages,
      
      // Méthodes
      loadFormations,
      handleSearch,
      truncateText,
      formatPrice,
      getStatusClass,
      openAddModal,
      viewDetails,
      editFormation,
      editFromDetail,
      deleteFromDetail,
      saveFormation,
      confirmDelete,
      deleteFormation,
      changePage,
      closeModal,
      closeDetailModal
    }
  }
}
</script>

<style scoped>
/* ========================================
   COULEURS PASTEL - PALETTE PRINCIPALE
   ======================================== */
:root {
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

/* ========================================
   LAYOUT PRINCIPAL
   ======================================== */
.formations-list {
  min-height: calc(100vh - 200px);
  padding: 2rem 1rem;
  background: linear-gradient(135deg, #F0F8FA 0%, #FFFFFF 100%);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* ========================================
   TYPOGRAPHIE
   ======================================== */
.page-title {
  color: var(--pastel-text-primary);
  font-weight: 600;
  letter-spacing: -0.5px;
}

.formation-title {
  color: var(--pastel-text-primary);
  font-weight: 600;
  font-size: 1.05rem;
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
  color: #FF8B94 !important;
}

.text-info-pastel {
  color: #7BA7D9 !important;
}

.text-secondary-pastel {
  color: #9B9B9B !important;
}

.text-muted-pastel {
  color: var(--pastel-text-muted) !important;
}

/* ========================================
   CARDS & CONTAINERS
   ======================================== */
.card-pastel {
  border: none;
  border-radius: 16px;
  background: #FFFFFF;
  transition: all 0.3s ease;
}

.card-pastel:hover {
  transform: translateY(-2px);
}

.shadow-pastel {
  box-shadow: 0 4px 20px rgba(168, 213, 226, 0.15) !important;
}

.card-filters {
  border: none;
  border-radius: 16px;
  background: #FFFFFF;
  box-shadow: 0 2px 15px rgba(74, 144, 226, 0.1);
}

.card-stats-pastel {
  border: 2px solid var(--pastel-blue-light);
  border-radius: 12px;
  background: linear-gradient(135deg, var(--pastel-blue-bg) 0%, #FFFFFF 100%);
}

.card-header-stats-pastel {
  background: var(--pastel-blue-light);
  border-bottom: 2px solid var(--pastel-blue-primary);
  color: var(--pastel-text-primary);
  font-weight: 600;
  border-radius: 10px 10px 0 0 !important;
  padding: 1rem 1.25rem;
}

/* ========================================
   BOUTONS
   ======================================== */
.btn-add-new {
  background: linear-gradient(135deg, #4A90E2 0%, #357ABD 100%);
  border: none;
  color: #FFFFFF;
  font-weight: 600;
  font-size: 1rem;
  padding: 0.875rem 2rem;
  border-radius: 12px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(74, 144, 226, 0.4);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.btn-add-new:hover {
  background: linear-gradient(135deg, #357ABD 0%, #2868A8 100%);
  transform: translateY(-3px);
  box-shadow: 0 6px 25px rgba(74, 144, 226, 0.5);
  color: #FFFFFF;
}

.btn-add-new i {
  font-size: 1.1rem;
}

.btn-outline-primary-pastel {
  border: 2px solid var(--pastel-blue-primary);
  color: var(--pastel-blue-dark);
  background: transparent;
  transition: all 0.3s ease;
  border-radius: 8px;
}

.btn-outline-primary-pastel:hover {
  background: var(--pastel-blue-light);
  border-color: var(--pastel-blue-dark);
  color: var(--pastel-blue-dark);
  transform: scale(1.05);
}

.btn-outline-success-pastel {
  border: 2px solid var(--pastel-success);
  color: #6BB896;
  background: transparent;
  transition: all 0.3s ease;
  border-radius: 8px;
}

.btn-outline-success-pastel:hover {
  background: var(--pastel-success-light);
  border-color: #6BB896;
  color: #5FA583;
  transform: scale(1.05);
}

.btn-outline-danger-pastel {
  border: 2px solid var(--pastel-danger);
  color: #FF8B94;
  background: transparent;
  transition: all 0.3s ease;
  border-radius: 8px;
}

.btn-outline-danger-pastel:hover {
  background: var(--pastel-danger-light);
  border-color: #FF8B94;
  color: #E6757E;
  transform: scale(1.05);
}

.btn-outline-secondary-pastel {
  border: 2px solid var(--pastel-secondary);
  color: #9B9B9B;
  background: transparent;
  transition: all 0.3s ease;
  border-radius: 8px;
}

.btn-outline-secondary-pastel:hover {
  background: var(--pastel-secondary-light);
  border-color: #BEBEBE;
  color: #7D7D7D;
}

.btn-group-pastel .btn {
  margin: 0 2px;
}

/* ========================================
   FILTRES ET RECHERCHE
   ======================================== */
.filter-label {
  color: #2C3E50;
  font-weight: 600;
  font-size: 0.9rem;
  display: block;
}

.search-wrapper {
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
}

.search-input {
  padding: 0.875rem 1rem 0.875rem 3rem;
  border: 2px solid #D4EBF2;
  border-radius: 12px;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  background: #F8FBFD;
  color: #2C3E50;
  width: 100%;
}

.search-input:focus {
  border-color: #4A90E2;
  background: #FFFFFF;
  box-shadow: 0 0 0 4px rgba(74, 144, 226, 0.1);
  outline: none;
}

.search-input::placeholder {
  color: #A5B4C6;
  font-size: 0.9rem;
}

.select-items {
  padding: 0.875rem 1rem;
  border: 2px solid #D4EBF2;
  border-radius: 12px;
  font-size: 0.95rem;
  font-weight: 500;
  transition: all 0.3s ease;
  background: #F8FBFD;
  color: #2C3E50;
  cursor: pointer;
}

.select-items:focus {
  border-color: #4A90E2;
  background: #FFFFFF;
  box-shadow: 0 0 0 4px rgba(74, 144, 226, 0.1);
  outline: none;
}

/* ========================================
   TABLEAU
   ======================================== */
.table-pastel {
  border-radius: 12px;
  overflow: hidden;
}

.table-light-pastel {
  background: linear-gradient(135deg, var(--pastel-blue-light) 0%, var(--pastel-blue-bg) 100%) !important;
  border-bottom: 3px solid var(--pastel-blue-primary);
}

.table-light-pastel th {
  border: none;
  font-weight: 600;
  color: var(--pastel-text-primary);
  padding: 1.25rem 1rem;
  text-transform: uppercase;
  font-size: 0.85rem;
  letter-spacing: 0.5px;
}

.table-pastel td {
  vertical-align: middle;
  padding: 1.25rem 1rem;
  border-bottom: 1px solid var(--pastel-blue-light);
  background: #FFFFFF;
}

.table-row-pastel:hover td {
  background: var(--pastel-blue-bg) !important;
  transition: all 0.3s ease;
}

.formation-description {
  color: var(--pastel-text-primary);
  line-height: 1.6;
  font-size: 0.95rem;
}

.competences-badge {
  display: inline-block;
  background: linear-gradient(135deg, var(--pastel-success-light) 0%, #FFFFFF 100%);
  color: #5FA583;
  padding: 0.35rem 0.75rem;
  border-radius: 12px;
  border: 1px solid var(--pastel-success);
  font-weight: 500;
}

/* ========================================
   INFORMATIONS ET BADGES
   ======================================== */
.info-container {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  background: var(--pastel-blue-bg);
  padding: 0.875rem;
  border-radius: 10px;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 500;
  color: var(--pastel-text-primary);
}

.info-item i {
  font-size: 1.1rem;
}

.badge-status {
  display: inline-flex;
  align-items: center;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.85rem;
  transition: all 0.3s ease;
}

.badge-status-active {
  background: linear-gradient(135deg, var(--pastel-success-light) 0%, var(--pastel-success) 100%);
  color: #5FA583;
  border: 2px solid var(--pastel-success);
}

.badge-status-inactive {
  background: linear-gradient(135deg, var(--pastel-danger-light) 0%, var(--pastel-danger) 100%);
  color: #E6757E;
  border: 2px solid var(--pastel-danger);
}

.badge-status-large {
  display: inline-flex;
  align-items: center;
  padding: 0.75rem 1.5rem;
  border-radius: 25px;
  font-weight: 700;
  font-size: 1rem;
}

/* ========================================
   PAGINATION
   ======================================== */
.pagination-footer {
  background: linear-gradient(180deg, #FFFFFF 0%, #F8FBFD 100%);
  border-top: 2px solid #E5F2F8;
  padding: 1.5rem 2rem;
  border-radius: 0 0 16px 16px;
}

.pagination-info {
  color: #2C3E50;
  font-size: 0.95rem;
  font-weight: 500;
  display: flex;
  align-items: center;
}

.pagination-info i {
  color: #4A90E2;
  font-size: 1.1rem;
}

.pagination-info strong {
  color: #4A90E2;
  font-weight: 700;
}

.pagination-modern .page-link-modern {
  border: 2px solid #D4EBF2;
  color: #2C3E50;
  background: #FFFFFF;
  margin: 0 4px;
  border-radius: 10px;
  padding: 0.625rem 1rem;
  font-weight: 600;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  min-width: 45px;
  text-align: center;
}

.pagination-modern .page-link-modern:hover:not(:disabled) {
  background: #4A90E2;
  border-color: #4A90E2;
  color: #FFFFFF;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(74, 144, 226, 0.3);
}

.pagination-modern .page-item.active .page-link-modern {
  background: linear-gradient(135deg, #4A90E2 0%, #357ABD 100%);
  border-color: #357ABD;
  color: #FFFFFF;
  box-shadow: 0 4px 12px rgba(74, 144, 226, 0.4);
  transform: translateY(-2px);
}

.pagination-modern .page-item.disabled .page-link-modern {
  background: #F0F4F8;
  border-color: #E0E6ED;
  color: #A5B4C6;
  cursor: not-allowed;
  opacity: 0.6;
}

/* ========================================
   MODALS
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
   FORMULAIRES
   ======================================== */
.form-section {
  background: #FFFFFF;
  border: 2px solid #E5F2F8;
  border-radius: 16px;
  padding: 2rem;
  margin-bottom: 1.5rem;
  transition: all 0.3s ease;
}

.form-section:hover {
  border-color: #D4EBF2;
  box-shadow: 0 4px 15px rgba(74, 144, 226, 0.08);
}

.form-section-status {
  border-color: #D4EBF2;
  background: linear-gradient(135deg, #F0F8FA 0%, #FFFFFF 100%);
}

.form-section-title {
  color: #2C3E50;
  font-weight: 700;
  font-size: 1.1rem;
  margin-bottom: 1.5rem;
  padding-bottom: 0.75rem;
  border-bottom: 3px solid #E5F2F8;
  display: flex;
  align-items: center;
}

.form-section-title i {
  color: #4A90E2;
  font-size: 1.2rem;
}

.form-group-modern {
  margin-bottom: 0;
}

.form-label-modern {
  color: #2C3E50;
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
  color: #2C3E50;
  font-weight: 500;
}

.form-control-modern:focus {
  border-color: #4A90E2;
  background: #FFFFFF;
  box-shadow: 0 0 0 4px rgba(74, 144, 226, 0.12);
  outline: none;
}

.form-control-modern::placeholder {
  color: #A5B4C6;
  font-weight: 400;
}

.textarea-modern {
  resize: vertical;
  min-height: 100px;
  line-height: 1.6;
}

.form-hint {
  color: #7D8DA6;
  font-size: 0.85rem;
  margin-top: 0.5rem;
  display: block;
  font-style: italic;
}

/* Switch moderne */
.form-check-modern {
  background: #F8FBFD;
  border: 2px solid #E5F2F8;
  border-radius: 12px;
  padding: 1.25rem 1.5rem;
  transition: all 0.3s ease;
}

.form-check-modern:hover {
  background: #FFFFFF;
  border-color: #D4EBF2;
}

.form-check-input-modern {
  width: 3.5rem;
  height: 1.75rem;
  cursor: pointer;
  border: 2px solid #D4EBF2;
  background-color: #E5F2F8;
  margin-right: 1rem;
}

.form-check-input-modern:checked {
  background-color: #4A90E2;
  border-color: #4A90E2;
}

.form-check-input-modern:focus {
  box-shadow: 0 0 0 4px rgba(74, 144, 226, 0.15);
}

.form-check-label-modern {
  cursor: pointer;
  flex: 1;
}

/* ========================================
   MODAL FOOTER
   ======================================== */
.modal-footer-modern {
  background: #FFFFFF;
  border-top: 2px solid #E5F2F8;
  padding: 1.75rem 2.5rem;
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}

.modal-footer-pastel {
  background: linear-gradient(180deg, #FFFFFF 0%, #F8FBFD 100%);
  border-top: 2px solid #E5F2F8;
  padding: 1.5rem 2rem;
}

.btn-cancel-modern {
  background: #F0F4F8;
  border: 2px solid #E0E6ED;
  color: #5A6C7D;
  font-weight: 600;
  padding: 0.875rem 2rem;
  border-radius: 12px;
  transition: all 0.3s ease;
  font-size: 0.95rem;
}

.btn-cancel-modern:hover {
  background: #E0E6ED;
  border-color: #D0D6DD;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.btn-submit-modern {
  background: linear-gradient(135deg, #27AE60 0%, #229954 100%);
  border: none;
  color: #FFFFFF;
  font-weight: 700;
  padding: 0.875rem 2.5rem;
  border-radius: 12px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
  font-size: 0.95rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.btn-submit-modern:hover:not(:disabled) {
  background: linear-gradient(135deg, #229954 0%, #1E8449 100%);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(39, 174, 96, 0.4);
}

.btn-submit-modern:disabled {
  background: #BDC3C7;
  cursor: not-allowed;
  opacity: 0.6;
  box-shadow: none;
}

.btn-secondary-pastel {
  background: var(--pastel-secondary);
  border: none;
  color: var(--pastel-text-primary);
  font-weight: 500;
  padding: 0.625rem 1.5rem;
  border-radius: 10px;
  transition: all 0.3s ease;
}

.btn-secondary-pastel:hover {
  background: #BEBEBE;
  transform: translateY(-2px);
}

/* ========================================
   DÉTAILS FORMATION
   ======================================== */
.detail-header {
  background: linear-gradient(135deg, var(--pastel-blue-bg) 0%, #FFFFFF 100%);
  padding: 1.5rem;
  border-radius: 12px;
  border: 2px solid var(--pastel-blue-light);
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
}

.competences-box {
  background: linear-gradient(135deg, var(--pastel-success-light) 0%, #FFFFFF 100%);
  border: 2px solid var(--pastel-success);
  border-left: 5px solid #6BB896;
  border-radius: 12px;
  padding: 1.25rem 1.5rem;
  color: var(--pastel-text-primary);
  line-height: 1.7;
  font-size: 0.95rem;
  font-weight: 500;
}

.info-card {
  background: #FFFFFF;
  border: 2px solid #E5F2F8;
  border-radius: 12px;
  padding: 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: all 0.3s ease;
}

.info-card:hover {
  border-color: #D4EBF2;
  box-shadow: 0 4px 12px rgba(74, 144, 226, 0.1);
  transform: translateY(-2px);
}

.info-card-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.info-card-icon i {
  font-size: 1.5rem;
}

.bg-primary-light-pastel {
  background: linear-gradient(135deg, var(--pastel-blue-light) 0%, var(--pastel-blue-bg) 100%);
}

.bg-success-light-pastel {
  background: linear-gradient(135deg, var(--pastel-success-light) 0%, var(--pastel-success) 100%);
}

.info-card-label {
  color: var(--pastel-text-muted);
  font-size: 0.85rem;
  font-weight: 500;
  margin-bottom: 0.25rem;
}

.info-card-value {
  color: var(--pastel-text-primary);
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0;
}

/* ========================================
   ALERTS
   ======================================== */
.alert-danger-pastel {
  background: linear-gradient(135deg, var(--pastel-danger-light) 0%, #FFFFFF 100%);
  border: 2px solid var(--pastel-danger);
  border-left: 5px solid var(--pastel-danger);
  color: #E6757E;
  border-radius: 12px;
  padding: 1rem 1.25rem;
}

/* ========================================
   SPINNER
   ======================================== */
.spinner-pastel {
  border-color: var(--pastel-blue-light);
  border-right-color: var(--pastel-blue-primary);
  width: 3rem;
  height: 3rem;
}

/* ========================================
   ÉTATS VIDES
   ======================================== */
.empty-state {
  padding: 4rem 2rem;
  background: linear-gradient(135deg, var(--pastel-blue-bg) 0%, #FFFFFF 100%);
  border-radius: 16px;
}

.empty-state i {
  font-size: 4rem;
  opacity: 0.5;
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
   RESPONSIVE
   ======================================== */
@media (max-width: 768px) {
  .formations-list {
    padding: 1rem 0.5rem;
  }
  
  .btn-add-new {
    width: 100%;
    padding: 1rem;
    font-size: 0.9rem;
  }
  
  .card-filters .card-body {
    padding: 1.5rem !important;
  }
  
  .filter-label {
    font-size: 0.85rem;
  }
  
  .search-input,
  .select-items {
    font-size: 0.9rem;
    padding: 0.75rem 1rem 0.75rem 2.75rem;
  }
  
  .pagination-footer {
    padding: 1.25rem 1rem;
  }
  
  .pagination-info {
    font-size: 0.85rem;
    margin-bottom: 1rem;
    text-align: center;
    width: 100%;
  }
  
  .pagination-modern .page-link-modern {
    padding: 0.5rem 0.75rem;
    font-size: 0.85rem;
    min-width: 40px;
    margin: 0 2px;
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
  
  .form-section {
    padding: 1.5rem 1.25rem;
    margin-bottom: 1rem;
  }
  
  .modal-footer-modern {
    padding: 1.25rem 1.5rem;
    flex-direction: column;
  }
  
  .btn-cancel-modern,
  .btn-submit-modern {
    width: 100%;
  }
  
  .btn-group-pastel {
    display: flex;
  }
  
  .btn-group-pastel .btn {
    flex: 1;
  }
}

/* ========================================
   SCROLLBAR PERSONNALISÉE
   ======================================== */
::-webkit-scrollbar {
  width: 10px;
  height: 10px;
}

::-webkit-scrollbar-track {
  background: var(--pastel-blue-bg);
  border-radius: 10px;
}

::-webkit-scrollbar-thumb {
  background: var(--pastel-blue-primary);
  border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
  background: var(--pastel-blue-dark);
}
</style>