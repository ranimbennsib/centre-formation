<template>
  <div class="dashboard-statistiques">
    <!-- ========================================
       EN-TÊTE SIMPLIFIÉ
       ======================================== -->
    <div class="dashboard-header">
      <div class="container-fluid">
        <div class="row align-items-center">
          <div class="col-lg-8">
            <div class="header-content">
              <div class="icon-wrapper">
                <i class="bi bi-bar-chart"></i>
              </div>
              <div>
                <h1 class="page-title mb-2">Tableau de Bord des Statistiques</h1>
                <p class="page-subtitle mb-0">
                  Données analytiques en temps réel
                </p>
              </div>
            </div>
          </div>
          <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <div class="header-actions">
              <button @click="refreshData" class="btn btn-outline-secondary btn-sm me-2" title="Rafraîchir">
                <i class="bi bi-arrow-clockwise"></i>
              </button>
              <button @click="exportStats" class="btn btn-primary btn-sm">
                <i class="bi bi-download me-1"></i>
                Exporter
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ========================================
       FILTRES SIMPLIFIÉS
       ======================================== -->
    <div class="container-fluid mt-3">
      <div class="filters-simple mb-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body p-3">
            <div class="row g-2">
              <div class="col-md-4">
                <label class="form-label small mb-1">
                  <i class="bi bi-calendar me-1"></i>Période
                </label>
                <select v-model="filterPeriod" @change="loadStatistics" class="form-select form-select-sm">
                  <option value="all">Toutes périodes</option>
                  <option value="today">Aujourd'hui</option>
                  <option value="week">Cette semaine</option>
                  <option value="month">Ce mois</option>
                  <option value="year">Cette année</option>
                </select>
              </div>
              
              <div class="col-md-4">
                <label class="form-label small mb-1">
                  <i class="bi bi-book me-1"></i>Formation
                </label>
                <select v-model="filterFormation" @change="loadStatistics" class="form-select form-select-sm">
                  <option value="all">Toutes formations</option>
                  <option v-for="formation in formations" :value="formation.id" :key="formation.id">
                    {{ formation.titre }}
                  </option>
                </select>
              </div>
              
              <div class="col-md-4">
                <label class="form-label small mb-1">
                  <i class="bi bi-graph-up me-1"></i>Affichage
                </label>
                <div class="period-indicator small bg-light p-2 rounded">
                  <i class="bi bi-info-circle me-1"></i>
                  <span class="text-muted">Période :</span>
                  <strong class="ms-1">{{ formatDateRange() }}</strong>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ========================================
         KPI PRINCIPAUX - CHIFFRES CLAIRS
         ======================================== -->
      <div class="row mb-4 g-3">
        <!-- Total Inscriptions -->
        <div class="col-xl-3 col-lg-6">
          <div class="kpi-card-simple kpi-primary">
            <div class="kpi-icon-simple">
              <i class="bi bi-person-plus"></i>
            </div>
            <div class="kpi-content-simple">
              <div class="kpi-label-simple">Total Inscriptions</div>
              <div class="kpi-value-simple">{{ formatNumber(stats.global?.total_inscriptions || 0) }}</div>
              <div class="kpi-details-simple">
                <span class="badge bg-primary bg-opacity-10 text-primary small">
                  Base de données
                </span>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Validées -->
        <div class="col-xl-3 col-lg-6">
          <div class="kpi-card-simple kpi-success">
            <div class="kpi-icon-simple">
              <i class="bi bi-check-circle"></i>
            </div>
            <div class="kpi-content-simple">
              <div class="kpi-label-simple">Inscriptions Validées</div>
              <div class="kpi-value-simple">{{ formatNumber(stats.global?.validees || 0) }}</div>
              <div class="kpi-details-simple">
                <span class="percentage-badge-success">
                  {{ calculatePercentage(stats.global?.validees, stats.global?.total_inscriptions) }}%
                </span>
                <span class="text-muted ms-2 small">du total</span>
              </div>
            </div>
          </div>
        </div>
        
        <!-- En attente -->
        <div class="col-xl-3 col-lg-6">
          <div class="kpi-card-simple kpi-warning">
            <div class="kpi-icon-simple">
              <i class="bi bi-clock"></i>
            </div>
            <div class="kpi-content-simple">
              <div class="kpi-label-simple">En Attente</div>
              <div class="kpi-value-simple">{{ formatNumber(stats.global?.en_attente || 0) }}</div>
              <div class="kpi-details-simple">
                <span class="percentage-badge-warning">
                  {{ calculatePercentage(stats.global?.en_attente, stats.global?.total_inscriptions) }}%
                </span>
                <span class="text-muted ms-2 small">à traiter</span>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Refusées + Annulées -->
        <div class="col-xl-3 col-lg-6">
          <div class="kpi-card-simple kpi-danger">
            <div class="kpi-icon-simple">
              <i class="bi bi-x-circle"></i>
            </div>
            <div class="kpi-content-simple">
              <div class="kpi-label-simple">Non Validées</div>
              <div class="kpi-value-simple">
                {{ formatNumber((stats.global?.refusees || 0) + (stats.global?.annulees || 0)) }}
              </div>
              <div class="kpi-details-simple">
                <div class="row g-1">
                  <div class="col-6">
                    <div class="small">
                      <span class="text-danger">{{ stats.global?.refusees || 0 }}</span>
                      <span class="text-muted"> refusées</span>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="small">
                      <span class="text-warning">{{ stats.global?.annulees || 0 }}</span>
                      <span class="text-muted"> annulées</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ========================================
         STATISTIQUES DÉTAILLÉES - FORMAT COMPACT
         ======================================== -->
      <div class="row mb-4 g-3">
        <!-- Répartition par statut - Version compacte -->
        <div class="col-lg-4">
          <div class="stat-card-compact">
            <div class="card-header-compact">
              <i class="bi bi-pie-chart me-2"></i>
              <h6 class="mb-0">Répartition par Statut</h6>
            </div>
            <div class="card-body-compact">

              <div v-if="loading" class="loading-mini">
                <div class="spinner-border spinner-border-sm text-primary"></div>
              </div>
              
              <div  class="status-distribution">
                <!-- Graphique simple -->
                <div class="distribution-chart">
                  <canvas ref="pieChart" height="180"></canvas>
                </div>
                
                <!-- Légende compacte -->
                <div class="distribution-legend mt-3">
                  <div v-for="stat in getStatusDistribution()" :key="stat.label" 
                       class="legend-item-compact">
                    <div class="legend-color" :style="{ backgroundColor: stat.color }"></div>
                    <div class="legend-text">
                      <span class="legend-label">{{ stat.label }}</span>
                      <span class="legend-value">{{ stat.value }}</span>
                      <span class="legend-percentage">({{ stat.percentage }}%)</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Évolution mensuelle - Format liste -->
        <div class="col-lg-8">
          <div class="stat-card-compact">
            <div class="card-header-compact d-flex justify-content-between align-items-center">
              <div>
                <i class="bi bi-calendar-month me-2"></i>
                <h6 class="mb-0">Évolution Mensuelle</h6>
              </div>
              <button @click="showAllMonths = !showAllMonths" class="btn btn-sm btn-outline-secondary">
                <i class="bi" :class="showAllMonths ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
              </button>
            </div>
            <div class="card-body-compact">
              <div v-if="loading" class="loading-mini">
                <div class="spinner-border spinner-border-sm text-primary"></div>
              </div>
              
              <div v-else-if="stats.par_mois && stats.par_mois.length > 0" class="monthly-evolution">
                <div class="evolution-list">
                  <div v-for="mois in displayedMonths" :key="mois.mois" 
                       class="evolution-item">
                    <div class="evolution-month">
                      <strong>{{ formatMonth(mois.mois) }}</strong>
                    </div>
                    <div class="evolution-bar">
                      <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-primary" 
                             :style="{ width: calculateBarWidth(mois.inscriptions) + '%' }"></div>
                      </div>
                    </div>
                    <div class="evolution-count">
                      <span class="badge bg-primary rounded-pill">{{ mois.inscriptions }}</span>
                    </div>
                  </div>
                </div>
                
                <div v-if="stats.par_mois.length > 6" class="text-center mt-2">
                  <small class="text-muted">
                    {{ displayedMonths.length }} des {{ stats.par_mois.length }} mois affichés
                  </small>
                </div>
              </div>
              
              <div v-else class="text-center py-3">
                <i class="bi bi-calendar-x text-muted"></i>
                <p class="text-muted small mb-0 mt-1">Aucune donnée historique</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ========================================
         PERFORMANCE PAR FORMATION - TABLEAU SIMPLE
         ======================================== -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="stat-card-compact">
            <div class="card-header-compact">
              <i class="bi bi-list-check me-2"></i>
              <h6 class="mb-0">Performance par Formation</h6>
            </div>
            <div class="card-body-compact p-0">
              <div v-if="loading" class="loading-table">
                <div class="spinner-border text-primary"></div>
              </div>
              
              <div v-else-if="stats.par_formation && stats.par_formation.length > 0" class="table-responsive">
                <table class="table table-hover mb-0">
                  <thead class="table-light">
                    <tr>
                      <th width="40">#</th>
                      <th>Formation</th>
                      <th class="text-center">Total</th>
                      <th class="text-center">Validées</th>
                      <th class="text-center">En attente</th>
                      <th class="text-center">Taux</th>
                      <th width="60"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(formation, index) in stats.par_formation" :key="formation.id">
                      <td class="text-center">
                        <span class="badge bg-secondary">{{ index + 1 }}</span>
                      </td>
                      <td>
                        <div class="formation-name">
                          <strong class="d-block">{{ formation.titre || 'Non spécifié' }}</strong>
                          <small class="text-muted">
                            {{ formation.total_inscriptions || 0 }} inscription(s)
                          </small>
                        </div>
                      </td>
                      <td class="text-center">
                        <span class="badge bg-primary">{{ formation.total_inscriptions || 0 }}</span>
                      </td>
                      <td class="text-center">
                        <span class="badge bg-success">{{ formation.validees || 0 }}</span>
                      </td>
                      <td class="text-center">
                        <span class="badge bg-warning">{{ formation.en_attente || 0 }}</span>
                      </td>
                      <td class="text-center">
                        <div class="progress-wrapper-compact">
                          <div class="progress-label">
                            {{ calculatePercentage(formation.validees, formation.total_inscriptions) }}%
                          </div>
                          <div class="progress progress-sm">
                            <div class="progress-bar" 
                                 :class="getTauxClass(calculatePercentage(formation.validees, formation.total_inscriptions))"
                                 :style="{ width: calculatePercentage(formation.validees, formation.total_inscriptions) + '%' }"></div>
                          </div>
                        </div>
                      </td>
                      <td class="text-center">
                        <button @click="viewFormationDetails(formation)" 
                                class="btn btn-sm btn-outline-primary">
                          <i class="bi bi-eye"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              
              <div v-else class="text-center py-4">
                <i class="bi bi-book text-muted"></i>
                <p class="text-muted mb-0">Aucune donnée de formation</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ========================================
         ANALYSE DÉTAILLÉE - SIMPLIFIÉE
         ======================================== -->
      <div class="row g-3">
        <!-- Détails des non-validations -->
        <div class="col-lg-6">
          <div class="stat-card-compact">
            <div class="card-header-compact">
              <i class="bi bi-exclamation-triangle me-2"></i>
              <h6 class="mb-0">Analyse des Non-Validations</h6>
            </div>
            <div class="card-body-compact">
              <div class="row">
                <div class="col-md-6">
                  <div class="detail-item">
                    <div class="detail-icon text-danger">
                      <i class="bi bi-x-circle-fill"></i>
                    </div>
                    <div class="detail-content">
                      <div class="detail-label">Inscriptions Refusées</div>
                      <div class="detail-value">{{ stats.global?.refusees || 0 }}</div>
                      <div class="detail-percentage">
                        {{ calculatePercentage(stats.global?.refusees, stats.global?.total_inscriptions) }}%
                        <span class="text-muted small">du total</span>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="col-md-6">
                  <div class="detail-item">
                    <div class="detail-icon text-warning">
                      <i class="bi bi-slash-circle-fill"></i>
                    </div>
                    <div class="detail-content">
                      <div class="detail-label">Inscriptions Annulées</div>
                      <div class="detail-value">{{ stats.global?.annulees || 0 }}</div>
                      <div class="detail-percentage">
                        {{ calculatePercentage(stats.global?.annulees, stats.global?.total_inscriptions) }}%
                        <span class="text-muted small">du total</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Insight simple -->
              <div class="insight-simple mt-3 p-2 bg-light rounded">
                <i class="bi bi-info-circle me-1"></i>
                <small v-if="stats.global?.refusees > stats.global?.annulees" class="text-warning">
                  <strong>À analyser :</strong> Plus de refus que d'annulations.
                </small>
                <small v-else class="text-success">
                  <strong>Situation stable :</strong> Taux d'annulation maîtrisé.
                </small>
              </div>
            </div>
          </div>
        </div>

        <!-- Indicateurs de performance - Basés sur les données réelles -->
        <div class="col-lg-6">
          <div class="stat-card-compact">
            <div class="card-header-compact">
              <i class="bi bi-speedometer2 me-2"></i>
              <h6 class="mb-0">Indicateurs Clés</h6>
            </div>
            <div class="card-body-compact">
              <div class="indicators-grid">
                <!-- Taux de conversion réel -->
                <div class="indicator-item-compact">
                  <div class="indicator-label-compact">Taux de Conversion</div>
                  <div class="indicator-value-compact">{{ stats.taux_conversion || '0%' }}</div>
                  <div class="indicator-progress">
                    <div class="progress" style="height: 4px;">
                      <div class="progress-bar bg-info" 
                           :style="{ width: getConversionScore() + '%' }"></div>
                    </div>
                    <div class="indicator-goal">
                      <small>Objectif : 80%</small>
                      <small :class="getConversionScore() >= 80 ? 'text-success' : 'text-warning'">
                        {{ getConversionScore() >= 80 ? '✓ Atteint' : 'En cours' }}
                      </small>
                    </div>
                  </div>
                </div>
                
                <!-- Taux de validation -->
                <div class="indicator-item-compact">
                  <div class="indicator-label-compact">Taux de Validation</div>
                  <div class="indicator-value-compact">
                    {{ calculatePercentage(stats.global?.validees, stats.global?.total_inscriptions) }}%
                  </div>
                  <div class="indicator-progress">
                    <div class="progress" style="height: 4px;">
                      <div class="progress-bar" 
                           :class="getTauxClass(calculatePercentage(stats.global?.validees, stats.global?.total_inscriptions))"
                           :style="{ width: calculatePercentage(stats.global?.validees, stats.global?.total_inscriptions) + '%' }"></div>
                    </div>
                  </div>
                </div>
                
                <!-- Score global basé sur les données réelles -->
                <div class="indicator-item-compact">
                  <div class="indicator-label-compact">Performance Globale</div>
                  <div class="indicator-value-compact">{{ getOverallScore() }}/100</div>
                  <div class="indicator-progress">
                    <div class="progress" style="height: 8px;">
                      <div class="progress-bar" 
                           :class="getScoreClass(getOverallScore())"
                           :style="{ width: getOverallScore() + '%' }"></div>
                    </div>
                    <div class="indicator-status">
                      <small :class="getScoreStatusClass(getOverallScore())">
                        {{ getScoreStatus(getOverallScore()) }}
                      </small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ========================================
       MODAL DÉTAILS FORMATION - SIMPLIFIÉ
       ======================================== -->
    <div v-if="showFormationModal" class="modal fade show d-block modal-overlay-simple" 
         @click.self="closeFormationModal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">
              <i class="bi bi-book me-2"></i>
              Détails de la Formation
            </h5>
            <button type="button" class="btn-close btn-close-white" @click="closeFormationModal"></button>
          </div>
          
          <div v-if="selectedFormation" class="modal-body">
            <!-- Titre et info de base -->
            <h5 class="mb-3">{{ selectedFormation.titre }}</h5>
            
            <div class="row mb-4">
              <div class="col-md-6">
                <div class="card border-0 shadow-sm mb-3">
                  <div class="card-body">
                    <h6 class="card-title">Statistiques</h6>
                    <div class="stats-grid">
                      <div class="stat-item">
                        <div class="stat-label">Total inscriptions</div>
                        <div class="stat-value">{{ selectedFormation.total_inscriptions }}</div>
                      </div>
                      <div class="stat-item">
                        <div class="stat-label">Validées</div>
                        <div class="stat-value text-success">{{ selectedFormation.validees }}</div>
                      </div>
                      <div class="stat-item">
                        <div class="stat-label">En attente</div>
                        <div class="stat-value text-warning">{{ selectedFormation.en_attente || 0 }}</div>
                      </div>
                      <div class="stat-item">
                        <div class="stat-label">Taux de validation</div>
                        <div class="stat-value" 
                             :class="getTauxTextClass(calculatePercentage(selectedFormation.validees, selectedFormation.total_inscriptions))">
                          {{ calculatePercentage(selectedFormation.validees, selectedFormation.total_inscriptions) }}%
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="card border-0 shadow-sm mb-3">
                  <div class="card-body">
                    <h6 class="card-title">Évaluation</h6>
                    <div class="evaluation">
                      <div class="evaluation-label">Performance</div>
                      <div class="evaluation-value" 
                           :class="getFormationPerformanceClass(selectedFormation)">
                        {{ getFormationPerformance(selectedFormation) }}
                      </div>
                      <div class="progress mt-2" style="height: 10px;">
                        <div class="progress-bar" 
                             :class="getFormationProgressClass(selectedFormation)"
                             :style="{ width: Math.min(calculatePercentage(selectedFormation.validees, selectedFormation.total_inscriptions), 100) + '%' }"></div>
                      </div>
                      <div class="evaluation-target mt-2">
                        <small class="text-muted">Objectif : 75%</small>
                        <small :class="getFormationPerformanceClass(selectedFormation)">
                          {{ getFormationPerformance(selectedFormation) }}
                        </small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Recommandation simple -->
            <div class="alert" 
                 :class="getFormationPerformanceClass(selectedFormation) === 'text-success' ? 'alert-success' : 'alert-warning'">
              <i class="bi" :class="getFormationPerformanceClass(selectedFormation) === 'text-success' ? 'bi-check-circle' : 'bi-exclamation-triangle'"></i>
              <span class="ms-2">
                <span v-if="getFormationPerformanceClass(selectedFormation) === 'text-success'">
                  Performance satisfaisante. Continuez ainsi.
                </span>
                <span v-else>
                  Potentiel d'amélioration. Analysez les inscriptions en attente.
                </span>
              </span>
            </div>
          </div>
          
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeFormationModal">
              Fermer
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import adminService from '@/services/admin'
import Chart from 'chart.js/auto'

