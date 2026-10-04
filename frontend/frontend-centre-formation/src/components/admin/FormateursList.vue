<template>
  <div class="formateurs-list">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="mb-1 page-title">
          <i class="bi bi-people-fill text-primary-pastel me-2"></i>
          Gestion des Formateurs
        </h2>
        <p class="text-muted-pastel mb-0">
          {{ formateurs.length }} formateur(s) trouvé(s)
        </p>
      </div>
      <button @click="openAddModal" class="btn btn-add-new">
        <i class="bi bi-person-plus-fill me-2"></i>Nouveau Formateur
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
                placeholder="Rechercher par nom, prénom, email, CIN ou spécialité..."
                v-model="search"
                @input="handleSearch"
              />
            </div>
          </div>
          <div class="col-md-3">
            <label class="filter-label mb-2">
              <i class="bi bi-funnel me-1"></i> Résultats
            </label>
            <select class="form-select select-items" v-model="itemsPerPage" @change="loadFormateurs">
              <option value="10">10 par page</option>
              <option value="25">25 par page</option>
              <option value="50">50 par page</option>
              <option value="100">100 par page</option>
            </select>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Liste des formateurs -->
    <div class="card card-pastel shadow-pastel">
      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary-pastel spinner-pastel" role="status">
            <span class="visually-hidden">Chargement...</span>
          </div>
          <p class="mt-3 text-muted-pastel">Chargement des formateurs...</p>
        </div>
        
        <div v-else-if="formateurs.length === 0" class="text-center py-5 empty-state">
          <i class="bi bi-people fs-1 text-muted-pastel mb-3"></i>
          <h5 class="text-muted-pastel">Aucun formateur trouvé</h5>
          <p class="text-muted-pastel">Cliquez sur "Nouveau Formateur" pour en ajouter un.</p>
        </div>
        
        <div v-else class="table-responsive">
          <table class="table table-hover table-pastel mb-0">
            <thead class="table-light-pastel">
              <tr>
                <th>Nom & Prénom</th>
                <th>Contact</th>
                <th>Spécialité</th>
                <th>CIN</th>
                <th>Statistiques</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="formateur in formateurs" :key="formateur.formateur_id || formateur.id" class="table-row-pastel">
                <td>
                  <div class="d-flex align-items-center">
                    <div class="avatar-circle bg-primary-light-pastel text-primary-pastel me-3">
                      {{ getInitials(formateur.nom, formateur.prenom) }}
                    </div>
                    <div>
                      <h6 class="mb-0 formation-title">{{ formateur.prenom }} {{ formateur.nom }}</h6>
                      <small class="text-muted-pastel">
                        <i class="bi bi-calendar me-1"></i>
                        Inscrit le {{ formatDate(formateur.date_inscription) }}
                      </small>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="contact-info">
                    <div class="mb-1">
                      <i class="bi bi-envelope-fill me-1 text-primary-pastel"></i>
                      <a :href="`mailto:${formateur.email}`" class="email-link">
                        {{ truncateText(formateur.email, 25) }}
                      </a>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="badge-specialite" v-if="formateur.specialite">
                    {{ formateur.specialite }}
                  </span>
                  <span class="badge-muted" v-else>
                    Non spécifié
                  </span>
                </td>
                <td>
                  <span class="badge-cin">
                    <i class="bi bi-card-text me-1"></i>
                    {{ formateur.cin || 'Non renseigné' }}
                  </span>
                </td>
                <td>
                  <div class="stats-container">
                    <div class="stat-item">
                      <div class="stat-value text-primary-pastel">{{ formateur.sessions_total || 0 }}</div>
                      <div class="stat-label">Sessions</div>
                    </div>
                    <div class="vr-pastel"></div>
                    <div class="stat-item">
                      <div class="stat-value text-success-pastel">{{ formateur.inscriptions_validees || 0 }}</div>
                      <div class="stat-label">Participants</div>
                    </div>
                  </div>
                </td>
                <td class="text-center">
                  <div class="btn-group btn-group-sm btn-group-pastel">
                    <button @click="viewDetails(formateur)" 
                            class="btn btn-outline-primary-pastel" title="Voir détails">
                      <i class="bi bi-eye"></i>
                    </button>
                    <button @click="editFormateur(formateur)" 
                            class="btn btn-outline-success-pastel" title="Modifier">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button @click="confirmDelete(formateur)" 
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
      <div v-if="formateurs.length > 0" class="card-footer pagination-footer">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
          <div class="pagination-info">
            <i class="bi bi-info-circle me-2"></i>
            Affichage de <strong>{{ startItem }}</strong> à <strong>{{ endItem }}</strong> sur <strong>{{ totalItems }}</strong> formateurs
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
                <i class="bi bi-person-badge-fill"></i>
              </div>
              <span>{{ isEditing ? 'Modifier le formateur' : 'Nouveau Formateur' }}</span>
            </h5>
            <button type="button" class="btn-close-modern" @click="closeModal">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
          <div class="modal-body modal-body-modern">
            <form @submit.prevent="saveFormateur">
              <!-- Section Informations Personnelles -->
              <div class="form-section">
                <h6 class="form-section-title">
                  <i class="bi bi-person-circle me-2"></i>
                  Informations Personnelles
                </h6>
                <div class="row g-4">
                  <div class="col-md-6">
                    <div class="form-group-modern">
                      <label class="form-label-modern">
                        <i class="bi bi-person me-1"></i> Prénom <span class="required">*</span>
                      </label>
                      <input type="text" class="form-control-modern" v-model="formData.prenom" 
                             required :disabled="saving" maxlength="100" 
                             placeholder="Ex: Jean">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group-modern">
                      <label class="form-label-modern">
                        <i class="bi bi-person-badge me-1"></i> Nom <span class="required">*</span>
                      </label>
                      <input type="text" class="form-control-modern" v-model="formData.nom" 
                             required :disabled="saving" maxlength="100" 
                             placeholder="Ex: Dupont">
                    </div>
                  </div>
                </div>
              </div>

              <!-- Section CIN et Email -->
              <div class="form-section">
                <h6 class="form-section-title">
                  <i class="bi bi-card-checklist me-2"></i>
                  Identification
                </h6>
                <div class="row g-4">
                  <div class="col-md-6">
                    <div class="form-group-modern">
                      <label class="form-label-modern">
                        <i class="bi bi-card-text me-1"></i> CIN <span class="required">*</span>
                      </label>
                    <input type="text" 
       inputmode="numeric"
       class="form-control-modern" 
       v-model="formData.cin" 
       required 
       :disabled="saving"
       placeholder="Ex: 12345678"
       maxlength="8"
       @input="validateCIN"
       :class="{'is-invalid-modern': cinError}">

                      <div v-if="cinError" class="invalid-feedback-modern">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        {{ cinError }}
                      </div>
                      <small class="form-hint">Exactement 8 chiffres, unique pour chaque formateur</small>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <div class="form-group-modern">
                      <label class="form-label-modern">
                        <i class="bi bi-envelope me-1"></i> Email <span class="required">*</span>
                      </label>
                      <input type="email" class="form-control-modern" v-model="formData.email" 
                             required :disabled="saving || isEditing"
                             placeholder="exemple@domaine.com">
                      <small v-if="isEditing" class="form-hint">
                        L'email ne peut pas être modifié pour des raisons de sécurité.
                      </small>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Section Spécialité -->
              <div class="form-section">
                <h6 class="form-section-title">
                  <i class="bi bi-award-fill me-2"></i>
                  Spécialité
                </h6>
                <div class="form-group-modern">
                  <label class="form-label-modern">
                    <i class="bi bi-tags me-1"></i> Domaine d'expertise
                  </label>
                  <input type="text" class="form-control-modern" v-model="formData.specialite" 
                         placeholder="Ex: Informatique, Management, Communication..."
                         :disabled="saving">
                  <small class="form-hint">Spécialité principale du formateur</small>
                </div>
              </div>
              
              <!-- Section Mot de passe pour la création uniquement -->
              <div v-if="!isEditing" class="form-section form-section-password">
                <h6 class="form-section-title">
                  <i class="bi bi-shield-lock-fill me-2"></i>
                  Mot de passe
                </h6>
                <div class="row g-4">
                  <div class="col-md-6">
                    <div class="form-group-modern">
                      <label class="form-label-modern">
                        <i class="bi bi-key me-1"></i> Mot de passe <span class="required">*</span>
                      </label>
                      <div class="input-group-modern">
                        <input 
                          :type="showPassword ? 'text' : 'password'" 
                          class="form-control-modern" 
                          v-model="formData.mot_de_passe"
                          required 
                          :disabled="saving"
                          placeholder="Définir le mot de passe"
                          @input="validatePassword"
                          :class="{'is-invalid-modern': passwordError}"
                        />
                        <button 
                          class="btn btn-password-toggle" 
                          type="button"
                          @click="showPassword = !showPassword"
                        >
                          <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                        </button>
                      </div>
                      <div v-if="passwordError" class="invalid-feedback-modern">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        {{ passwordError }}
                      </div>
                      <small class="form-hint">Minimum 6 caractères</small>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <div class="form-group-modern">
                      <label class="form-label-modern">
                        <i class="bi bi-key-fill me-1"></i> Confirmation <span class="required">*</span>
                      </label>
                      <input 
                        type="password" 
                        class="form-control-modern" 
                        v-model="formData.confirmation_mdp"
                        required 
                        :disabled="saving"
                        placeholder="Confirmer le mot de passe"
                        @input="validatePasswordConfirmation"
                        :class="{'is-invalid-modern': confirmationError}"
                      />
                      <div v-if="confirmationError" class="invalid-feedback-modern">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        {{ confirmationError }}
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="alert-info-modern">
                  <i class="bi bi-info-circle-fill me-2"></i>
                  <strong>Le mot de passe que vous définissez ici sera définitif.</strong>
                  Le formateur utilisera ce mot de passe pour toutes ses connexions.
                </div>
              </div>
              
              <div class="modal-footer-modern">
                <button type="button" class="btn btn-cancel-modern" 
                        @click="closeModal" :disabled="saving">
                  <i class="bi bi-x-circle me-1"></i>
                  Annuler
                </button>
                <button type="submit" class="btn btn-submit-modern" :disabled="saving || (!isEditing && !isFormValid)">
                  <span v-if="saving">
                    <span class="spinner-border spinner-border-sm me-2"></span>
                    {{ isEditing ? 'Modification...' : 'Création...' }}
                  </span>
                  <span v-else>
                    <i :class="isEditing ? 'bi bi-check-circle-fill' : 'bi bi-person-plus-fill'" class="me-1"></i>
                    {{ isEditing ? 'Enregistrer' : 'Créer le formateur' }}
                  </span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Modal Détails du Formateur -->
    <div v-if="showDetailModal" class="modal fade show d-block modal-overlay" 
         @click.self="closeDetailModal">
      <div class="modal-dialog modal-lg modal-centered">
        <div class="modal-content modal-modern">
          <div class="modal-header modal-header-modern">
            <h5 class="modal-title-modern">
              <div class="modal-icon">
                <i class="bi bi-person-badge-fill"></i>
              </div>
              <span>Détails du Formateur</span>
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
          <div v-else-if="formateurDetails" class="modal-body modal-body-modern">
            <!-- En-tête avec avatar -->
            <div class="mb-4 detail-header">
              <div class="d-flex align-items-center">
                <div class="avatar-large bg-primary-light-pastel text-primary-pastel me-4">
                  {{ getInitials(formateurDetails.nom, formateurDetails.prenom) }}
                </div>
                <div>
                  <h4 class="name-text-large mb-2">{{ formateurDetails.prenom }} {{ formateurDetails.nom }}</h4>
                  <div class="d-flex align-items-center gap-3 flex-wrap">
                    <span class="badge-info-modern">
                      <i class="bi bi-card-text me-1"></i>
                      {{ formateurDetails.cin || 'Non renseigné' }}
                    </span>
                    <small class="text-muted-pastel">
                      <i class="bi bi-hash me-1"></i>ID: {{ formateurDetails.id || formateurDetails.formateur_id }}
                    </small>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Informations de contact -->
            <div class="row mb-4">
              <div class="col-md-6 mb-3">
                <div class="info-card">
                  <div class="info-card-icon bg-primary-light-pastel">
                    <i class="bi bi-envelope-fill text-primary-pastel"></i>
                  </div>
                  <div class="info-card-content">
                    <label class="info-card-label">Email</label>
                    <p class="info-card-value">{{ formateurDetails.email }}</p>
                  </div>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <div class="info-card">
                  <div class="info-card-icon bg-info-light-pastel">
                    <i class="bi bi-award-fill text-info-pastel"></i>
                  </div>
                  <div class="info-card-content">
                    <label class="info-card-label">Spécialité</label>
                    <p class="info-card-value">
                      <span class="badge-specialite" v-if="formateurDetails.specialite">
                        {{ formateurDetails.specialite }}
                      </span>
                      <span class="badge-muted" v-else>Non spécifié</span>
                    </p>
                  </div>
                </div>
              </div>
              
              <div class="col-md-6 mb-3">
                <div class="info-card">
                  <div class="info-card-icon bg-secondary-light-pastel">
                    <i class="bi bi-calendar-fill text-secondary-pastel"></i>
                  </div>
                  <div class="info-card-content">
                    <label class="info-card-label">Date d'inscription</label>
                    <p class="info-card-value">{{ formatDate(formateurDetails.date_inscription) }}</p>
                  </div>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <div class="info-card">
                  <div class="info-card-icon bg-warning-light-pastel">
                    <i class="bi bi-person-fill text-warning-pastel"></i>
                  </div>
                  <div class="info-card-content">
                    <label class="info-card-label">ID Utilisateur</label>
                    <p class="info-card-value">{{ formateurDetails.id_utilisateur || formateurDetails.utilisateur_id }}</p>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Statistiques -->
            <div class="card card-stats-pastel mb-4">
              <div class="card-header card-header-stats-pastel">
                <h6 class="mb-0">
                  <i class="bi bi-bar-chart-fill me-2"></i>
                  Statistiques d'activité
                </h6>
              </div>
              <div class="card-body">
                <div class="row text-center">
                  <div class="col-4 mb-3">
                    <div class="display-6 fw-bold text-primary-pastel">
                      {{ statistiques?.sessions_total || 0 }}
                    </div>
                    <small class="text-muted-pastel">Sessions totales</small>
                  </div>
                  <div class="col-4 mb-3">
                    <div class="display-6 fw-bold text-success-pastel">
                      {{ statistiques?.inscriptions_validees || 0 }}
                    </div>
                    <small class="text-muted-pastel">Participants validés</small>
                  </div>
                  <div class="col-4 mb-3">
                    <div class="display-6 fw-bold text-warning-pastel">
                      {{ Math.round(statistiques?.places_moyennes || 0) }}
                    </div>
                    <small class="text-muted-pastel">Places moyennes</small>
                  </div>
                </div>
                
                <div v-if="statistiques" class="row text-center mt-3">
                  <div class="col-3 mb-2">
                    <div class="fw-bold text-info-pastel">{{ statistiques.sessions_acceptees || 0 }}</div>
                    <small class="text-muted-pastel">Sessions acceptées</small>
                  </div>
                  <div class="col-3 mb-2">
                    <div class="fw-bold text-warning-pastel">{{ statistiques.sessions_attente || 0 }}</div>
                    <small class="text-muted-pastel">En attente</small>
                  </div>
                  <div class="col-3 mb-2">
                    <div class="fw-bold text-danger-pastel">{{ statistiques.sessions_refusees || 0 }}</div>
                    <small class="text-muted-pastel">Refusées</small>
                  </div>
                  <div class="col-3 mb-2">
                    <div class="fw-bold text-secondary-pastel">{{ statistiques.inscriptions_totales || 0 }}</div>
                    <small class="text-muted-pastel">Inscriptions totales</small>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Sessions futures -->
            <div v-if="sessionsFutures && sessionsFutures.length > 0" class="mb-4">
              <h6 class="section-subtitle">
                <i class="bi bi-calendar-event-fill text-success-pastel me-2"></i>
                Sessions à venir
              </h6>
              <div class="list-group-modern">
                <div v-for="session in sessionsFutures" :key="session.id" class="list-group-item-modern">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <h6 class="mb-1">{{ session.formation_titre }}</h6>
                      <small class="text-muted-pastel">
                        {{ session.date_debut }} → {{ session.date_fin }} | 
                        {{ session.lieu }} | 
                        {{ session.places_prises }}/{{ session.places_max }} places
                      </small>
                    </div>
                    <span class="badge-status badge-status-active">Acceptée</span>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Sessions passées -->
            <div v-if="sessionsPassees && sessionsPassees.length > 0" class="mb-4">
              <h6 class="section-subtitle">
                <i class="bi bi-clock-history text-secondary-pastel me-2"></i>
                Sessions passées
              </h6>
              <div class="list-group-modern">
                <div v-for="session in sessionsPassees" :key="session.id" class="list-group-item-modern">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <h6 class="mb-1">{{ session.formation_titre }}</h6>
                      <small class="text-muted-pastel">
                        {{ session.date_debut }} | 
                        {{ session.places_prises }}/{{ session.places_max }} places
                      </small>
                    </div>
                    <span class="badge-status badge-status-inactive">Terminée</span>
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
            <button v-if="formateurDetails" type="button" class="btn btn-outline-primary-pastel"
                    @click="editFromDetail">
              <i class="bi bi-pencil me-1"></i>Modifier
            </button>
            <button v-if="formateurDetails" type="button" class="btn btn-outline-danger-pastel"
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
  name: 'FormateursList',
  
  setup() {
    // États existants
    const loading = ref(true)
    const saving = ref(false)
    const showModal = ref(false)
    const isEditing = ref(false)
    const showPassword = ref(false)
    
    const search = ref('')
    const searchTimeout = ref(null)
    
    const formateurs = ref([])
    const currentPage = ref(1)
    const itemsPerPage = ref(10)
    const totalItems = ref(0)
    const totalPages = ref(1)
    
    const formData = ref({
      nom: '',
      prenom: '',
      cin: '',
      email: '',
      specialite: '',
      mot_de_passe: '',
      confirmation_mdp: ''
    })
    
    const currentFormateurId = ref(null)
    
    // États pour validation mot de passe
    const passwordError = ref('')
    const confirmationError = ref('')
    const cinError = ref('')
    const isPasswordValid = ref(false)
    const isCINValid = ref(false)
    const isFormValid = computed(() => isPasswordValid.value && isCINValid.value)
    
    // États pour le modal de détails
    const showDetailModal = ref(false)
    const detailLoading = ref(false)
    const detailError = ref(null)
    const formateurDetails = ref(null)
    const statistiques = ref(null)
    const sessionsFutures = ref([])
    const sessionsPassees = ref([])
    
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
    
    // Méthodes
    const loadFormateurs = async () => {
      loading.value = true
      try {
        console.log('🔄 Chargement des formateurs...')
        
        const params = {
          limit: itemsPerPage.value,
          page: currentPage.value
        }
        
        if (search.value.trim()) {
          params.recherche = search.value.trim()
        }
        
        const response = await adminService.getFormateurs(params)
        
        console.log('📊 Réponse API formateurs:', response)
        
        if (response.success) {
          formateurs.value = response.data || []
          totalItems.value = response.pagination?.total || response.count || formateurs.value.length
          totalPages.value = response.pagination?.pages || 
                            Math.ceil(totalItems.value / itemsPerPage.value)
          
          console.log(`✅ ${formateurs.value.length} formateurs chargés`)
        } else {
          throw new Error(response.message || 'Erreur lors du chargement')
        }
      } catch (error) {
        console.error('❌ Erreur chargement formateurs:', error)
        alert(error.message || 'Erreur lors du chargement des formateurs')
      } finally {
        loading.value = false
      }
    }
    
    const handleSearch = () => {
      clearTimeout(searchTimeout.value)
      searchTimeout.value = setTimeout(() => {
        currentPage.value = 1
        loadFormateurs()
      }, 500)
    }
    
    const getInitials = (nom, prenom) => {
      if (!nom || !prenom) return '?'
      return `${prenom.charAt(0)}${nom.charAt(0)}`.toUpperCase()
    }
    
    const truncateText = (text, maxLength) => {
      if (!text) return ''
      if (text.length <= maxLength) return text
      return text.substring(0, maxLength) + '...'
    }
    
    const formatDate = (dateString) => {
      if (!dateString) return ''
      try {
        const date = new Date(dateString)
        return date.toLocaleDateString('fr-FR')
      } catch (error) {
        return dateString
      }
    }
    
   const validateCIN = () => {
  const cin = formData.value.cin

  if (!cin) {
    cinError.value = 'Le CIN est requis'
    isCINValid.value = false
    return
  }

  // CHANGÉ : exactement 8 chiffres
  const cinRegex = /^\d{8}$/
  if (!cinRegex.test(cin)) {
    cinError.value = 'Le CIN doit contenir exactement 8 chiffres'
    isCINValid.value = false
    return
  }

  cinError.value = ''
  isCINValid.value = true
}
    
    const validatePassword = () => {
      const password = formData.value.mot_de_passe
      
      if (!password) {
        passwordError.value = 'Le mot de passe est requis'
        isPasswordValid.value = false
        return
      }
      
      if (password.length < 6) {
        passwordError.value = 'Le mot de passe doit contenir au moins 6 caractères'
        isPasswordValid.value = false
        return
      }
      
      passwordError.value = ''
      
      validatePasswordConfirmation()
    }
    
    const validatePasswordConfirmation = () => {
      const password = formData.value.mot_de_passe
      const confirmation = formData.value.confirmation_mdp
      
      if (!confirmation) {
        confirmationError.value = 'La confirmation est requise'
        isPasswordValid.value = false
        return
      }
      
      if (password !== confirmation) {
        confirmationError.value = 'Les mots de passe ne correspondent pas'
        isPasswordValid.value = false
        return
      }
      
      confirmationError.value = ''
      
      if (password && confirmation && password.length >= 6 && password === confirmation) {
        isPasswordValid.value = true
      }
    }
    
    const openAddModal = () => {
      isEditing.value = false
      showPassword.value = false
      passwordError.value = ''
      confirmationError.value = ''
      cinError.value = ''
      isPasswordValid.value = false
      isCINValid.value = false
      formData.value = {
        nom: '',
        prenom: '',
        cin: '',
        email: '',
        specialite: '',
        mot_de_passe: '',
        confirmation_mdp: ''
      }
      showModal.value = true
    }
    
    const viewDetails = async (formateur) => {
      console.log('👁️ Affichage détails pour:', formateur)
      
      showDetailModal.value = true
      detailLoading.value = true
      detailError.value = null
      formateurDetails.value = null
      statistiques.value = null
      sessionsFutures.value = []
      sessionsPassees.value = []
      
      try {
        const id = formateur.formateur_id || formateur.id
        console.log('📤 Chargement détails formateur ID:', id)
        
        formateurDetails.value = formateur
        
        const response = await adminService.getFormateur(id)
        console.log('📊 Réponse détails:', response)
        
        if (response.success && response.data) {
          if (response.data.formateur) {
            formateurDetails.value = { ...formateurDetails.value, ...response.data.formateur }
            statistiques.value = response.data.statistiques || {}
            sessionsFutures.value = response.data.sessions_futures || []
            sessionsPassees.value = response.data.sessions_passees || []
          } else {
            formateurDetails.value = { ...formateurDetails.value, ...response.data }
          }
        }
        
        console.log('✅ Détails chargés:', formateurDetails.value)
      } catch (error) {
        console.error('❌ Erreur chargement détails:', error)
        detailError.value = error.message || 'Impossible de charger les détails'
        
        if (!formateurDetails.value) {
          formateurDetails.value = formateur
        }
      } finally {
        detailLoading.value = false
      }
    }
    
    const editFormateur = (formateur) => {
      isEditing.value = true
      showPassword.value = false
      passwordError.value = ''
      confirmationError.value = ''
      cinError.value = ''
      isPasswordValid.value = true
      isCINValid.value = true
      currentFormateurId.value = formateur.formateur_id || formateur.id
      formData.value = {
        nom: formateur.nom || '',
        prenom: formateur.prenom || '',
        cin: formateur.cin || '',
        email: formateur.email || '',
        specialite: formateur.specialite || '',
        mot_de_passe: '',
        confirmation_mdp: ''
      }
      showModal.value = true
      if (showDetailModal.value) {
        closeDetailModal()
      }
    }
    
    const editFromDetail = () => {
      if (formateurDetails.value) {
        editFormateur(formateurDetails.value)
      }
    }
    
    const deleteFromDetail = () => {
      if (formateurDetails.value) {
        const name = `${formateurDetails.value.prenom} ${formateurDetails.value.nom}`
        if (confirm(`Êtes-vous sûr de vouloir supprimer "${name}" ?\n\nCette action est irréversible.`)) {
          deleteFormateur(formateurDetails.value.formateur_id || formateurDetails.value.id)
          closeDetailModal()
        }
      }
    }
    
    const saveFormateur = async () => {
 if (!formData.value.cin || !/^\d{8}$/.test(formData.value.cin)) {
  alert('Le CIN doit contenir exactement 8 chiffres')
  return
}
      
      if (!isEditing.value) {
        if (!formData.value.mot_de_passe) {
          alert('Le mot de passe est requis')
          return
        }
        
        if (formData.value.mot_de_passe.length < 6) {
          alert('Le mot de passe doit contenir au moins 6 caractères')
          return
        }
        
        if (formData.value.mot_de_passe !== formData.value.confirmation_mdp) {
          alert('Les mots de passe ne correspondent pas')
          return
        }
      }
      
      saving.value = true
      try {
        console.log('💾 Sauvegarde formateur:', formData.value)
        
        const dataToSend = {
          nom: formData.value.nom,
          prenom: formData.value.prenom,
          email: formData.value.email,
          specialite: formData.value.specialite,
          cin: formData.value.cin
        }
        
        if (!isEditing.value) {
          dataToSend.mot_de_passe = formData.value.mot_de_passe
        }
        
        console.log('📤 Données envoyées à l\'API:', dataToSend)
        
        if (isEditing.value) {
          const response = await adminService.updateFormateur(currentFormateurId.value, dataToSend)
          console.log('✅ Réponse modification:', response)
          alert('✅ Formateur modifié avec succès')
        } else {
          const response = await adminService.createFormateur(dataToSend)
          console.log('✅ Réponse création:', response)
          
          if (response.success) {
            alert('✅ Formateur créé avec succès !\n\n' +
                  `Email: ${response.data.email}\n` +
                  `Nom: ${response.data.nom_complet || formData.value.prenom + ' ' + formData.value.nom}\n` +
                  `CIN: ${response.data.cin || formData.value.cin}`)
          } else {
            alert('❌ ' + response.message)
          }
        }
        
        closeModal()
        loadFormateurs()
        
      } catch (error) {
        console.error('❌ Erreur sauvegarde:', error)
        const errorMessage = error.response?.data?.message || 
                           error.message || 
                           'Erreur lors de la sauvegarde'
        
        let userMessage = errorMessage
        if (error.response?.data?.error) {
          userMessage += `\n\nDétail: ${error.response.data.error}`
        }
        
        alert(`❌ ${userMessage}`)
      } finally {
        saving.value = false
      }
    }
    
    const confirmDelete = (formateur) => {
      const name = `${formateur.prenom} ${formateur.nom}`
      
      const userChoice = confirm(
        `Que voulez-vous faire avec "${name}" ?\n\n` +
        "OK = Supprimer définitivement (marqué comme supprimé)\n" +
        "Annuler = Changer son rôle en EMPLOYÉ"
      )
      
      const action = userChoice ? 'soft_delete' : 'change_role'
      
      deleteFormateur(formateur.formateur_id || formateur.id, action)
    }
    
    const deleteFormateur = async (id, action) => {
      try {
        await adminService.deleteFormateur(id, action)
        
        if (action === 'soft_delete') {
          alert('✅ Formateur supprimé avec succès')
        } else {
          alert('✅ Rôle changé en EMPLOYÉ avec succès')
        }
        
        loadFormateurs()
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
        loadFormateurs()
      }
    }
    
    const closeModal = () => {
      showModal.value = false
      isEditing.value = false
      showPassword.value = false
      passwordError.value = ''
      confirmationError.value = ''
      cinError.value = ''
      isPasswordValid.value = false
      isCINValid.value = false
      formData.value = {
        nom: '',
        prenom: '',
        cin: '',
        email: '',
        specialite: '',
        mot_de_passe: '',
        confirmation_mdp: ''
      }
      currentFormateurId.value = null
    }
    
    const closeDetailModal = () => {
      showDetailModal.value = false
      formateurDetails.value = null
      statistiques.value = null
      sessionsFutures.value = []
      sessionsPassees.value = []
      detailError.value = null
    }
    
    // Lifecycle
    onMounted(() => {
      loadFormateurs()
    })
    
    return {
      // États
      loading,
      saving,
      showModal,
      isEditing,
      showPassword,
      search,
      formateurs,
      currentPage,
      itemsPerPage,
      totalItems,
      totalPages,
      formData,
      passwordError,
      confirmationError,
      cinError,
      isPasswordValid,
      isCINValid,
      isFormValid,
      showDetailModal,
      detailLoading,
      detailError,
      formateurDetails,
      statistiques,
      sessionsFutures,
      sessionsPassees,
      
      // Computed
      startItem,
      endItem,
      displayedPages,
      
      // Méthodes
      loadFormateurs,
      handleSearch,
      getInitials,
      truncateText,
      formatDate,
      validateCIN,
      validatePassword,
      validatePasswordConfirmation,
      openAddModal,
      viewDetails,
      editFormateur,
      editFromDetail,
      deleteFromDetail,
      saveFormateur,
      confirmDelete,
      deleteFormateur,
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
  --pastel-success-dark: #6BB896;
  
  --pastel-warning: #FFE5B4;
  --pastel-warning-light: #FFF4E0;
  --pastel-warning-dark: #F4B860;
  
  --pastel-danger: #FFB3BA;
  --pastel-danger-light: #FFD9DC;
  --pastel-danger-dark: #FF8B94;
  
  --pastel-info: #B4D7FF;
  --pastel-info-light: #E0EEFF;
  --pastel-info-dark: #7BA7D9;
  
  --pastel-secondary: #D4D4D4;
  --pastel-secondary-light: #EEEEEE;
  --pastel-secondary-dark: #9B9B9B;
  
  --pastel-text-primary: #2C3E50;
  --pastel-text-muted: #7D8DA6;
  --pastel-text-light: #A5B4C6;
}

/* ========================================
   LAYOUT PRINCIPAL
   ======================================== */
.formateurs-list {
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
  color: var(--pastel-success-dark) !important;
}

.text-warning-pastel {
  color: var(--pastel-warning-dark) !important;
}

.text-danger-pastel {
  color: var(--pastel-danger-dark) !important;
}

.text-info-pastel {
  color: var(--pastel-info-dark) !important;
}

.text-secondary-pastel {
  color: var(--pastel-secondary-dark) !important;
}

.text-muted-pastel {
  color: var(--pastel-text-muted) !important;
}

/* ========================================
   AVATARS ET IMAGES
   ======================================== */
.avatar-circle {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1.2rem;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
}

.avatar-large {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 2rem;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
}

.bg-primary-light-pastel {
  background: linear-gradient(135deg, var(--pastel-blue-light) 0%, var(--pastel-blue-bg) 100%);
}

.bg-success-light-pastel {
  background: linear-gradient(135deg, var(--pastel-success-light) 0%, var(--pastel-success) 100%);
}

.bg-warning-light-pastel {
  background: linear-gradient(135deg, var(--pastel-warning-light) 0%, var(--pastel-warning) 100%);
}

.bg-danger-light-pastel {
  background: linear-gradient(135deg, var(--pastel-danger-light) 0%, var(--pastel-danger) 100%);
}

.bg-info-light-pastel {
  background: linear-gradient(135deg, var(--pastel-info-light) 0%, var(--pastel-info) 100%);
}

.bg-secondary-light-pastel {
  background: linear-gradient(135deg, var(--pastel-secondary-light) 0%, var(--pastel-secondary) 100%);
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
  padding: 0.375rem 0.75rem;
}

.btn-outline-primary-pastel:hover {
  background: var(--pastel-blue-light);
  border-color: var(--pastel-blue-dark);
  color: var(--pastel-blue-dark);
  transform: scale(1.05);
}

.btn-outline-success-pastel {
  border: 2px solid var(--pastel-success);
  color: var(--pastel-success-dark);
  background: transparent;
  transition: all 0.3s ease;
  border-radius: 8px;
  padding: 0.375rem 0.75rem;
}

.btn-outline-success-pastel:hover {
  background: var(--pastel-success-light);
  border-color: var(--pastel-success-dark);
  color: var(--pastel-success-dark);
  transform: scale(1.05);
}

.btn-outline-danger-pastel {
  border: 2px solid var(--pastel-danger);
  color: var(--pastel-danger-dark);
  background: transparent;
  transition: all 0.3s ease;
  border-radius: 8px;
  padding: 0.375rem 0.75rem;
}

.btn-outline-danger-pastel:hover {
  background: var(--pastel-danger-light);
  border-color: var(--pastel-danger-dark);
  color: var(--pastel-danger-dark);
  transform: scale(1.05);
}

.btn-outline-secondary-pastel {
  border: 2px solid var(--pastel-secondary);
  color: var(--pastel-secondary-dark);
  background: transparent;
  transition: all 0.3s ease;
  border-radius: 8px;
  padding: 0.625rem 1.5rem;
}

.btn-outline-secondary-pastel:hover {
  background: var(--pastel-secondary-light);
  border-color: var(--pastel-secondary-dark);
  color: var(--pastel-secondary-dark);
}

.btn-group-pastel .btn {
  margin: 0 2px;
}

.btn-password-toggle {
  border: 2px solid #E5F2F8;
  border-left: none;
  background: #F8FBFD;
  color: var(--pastel-text-muted);
  border-radius: 0 12px 12px 0;
  transition: all 0.3s ease;
}

.btn-password-toggle:hover {
  background: var(--pastel-blue-light);
  color: var(--pastel-blue-dark);
}

/* ========================================
   BADGES ET ÉTIQUETTES
   ======================================== */
.badge-specialite {
  display: inline-block;
  background: linear-gradient(135deg, var(--pastel-info-light) 0%, #FFFFFF 100%);
  color: var(--pastel-info-dark);
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.9rem;
  border: 2px solid var(--pastel-info);
}

.badge-cin {
  display: inline-block;
  background: linear-gradient(135deg, var(--pastel-secondary-light) 0%, #FFFFFF 100%);
  color: var(--pastel-secondary-dark);
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.9rem;
  border: 2px solid var(--pastel-secondary);
}

.badge-muted {
  display: inline-block;
  background: linear-gradient(135deg, #F0F4F8 0%, #FFFFFF 100%);
  color: var(--pastel-text-muted);
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.9rem;
  border: 2px solid #E0E6ED;
}

.badge-info-modern {
  display: inline-block;
  background: linear-gradient(135deg, var(--pastel-info-light) 0%, var(--pastel-info) 100%);
  color: var(--pastel-text-primary);
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.9rem;
  border: 2px solid var(--pastel-info-dark);
}

.badge-status {
  display: inline-flex;
  align-items: center;
  padding: 0.4rem 0.8rem;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.85rem;
  transition: all 0.3s ease;
}

.badge-status-active {
  background: linear-gradient(135deg, var(--pastel-success-light) 0%, var(--pastel-success) 100%);
  color: var(--pastel-success-dark);
  border: 2px solid var(--pastel-success-dark);
}

.badge-status-inactive {
  background: linear-gradient(135deg, var(--pastel-secondary-light) 0%, var(--pastel-secondary) 100%);
  color: var(--pastel-secondary-dark);
  border: 2px solid var(--pastel-secondary-dark);
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

.contact-info {
  color: var(--pastel-text-primary);
}

.email-link {
  color: var(--pastel-blue-dark);
  text-decoration: none;
  font-weight: 500;
  transition: all 0.3s ease;
}

.email-link:hover {
  color: #4A90E2;
  text-decoration: underline;
}

.stats-container {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stat-item {
  text-align: center;
}

.stat-value {
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 0.25rem;
}

.stat-label {
  font-size: 0.85rem;
  color: var(--pastel-text-muted);
  font-weight: 500;
}

.vr-pastel {
  width: 1px;
  height: 30px;
  background-color: var(--pastel-blue-light);
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

.form-section-password {
  border-color: var(--pastel-warning);
  background: linear-gradient(135deg, var(--pastel-warning-light) 0%, #FFFFFF 100%);
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
  width: 100%;
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

.form-control-modern.is-invalid-modern {
  border-color: var(--pastel-danger-dark);
  background: linear-gradient(135deg, var(--pastel-danger-light) 0%, #FFFFFF 100%);
}

.form-control-modern.is-invalid-modern:focus {
  border-color: var(--pastel-danger-dark);
  box-shadow: 0 0 0 4px rgba(255, 139, 148, 0.12);
}

.invalid-feedback-modern {
  color: var(--pastel-danger-dark);
  font-size: 0.85rem;
  margin-top: 0.5rem;
  display: flex;
  align-items: center;
}

.form-hint {
  color: #7D8DA6;
  font-size: 0.85rem;
  margin-top: 0.5rem;
  display: block;
  font-style: italic;
}

.input-group-modern {
  display: flex;
  position: relative;
}

.input-group-modern .form-control-modern {
  border-radius: 12px 0 0 12px;
}

/* ========================================
   ALERTS ET MESSAGES
   ======================================== */
.alert-info-modern {
  background: linear-gradient(135deg, var(--pastel-info-light) 0%, #FFFFFF 100%);
  border: 2px solid var(--pastel-info);
  border-left: 5px solid var(--pastel-info-dark);
  color: var(--pastel-text-primary);
  border-radius: 12px;
  padding: 1rem 1.25rem;
  margin-top: 1.5rem;
  font-size: 0.95rem;
}

.alert-danger-pastel {
  background: linear-gradient(135deg, var(--pastel-danger-light) 0%, #FFFFFF 100%);
  border: 2px solid var(--pastel-danger);
  border-left: 5px solid var(--pastel-danger-dark);
  color: var(--pastel-danger-dark);
  border-radius: 12px;
  padding: 1rem 1.25rem;
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
   DÉTAILS FORMATEUR
   ======================================== */
.detail-header {
  background: linear-gradient(135deg, var(--pastel-blue-bg) 0%, #FFFFFF 100%);
  padding: 1.5rem;
  border-radius: 12px;
  border: 2px solid var(--pastel-blue-light);
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
  height: 100%;
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
  flex-shrink: 0;
}

.info-card-icon i {
  font-size: 1.5rem;
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

.list-group-modern {
  background: #FFFFFF;
  border-radius: 12px;
  overflow: hidden;
  border: 2px solid #E5F2F8;
}

.list-group-item-modern {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #E5F2F8;
  transition: all 0.3s ease;
}

.list-group-item-modern:hover {
  background: var(--pastel-blue-bg);
}

.list-group-item-modern:last-child {
  border-bottom: none;
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
  .formateurs-list {
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
  
  .avatar-circle {
    width: 40px;
    height: 40px;
    font-size: 1rem;
  }
  
  .avatar-large {
    width: 60px;
    height: 60px;
    font-size: 1.5rem;
  }
  
  .stats-container {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .vr-pastel {
    width: 30px;
    height: 1px;
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