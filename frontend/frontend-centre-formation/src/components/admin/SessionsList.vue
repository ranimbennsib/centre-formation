<template>
  <div class="sessions-list">
    <!-- En-tête avec gradient -->
    <div class="page-header">
      <div class="container-fluid">
        <div class="row align-items-center">
          <div class="col-lg-8">
            <div class="header-content">
              <div class="icon-wrapper">
                <i class="bi bi-calendar-event"></i>
              </div>
              <div>
                <h1 class="page-title primary-text mb-2">Gestion des Sessions</h1>
                <p class="page-subtitle mb-0">
                  <span class="badge badge-count dark-text">{{ sessions.length }}</span>
                  session(s) trouvée(s)
                </p>
              </div>
            </div>
          </div>
          <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <button @click="openAddModal" class="btn btn-create" :disabled="formateurs.length === 0">
              <i class="bi bi-plus-circle me-2"></i>
              Nouvelle Session
              <span v-if="formateurs.length === 0" class="ms-2 badge bg-warning">!</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  

    <div class="container-fluid mt-4">
      <!-- Avertissement si aucun formateur -->
      <div v-if="formateurs.length === 0 && !loading" class="alert alert-warning-custom mb-4">
        <div class="alert-icon">
          <i class="bi bi-exclamation-triangle"></i>
        </div>
        <div class="alert-content">
          <h6 class="alert-title mb-1">Attention</h6>
          <p class="mb-0">
            Aucun formateur n'est disponible. Vous devez créer au moins un formateur avant de pouvoir créer une session.
            <a href="/admin/formateurs" class="alert-link">Créer un formateur</a>
          </p>
        </div>
      </div>
      
      <!-- Statistiques rapides -->
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
            <div class="stat-chart">
              <div class="sparkline"></div>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="stat-card stat-accepted">
            <div class="stat-icon">
              <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-content">
              <h3 class="stat-value">{{ stats.acceptees || 0 }}</h3>
              <p class="stat-label">Acceptées</p>
            </div>
            <div class="stat-chart">
              <div class="sparkline"></div>
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
            <div class="stat-chart">
              <div class="sparkline"></div>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="stat-card stat-places">
            <div class="stat-icon">
              <i class="bi bi-people"></i>
            </div>
            <div class="stat-content">
              <h3 class="stat-value">{{ stats.places_restantes || 0 }}</h3>
              <p class="stat-label">Places restantes</p>
            </div>
            <div class="stat-chart">
              <div class="sparkline"></div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Filtres et recherche -->
      <div class="filters-card mb-4">
        <div class="card-body">
          <div class="row g-3">
            <div class="col-lg-6 col-md-6">
              <div class="search-box">
                <i class="bi bi-search search-icon"></i>
                <input 
                  type="text" 
                  class="form-control form-control-search" 
                  placeholder="Rechercher par formation ou salle..."
                  v-model="search"
                  @input="handleSearch"
                />
              </div>
            </div>
            <div class="col-lg-3 col-md-3">
              <select class="form-select form-select-custom" v-model="filterStatus" @change="loadSessions">
                <option value="">Tous les statuts</option>
                <option value="EN_ATTENTE">En attente</option>
                <option value="ACCEPTEE">Acceptées</option>
                <option value="REFUSEE">Refusées</option>
              </select>
            </div>
            <div class="col-lg-3 col-md-3">
              <select class="form-select form-select-custom" v-model="itemsPerPage" @change="loadSessions">
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
                <label class="form-label-custom">Formation</label>
                <select class="form-select form-select-custom" v-model="filterFormation" @change="loadSessions">
                  <option value="">Toutes les formations</option>
                  <option v-for="formation in formations" :value="formation.id" :key="formation.id">
                    {{ formation.titre }}
                  </option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label-custom">Formateur</label>
                <select class="form-select form-select-custom" v-model="filterFormateur" @change="loadSessions">
                  <option value="">Tous les formateurs</option>
                  <option v-for="formateur in formateurs" :value="formateur.id" :key="formateur.id">
                    {{ formateur.prenom }} {{ formateur.nom }}
                  </option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label-custom">Période</label>
                <div class="date-range-group">
                  <input type="date" class="form-control form-control-custom" v-model="filterDateFrom" @change="loadSessions">
                  <span class="date-separator">à</span>
                  <input type="date" class="form-control form-control-custom" v-model="filterDateTo" @change="loadSessions">
                </div>
              </div>
            </div>
          </transition>
          
          <div class="mt-3">
            <button class="btn btn-outline-primary btn-sm btn-filter-toggle" @click="toggleAdvancedFilters">
              <i class="bi bi-funnel me-2"></i>
              {{ showAdvancedFilters ? 'Masquer les filtres' : 'Plus de filtres' }}
            </button>
          </div>
        </div>
      </div>
      
      <!-- Liste des sessions -->
      <div class="sessions-table-card">
        <div class="card-body p-0">
          <div v-if="loading" class="loading-state">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Chargement...</span>
            </div>
            <p class="mt-3 text-muted">Chargement des sessions...</p>
          </div>
          
          <div v-else-if="sessions.length === 0" class="empty-state">
            <div class="empty-icon">
              <i class="bi bi-calendar-event"></i>
            </div>
            <h5 class="empty-title">Aucune session trouvée</h5>
            <p class="empty-text">Cliquez sur "Nouvelle Session" pour en ajouter une.</p>
          </div>
          
          <div v-else class="table-responsive">
            <table class="table table-custom mb-0">
              <thead>
                <tr>
                  <th>Formation</th>
                  <th>Dates</th>
                  <th>Lieu</th>
                  <th>Places</th>
                  <th>Formateur</th>
                  <th>Statut</th>
                  <th class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="session in sessions" :key="session.id" class="session-row">
                  <td>
                    <div class="formation-info">
                      <h6 class="formation-title">{{ session.formation_titre }}</h6>
                      <span class="formation-id">ID: {{ session.id }}</span>
                    </div>
                  </td>
                  <td>
                    <div class="dates-info">
                      <div class="date-item">
                        <span class="date-label">Début</span>
                        <span class="date-value">{{ formatDate(session.date_debut) }}</span>
                      </div>
                      <div class="date-item">
                        <span class="date-label">Fin</span>
                        <span class="date-value">{{ formatDate(session.date_fin) }}</span>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="badge badge-location">
                      <i class="bi bi-geo-alt me-1"></i>
                      {{ session.salle }}
                    </span>
                  </td>
                  <td>
                    <div class="places-wrapper">
                      <div class="progress progress-custom">
                        <div class="progress-bar" 
                             :class="getPlacesClass(session)"
                             :style="{ width: getPlacesPercentage(session) + '%' }">
                        </div>
                      </div>
                      <span class="places-text">{{ session.places_restantes }}/{{ session.places_max }}</span>
                    </div>
                  </td>
                  <td>
                    <div v-if="session.formateur_nom && session.formateur_prenom" class="formateur-info">
                      <div class="formateur-avatar">
                        <i class="bi bi-person-circle"></i>
                      </div>
                      <span class="formateur-name">
                        {{ session.formateur_prenom }} {{ session.formateur_nom }}
                      </span>
                    </div>
                    <div v-else class="formateur-empty">
                      <i class="bi bi-person-x me-1"></i>
                      Non assigné
                    </div>
                  </td>
                  <td>
                    <span class="badge badge-status" :class="getStatusClass(session.statut_formateur)">
                      <i class="bi me-1" :class="getStatusIconClass(session.statut_formateur)"></i>
                      {{ getStatusText(session.statut_formateur) }}
                    </span>
                    <div v-if="session.statut_formateur === 'REFUSEE' && session.raison_refus_formateur" 
                         class="refusal-reason">
                      <i class="bi bi-exclamation-circle me-1"></i>
                      {{ session.raison_refus_formateur }}
                    </div>
                  </td>
                  <td>
                    <div class="action-buttons">
                      <button @click="viewDetails(session)" 
                              class="btn btn-action btn-view" 
                              title="Voir détails">
                        <i class="bi bi-eye"></i>
                      </button>
                      <button @click="editSession(session)" 
                              class="btn btn-action btn-edit" 
                              title="Modifier"
                              :disabled="session.statut_formateur === 'REFUSEE'">
                        <i class="bi bi-pencil"></i>
                      </button>
                      <!-- BOUTON D'ASSIGNATION CORRIGÉ -->
                      <button @click="handleAssignClick(session)" 
                              class="btn btn-action btn-assign" 
                              :title="getAssignButtonTooltip(session)">
                        <i class="bi bi-person-plus"></i>
                        <span v-if="session.statut_formateur === 'REFUSEE'" class="badge bg-warning badge-sm ms-1">!</span>
                      </button>
                      <button @click="cancelSession(session)" 
                              class="btn btn-action btn-cancel" 
                              title="Annuler"
                              :disabled="session.statut_formateur === 'REFUSEE'">
                        <i class="bi bi-x-circle"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        
        <!-- Pagination -->
        <div v-if="sessions.length > 0" class="card-footer pagination-footer">
          <div class="pagination-info">
            Affichage de <strong>{{ startItem }}</strong> à <strong>{{ endItem }}</strong> sur <strong>{{ totalItems }}</strong> sessions
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
    
    <!-- Modal Ajout/Modification de Session - STYLE MIS À JOUR -->
    <div v-if="showModal" class="modal fade show d-block modal-overlay" 
         @click.self="closeModal">
      <div class="modal-dialog modal-lg modal-centered">
        <div class="modal-content modal-modern">
          <div class="modal-header modal-header-modern">
            <h5 class="modal-title-modern">
              <div class="modal-icon">
                <i :class="isEditing ? 'bi bi-calendar-check' : 'bi bi-calendar-plus'"></i>
              </div>
              <span>{{ isEditing ? 'Modifier la session' : 'Nouvelle Session' }}</span>
            </h5>
            <button type="button" class="btn-close-modern" @click="closeModal">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
          <div class="modal-body modal-body-modern">
            <form @submit.prevent="saveSession">
              
              <!-- Section Formation -->
              <div class="form-section">
                <h6 class="form-section-title">
                  <i class="bi bi-mortarboard me-2"></i>
                  Formation
                </h6>
                <div class="form-group-modern">
                  <label class="form-label-modern">
                    <i class="bi bi-book me-1"></i> Formation <span class="required">*</span>
                  </label>
                  <select class="form-control-modern" v-model.number="formData.id_formation" required :disabled="saving">
                    <option value="">Sélectionnez une formation</option>
                    <option v-for="formation in formations" :value="formation.id" :key="formation.id">
                      {{ formation.titre }} ({{ formation.duree_heures }}h)
                    </option>
                  </select>
                  <small class="form-hint">Choisissez la formation pour cette session</small>
                </div>
              </div>

              <!-- Section Formateur -->
              <div class="form-section">
                <h6 class="form-section-title">
                  <i class="bi bi-person-badge me-2"></i>
                  Formateur
                </h6>
                <div class="form-group-modern">
                  <label class="form-label-modern">
                    <i class="bi bi-person me-1"></i> Formateur <span class="required">*</span>
                  </label>
                  <select class="form-control-modern" 
                          v-model.number="formData.id_formateur" 
                          :disabled="saving || formateurs.length === 0"
                          required>
                    <option v-if="formateurs.length > 0" value="" disabled>
                      Sélectionnez un formateur
                    </option>
                    <option v-if="formateurs.length === 0" value="" disabled>
                      Aucun formateur disponible
                    </option>
                    <option v-for="formateur in formateurs" 
                            :value="formateur.id" 
                            :key="formateur.id">
                      {{ formateur.prenom }} {{ formateur.nom }} - {{ formateur.specialite || 'Sans spécialité' }}
                    </option>
                  </select>
                  <small v-if="formateurs.length === 0" class="form-hint form-hint-warning">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Aucun formateur disponible. Veuillez d'abord créer un formateur.
                  </small>
                </div>
              </div>

              <!-- Section Dates -->
              <div class="form-section">
                <h6 class="form-section-title">
                  <i class="bi bi-calendar-range me-2"></i>
                  Dates
                </h6>
                <div class="row g-4">
                  <div class="col-md-6">
                    <div class="form-group-modern">
                      <label class="form-label-modern">
                        <i class="bi bi-calendar-plus me-1"></i> Date de début <span class="required">*</span>
                      </label>
                      <input type="datetime-local" class="form-control-modern" v-model="formData.date_debut" 
                             required :disabled="saving" min="2024-01-01T00:00">
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <div class="form-group-modern">
                      <label class="form-label-modern">
                        <i class="bi bi-calendar-check me-1"></i> Date de fin <span class="required">*</span>
                      </label>
                      <input type="datetime-local" class="form-control-modern" v-model="formData.date_fin" 
                             required :disabled="saving" :min="formData.date_debut">
                    </div>
                  </div>
                </div>
              </div>

              <!-- Section Lieu et Places -->
              <div class="form-section">
                <h6 class="form-section-title">
                  <i class="bi bi-geo-alt me-2"></i>
                  Lieu & Places
                </h6>
                <div class="row g-4">
                  <div class="col-md-8">
                    <div class="form-group-modern">
                      <label class="form-label-modern">
                        <i class="bi bi-building me-1"></i> Salle/Lieu <span class="required">*</span>
                      </label>
                      <input type="text" class="form-control-modern" v-model="formData.salle" 
                             required :disabled="saving" placeholder="Ex: Salle A, Amphi B, etc.">
                      <small class="form-hint">Indiquez le lieu où se déroulera la session</small>
                    </div>
                  </div>
                  
                  <div class="col-md-4">
                    <div class="form-group-modern">
                      <label class="form-label-modern">
                        <i class="bi bi-people me-1"></i> Places maximum <span class="required">*</span>
                      </label>
                      <input type="number" class="form-control-modern" v-model.number="formData.places_max" 
                             required :disabled="saving" min="1" max="500">
                      <small class="form-hint">Nombre maximum de participants</small>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Section Description -->
              <div class="form-section">
                <h6 class="form-section-title">
                  <i class="bi bi-text-paragraph me-2"></i>
                  Description
                </h6>
                <div class="form-group-modern">
                  <label class="form-label-modern">
                    <i class="bi bi-chat-left-text me-1"></i> Description (optionnelle)
                  </label>
                  <textarea class="form-control-modern" v-model="formData.description" 
                            rows="4" :disabled="saving"
                            placeholder="Description détaillée de la session..."></textarea>
                  <small class="form-hint">Vous pouvez ajouter des informations complémentaires sur cette session</small>
                </div>
              </div>

              <!-- Section Statut (uniquement en modification) -->
              <div v-if="isEditing" class="form-section form-section-password">
                <h6 class="form-section-title">
                  <i class="bi bi-info-circle me-2"></i>
                  Statut de la session
                </h6>
                <div class="alert alert-info-modern mb-4">
                  <i class="bi bi-exclamation-circle-fill me-2"></i>
                  <strong>Statut actuel :</strong> 
                  <span class="badge-status-large ms-2" :class="getStatusClass(formData.statut_formateur)">
                    <i class="bi me-1" :class="getStatusIconClass(formData.statut_formateur)"></i>
                    {{ getStatusText(formData.statut_formateur) }}
                  </span>
                </div>
                
                <div v-if="formData.statut_formateur === 'EN_ATTENTE'" class="alert alert-warning mb-3">
                  <i class="bi bi-clock-history me-2"></i>
                  <strong>En attente de réponse du formateur.</strong> Vous pouvez modifier les informations pratiques.
                </div>
                
                <div v-if="formData.statut_formateur === 'ACCEPTEE'" class="alert alert-success mb-3">
                  <i class="bi bi-check-circle me-2"></i>
                  <strong>Session acceptée par le formateur.</strong>
                  <span v-if="formData.places_restantes > 0">Les inscriptions sont ouvertes.</span>
                  <div class="mt-2 text-danger">
                    <strong>Note :</strong> Impossible de réassigner un formateur à une session acceptée.
                  </div>
                </div>
                
                <div v-if="formData.statut_formateur === 'REFUSEE'" class="alert alert-danger-pastel mb-3">
                  <i class="bi bi-x-circle me-2"></i>
                  <strong>Session refusée par le formateur.</strong>
                  <div v-if="formData.raison_refus_formateur" class="mt-2">
                    <strong>Raison du refus :</strong> <em>"{{ formData.raison_refus_formateur }}"</em>
                  </div>
                  <div class="mt-2">
                    <strong>Action possible :</strong> Réassigner à un autre formateur
                  </div>
                </div>
              </div>
              
              <div class="modal-footer-modern">
                <button type="button" class="btn btn-cancel-modern" 
                        @click="closeModal" :disabled="saving">
                  <i class="bi bi-x-circle me-1"></i>
                  Annuler
                </button>
                <button type="submit" class="btn btn-submit-modern" :disabled="saving || formateurs.length === 0">
                  <span v-if="saving">
                    <span class="spinner-border spinner-border-sm me-2"></span>
                    {{ isEditing ? 'Modification...' : 'Création...' }}
                  </span>
                  <span v-else>
                    <i :class="isEditing ? 'bi bi-check-circle-fill' : 'bi bi-plus-circle-fill'" class="me-1"></i>
                    {{ isEditing ? 'Enregistrer' : 'Créer la session' }}
                  </span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Modal Détails de la Session -->
    <div v-if="showDetailModal" class="modal fade show d-block modal-overlay" 
       @click.self="closeDetailModal">
      <div class="modal-dialog modal-lg modal-centered">
        <div class="modal-content modal-modern">
          <div class="modal-header modal-header-modern">
            <h5 class="modal-title-modern">
              <div class="modal-icon">
                <i class="bi bi-calendar-event"></i>
              </div>
              <span>Détails de la Session</span>
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
          <div v-else-if="sessionDetails" class="modal-body modal-body-modern">
            <!-- En-tête avec titre et statut -->
            <div class="mb-4 detail-header">
              <h4 class="name-text-large mb-3">{{ sessionDetails.formation_titre }}</h4>
              <div class="d-flex align-items-center gap-3 flex-wrap">
                <span class="badge-status-large" :class="getStatusClass(sessionDetails.statut_formateur)">
                  <i class="bi me-1" :class="getStatusIconClass(sessionDetails.statut_formateur)"></i>
                  {{ getStatusText(sessionDetails.statut_formateur) }}
                </span>
                <small class="text-muted-pastel">
                  <i class="bi bi-hash me-1"></i>ID: {{ sessionDetails.id }}
                </small>
              </div>
            </div>
            
            <!-- Description -->
            <div v-if="sessionDetails.description" class="mb-4">
              <h6 class="section-subtitle">
                <i class="bi bi-text-paragraph me-2"></i>
                Description
              </h6>
              <div class="description-box">
                {{ sessionDetails.description }}
              </div>
            </div>
            
            <!-- Informations détaillées -->
            <div class="session-grid mb-4">
              <!-- Dates -->
              <div class="session-info-card">
                <div class="session-info-icon bg-primary-light-pastel">
                  <i class="bi bi-calendar text-primary-pastel"></i>
                </div>
                <div class="session-info-content">
                  <label class="session-info-label">Dates</label>
                  <p class="session-info-value mb-1">{{ formatDate(sessionDetails.date_debut) }}</p>
                  <small class="text-muted-pastel">au {{ formatDate(sessionDetails.date_fin) }}</small>
                </div>
              </div>
              
              <!-- Lieu -->
              <div class="session-info-card">
                <div class="session-info-icon bg-warning-light-pastel">
                  <i class="bi bi-geo-alt text-warning-pastel"></i>
                </div>
                <div class="session-info-content">
                  <label class="session-info-label">Lieu</label>
                  <p class="session-info-value">{{ sessionDetails.salle }}</p>
                </div>
              </div>
              
              <!-- Places -->
              <div class="session-info-card">
                <div class="session-info-icon bg-success-light-pastel">
                  <i class="bi bi-people text-success-pastel"></i>
                </div>
                <div class="session-info-content">
                  <label class="session-info-label">Places</label>
                  <div class="session-places-progress">
                    <div class="session-places-bar" 
                         :class="getPlacesClass(sessionDetails)"
                         :style="{ width: getPlacesPercentage(sessionDetails) + '%' }">
                    </div>
                  </div>
                  <div class="session-places-info">
                    <span class="session-places-text">{{ sessionDetails.places_restantes }}/{{ sessionDetails.places_max }}</span>
                    <span class="session-places-percentage">{{ getPlacesPercentage(sessionDetails).toFixed(0) }}%</span>
                  </div>
                </div>
              </div>
              
              <!-- Formateur -->
              <div class="session-info-card">
                <div class="session-info-icon bg-info-light-pastel">
                  <i class="bi bi-person text-info-pastel"></i>
                </div>
                <div class="session-info-content">
                  <label class="session-info-label">Formateur</label>
                  <div v-if="sessionDetails.formateur_nom && sessionDetails.formateur_prenom">
                    <p class="session-info-value mb-1">
                      {{ sessionDetails.formateur_prenom }} {{ sessionDetails.formateur_nom }}
                    </p>
                    <small class="text-muted-pastel">{{ sessionDetails.formateur_email || '' }}</small>
                  </div>
                  <div v-else>
                    <p class="text-muted-pastel fst-italic">
                      <i class="bi bi-person-x me-1"></i>Non assigné
                    </p>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Formateur détaillé -->
            <div v-if="sessionDetails.formateur_nom && sessionDetails.formateur_prenom" class="mb-4">
              <h6 class="section-subtitle">
                <i class="bi bi-person-badge me-2"></i>
                Informations du Formateur
              </h6>
              <div class="formateur-info-card">
                <div class="formateur-avatar-large">
                  <i class="bi bi-person-circle"></i>
                </div>
                <div class="formateur-details">
                  <div class="formateur-name">
                    {{ sessionDetails.formateur_prenom }} {{ sessionDetails.formateur_nom }}
                  </div>
                  <div v-if="sessionDetails.formateur_email" class="formateur-email">
                    <i class="bi bi-envelope me-1"></i>{{ sessionDetails.formateur_email }}
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Raison du refus -->
            <div v-if="sessionDetails.statut_formateur === 'REFUSEE' && sessionDetails.raison_refus_formateur" 
                 class="mb-4">
              <h6 class="section-subtitle">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Raison du refus
              </h6>
              <div class="alert alert-danger-pastel">
                <i class="bi bi-exclamation-triangle me-2"></i>
                {{ sessionDetails.raison_refus_formateur }}
              </div>
            </div>
            
            <!-- Bouton de réassignation -->
            <div v-if="sessionDetails.statut_formateur === 'REFUSEE'" class="mb-4">
              <button @click="reassignFormateur(sessionDetails)" 
                      class="btn btn-outline-warning-pastel">
                <i class="bi bi-arrow-repeat me-1"></i>
                Réassigner à un autre formateur
              </button>
            </div>
            
            <!-- Message si session acceptée -->
            <div v-if="sessionDetails.statut_formateur === 'ACCEPTEE'" class="alert alert-info mt-3">
              <i class="bi bi-info-circle me-2"></i>
              Cette session a été acceptée par le formateur. Vous ne pouvez pas changer de formateur.
            </div>
          </div>
          
          <!-- Actions -->
          <div class="modal-footer modal-footer-pastel">
            <button type="button" class="btn btn-outline-secondary-pastel" 
                    @click="closeDetailModal">
              <i class="bi bi-x-circle me-1"></i>
              Fermer
            </button>
            <button v-if="sessionDetails && sessionDetails.statut_formateur !== 'REFUSEE'" 
                    type="button" class="btn btn-outline-primary-pastel"
                    @click="editFromDetail">
              <i class="bi bi-pencil me-1"></i>Modifier
            </button>
            <button v-if="sessionDetails && sessionDetails.statut_formateur === 'REFUSEE'" 
                    type="button" class="btn btn-outline-warning-pastel"
                    @click="reassignFormateur(sessionDetails)">
              <i class="bi bi-arrow-repeat me-1"></i>Réassigner
            </button>
            <button v-if="sessionDetails && sessionDetails.statut_formateur !== 'REFUSEE'" 
                    type="button" class="btn btn-outline-danger-pastel"
                    @click="cancelFromDetail">
              <i class="bi bi-x-circle me-1"></i>Annuler
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Modal Assigner/Réassigner Formateur -->
    <div v-if="showAssignModal" class="modal fade show d-block modal-overlay" 
         @click.self="closeAssignModal">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-modern">
          <div class="modal-header modal-header-modern">
            <h5 class="modal-title-modern">
              <div class="modal-icon">
                <i class="bi bi-person-plus"></i>
              </div>
              <span>{{ getAssignModalTitle(sessionToAssign?.statut_formateur) }}</span>
            </h5>
            <button type="button" class="btn-close-modern" @click="closeAssignModal">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
          <div class="modal-body modal-body-modern">
            <div v-if="sessionToAssign" class="session-info-box mb-4">
              <h6 class="session-info-title">{{ sessionToAssign.formation_titre }}</h6>
              <p class="session-info-text mb-0">
                <i class="bi bi-calendar me-1"></i>
                {{ formatDate(sessionToAssign.date_debut) }} 
                <span class="mx-2">•</span>
                <i class="bi bi-geo-alt me-1"></i>
                {{ sessionToAssign.salle }}
              </p>
              <div v-if="sessionToAssign.statut_formateur === 'REFUSEE'" class="alert alert-warning mt-2 mb-0">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Cette session a été refusée par le formateur.
              </div>
              <div v-if="sessionToAssign.statut_formateur === 'ACCEPTEE'" class="alert alert-success mt-2 mb-0">
                <i class="bi bi-check-circle me-2"></i>
                Cette session a été acceptée par le formateur.
              </div>
            </div>
            
            <!-- Message d'avertissement pour les sessions acceptées -->
            <div v-if="sessionToAssign?.statut_formateur === 'ACCEPTEE'" class="alert alert-danger">
              <i class="bi bi-exclamation-triangle me-2"></i>
              <strong>Attention :</strong> Cette session a déjà été acceptée par le formateur.
              La réassignation n'est pas autorisée car les inscriptions peuvent être en cours.
              Si vous devez absolument changer de formateur, annulez d'abord cette session.
            </div>
            
            <form @submit.prevent="assignFormateurToSession" v-if="sessionToAssign?.statut_formateur !== 'ACCEPTEE'">
              <div class="form-section">
                <h6 class="form-section-title">
                  <i class="bi bi-person-badge me-2"></i>
                  Sélection du formateur
                </h6>
                <div class="form-group-modern">
                  <label class="form-label-modern">
                    <i class="bi bi-person me-1"></i> Formateur <span class="required">*</span>
                  </label>
                  <select class="form-control-modern" v-model.number="selectedFormateurId" required :disabled="assigning">
                    <option value="">Sélectionnez un formateur</option>
                    <option v-for="formateur in formateurs" 
                            :value="formateur.id" 
                            :key="formateur.id">
                      {{ formateur.prenom }} {{ formateur.nom }}
                      <span v-if="formateur.specialite"> - {{ formateur.specialite }}</span>
                    </option>
                  </select>
                </div>
              </div>
              
              <div class="modal-footer-modern">
                <button type="button" class="btn btn-cancel-modern" 
                        @click="closeAssignModal" :disabled="assigning">
                  <i class="bi bi-x-circle me-1"></i>
                  Annuler
                </button>
                <button type="submit" class="btn btn-submit-modern" :disabled="assigning">
                  <span v-if="assigning">
                    <span class="spinner-border spinner-border-sm me-1"></span>
                    Assignation...
                  </span>
                  <span v-else>
                    <i class="bi bi-check-circle me-1"></i>
                    {{ sessionToAssign?.statut_formateur === 'REFUSEE' ? 'Réassigner' : 'Assigner' }}
                  </span>
                </button>
              </div>
            </form>
            
            <!-- Bouton de fermeture si session acceptée -->
            <div v-if="sessionToAssign?.statut_formateur === 'ACCEPTEE'" class="text-center">
              <button type="button" class="btn btn-cancel-modern" @click="closeAssignModal">
                <i class="bi bi-x-circle me-1"></i>
                Fermer
              </button>
            </div>
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
  name: 'SessionsList',
  
  setup() {
    // États principaux
    const loading = ref(true)
    const saving = ref(false)
    const assigning = ref(false)
    const showModal = ref(false)
    const showAssignModal = ref(false)
    const showDetailModal = ref(false)
    const isEditing = ref(false)
    const showAdvancedFilters = ref(false)
    
    // Filtres
    const search = ref('')
    const searchTimeout = ref(null)
    const filterStatus = ref('')
    const filterFormation = ref('')
    const filterFormateur = ref('')
    const filterDateFrom = ref('')
    const filterDateTo = ref('')
    
    // Données
    const sessions = ref([])
    const formations = ref([])
    const formateurs = ref([])
    
    // Pagination
    const currentPage = ref(1)
    const itemsPerPage = ref(10)
    const totalItems = ref(0)
    const totalPages = ref(1)
    
    // Formulaires
    const formData = ref({
      id_formation: '',
      id_formateur: '',
      date_debut: '',
      date_fin: '',
      salle: '',
      places_max: 20,
      description: '',
      statut_formateur: 'EN_ATTENTE',
      raison_refus_formateur: ''
    })
    
    const sessionToAssign = ref(null)
    const selectedFormateurId = ref('')
    const currentSessionId = ref(null)
    
    // Détails
    const detailLoading = ref(false)
    const detailError = ref(null)
    const sessionDetails = ref(null)
    
    // Statistiques
    const stats = ref({
      en_attente: 0,
      acceptees: 0,
      refusees: 0,
      places_restantes: 0
    })
    
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
    const formatDate = (dateString) => {
      if (!dateString) return ''
      try {
        const date = new Date(dateString)
        return date.toLocaleDateString('fr-FR', {
          weekday: 'short',
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
        'ACCEPTEE': 'Acceptée',
        'REFUSEE': 'Refusée',
        null: 'Non assigné'
      }
      return statusMap[status] || status
    }
    
    const getStatusClass = (status) => {
      const classMap = {
        'EN_ATTENTE': 'status-pending',
        'ACCEPTEE': 'status-accepted',
        'REFUSEE': 'status-refused',
        null: 'status-unassigned'
      }
      return classMap[status] || 'status-unassigned'
    }
    
    const getStatusIconClass = (status) => {
      const iconMap = {
        'EN_ATTENTE': 'bi-clock',
        'ACCEPTEE': 'bi-check-circle-fill',
        'REFUSEE': 'bi-x-circle-fill',
        null: 'bi-question-circle'
      }
      return iconMap[status] || 'bi-question-circle'
    }

    const getStatusAlertClass = (status) => {
      const alertMap = {
        'EN_ATTENTE': 'alert-warning',
        'ACCEPTEE': 'alert-success',
        'REFUSEE': 'alert-danger',
        null: 'alert-secondary'
      }
      return alertMap[status] || 'alert-secondary'
    }
    
    const getPlacesPercentage = (session) => {
      if (!session.places_max) return 0
      const taken = session.places_max - session.places_restantes
      return (taken / session.places_max) * 100
    }
    
    const getPlacesClass = (session) => {
      const percentage = getPlacesPercentage(session)
      if (percentage >= 90) return 'bg-danger'
      if (percentage >= 70) return 'bg-warning'
      return 'bg-success'
    }
    
    const getPlacesText = (session) => {
      const taken = session.places_max - session.places_restantes
      return `${taken} place(s) prise(s) sur ${session.places_max}`
    }
    
    // NOUVELLES MÉTHODES POUR LA GESTION DES ASSIGNATIONS
    const getAssignButtonTooltip = (session) => {
      switch (session.statut_formateur) {
        case 'ACCEPTEE':
          return 'Session déjà acceptée - Impossible de changer de formateur';
        case 'REFUSEE':
          return 'Réassigner à un autre formateur';
        case 'EN_ATTENTE':
          return 'Changer de formateur';
        default:
          return 'Assigner un formateur';
      }
    }
    
    const getAssignModalTitle = (status) => {
      switch (status) {
        case 'REFUSEE':
          return 'Réassigner un formateur (Session refusée)';
        case 'EN_ATTENTE':
          return 'Changer de formateur (En attente)';
        case 'ACCEPTEE':
          return 'Session acceptée - Réassignation impossible';
        default:
          return 'Assigner un formateur';
      }
    }
    
    const handleAssignClick = (session) => {
      if (session.statut_formateur === 'ACCEPTEE') {
        alert('❌ Impossible de réassigner : Cette session a déjà été acceptée par le formateur.\n\nSi vous devez absolument changer de formateur, annulez d\'abord cette session et créez-en une nouvelle.');
        return;
      }
      assignFormateur(session);
    }
    
    const canAssignFormateur = (session) => {
      // Toujours possible si refusée
      if (session.statut_formateur === 'REFUSEE') return true;
      
      // Impossible si acceptée
      if (session.statut_formateur === 'ACCEPTEE') return false;
      
      // Possible si en attente
      return true;
    }
    
    const calculateStats = () => {
      stats.value = {
        en_attente: 0,
        acceptees: 0,
        refusees: 0,
        places_restantes: 0
      }
      
      sessions.value.forEach(session => {
        switch (session.statut_formateur) {
          case 'EN_ATTENTE':
            stats.value.en_attente++
            break
          case 'ACCEPTEE':
            stats.value.acceptees++
            stats.value.places_restantes += session.places_restantes
            break
          case 'REFUSEE':
            stats.value.refusees++
            break
        }
      })
    }
    
    // Méthodes principales
    const loadSessions = async () => {
      loading.value = true
      try {
        const params = {
          limit: itemsPerPage.value,
          page: currentPage.value
        }
        
        if (search.value.trim()) {
          params.recherche = search.value.trim()
        }
        
        if (filterStatus.value) {
          params.statut = filterStatus.value
        }
        
        if (filterFormation.value) {
          params.formation_id = filterFormation.value
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
        
        const response = await adminService.getSessions(params)
        
        if (response.success) {
          sessions.value = response.data || []
          totalItems.value = response.pagination?.total || sessions.value.length
          totalPages.value = response.pagination?.pages || Math.ceil(totalItems.value / itemsPerPage.value)
          
          calculateStats()
        } else {
          throw new Error(response.message || 'Erreur lors du chargement')
        }
      } catch (error) {
        console.error('❌ Erreur chargement sessions:', error)
        alert(error.message || 'Erreur lors du chargement des sessions')
      } finally {
        loading.value = false
      }
    }
    
    const loadFormations = async () => {
      try {
        const response = await adminService.getFormations()
        if (response.success) {
          formations.value = response.data || []
        }
      } catch (error) {
        console.error('❌ Erreur chargement formations:', error)
      }
    }
    
    const loadFormateurs = async () => {
      try {
        const response = await adminService.getFormateurs()
        
        if (response.success) {
          formateurs.value = response.data || []
          
          formateurs.value = formateurs.value.map(formateur => ({
            ...formateur,
            id: formateur.formateur_id || formateur.id
          }))
        }
      } catch (error) {
        console.error('❌ Erreur chargement formateurs:', error)
      }
    }
    
    const handleSearch = () => {
      clearTimeout(searchTimeout.value)
      searchTimeout.value = setTimeout(() => {
        currentPage.value = 1
        loadSessions()
      }, 500)
    }
    
    const toggleAdvancedFilters = () => {
      showAdvancedFilters.value = !showAdvancedFilters.value
    }
    
    const openAddModal = () => {
      if (formateurs.value.length === 0) {
        alert('❌ Aucun formateur disponible. Créez d\'abord un formateur.')
        return
      }
      
      isEditing.value = false
      formData.value = {
        id_formation: '',
        id_formateur: formateurs.value[0]?.id || '',
        date_debut: '',
        date_fin: '',
        salle: '',
        places_max: 20,
        description: '',
        statut_formateur: 'EN_ATTENTE',
        raison_refus_formateur: ''
      }
      
      showModal.value = true
    }
    
    const editSession = (session) => {
      isEditing.value = true
      currentSessionId.value = session.id
      
      formData.value = {
        id_formation: session.id_formation,
        id_formateur: session.id_formateur || '',
        date_debut: session.date_debut ? session.date_debut.replace(' ', 'T').substring(0, 16) : '',
        date_fin: session.date_fin ? session.date_fin.replace(' ', 'T').substring(0, 16) : '',
        salle: session.salle,
        places_max: session.places_max,
        description: session.description || '',
        statut_formateur: session.statut_formateur || 'EN_ATTENTE',
        raison_refus_formateur: session.raison_refus_formateur || ''
      }
      
      showModal.value = true
    }
    
    const assignFormateur = (session) => {
      sessionToAssign.value = session
      selectedFormateurId.value = session.id_formateur || ''
      showAssignModal.value = true
    }
    
    const reassignFormateur = (session) => {
      sessionToAssign.value = session
      selectedFormateurId.value = ''
      showAssignModal.value = true
    }
    
    const assignFormateurToSession = async () => {
      if (!selectedFormateurId.value || !sessionToAssign.value) {
        alert('Veuillez sélectionner un formateur')
        return
      }
      
      assigning.value = true
      try {
        const updateData = {
          id_formateur: selectedFormateurId.value
        }
        
        await adminService.updateSession(sessionToAssign.value.id, updateData)
        
        if (sessionToAssign.value.statut_formateur === 'REFUSEE') {
          alert('✅ Session réassignée avec succès. Statut remis à "En attente".')
        } else {
          alert('✅ Formateur assigné avec succès')
        }
        
        closeAssignModal()
        loadSessions()
      } catch (error) {
        console.error('❌ Erreur assignation:', error)
        alert(error.response?.data?.message || error.message || 'Erreur lors de l\'assignation')
      } finally {
        assigning.value = false
      }
    }
    
    const viewDetails = async (session) => {
      showDetailModal.value = true
      detailLoading.value = true
      detailError.value = null
      sessionDetails.value = null
      
      try {
        const response = await adminService.getSession(session.id)
        
        if (response.success && response.session) {
          sessionDetails.value = response.session
        } else {
          sessionDetails.value = session
        }
      } catch (error) {
        console.error('❌ Erreur chargement détails:', error)
        detailError.value = error.message || 'Impossible de charger les détails'
        sessionDetails.value = session
      } finally {
        detailLoading.value = false
      }
    }
    
    const editFromDetail = () => {
      if (sessionDetails.value) {
        editSession(sessionDetails.value)
      }
    }
    
    const cancelFromDetail = () => {
      if (sessionDetails.value) {
        cancelSession(sessionDetails.value)
      }
    }
    
    const saveSession = async () => {
      if (!formData.value.id_formation || !formData.value.id_formateur || 
          !formData.value.date_debut || !formData.value.date_fin || 
          !formData.value.salle || !formData.value.places_max) {
        alert('❌ Veuillez remplir tous les champs obligatoires')
        return
      }
      
      saving.value = true
      try {
        const sessionData = {
          id_formation: formData.value.id_formation,
          id_formateur: formData.value.id_formateur,
          date_debut: formData.value.date_debut.replace('T', ' ') + ':00',
          date_fin: formData.value.date_fin.replace('T', ' ') + ':00',
          salle: formData.value.salle,
          places_max: formData.value.places_max,
          description: formData.value.description || ''
        }
        
        if (isEditing.value) {
          await adminService.updateSession(currentSessionId.value, sessionData)
          alert('✅ Informations de session modifiées avec succès')
        } else {
          sessionData.statut_formateur = 'EN_ATTENTE'
          await adminService.createSession(sessionData)
          alert('✅ Session créée avec succès. Statut : En attente du formateur.')
        }
        
        closeModal()
        loadSessions()
      } catch (error) {
        console.error('❌ Erreur sauvegarde:', error)
        alert(error.response?.data?.message || error.message || 'Erreur lors de la sauvegarde')
      } finally {
        saving.value = false
      }
    }
    
    const cancelSession = (session) => {
      if (confirm(`Êtes-vous sûr de vouloir annuler la session "${session.formation_titre}" ?`)) {
        deleteSession(session.id)
      }
    }
    
    const deleteSession = async (id) => {
      try {
        await adminService.deleteSession(id)
        alert('✅ Session annulée avec succès')
        loadSessions()
        if (showDetailModal.value) {
          closeDetailModal()
        }
      } catch (error) {
        console.error('❌ Erreur suppression:', error)
        alert(error.response?.data?.message || error.message || 'Erreur lors de l\'annulation')
      }
    }
    
    const changePage = (page) => {
      if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page
        loadSessions()
      }
    }
    
    const closeModal = () => {
      showModal.value = false
      isEditing.value = false
      formData.value = {
        id_formation: '',
        id_formateur: '',
        date_debut: '',
        date_fin: '',
        salle: '',
        places_max: 20,
        description: '',
        statut_formateur: 'EN_ATTENTE',
        raison_refus_formateur: ''
      }
      currentSessionId.value = null
    }
    
    const closeAssignModal = () => {
      showAssignModal.value = false
      sessionToAssign.value = null
      selectedFormateurId.value = ''
    }
    
    const closeDetailModal = () => {
      showDetailModal.value = false
      sessionDetails.value = null
      detailError.value = null
    }
    
    // Lifecycle
    onMounted(async () => {
      await loadSessions()
      await loadFormations()
      await loadFormateurs()
    })
    
    return {
      loading,
      saving,
      assigning,
      showModal,
      showAssignModal,
      showDetailModal,
      isEditing,
      showAdvancedFilters,
      search,
      filterStatus,
      filterFormation,
      filterFormateur,
      filterDateFrom,
      filterDateTo,
      sessions,
      formations,
      formateurs,
      currentPage,
      itemsPerPage,
      totalItems,
      totalPages,
      formData,
      sessionToAssign,
      selectedFormateurId,
      detailLoading,
      detailError,
      sessionDetails,
      stats,
      startItem,
      endItem,
      displayedPages,
      loadSessions,
      handleSearch,
      toggleAdvancedFilters,
      formatDate,
      getStatusText,
      getStatusClass,
      getStatusIconClass,
      getStatusAlertClass,
      getPlacesPercentage,
      getPlacesClass,
      getPlacesText,
      getAssignButtonTooltip,
      getAssignModalTitle,
      handleAssignClick,
      canAssignFormateur,
      openAddModal,
      editSession,
      assignFormateur,
      reassignFormateur,
      assignFormateurToSession,
      viewDetails,
      editFromDetail,
      cancelFromDetail,
      cancelSession,
      saveSession,
      changePage,
      closeModal,
      closeAssignModal,
      closeDetailModal
    }
  }
}
</script>

<style scoped>
/* Variables et base */
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
  
  /* Pour le dark-text demandé */
  --pastel-text-dark: #1A2530;
  --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
  --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
  --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
  --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
  --border-radius: 12px;
  --border-radius-sm: 8px;
  --border-radius-lg: 16px;
}