export default {
  name: 'StatistiquesList',
  
  setup() {
    // États
    const loading = ref(true)
    const stats = ref({
      global: {
        total_inscriptions: 0,
        validees: 0,
        en_attente: 0,
        refusees: 0,
        annulees: 0
      },
      par_formation: [],
      par_mois: [],
      taux_conversion: '0%'
    })
    
    // Filtres
    const filterPeriod = ref('all')
    const filterFormation = ref('all')
    const comparisonMode = ref('none')
    const showAllMonths = ref(false)
    
    // Données pour les filtres
    const formationsList = ref([])
    
    // Modal
    const showFormationModal = ref(false)
    const selectedFormation = ref(null)
    
    // Références
    const pieChart = ref(null)
    let chartInstance = null
    
    // ========================================
    // FONCTIONS UTILITAIRES AJOUTÉES
    // ========================================
    
    // 1. Fonctions de style pour les taux
    const getTauxClass = (taux) => {
      if (taux >= 75) return 'bg-success'
      if (taux >= 50) return 'bg-info'
      if (taux >= 25) return 'bg-warning'
      return 'bg-danger'
    }
    
    const getTauxTextClass = (taux) => {
      if (taux >= 75) return 'text-success fw-bold'
      if (taux >= 50) return 'text-info fw-bold'
      if (taux >= 25) return 'text-warning fw-bold'
      return 'text-danger fw-bold'
    }
    
    const getScoreClass = (score) => {
      if (score >= 85) return 'bg-success'
      if (score >= 70) return 'bg-info'
      if (score >= 50) return 'bg-warning'
      return 'bg-danger'
    }
    
    const getScoreStatus = (score) => {
      if (score >= 85) return 'Excellent'
      if (score >= 70) return 'Bon'
      if (score >= 50) return 'Moyen'
      return 'À améliorer'
    }
    
    const getScoreStatusClass = (score) => {
      if (score >= 85) return 'text-success'
      if (score >= 70) return 'text-info'
      if (score >= 50) return 'text-warning'
      return 'text-danger'
    }
    
    // Fonction pour calculer la largeur des barres
    const calculateBarWidth = (value) => {
      const maxValue = Math.max(...(stats.value.par_mois?.map(m => m.inscriptions) || [0]))
      if (maxValue === 0) return 0
      return (value / maxValue) * 100
    }
    
    // 3. Calcul du score global
    const getOverallScore = () => {
      const conversion = getConversionScore()
      const validationRate = calculatePercentage(stats.value.global?.validees, stats.value.global?.total_inscriptions)
      const pendingRate = calculatePercentage(stats.value.global?.en_attente, stats.value.global?.total_inscriptions)
      
      let score = 0
      score += conversion * 0.4 // 40% pour le taux de conversion
      score += validationRate * 0.4 // 40% pour le taux de validation
      score += (100 - pendingRate) * 0.2 // 20% pour réduire les en attente
      
      return Math.min(Math.round(score), 100)
    }
    
    // ========================================
    // VOS FONCTIONS EXISTANTES
    // ========================================
    
    // Computed
    const formations = computed(() => {
      if (formationsList.value.length > 0) {
        return formationsList.value
      }
      return stats.value.par_formation.map(f => ({
        id: f.id,
        titre: f.titre
      }))
    })
    
    const displayedMonths = computed(() => {
      if (showAllMonths.value) {
        return stats.value.par_mois || []
      }
      return (stats.value.par_mois || []).slice(0, 6)
    })
    
    // Méthodes utilitaires
    const formatNumber = (num) => {
      return new Intl.NumberFormat('fr-FR').format(num)
    }
    
    const calculatePercentage = (part, total) => {
      if (!total || total === 0) return 0
      return Math.round((part / total) * 100)
    }
    
    const formatMonth = (monthStr) => {
      if (!monthStr) return ''
      try {
        const [year, month] = monthStr.split('-')
        const date = new Date(year, month - 1)
        return date.toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' })
      } catch (e) {
        return monthStr
      }
    }
    
    const formatDateRange = () => {
      switch (filterPeriod.value) {
        case 'today': return "Aujourd'hui"
        case 'week': return "Cette semaine"
        case 'month': return "Ce mois"
        case 'quarter': return "Ce trimestre"
        case 'year': return "Cette année"
        default: return "Toutes périodes"
      }
    }
    
    const getConversionScore = () => {
      try {
        const tauxStr = stats.value.taux_conversion || '0%'
        const taux = parseFloat(tauxStr.replace('%', '')) || 0
        return taux
      } catch (e) {
        return 0
      }
    }
    
    const getStatusDistribution = () => {
      const global = stats.value.global || {}
      const total = global.total_inscriptions || 1
      
      return [
        {
          label: 'Validées',
          value: global.validees || 0,
          percentage: calculatePercentage(global.validees, total),
          class: 'bg-success',
          color: '#28a745'
        },
        {
          label: 'En attente',
          value: global.en_attente || 0,
          percentage: calculatePercentage(global.en_attente, total),
          class: 'bg-warning',
          color: '#ffc107'
        },
        {
          label: 'Refusées',
          value: global.refusees || 0,
          percentage: calculatePercentage(global.refusees, total),
          class: 'bg-danger',
          color: '#dc3545'
        },
        {
          label: 'Annulées',
          value: global.annulees || 0,
          percentage: calculatePercentage(global.annulees, total),
          class: 'bg-secondary',
          color: '#6c757d'
        }
      ]
    }
    
    const getFormationPerformance = (formation) => {
      if (!formation) return 'N/A'
      const taux = calculatePercentage(formation.validees, formation.total_inscriptions)
      if (taux >= 75) return 'Excellent'
      if (taux >= 50) return 'Bon'
      if (taux >= 25) return 'Moyen'
      return 'À améliorer'
    }
    
    const getFormationPerformanceClass = (formation) => {
      if (!formation) return 'text-muted'
      const taux = calculatePercentage(formation.validees, formation.total_inscriptions)
      if (taux >= 75) return 'text-success'
      if (taux >= 50) return 'text-info'
      if (taux >= 25) return 'text-warning'
      return 'text-danger'
    }
    
    const getFormationProgressClass = (formation) => {
      if (!formation) return 'bg-secondary'
      const taux = calculatePercentage(formation.validees, formation.total_inscriptions)
      if (taux >= 75) return 'bg-success'
      if (taux >= 50) return 'bg-info'
      if (taux >= 25) return 'bg-warning'
      return 'bg-danger'
    }
    
    // Méthodes principales
    const loadFormations = async () => {
      try {
        const response = await adminService.getFormations ? await adminService.getFormations() : null
        
        if (response && response.success) {
          formationsList.value = response.data || []
        } else {
          formationsList.value = stats.value.par_formation.map(f => ({
            id: f.id,
            titre: f.titre
          }))
        }
      } catch (error) {
        console.error('Erreur chargement formations:', error)
        formationsList.value = []
      }
    }
    
    const loadStatistics = async () => {
      loading.value = true
      try {
        console.log('📊 Chargement des statistiques...')
        
        const params = {}
        if (filterPeriod.value !== 'all') {
          params.period = filterPeriod.value
        }
        if (filterFormation.value !== 'all') {
          params.formation_id = filterFormation.value
        }
        if (comparisonMode.value !== 'none') {
          params.comparison = comparisonMode.value
        }
        
        console.log('🔍 Paramètres statistiques:', params)
        
        const response = await adminService.getStats(params)
        console.log('📈 Réponse API statistiques:', response)
        
        if (response && response.success && response.data) {
          stats.value = {
            global: response.data.global || {
              total_inscriptions: 0,
              validees: 0,
              en_attente: 0,
              refusees: 0,
              annulees: 0
            },
            par_formation: response.data.par_formation || [],
            par_mois: response.data.par_mois || [],
            taux_conversion: response.data.taux_conversion || '0%'
          }
          
          if (formationsList.value.length === 0 && stats.value.par_formation.length > 0) {
            formationsList.value = stats.value.par_formation.map(f => ({
              id: f.id,
              titre: f.titre
            })).filter(f => f.id && f.titre)
          }
          
          console.log('✅ Statistiques chargées avec succès')
          
          await nextTick()
          updatePieChart()
        } else {
          const errorMsg = response?.message || 'Réponse invalide de l\'API'
          console.error('❌ Erreur API:', errorMsg)
          throw new Error(errorMsg)
        }
      } catch (error) {
        console.error('❌ Erreur lors du chargement des statistiques:', error)
        
        // Données de test en cas d'erreur
        stats.value = {
          global: {
            total_inscriptions: 150,
            validees: 100,
            en_attente: 25,
            refusees: 15,
            annulees: 10
          },
          par_formation: [
            { id: 1, titre: 'Formation Développement Web', total_inscriptions: 50, validees: 35, en_attente: 10, refusees: 3, annulees: 2 },
            { id: 2, titre: 'Formation Marketing Digital', total_inscriptions: 40, validees: 30, en_attente: 5, refusees: 3, annulees: 2 },
            { id: 3, titre: 'Formation Gestion de Projet', total_inscriptions: 60, validees: 35, en_attente: 10, refusees: 9, annulees: 6 }
          ],
          par_mois: [
            { mois: '2024-01', inscriptions: 25 },
            { mois: '2024-02', inscriptions: 30 },
            { mois: '2024-03', inscriptions: 35 },
            { mois: '2024-04', inscriptions: 28 },
            { mois: '2024-05', inscriptions: 32 },
            { mois: '2024-06', inscriptions: 40 }
          ],
          taux_conversion: '66.67%'
        }
        
        formationsList.value = stats.value.par_formation.map(f => ({
          id: f.id,
          titre: f.titre
        }))
        
        await nextTick()
        updatePieChart()
        
        alert('Les données de test sont affichées. L\'API semble rencontrer des problèmes.')
      } finally {
        loading.value = false
      }
    }
    
    const updatePieChart = () => {
      if (!pieChart.value) {
        console.warn('Canvas pieChart non trouvé, vérifiez la référence dans le template')
        return
      }
      
      if (chartInstance) {
        chartInstance.destroy()
        chartInstance = null
      }
      
      const distribution = getStatusDistribution()
      const hasData = distribution.some(d => d.value > 0)
      
      if (!hasData) {
        console.log('Aucune donnée pour le graphique')
        const ctx = pieChart.value.getContext('2d')
        ctx.clearRect(0, 0, pieChart.value.width, pieChart.value.height)
        ctx.fillStyle = '#6c757d'
        ctx.font = '14px Arial'
        ctx.textAlign = 'center'
        ctx.fillText('Aucune donnée', pieChart.value.width / 2, pieChart.value.height / 2)
        return
      }
      
      try {
        const ctx = pieChart.value.getContext('2d')
        chartInstance = new Chart(ctx, {
          type: 'doughnut',
          data: {
            labels: distribution.map(d => d.label),
            datasets: [{
              data: distribution.map(d => d.value),
              backgroundColor: distribution.map(d => d.color),
              borderWidth: 2,
              borderColor: '#fff'
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: false
              },
              tooltip: {
                callbacks: {
                  label: function(context) {
                    const label = context.label || ''
                    const value = context.raw || 0
                    const percentage = distribution[context.dataIndex]?.percentage || 0
                    return `${label}: ${value} (${percentage}%)`
                  }
                }
              }
            },
            cutout: '70%',
            animation: {
              animateScale: true,
              animateRotate: true
            }
          }
        })
        
        console.log('✅ Graphique mis à jour avec succès')
      } catch (error) {
        console.error('❌ Erreur création graphique:', error)
      }
    }
    
    const refreshData = () => {
      console.log('🔄 Rafraîchissement des données...')
      loadStatistics()
    }
    
    const exportStats = () => {
      try {
        const dataStr = JSON.stringify(stats.value, null, 2)
        const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr)
        
        const exportFileDefaultName = `statistiques_${new Date().toISOString().split('T')[0]}.json`
        
        const linkElement = document.createElement('a')
        linkElement.setAttribute('href', dataUri)
        linkElement.setAttribute('download', exportFileDefaultName)
        linkElement.click()
        
        alert('Export terminé !')
      } catch (error) {
        console.error('❌ Erreur export:', error)
        alert('Erreur lors de l\'export')
      }
    }
    
    const viewFormationDetails = (formation) => {
      if (!formation) return
      selectedFormation.value = formation
      showFormationModal.value = true
      console.log('📋 Détails formation:', formation)
    }
    
    const closeFormationModal = () => {
      showFormationModal.value = false
      selectedFormation.value = null
    }
    
    // Initialisation
    const init = async () => {
      console.log('🚀 Initialisation StatistiquesList...')
      try {
        await loadFormations()
        await loadStatistics()
        console.log('✅ StatistiquesList initialisé avec succès')
      } catch (error) {
        console.error('❌ Erreur initialisation:', error)
      }
    }
    
    // Lifecycle
    onMounted(() => {
      init()
    })
    
    onUnmounted(() => {
      if (chartInstance) {
        chartInstance.destroy()
        chartInstance = null
      }
    })
    
    // ========================================
    // RETURN - SEULEMENT LES FONCTIONS UTILISÉES
    // ========================================
    return {
      // États
      loading,
      stats,
      filterPeriod,
      filterFormation,
      comparisonMode,
      showAllMonths,
      showFormationModal,
      selectedFormation,
      
      // Références
      pieChart,
      
      // Computed
      formations,
      displayedMonths,
      
      // Méthodes existantes
      formatNumber,
      calculatePercentage,
      formatMonth,
      formatDateRange,
      getConversionScore,
      getStatusDistribution,
      getFormationPerformance,
      getFormationPerformanceClass,
      getFormationProgressClass,
      loadStatistics,
      refreshData,
      exportStats,
      viewFormationDetails,
      closeFormationModal,
      
      // NOUVELLES FONCTIONS
      getTauxClass,
      getTauxTextClass,
      getScoreClass,
      getScoreStatus,
      getScoreStatusClass,
      calculateBarWidth,
      getOverallScore
    }
  }
}
</script>

