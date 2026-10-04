<template>
  <div class="employes-management">
    <!-- ========================================
       EN-TÊTE PRINCIPAL - STYLE MODERNE
       ======================================== -->
    <div class="page-header">
      <div class="container-fluid">
        <div class="row align-items-center">
          <div class="col-lg-8">
            <div class="header-content">
              <div class="icon-wrapper">
                <i class="bi bi-people primary-text"></i>
              </div>
              <div>
                <h1 class="page-title primary-text mb-2">Gestion des Employés</h1>
                <p class="page-subtitle mb-0">
                  <span class="badge badge-count primary-text">{{ totalItems }}</span>
                  employé(s) au total
                </p>
              </div>
            </div>
          </div>
          <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <!-- Bouton modifié pour ouvrir le modal -->
            <button @click="openAddModal" class="btn btn-add-new-employe">
              <i class="bi bi-person-plus-fill me-2"></i>Nouvel Employé
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ========================================
       LISTE DES EMPLOYÉS - DESIGN MODERNE
       ======================================== -->
    <div class="list-section-modern">
      <div class="container-fluid">
        <!-- Statistiques rapides -->
        <div class="row mb-4 g-3">
          <div class="col-lg-3 col-md-6">
            <div class="stat-card stat-total">
              <div class="stat-icon">
                <i class="bi bi-people"></i>
              </div>
              <div class="stat-content">
                <h3 class="stat-value">{{ totalItems || 0 }}</h3>
                <p class="stat-label">Total employés</p>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="stat-card stat-active">
              <div class="stat-icon">
                <i class="bi bi-person-check"></i>
              </div>
              <div class="stat-content">
                <h3 class="stat-value">{{ activeCount }}</h3>
                <p class="stat-label">Actifs</p>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="stat-card stat-inactive">
              <div class="stat-icon">
                <i class="bi bi-person-x"></i>
              </div>
              <div class="stat-content">
                <h3 class="stat-value">{{ inactiveCount }}</h3>
                <p class="stat-label">Inactifs</p>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="stat-card stat-departments">
              <div class="stat-icon">
                <i class="bi bi-building"></i>
              </div>
              <div class="stat-content">
                <h3 class="stat-value">{{ availableDepartements.length }}</h3>
                <p class="stat-label">Départements</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Filtres modernes -->
        <div class="filters-card mb-4">
          <div class="card-body">
            <div class="row g-3">
              <div class="col-lg-5 col-md-6">
                <div class="search-box">
                  <i class="bi bi-search search-icon"></i>
                  <input type="text" 
                         class="form-control form-control-search" 
                         v-model="filters.search" 
                         @keyup="onSearchInput"
                         placeholder="Rechercher par nom, prénom, email ou CIN...">
                </div>
              </div>
              
              <div class="col-lg-3 col-md-4">
                <select class="form-select form-select-custom" v-model="filters.departement" @change="loadEmployes">
                  <option value="">Tous départements</option>
                  <option v-for="dept in availableDepartements" :key="dept" :value="dept">{{ dept }}</option>
                </select>
              </div>
              
              <div class="col-lg-2 col-md-3">
                <select class="form-select form-select-custom" v-model="filters.statut" @change="loadEmployes">
                  <option value="">Tous statuts</option>
                  <option value="actif">Actifs</option>
                  <option value="inactif">Inactifs</option>
                </select>
              </div>
              
              <div class="col-lg-2 col-md-3">
                <button class="btn btn-outline-secondary-modern w-100" @click="resetFilters">
                  <i class="bi bi-x-circle me-1"></i>
                  Réinitialiser
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Tableau moderne -->
        <div class="employes-table-card">
          <div class="card-body p-0">
            <!-- État de chargement -->
            <div v-if="loading" class="loading-state">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
              </div>
              <p class="mt-3 text-muted">Chargement des employés...</p>
            </div>
            
            <!-- État vide -->
            <div v-else-if="employes.length === 0" class="empty-state">
              <div class="empty-icon">
                <i class="bi bi-people"></i>
              </div>
              <h5 class="empty-title">Aucun employé trouvé</h5>
              <p v-if="hasActiveFilters" class="empty-text">
                Aucun employé ne correspond à vos critères de recherche.
              </p>
              <p v-else class="empty-text">
                Commencez par ajouter votre premier employé.
              </p>
              <!-- Bouton modifié pour ouvrir le modal -->
              <button @click="openAddModal" class="btn btn-add-new-employe mt-3">
                <i class="bi bi-person-plus-fill me-1"></i>
                Créer un premier employé
              </button>
            </div>
            
            <!-- Liste des employés -->
            <div v-else class="table-responsive">
              <table class="table table-custom mb-0">
                <thead>
                  <tr>
                    <th>Employé</th>
                    <th>CIN</th>
                    <th>Informations professionnelles</th>
                    <th>Date d'embauche</th>
                    <th>Statut</th>
                    <th class="text-center">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="employe in employes" :key="employe.id" class="employe-row">
                    <!-- Informations employé -->
                    <td>
                      <div class="employe-info">
                        <div class="employe-avatar" :class="{'inactive': employe.deleted_at}">
                          <i class="bi bi-person-circle"></i>
                        </div>
                        <div>
                          <h6 class="employe-name">{{ employe.nom }} {{ employe.prenom }}</h6>
                          <span class="employe-email">{{ employe.email }}</span>
                          <span class="employe-id">ID: {{ employe.id }}</span>
                        </div>
                      </div>
                    </td>
                    
                    <!-- CIN -->
                    <td>
                      <span class="badge badge-cin">{{ employe.cin }}</span>
                    </td>
                    
                    <!-- Informations professionnelles -->
                    <td>
                      <div class="professional-info">
                        <div v-if="employe.poste" class="info-item">
                          <i class="bi bi-person-badge me-1"></i>
                          <span class="info-label">{{ employe.poste }}</span>
                        </div>
                        <div v-if="employe.departement" class="info-item">
                          <i class="bi bi-building me-1"></i>
                          <span class="badge badge-department">{{ employe.departement }}</span>
                        </div>
                      </div>
                    </td>
                    
                    <!-- Date embauche -->
                    <td>
                      <div class="date-info">
                        <div class="date-label">Embauché le</div>
                        <div class="date-value">{{ formatDate(employe.date_embauche) }}</div>
                      </div>
                    </td>
                    
                    <!-- Statut -->
                    <td>
                      <span class="badge badge-status" :class="employe.deleted_at ? 'status-inactive' : 'status-active'">
                        <i class="bi me-1" :class="employe.deleted_at ? 'bi-person-x' : 'bi-person-check'"></i>
                        {{ employe.deleted_at ? 'Inactif' : 'Actif' }}
                      </span>
                      <div v-if="employe.deleted_at" class="inactive-date">
                        <small class="text-muted">Depuis {{ formatDate(employe.deleted_at) }}</small>
                      </div>
                    </td>
                    
                    <!-- Actions -->
                    <td>
                      <div class="action-buttons">
                        <button @click="viewDetails(employe)" 
                                class="btn btn-action btn-view" 
                                title="Voir détails">
                          <i class="bi bi-eye"></i>
                        </button>
                        <button @click="editEmploye(employe)" 
                                class="btn btn-action btn-edit" 
                                title="Modifier"
                                :disabled="!!employe.deleted_at">
                          <i class="bi bi-pencil"></i>
                        </button>
                        <button v-if="!employe.deleted_at" 
                                @click="confirmDelete(employe.id, employe.nom + ' ' + employe.prenom)" 
                                class="btn btn-action btn-delete" 
                                title="Désactiver">
                          <i class="bi bi-trash"></i>
                        </button>
                        <button v-else 
                                @click="restoreEmploye(employe.id)" 
                                class="btn btn-action btn-restore" 
                                title="Réactiver">
                          <i class="bi bi-arrow-clockwise"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          
          <!-- Pagination -->
          <div v-if="employes.length > 0" class="card-footer pagination-footer">
            <div class="pagination-info">
              Affichage de <strong>{{ startItem }}</strong> à <strong>{{ endItem }}</strong> sur <strong>{{ totalItems }}</strong> employés
            </div>
            <nav>
              <ul class="pagination pagination-custom mb-0">
                <li class="page-item" :class="{disabled: currentPage === 1}">
                  <button class="page-link" @click="changePage(currentPage - 1)">
                    <i class="bi bi-chevron-left"></i>
                  </button>
                </li>
                
                <li v-for="page in pagesToShow" :key="page" 
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
    </div>

    <!-- ========================================
       MODAL D'AJOUT/MODIFICATION D'EMPLOYÉ - NOUVEAU DESIGN
       ======================================== -->
    <div v-if="showAddModal" class="modal fade show d-block modal-overlay" 
         @click.self="closeAddModal">
      <div class="modal-dialog modal-lg modal-centered">
        <div class="modal-content modal-modern">
          <div class="modal-header modal-header-modern">
            <h5 class="modal-title-modern">
              <div class="modal-icon">
                <i class="bi bi-person-badge-fill"></i>
              </div>
              <span>{{ isEditing ? 'Modifier l\'employé' : 'Nouvel Employé' }}</span>
            </h5>
            <button type="button" class="btn-close-modern" @click="closeAddModal">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
          <div class="modal-body modal-body-modern">
            <form @submit.prevent="saveEmploye">
              <!-- Section 1: Informations personnelles -->
              <div class="form-section">
                <h6 class="form-section-title">
                  <i class="bi bi-person-circle me-2"></i>
                  Informations Personnelles
                </h6>
                <div class="row g-4">
                  <div class="col-md-6">
                    <div class="form-group-modern">
                      <label class="form-label-modern">
                        <i class="bi bi-person me-1"></i>Nom <span class="required">*</span>
                      </label>
                      <input type="text" 
                             class="form-control-modern" 
                             v-model="formData.nom" 
                             required
                             :disabled="loading"
                             placeholder="Entrez le nom">
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <div class="form-group-modern">
                      <label class="form-label-modern">
                        <i class="bi bi-person me-1"></i>Prénom <span class="required">*</span>
                      </label>
                      <input type="text" 
                             class="form-control-modern" 
                             v-model="formData.prenom" 
                             required
                             :disabled="loading"
                             placeholder="Entrez le prénom">
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <div class="form-group-modern">
                      <label class="form-label-modern">
                        <i class="bi bi-card-text me-1"></i>CIN (Carte d'Identité) <span class="required">*</span>
                      </label>
                      <input type="text" 
                             class="form-control-modern" 
                             v-model="formData.cin" 
                             required
                             :disabled="loading"
                             placeholder="Ex: AA123456"
                             maxlength="20">
                      <small class="form-hint">Numéro unique d'identification nationale</small>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <div class="form-group-modern">
                      <label class="form-label-modern">
                        <i class="bi bi-envelope me-1"></i>Email <span class="required">*</span>
                      </label>
                      <input type="email" 
                             class="form-control-modern" 
                             v-model="formData.email" 
                             required
                             :disabled="loading || isEditing"
                             placeholder="employe@entreprise.com">
                      <small class="form-hint">L'employé utilisera cet email pour se connecter</small>
                      <small v-if="isEditing" class="form-hint form-hint-warning">
                        <i class="bi bi-lock-fill me-1"></i>
                        L'email ne peut pas être modifié pour des raisons de sécurité
                      </small>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Section 2: Informations professionnelles -->
              <div class="form-section">
                <h6 class="form-section-title">
                  <i class="bi bi-briefcase me-2"></i>
                  Informations Professionnelles
                </h6>
                <div class="row g-4">
                  <div class="col-md-6">
                    <div class="form-group-modern">
                      <label class="form-label-modern">
                        <i class="bi bi-person-badge me-1"></i>Poste
                      </label>
                      <input type="text" 
                             class="form-control-modern" 
                             v-model="formData.poste" 
                             :disabled="loading"
                             placeholder="Ex: Développeur, Chef de projet...">
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <div class="form-group-modern">
                      <label class="form-label-modern">
                        <i class="bi bi-building me-1"></i>Département
                      </label>
                      <select class="form-select-modern" v-model="formData.departement" :disabled="loading">
                        <option value="">Sélectionner un département</option>
                        <option v-for="dept in availableDepartements" :key="dept" :value="dept">
                          {{ dept }}
                        </option>
                      </select>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <div class="form-group-modern">
                      <label class="form-label-modern">
                        <i class="bi bi-calendar-event me-1"></i>Date d'embauche
                      </label>
                      <input type="date" 
                             class="form-control-modern date-input" 
                             v-model="formData.date_embauche"
                             :disabled="loading">
                      <small class="form-hint">Date d'entrée dans l'entreprise</small>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Section 3: Sécurité (création seulement) -->
              <div v-if="!isEditing" class="form-section form-section-password">
                <h6 class="form-section-title">
                  <i class="bi bi-shield-lock me-2"></i>
                  Sécurité
                </h6>
                <div class="alert alert-info-modern mb-4">
                  <i class="bi bi-info-circle-fill me-2"></i>
                  <strong>Important:</strong> Le mot de passe défini ici sera permanent. L'employé l'utilisera pour toutes ses connexions.
                </div>
                <div class="row g-4">
                  <div class="col-md-6">
                    <div class="form-group-modern">
                      <label class="form-label-modern">
                        <i class="bi bi-key me-1"></i>Mot de passe <span class="required">*</span>
                      </label>
                      <div class="password-input-wrapper">
                        <input :type="showPassword ? 'text' : 'password'" 
                               class="form-control-modern password-input" 
                               v-model="formData.mot_de_passe"
                               required
                               :disabled="loading"
                               minlength="6"
                               placeholder="Minimum 6 caractères"
                               @input="validatePassword">
                        <button type="button" 
                                class="password-toggle"
                                @click="showPassword = !showPassword"
                                tabindex="-1">
                          <i :class="showPassword ? 'bi bi-eye-slash-fill' : 'bi bi-eye-fill'"></i>
                        </button>
                      </div>
                      <small class="form-hint">Le mot de passe doit contenir au moins 6 caractères</small>
                      <div v-if="passwordError" class="error-message">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        {{ passwordError }}
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <div class="form-group-modern">
                      <label class="form-label-modern">
                        <i class="bi bi-check2-circle me-1"></i>Confirmation <span class="required">*</span>
                      </label>
                      <input type="password" 
                             class="form-control-modern" 
                             v-model="formData.confirmation_mdp"
                             required
                             :disabled="loading"
                             placeholder="Confirmez le mot de passe"
                             @input="validatePasswordConfirmation">
                      <div v-if="confirmationError" class="error-message">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        {{ confirmationError }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Actions du formulaire -->
              <div class="modal-footer-modern">
                <button type="button" 
                        class="btn btn-cancel-modern" 
                        @click="closeAddModal"
                        :disabled="loading">
                  <i class="bi bi-x-circle me-1"></i>
                  Annuler
                </button>
                
                <div class="action-group-modal">
                  <button v-if="isEditing" 
                          type="button" 
                          class="btn btn-outline-danger-modern-modal"
                          @click="confirmDelete(formData.id, formData.nom + ' ' + formData.prenom)"
                          :disabled="loading || formData.deleted_at">
                    <i class="bi bi-trash me-1"></i>
                    Supprimer
                  </button>
                  
                  <button type="submit" 
                          class="btn btn-submit-modern"
                          :disabled="loading || (!isEditing && formData.mot_de_passe !== formData.confirmation_mdp)">
                    <span v-if="loading">
                      <span class="spinner-border spinner-border-sm me-2"></span>
                      {{ isEditing ? 'Modification...' : 'Création...' }}
                    </span>
                    <span v-else>
                      <i class="bi me-1" :class="isEditing ? 'bi bi-check-circle-fill' : 'bi bi-plus-circle-fill'"></i>
                      {{ isEditing ? 'Mettre à jour' : 'Créer l\'employé' }}
                    </span>
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- ========================================
       MODAL DÉTAILS - DESIGN MODERNE
       ======================================== -->
    <div v-if="showDetailsModal" class="modal fade show d-block modal-overlay" 
         @click.self="closeDetailsModal">
      <div class="modal-dialog modal-lg modal-centered">
        <div class="modal-content modal-modern">
          <div class="modal-header modal-header-modern">
            <h5 class="modal-title-modern">
              <div class="modal-icon">
                <i class="bi bi-person-badge"></i>
              </div>
              <span>Détails de l'Employé</span>
            </h5>
            <button type="button" class="btn-close-modern" @click="closeDetailsModal">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
          
          <div v-if="selectedEmploye" class="modal-body modal-body-modern">
            <!-- En-tête avec nom complet -->
            <div class="mb-4 detail-header">
              <h4 class="name-text-large mb-3">{{ selectedEmploye.nom }} {{ selectedEmploye.prenom }}</h4>
              <div class="d-flex align-items-center gap-3 flex-wrap">
                <span class="badge-status-large" :class="selectedEmploye.deleted_at ? 'status-inactive' : 'status-active'">
                  <i class="bi me-1" :class="selectedEmploye.deleted_at ? 'bi-person-x' : 'bi-person-check'"></i>
                  {{ selectedEmploye.deleted_at ? 'Inactif' : 'Actif' }}
                </span>
                <small class="text-muted-pastel">
                  <i class="bi bi-hash me-1"></i>ID: {{ selectedEmploye.id }}
                </small>
                <small class="text-muted-pastel">
                  <i class="bi bi-envelope me-1"></i>
                  {{ selectedEmploye.email }}
                </small>
              </div>
            </div>
            
            <!-- Grille d'informations -->
            <div class="employe-grid mb-4">
              <!-- Informations personnelles -->
              <div class="employe-info-card">
                <div class="employe-info-icon bg-primary-light-pastel">
                  <i class="bi bi-person-vcard text-primary-pastel"></i>
                </div>
                <div class="employe-info-content">
                  <label class="employe-info-label">Informations personnelles</label>
                  <div class="info-items">
                    <div class="info-item">
                      <span class="info-label">CIN</span>
                      <span class="info-value badge-cin-inline">{{ selectedEmploye.cin }}</span>
                    </div>
                    <div class="info-item">
                      <span class="info-label">Nom complet</span>
                      <span class="info-value">{{ selectedEmploye.nom }} {{ selectedEmploye.prenom }}</span>
                    </div>
                    <div class="info-item">
                      <span class="info-label">Email</span>
                      <span class="info-value">{{ selectedEmploye.email }}</span>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Informations professionnelles -->
              <div class="employe-info-card">
                <div class="employe-info-icon bg-info-light-pastel">
                  <i class="bi bi-briefcase text-info-pastel"></i>
                </div>
                <div class="employe-info-content">
                  <label class="employe-info-label">Informations professionnelles</label>
                  <div class="info-items">
                    <div class="info-item">
                      <span class="info-label">Poste</span>
                      <span class="info-value">{{ selectedEmploye.poste || 'Non spécifié' }}</span>
                    </div>
                    <div class="info-item">
                      <span class="info-label">Département</span>
                      <span class="info-value">{{ selectedEmploye.departement || 'Non spécifié' }}</span>
                    </div>
                    <div class="info-item">
                      <span class="info-label">Date d'embauche</span>
                      <span class="info-value">{{ formatDate(selectedEmploye.date_embauche) }}</span>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Statut -->
              <div class="employe-info-card">
                <div class="employe-info-icon" :class="selectedEmploye.deleted_at ? 'bg-danger-light-pastel' : 'bg-success-light-pastel'">
                  <i :class="selectedEmploye.deleted_at ? 'bi-person-x text-danger-pastel' : 'bi-person-check text-success-pastel'"></i>
                </div>
                <div class="employe-info-content">
                  <label class="employe-info-label">Statut du compte</label>
                  <div class="info-items">
                    <div class="info-item">
                      <span class="info-label">Statut</span>
                      <span class="info-value badge-status" :class="selectedEmploye.deleted_at ? 'status-inactive' : 'status-active'">
                        {{ selectedEmploye.deleted_at ? 'Inactif' : 'Actif' }}
                      </span>
                    </div>
                    <div v-if="selectedEmploye.deleted_at" class="info-item">
                      <span class="info-label">Désactivé le</span>
                      <span class="info-value">{{ formatDate(selectedEmploye.deleted_at) }}</span>
                    </div>
                    <div v-else class="info-item">
                      <span class="info-label">Compte</span>
                      <span class="info-value text-success-pastel">Actif et accessible</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="modal-footer modal-footer-pastel">
            <button type="button" class="btn btn-outline-secondary-pastel" 
                    @click="closeDetailsModal">
              <i class="bi bi-x-circle me-1"></i>
              Fermer
            </button>
            <button type="button" class="btn btn-outline-warning-pastel" 
                    @click="editSelectedEmploye"
                    :disabled="!!selectedEmploye?.deleted_at">
              <i class="bi bi-pencil me-1"></i>
              Modifier
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ========================================
       MODAL SUPPRESSION - DESIGN MODERNE
       ======================================== -->
    <div v-if="showDeleteModal" class="modal fade show d-block modal-overlay" 
         @click.self="cancelDelete">
      <div class="modal-dialog modal-centered">
        <div class="modal-content modal-modern">
          <div class="modal-header modal-header-modern bg-danger-pastel">
            <h5 class="modal-title-modern">
              <div class="modal-icon">
                <i class="bi bi-exclamation-triangle"></i>
              </div>
              <span>Confirmation de suppression</span>
            </h5>
            <button type="button" class="btn-close-modern" @click="cancelDelete">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
          <div class="modal-body modal-body-modern">
            <div class="alert alert-warning-pastel mb-4">
              <i class="bi bi-exclamation-triangle me-2"></i>
              <strong>Attention :</strong> Vous êtes sur le point de désactiver un employé.
            </div>
            
            <div class="employe-preview mb-4">
              <div class="row">
                <div class="col-md-6">
                  <div class="preview-item">
                    <label class="preview-label">Employé</label>
                    <p class="preview-value">{{ employeToDeleteName }}</p>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="preview-item">
                    <label class="preview-label">Action</label>
                    <p class="preview-value text-danger-pastel">Désactivation du compte</p>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="alert alert-info-pastel">
              <i class="bi bi-info-circle me-2"></i>
              <strong>Note importante :</strong> Cette action rendra le compte inaccessible (soft delete). 
              Vous pourrez le réactiver ultérieurement depuis la liste des employés.
            </div>
          </div>
          <div class="modal-footer modal-footer-pastel">
            <button type="button" class="btn btn-outline-secondary-pastel" 
                    @click="cancelDelete">
              <i class="bi bi-x-circle me-1"></i>
              Annuler
            </button>
            <button type="button" class="btn btn-outline-danger-pastel" 
                    @click="deleteEmploye" :disabled="deleting">
              <span v-if="deleting">
                <span class="spinner-border spinner-border-sm me-1"></span>
                Traitement...
              </span>
              <span v-else>
                <i class="bi bi-trash me-1"></i>Confirmer la désactivation
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
  name: 'EmployesManagement',
  
  setup() {
    // États
    const loading = ref(false)
    const deleting = ref(false)
    const showAddModal = ref(false) // Changé de showForm à showAddModal
    const showDetailsModal = ref(false)
    const showDeleteModal = ref(false)
    const showSuccess = ref(false)
    const showPassword = ref(false)
    const isEditing = ref(false)
    
    // États pour validation mot de passe
    const passwordError = ref('')
    const confirmationError = ref('')
    
    // Données
    const employes = ref([])
    const selectedEmploye = ref(null)
    const successData = ref({})
    const employeToDeleteId = ref(null)
    const employeToDeleteName = ref('')
    const availableDepartements = ref([])
    const availablePostes = ref([])
    
    // Filtres et pagination
    const filters = ref({
      search: '',
      departement: '',
      statut: '',
      poste: '',
      page: 1,
      limit: 10
    })
    
    const currentPage = ref(1)
    const totalPages = ref(0)
    const totalItems = ref(0)
    
    const startItem = computed(() => {
      return (currentPage.value - 1) * filters.value.limit + 1
    })
    
    const endItem = computed(() => {
      const end = currentPage.value * filters.value.limit
      return end > totalItems.value ? totalItems.value : end
    })
    
    const hasActiveFilters = computed(() => {
      return filters.value.search || filters.value.departement || filters.value.statut
    })
    
    const activeCount = computed(() => {
      return employes.value.filter(employe => !employe.deleted_at).length
    })
    
    const inactiveCount = computed(() => {
      return employes.value.filter(employe => employe.deleted_at).length
    })
    
    // Timer pour la recherche avec debounce
    let searchTimer = null
    
    // Formulaire
    const formData = ref({
      id: null,
      nom: '',
      prenom: '',
      cin: '',
      email: '',
      mot_de_passe: '',
      confirmation_mdp: '',
      poste: '',
      departement: '',
      date_embauche: new Date().toISOString().split('T')[0],
      statut: 'actif'
    })
    
    // Méthodes utilitaires
    const formatDate = (dateString) => {
      if (!dateString) return '-'
      return new Date(dateString).toLocaleDateString('fr-FR')
    }
    
    // Méthodes de validation
    const validatePassword = () => {
      const password = formData.value.mot_de_passe
      
      if (!password) {
        passwordError.value = 'Le mot de passe est requis'
        return
      }
      
      if (password.length < 6) {
        passwordError.value = 'Le mot de passe doit contenir au moins 6 caractères'
        return
      }
      
      passwordError.value = ''
    }
    
    const validatePasswordConfirmation = () => {
      const password = formData.value.mot_de_passe
      const confirmation = formData.value.confirmation_mdp
      
      if (!confirmation) {
        confirmationError.value = 'La confirmation est requise'
        return
      }
      
      if (password !== confirmation) {
        confirmationError.value = 'Les mots de passe ne correspondent pas'
        return
      }
      
      confirmationError.value = ''
    }
    
    // Méthodes de recherche
    const onSearchInput = (event) => {
      // Clear existing timer
      clearTimeout(searchTimer)
      
      // Set a new timer to trigger search after 500ms of inactivity
      searchTimer = setTimeout(() => {
        if (filters.value.search !== '' || event.key === 'Enter') {
          filters.value.page = 1 // Reset to first page when searching
          loadEmployes()
        }
      }, 500)
    }
    
    // CRUD Operations
    const loadEmployes = async () => {
      loading.value = true
      try {
        console.log('📤 Chargement employés depuis la BDD avec filtres:', filters.value)
        
        // Préparer les paramètres
        const params = {
          page: filters.value.page,
          limit: filters.value.limit,
          search: filters.value.search,
          departement: filters.value.departement,
          statut: filters.value.statut,
          poste: filters.value.poste
        }
        
        // Nettoyer les paramètres vides
        Object.keys(params).forEach(key => {
          if (params[key] === '' || params[key] === null || params[key] === undefined) {
            delete params[key]
          }
        })
        
        console.log('📤 Paramètres envoyés:', params)
        
        // Appel API RÉEL vers votre backend
        const response = await adminService.getEmployes(params)
        
        console.log('📊 Réponse API employés:', response)
        
        if (response.success) {
          employes.value = response.data
          totalItems.value = response.pagination?.total_items || response.data.length
          totalPages.value = response.pagination?.total_pages || 1
          currentPage.value = response.pagination?.page || 1
          
          // Mettre à jour les départements disponibles depuis la réponse API
          if (response.available_filters?.departements) {
            availableDepartements.value = response.available_filters.departements
          }
          
          if (response.available_filters?.postes) {
            availablePostes.value = response.available_filters.postes
          }
        } else {
          throw new Error(response.message || 'Erreur lors du chargement des employés')
        }
        
      } catch (error) {
        console.error('❌ Erreur chargement employés:', error)
        alert('Erreur lors du chargement des employés: ' + error.message)
        
        // Fallback: Si l'API n'est pas encore implémentée, afficher un message
        employes.value = []
        totalItems.value = 0
        totalPages.value = 0
      } finally {
        loading.value = false
      }
    }
    
    const saveEmploye = async () => {
      // Validation
      if (!formData.value.nom || !formData.value.prenom || !formData.value.cin || !formData.value.email) {
        alert('Veuillez remplir tous les champs obligatoires')
        return
      }
      
      if (!isEditing.value && formData.value.mot_de_passe !== formData.value.confirmation_mdp) {
        alert('Les mots de passe ne correspondent pas')
        return
      }
      
      if (!isEditing.value && formData.value.mot_de_passe.length < 6) {
        alert('Le mot de passe doit contenir au moins 6 caractères')
        return
      }
      
      loading.value = true
      try {
        const dataToSend = { ...formData.value }
        
        // Nettoyer les données
        delete dataToSend.confirmation_mdp
        delete dataToSend.statut
        
        if (isEditing.value) {
          delete dataToSend.mot_de_passe
          
          // Appel API RÉEL pour la mise à jour
          console.log('📤 Mise à jour employé:', dataToSend)
          const response = await adminService.updateEmploye(formData.value.id, dataToSend)
          
          if (response.success) {
            resetForm()
            isEditing.value = false
            showAddModal.value = false
            
            // Recharger la liste
            loadEmployes()
            
            // Message de succès rapide (optionnel)
            alert('✅ Employé mis à jour avec succès !')
            
          } else {
            throw new Error(response.message)
          }
        } else {
          // Appel API RÉEL pour la création
          console.log('📤 Création employé:', dataToSend)
          const response = await adminService.createEmploye(dataToSend)
          
          if (response.success) {
            resetForm()
            showAddModal.value = false
            
            // Recharger la liste
            loadEmployes()
            
            // Message de succès rapide (optionnel)
            alert('✅ Employé créé avec succès !')
          } else {
            throw new Error(response.message)
          }
        }
        
      } catch (error) {
        console.error('❌ Erreur:', error)
        alert(`Erreur: ${error.message}`)
      } finally {
        loading.value = false
      }
    }
    
    const viewDetails = (employe) => {
      selectedEmploye.value = employe
      showDetailsModal.value = true
    }
    
    const editEmploye = async (employe) => {
      isEditing.value = true
      showAddModal.value = true
      
      try {
        // Charger les détails complets depuis l'API
        const response = await adminService.getEmploye(employe.id)
        
        if (response.success) {
          const employeDetails = response.data
          
          // Remplir le formulaire avec les données de l'employé
          formData.value = {
            id: employeDetails.id,
            nom: employeDetails.nom,
            prenom: employeDetails.prenom,
            cin: employeDetails.cin,
            email: employeDetails.email,
            mot_de_passe: '',
            confirmation_mdp: '',
            poste: employeDetails.poste || '',
            departement: employeDetails.departement || '',
            date_embauche: employeDetails.date_embauche || new Date().toISOString().split('T')[0],
            statut: employeDetails.deleted_at ? 'inactif' : 'actif'
          }
        } else {
          throw new Error('Erreur lors du chargement des détails')
        }
      } catch (error) {
        console.error('Erreur chargement détails:', error)
        alert('Erreur lors du chargement des détails de l\'employé')
        
        // Fallback: utiliser les données de base
        formData.value = {
          id: employe.id,
          nom: employe.nom,
          prenom: employe.prenom,
          cin: employe.cin,
          email: employe.email,
          mot_de_passe: '',
          confirmation_mdp: '',
          poste: employe.poste || '',
          departement: employe.departement || '',
          date_embauche: employe.date_embauche || new Date().toISOString().split('T')[0],
          statut: employe.deleted_at ? 'inactif' : 'actif'
        }
      }
    }
    
    const editSelectedEmploye = () => {
      if (selectedEmploye.value) {
        editEmploye(selectedEmploye.value)
        closeDetailsModal()
      }
    }
    
    const confirmDelete = (id, name) => {
      employeToDeleteId.value = id
      employeToDeleteName.value = name
      showDeleteModal.value = true
    }
    
    const deleteEmploye = async () => {
      deleting.value = true
      try {
        // Appel API RÉEL pour la suppression
        console.log('🗑️ Suppression employé ID:', employeToDeleteId.value)
        const response = await adminService.deleteEmploye(employeToDeleteId.value, 'soft_delete')
        
        if (response.success) {
          showDeleteModal.value = false
          deleting.value = false
          employeToDeleteId.value = null
          employeToDeleteName.value = ''
          
          alert('Employé désactivé avec succès')
          loadEmployes() // Recharger la liste
        } else {
          throw new Error(response.message)
        }
        
      } catch (error) {
        console.error('❌ Erreur suppression:', error)
        alert('Erreur lors de la suppression: ' + error.message)
        deleting.value = false
      }
    }
    
    const restoreEmploye = async (id) => {
      try {
        // Appel API RÉEL pour la restauration
        console.log('🔄 Restauration employé ID:', id)
        const response = await adminService.restoreEmploye(id)
        
        if (response.success) {
          alert('Employé réactivé avec succès')
          loadEmployes() // Recharger la liste
        } else {
          throw new Error(response.message)
        }
        
      } catch (error) {
        console.error('❌ Erreur restauration:', error)
        alert('Erreur lors de la restauration: ' + error.message)
      }
    }
    
    const cancelDelete = () => {
      showDeleteModal.value = false
      employeToDeleteId.value = null
      employeToDeleteName.value = ''
    }
    
    const closeDetailsModal = () => {
      showDetailsModal.value = false
      selectedEmploye.value = null
    }
    
    // NOUVELLES MÉTHODES POUR LE MODAL D'AJOUT
    const openAddModal = () => {
      isEditing.value = false
      showPassword.value = false
      passwordError.value = ''
      confirmationError.value = ''
      resetForm()
      showAddModal.value = true
    }
    
    const closeAddModal = () => {
      showAddModal.value = false
      isEditing.value = false
      showPassword.value = false
      passwordError.value = ''
      confirmationError.value = ''
      resetForm()
    }
    
    const resetForm = () => {
      formData.value = {
        id: null,
        nom: '',
        prenom: '',
        cin: '',
        email: '',
        mot_de_passe: '',
        confirmation_mdp: '',
        poste: '',
        departement: '',
        date_embauche: new Date().toISOString().split('T')[0],
        statut: 'actif'
      }
    }
    
    const resetFilters = () => {
      filters.value = {
        search: '',
        departement: '',
        statut: '',
        poste: '',
        page: 1,
        limit: 10
      }
      loadEmployes()
    }
    
    const changePage = (page) => {
      if (page < 1 || page > totalPages.value) return
      currentPage.value = page
      filters.value.page = page
      loadEmployes()
    }
    
    const pagesToShow = computed(() => {
      const pages = []
      const maxPagesToShow = 5
      let start = Math.max(1, currentPage.value - Math.floor(maxPagesToShow / 2))
      let end = start + maxPagesToShow - 1
      
      if (end > totalPages.value) {
        end = totalPages.value
        start = Math.max(1, end - maxPagesToShow + 1)
      }
      
      for (let i = start; i <= end; i++) {
        pages.push(i)
      }
      
      return pages
    })
    
    // Initialisation
    onMounted(() => {
      loadEmployes()
    })
    
    return {
      // États
      loading,
      deleting,
      showAddModal, // Changé de showForm à showAddModal
      showDetailsModal,
      showDeleteModal,
      showSuccess,
      showPassword,
      isEditing,
      passwordError,
      confirmationError,
      
      // Données
      employes,
      selectedEmploye,
      successData,
      employeToDeleteName,
      availableDepartements,
      availablePostes,
      
      // Filtres & Pagination
      filters,
      currentPage,
      totalPages,
      totalItems,
      
      // Formulaire
      formData,
      
      // Computed
      pagesToShow,
      
      // Méthodes
      formatDate,
      loadEmployes,
      saveEmploye,
      viewDetails,
      editEmploye,
      editSelectedEmploye,
      confirmDelete,
      deleteEmploye,
      restoreEmploye,
      cancelDelete,
      closeDetailsModal,
      resetFilters,
      changePage,
      onSearchInput,
      startItem,
      endItem,
      hasActiveFilters,
      activeCount,
      inactiveCount,
      
      // Nouvelles méthodes pour le modal
      openAddModal,
      closeAddModal,
      validatePassword,
      validatePasswordConfirmation
    }
  }
}
</script>

<style scoped>
/* ========================================
   VARIABLES ET BASE - MÊME QUE INSCRIPTIONS
   ======================================== */
:root {
  /* Variables CSS identiques à la page inscriptions */
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

.employes-management {
  min-height: calc(100vh - 200px);
  background: linear-gradient(135deg, #f5f7fa 0%, #e4e9f2 100%);
  padding-bottom: 3rem;
}

/* ========================================
   EN-TÊTE - IDENTIQUE À INSCRIPTIONS
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
  color: black;
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
  color:black!important;
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
   BOUTON "NOUVEL EMPLOYÉ" - NOUVEAU DESIGN
   ======================================== */
.btn-add-new-employe {
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

.btn-add-new-employe:hover {
  background: linear-gradient(135deg, #357ABD 0%, #2868A8 100%);
  transform: translateY(-3px);
  box-shadow: 0 6px 25px rgba(74, 144, 226, 0.5);
  color: #FFFFFF;
}

.btn-add-new-employe:active {
  transform: translateY(-1px);
}

.btn-add-new-employe i {
  font-size: 1.1rem;
}

/* ========================================
   STATISTIQUES - MÊME STYLE
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

.stat-total::before { background: #4A90E2; }
.stat-active::before { background: #10b981; }
.stat-inactive::before { background: #ef4444; }
.stat-departments::before { background: #8b5cf6; }

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

.stat-total .stat-icon { background: rgba(74, 144, 226, 0.1); color: #4A90E2; }
.stat-active .stat-icon { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.stat-inactive .stat-icon { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
.stat-departments .stat-icon { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }

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
   FILTRES - MÊME STYLE
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

.btn-outline-secondary-modern {
  border: 2px solid var(--pastel-secondary);
  color: #9B9B9B;
  background: transparent;
  transition: all 0.3s ease;
  border-radius: 8px;
  padding: 0.625rem 1.5rem;
  font-weight: 600;
  font-size: 0.95rem;
}

.btn-outline-secondary-modern:hover {
  background: var(--pastel-secondary-light);
  border-color: #BEBEBE;
  color: #7D7D7D;
  transform: translateY(-2px);
}

/* ========================================
   TABLEAU EMPLOYÉS
   ======================================== */
.employes-table-card {
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

.employe-row {
  transition: all 0.2s ease;
}

.employe-row:hover {
  background: var(--gray-50);
}

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

.employe-avatar.inactive {
  background: linear-gradient(135deg, var(--pastel-secondary-light) 0%, var(--pastel-secondary) 100%);
  opacity: 0.7;
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

.employe-id {
  font-size: 0.75rem;
  color: var(--gray-500);
  background: var(--gray-100);
  padding: 0.125rem 0.5rem;
  border-radius: 4px;
}

.badge-cin {
  background: linear-gradient(135deg, var(--pastel-warning-light) 0%, var(--pastel-warning) 100%);
  color: #F4B860;
  padding: 0.375rem 0.75rem;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.8rem;
  border: 1px solid rgba(244, 184, 96, 0.3);
}

.badge-department {
  background: linear-gradient(135deg, var(--pastel-info-light) 0%, var(--pastel-info) 100%);
  color: #4A90E2;
  padding: 0.25rem 0.625rem;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.75rem;
}

.badge-status {
  padding: 0.5rem 1rem;
  border-radius: var(--border-radius-sm);
  font-weight: 600;
  font-size: 0.8125rem;
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
}

.status-active {
  background: rgba(16, 185, 129, 0.1);
  color: #059669;
  border: 1px solid rgba(16, 185, 129, 0.3);
}

.status-inactive {
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.professional-info {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.info-label {
  color: var(--gray-700);
  font-size: 0.875rem;
}

.date-info {
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

.inactive-date {
  margin-top: 0.25rem;
}

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

.btn-edit {
  background: rgba(245, 158, 11, 0.05);
  border-color: rgba(245, 158, 11, 0.3);
  color: var(--warning-color);
}

.btn-edit:hover:not(:disabled) {
  background: var(--warning-color);
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

.btn-restore {
  background: rgba(16, 185, 129, 0.05);
  border-color: rgba(16, 185, 129, 0.3);
  color: var(--success-color);
}

.btn-restore:hover:not(:disabled) {
  background: var(--success-color);
  color: white;
  transform: translateY(-2px);
  box-shadow: var(--shadow-sm);
}

.btn-action:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

/* ========================================
   PAGINATION - MÊME STYLE
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
   MODAL D'AJOUT/MODIFICATION - NOUVEAU DESIGN
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

.modal-header-modern.bg-danger-pastel {
  background: linear-gradient(135deg, #FFB3BA 0%, #E6757E 100%);
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

/* Sections de formulaire */
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
  border-color: #FFE5B4;
  background: linear-gradient(135deg, #FFFBF5 0%, #FFFFFF 100%);
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

/* Groupes de formulaire */
.form-group-modern {
  margin-bottom: 1.5rem;
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

/* Inputs modernes */
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

.form-control-modern:disabled {
  background: #F0F4F8;
  color: #7D8DA6;
  cursor: not-allowed;
  opacity: 0.7;
}

.form-control-modern::placeholder {
  color: #A5B4C6;
  font-weight: 400;
}

.form-select-modern {
  border: 2px solid #E5F2F8;
  border-radius: 12px;
  padding: 0.875rem 1.125rem;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  background: #F8FBFD;
  color: #2C3E50;
  font-weight: 500;
  width: 100%;
  cursor: pointer;
}

.form-select-modern:focus {
  border-color: #4A90E2;
  background: #FFFFFF;
  box-shadow: 0 0 0 4px rgba(74, 144, 226, 0.12);
  outline: none;
}

/* Wrapper pour input de mot de passe */
.password-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.password-input {
  padding-right: 3.5rem !important;
}

.password-toggle {
  position: absolute;
  right: 8px;
  top: 50%;
  transform: translateY(-50%);
  background: #E5F2F8;
  border: none;
  border-radius: 8px;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  color: #4A90E2;
}

.password-toggle:hover {
  background: #4A90E2;
  color: #FFFFFF;
  transform: translateY(-50%) scale(1.05);
}

.password-toggle i {
  font-size: 1.1rem;
}

/* Messages d'erreur */
.error-message {
  color: #E74C3C;
  font-size: 0.875rem;
  font-weight: 500;
  margin-top: 0.5rem;
  display: flex;
  align-items: center;
}

.error-message i {
  font-size: 0.95rem;
}

/* Hints de formulaire */
.form-hint {
  color: #7D8DA6;
  font-size: 0.85rem;
  margin-top: 0.5rem;
  display: block;
  font-style: italic;
}

.form-hint-warning {
  color: #F39C12;
  font-weight: 500;
  font-style: normal;
}

.form-hint-warning i {
  font-size: 0.9rem;
}

/* Alert moderne */
.alert-info-modern {
  background: linear-gradient(135deg, #E3F2FD 0%, #F0F8FF 100%);
  border: 2px solid #4A90E2;
  border-left: 5px solid #4A90E2;
  border-radius: 12px;
  padding: 1rem 1.25rem;
  color: #2C3E50;
  font-size: 0.9rem;
}

.alert-info-modern i {
  color: #4A90E2;
  font-size: 1.1rem;
}

.alert-info-modern strong {
  color: #357ABD;
}

/* Footer du modal */
.modal-footer-modern {
  background: #FFFFFF;
  border-top: 2px solid #E5F2F8;
  padding: 1.75rem 2.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.action-group-modal {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}

/* Bouton Annuler */
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

/* Bouton Supprimer dans le modal */
.btn-outline-danger-modern-modal {
  border: 2px solid var(--pastel-danger);
  color: #FF8B94;
  background: transparent;
  transition: all 0.3s ease;
  border-radius: 8px;
  padding: 0.875rem 1.5rem;
  font-weight: 600;
  font-size: 0.95rem;
}

.btn-outline-danger-modern-modal:hover:not(:disabled) {
  background: var(--pastel-danger-light);
  border-color: #FF8B94;
  color: #E6757E;
  transform: translateY(-2px);
}

/* Bouton Soumettre */
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

.btn-submit-modern i {
  font-size: 1.1rem;
}

/* ========================================
   MODAL DÉTAILS - STYLE EXISTANT
   ======================================== */
.employe-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.employe-info-card {
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

.employe-info-card:hover {
  border-color: #D4EBF2;
  box-shadow: 0 4px 12px rgba(74, 144, 226, 0.1);
  transform: translateY(-2px);
}

.employe-info-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.employe-info-content {
  flex: 1;
}

.employe-info-label {
  color: var(--pastel-text-muted);
  font-size: 0.85rem;
  font-weight: 500;
  margin-bottom: 0.75rem;
}

.info-items {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.info-label {
  color: var(--pastel-text-muted);
  font-size: 0.85rem;
  font-weight: 500;
}

.info-value {
  color: var(--pastel-text-primary);
  font-size: 0.95rem;
  font-weight: 600;
}

.badge-cin-inline {
  background: linear-gradient(135deg, var(--pastel-warning-light) 0%, var(--pastel-warning) 100%);
  color: #F4B860;
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
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

.bg-danger-light-pastel {
  background: linear-gradient(135deg, var(--pastel-danger-light) 0%, #FFFFFF 100%);
}

/* Badge de statut amélioré */
.badge-status-large {
  display: inline-flex;
  align-items: center;
  padding: 0.75rem 1.5rem;
  border-radius: 25px;
  font-weight: 700;
  font-size: 1rem;
}

/* Alertes pastel */
.alert-warning-pastel {
  background: linear-gradient(135deg, var(--pastel-warning-light) 0%, #FFFFFF 100%);
  border: 2px solid var(--pastel-warning);
  border-left: 5px solid #F4B860;
  color: #F4B860;
  border-radius: 12px;
  padding: 1rem 1.25rem;
  margin: 1rem 0;
}

.alert-info-pastel {
  background: linear-gradient(135deg, var(--pastel-info-light) 0%, #FFFFFF 100%);
  border: 2px solid var(--pastel-info);
  border-left: 5px solid #4A90E2;
  color: #4A90E2;
  border-radius: 12px;
  padding: 1rem 1.25rem;
  margin: 1rem 0;
}

.alert-danger-pastel {
  background: linear-gradient(135deg, var(--pastel-danger-light) 0%, #FFFFFF 100%);
  border: 2px solid var(--pastel-danger);
  border-left: 5px solid var(--pastel-danger);
  color: #E6757E;
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
.btn-outline-warning-pastel {
  border: 2px solid var(--pastel-warning);
  color: #F4B860;
  background: transparent;
  transition: all 0.3s ease;
  border-radius: 8px;
  padding: 0.625rem 1.5rem;
  font-weight: 600;
  font-size: 0.95rem;
}

.btn-outline-warning-pastel:hover:not(:disabled) {
  background: var(--pastel-warning-light);
  border-color: #F4B860;
  color: #F4B860;
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

.btn-outline-danger-pastel:hover:not(:disabled) {
  background: var(--pastel-danger-light);
  border-color: #FF8B94;
  color: #E6757E;
  transform: translateY(-2px);
}

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
  color: #4A90E2 !important;
}

.text-muted-pastel {
  color: var(--pastel-text-muted) !important;
}

/* ========================================
   PRÉVISUALISATION
   ======================================== */
.employe-preview {
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
   ANIMATIONS
   ======================================== */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
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
    gap: 1rem;
  }
  
  .page-title {
    font-size: 1.5rem;
  }
  
  .btn-add-new-employe {
    width: 100%;
    padding: 1rem;
    font-size: 0.9rem;
    margin-top: 1rem;
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
  
  .form-section-title {
    font-size: 1rem;
    margin-bottom: 1.25rem;
  }
  
  .form-label-modern {
    font-size: 0.9rem;
  }
  
  .form-control-modern {
    font-size: 0.9rem;
    padding: 0.75rem 1rem;
  }
  
  .password-toggle {
    width: 36px;
    height: 36px;
  }
  
  .alert-info-modern {
    font-size: 0.85rem;
    padding: 0.875rem 1rem;
  }
  
  .modal-footer-modern {
    padding: 1.25rem 1.5rem;
    flex-direction: column;
  }
  
  .action-group-modal {
    width: 100%;
    justify-content: space-between;
    margin-top: 1rem;
  }
  
  .btn-cancel-modern,
  .btn-outline-danger-modern-modal,
  .btn-submit-modern {
    width: 100%;
    padding: 0.875rem 1.5rem;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
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
  
  .employe-info {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .employe-grid {
    grid-template-columns: 1fr;
  }
}
</style>