.sessions-list {
  min-height: calc(100vh - 200px);
  background: linear-gradient(135deg, #f5f7fa 0%, #e4e9f2 100%);
  padding-bottom: 3rem;
}

/* En-tête avec gradient */
.page-header {
  background: white;
  padding: 2rem 0;
  margin-bottom: 2rem;
  border-bottom: 1px solid var(--pastel-border);
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
  border-radius: var(--radius-lg);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: var(--shadow-md);
  margin-right: 0px;
}

.icon-wrapper i {
  font-size: 2rem;
   color: var(--pastel-text-dark);
}

/* TITRE PLUS CLAIR ET PLUS VISIBLE */
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
  color: var(--pastel-text-dark);
  font-weight: 700;
  font-size: 0.875rem;
}

/* ========================================
   BOUTON "NOUVELLE SESSION" COMME LA PAGE DE RÉFÉRENCE
   ======================================== */
.btn-create {
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
  display: inline-flex;
  align-items: center;
}

.btn-create:hover:not(:disabled) {
  background: linear-gradient(135deg, #357ABD 0%, #2868A8 100%);
  transform: translateY(-3px);
  box-shadow: 0 6px 25px rgba(74, 144, 226, 0.5);
  color: #FFFFFF;
}

.btn-create:disabled {
  background: var(--pastel-secondary);
  color: var(--pastel-text-light);
  cursor: not-allowed;
  opacity: 0.7;
  box-shadow: none;
}

.btn-create:disabled:hover {
  transform: none;
  box-shadow: none;
}

.btn-create i {
  font-size: 1.1rem;
  margin-right: 0.5rem;
}

/* Alerte personnalisée */
.alert-warning-custom {
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
  border: 2px solid #f59e0b;
  border-radius: var(--border-radius);
  padding: 1.5rem;
  display: flex;
  align-items: start;
  gap: 1rem;
  box-shadow: var(--shadow-md);
}

.alert-icon {
  width: 40px;
  height: 40px;
  background: #f59e0b;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.alert-icon i {
  font-size: 1.25rem;
}

.alert-content {
  flex: 1;
}

.alert-title {
  font-weight: 700;
  color: var(--gray-900);
  margin-bottom: 0.5rem;
}

.alert-link {
  color: var(--primary-color);
  font-weight: 600;
  text-decoration: underline;
}

.alert-link:hover {
  color: var(--primary-dark);
}

/* Cartes de statistiques */
.stat-card {
  background: white;
  border-radius: var(--border-radius);
  padding: 1.5rem;
  box-shadow: var(--shadow-md);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
  border: 1px solid var(--gray-200);
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
  background: var(--primary-color);
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

.stat-places::before {
  background: var(--info-color);
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

.stat-places .stat-icon {
  background: rgba(59, 130, 246, 0.1);
  color: var(--info-color);
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

/* Carte de filtres */
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

.form-control-search::placeholder {
  color: #A5B4C6;
  font-size: 0.9rem;
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

/* Carte de tableau */
.sessions-table-card {
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

.session-row {
  transition: all 0.2s ease;
}

.session-row:hover {
  background: var(--gray-50);
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

.formation-id {
  font-size: 0.75rem;
  color: var(--gray-500);
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

/* Badge location */
.badge-location {
  background: linear-gradient(135deg, var(--gray-100) 0%, var(--gray-200) 100%);
  color: var(--gray-700);
  padding: 0.5rem 1rem;
  border-radius: var(--border-radius-sm);
  font-weight: 600;
  font-size: 0.875rem;
  display: inline-flex;
  align-items: center;
}

/* Places */
.places-wrapper {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.progress-custom {
  height: 8px;
  border-radius: 10px;
  background: var(--gray-200);
  flex: 1;
  overflow: hidden;
}

.progress-custom .progress-bar {
  border-radius: 10px;
  transition: width 0.6s ease;
}

.places-text {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--gray-700);
  white-space: nowrap;
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
  background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary-color) 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
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

.status-unassigned {
  background: var(--gray-100);
  color: var(--gray-600);
  border: 1px solid var(--gray-300);
}

.refusal-reason {
  margin-top: 0.5rem;
  font-size: 0.75rem;
  color: var(--danger-color);
  font-style: italic;
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
  position: relative;
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
  background: rgba(16, 185, 129, 0.05);
  border-color: rgba(16, 185, 129, 0.3);
  color: var(--success-color);
}

.btn-edit:hover:not(:disabled) {
  background: var(--success-color);
  color: white;
  transform: translateY(-2px);
  box-shadow: var(--shadow-sm);
}

.btn-assign {
  background: rgba(245, 158, 11, 0.05);
  border-color: rgba(245, 158, 11, 0.3);
  color: var(--warning-color);
}

.btn-assign:hover {
  background: var(--warning-color);
  color: white;
  transform: translateY(-2px);
  box-shadow: var(--shadow-sm);
}

.btn-assign:disabled {
  background: rgba(209, 213, 219, 0.3);
  border-color: rgba(209, 213, 219, 0.5);
  color: var(--gray-400);
  cursor: not-allowed;
}

.btn-assign:disabled:hover {
  transform: none;
  box-shadow: none;
  background: rgba(209, 213, 219, 0.3);
  border-color: rgba(209, 213, 219, 0.5);
}

.btn-cancel {
  background: rgba(239, 68, 68, 0.05);
  border-color: rgba(239, 68, 68, 0.3);
  color: var(--danger-color);
}

.btn-cancel:hover:not(:disabled) {
  background: var(--danger-color);
  color: white;
  transform: translateY(-2px);
  box-shadow: var(--shadow-sm);
}

.btn-action:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

/* Badge sur le bouton d'assignation */
.badge-sm {
  position: absolute;
  top: -4px;
  right: -4px;
  font-size: 0.5rem;
  padding: 0.15rem 0.3rem;
  border-radius: 50%;
  min-width: 16px;
  height: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Pagination */
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
   STYLES DU MODAL MODERNE (COMME FORMATEURS)
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

.is-invalid-modern {
  border-color: #E74C3C !important;
  background: #FFF5F5 !important;
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
  justify-content: flex-end;
  gap: 1rem;
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

/* Badge status large */
.badge-status-large {
  display: inline-flex;
  align-items: center;
  padding: 0.75rem 1.5rem;
  border-radius: 25px;
  font-weight: 700;
  font-size: 1rem;
}

/* Animations */
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

/* Détails modal */
.detail-header {
  background: linear-gradient(135deg, #F0F8FA 0%, #FFFFFF 100%);
  padding: 1.5rem;
  border-radius: 12px;
  border: 2px solid #D4EBF2;
  margin-bottom: 2rem;
}

.name-text-large {
  color: #2C3E50;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.section-subtitle {
  color: #2C3E50;
  font-weight: 600;
  font-size: 1rem;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #D4EBF2;
}

.description-box {
  background: #F8FBFD;
  border: 2px solid #E5F2F8;
  border-left: 5px solid #4A90E2;
  border-radius: 12px;
  padding: 1.25rem 1.5rem;
  color: #2C3E50;
  line-height: 1.7;
  font-size: 0.95rem;
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
}

.bg-primary-light-pastel {
  background: linear-gradient(135deg, #D4EBF2 0%, #F0F8FA 100%);
}

.bg-success-light-pastel {
  background: linear-gradient(135deg, #D6F0E7 0%, #B8E0D2 100%);
}

.info-card-content {
  flex: 1;
}

.info-card-label {
  color: #7D8DA6;
  font-size: 0.85rem;
  font-weight: 500;
  margin-bottom: 0.25rem;
}

.info-card-value {
  color: #2C3E50;
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0;
}

.competences-box {
  background: linear-gradient(135deg, #FFF4E0 0%, #FFE5B4 100%);
  border: 2px solid #FFE5B4;
  border-left: 5px solid #F4B860;
  border-radius: 12px;
  padding: 1.25rem 1.5rem;
  color: #2C3E50;
  line-height: 1.7;
  font-size: 0.95rem;
  font-weight: 500;
}

/* Session info box */
.session-info-box {
  background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
  padding: 1.25rem;
  border-radius: var(--border-radius);
  border: 1px solid var(--gray-200);
}

.session-info-title {
  font-weight: 700;
  color: var(--gray-900);
  margin-bottom: 0.5rem;
}

.session-info-text {
  color: var(--gray-600);
  font-size: 0.875rem;
}

.status-info-box {
  background: var(--gray-50);
  padding: 1rem;
  border-radius: var(--border-radius);
  margin-bottom: 1rem;
}

.alert-status {
  margin-bottom: 0;
}

/* AJOUTER ou MODIFIER ces styles : */
.alert-danger-pastel {
  background: linear-gradient(135deg, #FFD9DC 0%, #FFB3BA 100%);
  border: 2px solid #FFB3BA;
  border-left: 5px solid #FF8B94;
  color: #E6757E;
  border-radius: 12px;
  padding: 1rem 1.25rem;
}

.btn-secondary-pastel {
  background: #D4D4D4;
  border: none;
  color: #2C3E50;
  font-weight: 500;
  padding: 0.625rem 1.5rem;
  border-radius: 10px;
  transition: all 0.3s ease;
}

.btn-secondary-pastel:hover {
  background: #BEBEBE;
  transform: translateY(-2px);
}

.text-muted-pastel {
  color: #7D8DA6 !important;
}

.text-primary-pastel {
  color: #7BB8CC !important;
}

.text-success-pastel {
  color: #6BB896 !important;
}

.text-warning-pastel {
  color: #F4B860 !important;
}

/* Styles pour la session acceptée */
.alert-info {
  background: linear-gradient(135deg, #D4EBF2 0%, #B4D7FF 100%);
  border: 2px solid #4A90E2;
  color: #2C3E50;
  border-radius: 12px;
  padding: 1rem 1.25rem;
}

/* Styles pour le bouton d'assignation désactivé */
.btn-assign.disabled-assign {
  position: relative;
  cursor: not-allowed;
}

.btn-assign.disabled-assign::after {
  content: "✓";
  position: absolute;
  top: -5px;
  right: -5px;
  background: var(--success-color);
  color: white;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  font-size: 0.6rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

/* Session grid pour les détails */
.session-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.session-info-card {
  background: white;
  border-radius: 12px;
  padding: 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  border: 2px solid #E5F2F8;
  transition: all 0.3s ease;
}

.session-info-card:hover {
  border-color: #D4EBF2;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(74, 144, 226, 0.1);
}

.session-info-icon {
  width: 50px;
  height: 50px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.session-info-content {
  flex: 1;
}

.session-info-label {
  color: #7D8DA6;
  font-size: 0.85rem;
  font-weight: 500;
  margin-bottom: 0.25rem;
  display: block;
}

.session-info-value {
  color: #2C3E50;
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0;
}

/* Progrès des places dans les détails */
.session-places-progress {
  height: 8px;
  background: #E5F2F8;
  border-radius: 4px;
  overflow: hidden;
  margin: 0.5rem 0;
}

.session-places-bar {
  height: 100%;
  border-radius: 4px;
  transition: width 0.6s ease;
}

.session-places-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.session-places-text {
  font-size: 0.9rem;
  font-weight: 600;
  color: #2C3E50;
}

.session-places-percentage {
  font-size: 0.85rem;
  font-weight: 600;
  color: #7BB8CC;
}

/* Formateur info card */
.formateur-info-card {
  background: linear-gradient(135deg, #F0F8FA 0%, #E0EEFF 100%);
  border: 2px solid #D4EBF2;
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.formateur-avatar-large {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: linear-gradient(135deg, #4A90E2 0%, #357ABD 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
}

.formateur-details {
  flex: 1;
}

.formateur-name {
  font-size: 1.2rem;
  font-weight: 700;
  color: #2C3E50;
  margin-bottom: 0.25rem;
}

.formateur-email {
  color: #7D8DA6;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

/* Responsive */
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
  
  .session-grid {
    grid-template-columns: 1fr;
  }
  
  .formateur-info-card {
    flex-direction: column;
    text-align: center;
  }
  
  /* Modal responsive */
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
    margin-right: 0.75rem;
  }
  
  .modal-icon i {
    font-size: 1.3rem;
  }
  
  .btn-close-modern {
    width: 36px;
    height: 36px;
    right: 1.25rem;
  }
  
  .modal-body-modern {
    padding: 1.5rem 1.25rem;
    max-height: calc(100vh - 200px);
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
  
  .modal-footer-modern {
    padding: 1.25rem 1.5rem;
    flex-direction: column;
  }
  
  .btn-cancel-modern,
  .btn-submit-modern {
    width: 100%;
    padding: 0.875rem 1.5rem;
    font-size: 0.9rem;
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
  
  .modal-footer-pastel {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .modal-footer-pastel button {
    width: 100%;
  }
}

/* AJOUTER ces animations à la fin de votre CSS : */
@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

/* Tooltip personnalisé pour les boutons d'action */
.btn-action {
  position: relative;
}

.btn-action:hover::before {
  content: attr(title);
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  background: var(--gray-800);
  color: white;
  padding: 0.5rem 0.75rem;
  border-radius: 6px;
  font-size: 0.75rem;
  white-space: nowrap;
  z-index: 1000;
  pointer-events: none;
  margin-bottom: 8px;
}

.btn-action:hover::after {
  content: '';
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  border: 5px solid transparent;
  border-top-color: var(--gray-800);
  z-index: 1000;
  pointer-events: none;
  margin-bottom: -2px;
}
</style>