<style scoped>
/* ========================================
   STYLES SIMPLIFIÉS ET FONCTIONNELS
   ======================================== */
.dashboard-statistiques {
  min-height: calc(100vh - 200px);
  background: #f8f9fa;
  padding-bottom: 2rem;
}

/* Header simplifié */
.dashboard-header {
  background: white;
  padding: 1.5rem 0;
  margin-bottom: 1rem;
  border-bottom: 1px solid #dee2e6;
  box-shadow: 0 2px 4px rgba(0,0,0,.1);
}

.header-content {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.icon-wrapper {
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, #0d6efd, #0a58ca);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.icon-wrapper i {
  font-size: 1.5rem;
  color: white;
}

.page-title {
  color: #212529;
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.page-subtitle {
  color: #6c757d;
  font-size: 0.9rem;
}

/* KPI Cards simplifiées */
.kpi-card-simple {
  background: white;
  border-radius: 10px;
  padding: 1.25rem;
  box-shadow: 0 2px 8px rgba(0,0,0,.08);
  border: 1px solid #e9ecef;
  height: 100%;
  transition: all 0.2s ease;
}

.kpi-card-simple:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,.1);
}

.kpi-icon-simple {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  margin-bottom: 1rem;
}

.kpi-primary .kpi-icon-simple {
  background: rgba(13, 110, 253, 0.1);
  color: #0d6efd;
}

.kpi-success .kpi-icon-simple {
  background: rgba(25, 135, 84, 0.1);
  color: #198754;
}

.kpi-warning .kpi-icon-simple {
  background: rgba(255, 193, 7, 0.1);
  color: #ffc107;
}

.kpi-danger .kpi-icon-simple {
  background: rgba(220, 53, 69, 0.1);
  color: #dc3545;
}

.kpi-label-simple {
  color: #6c757d;
  font-size: 0.85rem;
  font-weight: 500;
  margin-bottom: 0.5rem;
  text-transform: uppercase;
}

.kpi-value-simple {
  font-size: 2rem;
  font-weight: 700;
  color: #212529;
  line-height: 1;
  margin-bottom: 0.75rem;
}

.kpi-details-simple {
  font-size: 0.85rem;
}

.percentage-badge-success {
  background: rgba(25, 135, 84, 0.1);
  color: #198754;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-weight: 600;
}

.percentage-badge-warning {
  background: rgba(255, 193, 7, 0.1);
  color: #ffc107;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-weight: 600;
}

/* Cartes de statistiques compactes */
.stat-card-compact {
  background: white;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0,0,0,.08);
  border: 1px solid #e9ecef;
  height: 100%;
  overflow: hidden;
}

.card-header-compact {
  padding: 1rem 1.25rem;
  background: #f8f9fa;
  border-bottom: 1px solid #e9ecef;
  display: flex;
  align-items: center;
}

.card-header-compact h6 {
  color: #212529;
  font-weight: 600;
  margin: 0;
}

.card-body-compact {
  padding: 1.25rem;
}

/* Distribution de statut */
.loading-mini {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 180px;
}

.distribution-chart {
  height: 180px;
  position: relative;
}

.distribution-legend {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.legend-item-compact {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.25rem;
}

.legend-color {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  flex-shrink: 0;
}

.legend-text {
  flex: 1;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.85rem;
}

.legend-label {
  color: #212529;
  font-weight: 500;
}

.legend-value {
  color: #212529;
  font-weight: 600;
  margin: 0 0.5rem;
}

.legend-percentage {
  color: #6c757d;
  min-width: 45px;
  text-align: right;
}

/* Évolution mensuelle */
.monthly-evolution {
  padding: 0.25rem 0;
}

.evolution-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.evolution-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.5rem 0;
}

.evolution-month {
  width: 140px;
  flex-shrink: 0;
  font-size: 0.9rem;
}

.evolution-bar {
  flex: 1;
}

.evolution-count {
  width: 60px;
  text-align: right;
  flex-shrink: 0;
}

/* Tableau formations */
.loading-table {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 200px;
}

.progress-wrapper-compact {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.progress-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #212529;
}

/* Détails non-validations */
.detail-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  background: #f8f9fa;
}

.detail-icon {
  font-size: 1.5rem;
  flex-shrink: 0;
}

.detail-content {
  flex: 1;
}

.detail-label {
  color: #6c757d;
  font-size: 0.85rem;
  margin-bottom: 0.25rem;
}

.detail-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: #212529;
  line-height: 1;
  margin-bottom: 0.25rem;
}

.detail-percentage {
  font-size: 0.85rem;
  font-weight: 600;
}

.insight-simple {
  background: #f8f9fa !important;
  border-left: 3px solid #ffc107;
}

/* Indicateurs clés */
.indicators-grid {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.indicator-item-compact {
  padding: 0.75rem;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  background: #f8f9fa;
}

.indicator-label-compact {
  color: #6c757d;
  font-size: 0.85rem;
  font-weight: 500;
  margin-bottom: 0.5rem;
}

.indicator-value-compact {
  font-size: 1.5rem;
  font-weight: 700;
  color: #212529;
  line-height: 1;
  margin-bottom: 0.75rem;
}

.indicator-progress {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.indicator-goal {
  display: flex;
  justify-content: space-between;
  font-size: 0.75rem;
}

.indicator-status {
  text-align: center;
  margin-top: 0.25rem;
}

/* Modal simplifié */
.modal-overlay-simple {
  background: rgba(0,0,0,0.5);
  backdrop-filter: blur(3px);
}

/* Responsive */
@media (max-width: 768px) {
  .header-content {
    flex-direction: column;
    text-align: center;
    gap: 0.75rem;
  }
  
  .kpi-value-simple {
    font-size: 1.75rem;
  }
  
  .evolution-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  
  .evolution-month,
  .evolution-bar,
  .evolution-count {
    width: 100%;
  }
  
  .detail-item {
    flex-direction: column;
    text-align: center;
    gap: 0.75rem;
  }
}

@media (max-width: 576px) {
  .icon-wrapper {
    width: 40px;
    height: 40px;
  }
  
  .icon-wrapper i {
    font-size: 1.25rem;
  }
  
  .page-title {
    font-size: 1.25rem;
  }
  
  .kpi-card-simple {
    padding: 1rem;
  }
  
  .card-body-compact {
    padding: 1rem;
  }
}
</style>