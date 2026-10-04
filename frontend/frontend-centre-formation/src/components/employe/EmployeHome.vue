<template>
  <div class="employe-home">
    <!-- Hero Section -->
    <section class="hero-section">
      <div class="hero-overlay"></div>
      <div class="container">
        <div class="hero-content">
          <div class="row align-items-center">
            <div class="col-lg-6">
              <h1 class="hero-title">
                Votre parcours d'excellence 
                <span class="highlight">STIR</span>
              </h1>
              <p class="hero-subtitle">
                Développez vos compétences avec notre plateforme de formation interne. 
                Accédez à des programmes personnalisés pour booster votre carrière.
              </p>
              <div class="hero-buttons">
                <router-link to="/employe/formations" class="btn btn-primary btn-lg">
                  <i class="bi bi-search me-2"></i>
                  Explorer les formations
                </router-link>
                <router-link to="/employe/mes-inscriptions" class="btn btn-outline-light btn-lg">
                  <i class="bi bi-list-check me-2"></i>
                  Mes inscriptions
                </router-link>
              </div>
              <div class="hero-stats mt-5">
                <div class="stat-item">
                  <i class="bi bi-people"></i>
                  <div>
                    <h4>500+</h4>
                    <p>Employés formés</p>
                  </div>
                </div>
                <div class="stat-item">
                  <i class="bi bi-book"></i>
                  <div>
                    <h4>120+</h4>
                    <p>Formations disponibles</p>
                  </div>
                </div>
                <div class="stat-item">
                  <i class="bi bi-award"></i>
                  <div>
                    <h4>98%</h4>
                    <p>Satisfaction</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="hero-image">
                <!-- Image temporaire - remplacez par votre SVG -->
                
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- À propos -->
    <section class="about-section-modern">
      <div class="container">
        <!-- En-tête élégant -->
        <div class="section-header-modern">
          <h2 class="section-title-elegant">Excellence & Développement</h2>
          <p class="section-subtitle-elegant">
            Chez STIR, nous croyons au développement continu de nos talents à travers des formations innovantes et personnalisées
          </p>
        </div>
        
        <!-- Cartes de fonctionnalités modernisées -->
        <div class="row g-4 mb-5">
          <div class="col-lg-4 col-md-6">
            <div class="feature-card-elegant">
              <div class="feature-icon-modern purple-gradient">
                <i class="bi bi-person-badge"></i>
                <div class="icon-glow purple"></div>
              </div>
              <h4 class="feature-title-elegant">Développement Personnalisé</h4>
              <p class="feature-desc-elegant">Des parcours sur-mesure adaptés à votre profil et vos ambitions professionnelles pour une progression optimale.</p>
            </div>
          </div>
          
          <div class="col-lg-4 col-md-6">
            <div class="feature-card-elegant">
              <div class="feature-icon-modern blue-gradient">
                <i class="bi bi-rocket-takeoff"></i>
                <div class="icon-glow blue"></div>
              </div>
              <h4 class="feature-title-elegant">Innovation Continue</h4>
              <p class="feature-desc-elegant">Contenu actualisé régulièrement avec les dernières tendances et technologies du marché professionnel.</p>
            </div>
          </div>
          
          <div class="col-lg-4 col-md-6">
            <div class="feature-card-elegant">
              <div class="feature-icon-modern green-gradient">
                <i class="bi bi-shield-check"></i>
                <div class="icon-glow green"></div>
              </div>
              <h4 class="feature-title-elegant">Certification Reconnue</h4>
              <p class="feature-desc-elegant">Obtenez des certifications valorisantes et reconnues qui boostent votre carrière et votre expertise.</p>
            </div>
          </div>
        </div>

        <!-- Section Pourquoi STIR - Design Premium -->
        <div class="why-stir-section">
          <div class="row align-items-center g-5">
            <div class="col-lg-6">
              <div class="about-image-modern">
                <div class="image-wrapper">
                  <div class="floating-card card-1">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>+150%</span>
                    <p>Croissance</p>
                  </div>
                  <div class="floating-card card-2">
                    <i class="bi bi-people-fill"></i>
                    <span>500+</span>
                    <p>Formés</p>
                  </div>
                  <div class="floating-card card-3">
                    <i class="bi bi-award-fill"></i>
                    <span>98%</span>
                    <p>Satisfaction</p>
                  </div>
                  <div class="main-illustration">
                    <i class="bi bi-mortarboard-fill"></i>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="col-lg-6">
              <div class="about-content-modern">
                <h3 class="about-title-elegant">
                  Pourquoi choisir 
                  <span class="gradient-text-elegant">STIR Formation</span> ?
                </h3>
                <p class="about-intro-elegant">
                  Une plateforme complète conçue pour votre réussite professionnelle
                </p>
                
                <ul class="features-list-modern">
                  <li class="feature-item-modern">
                    <div class="check-icon-modern">
                      <i class="bi bi-check2"></i>
                    </div>
                    <div class="feature-text-modern">
                      <h5>Expertise Reconnue</h5>
                      <p>Formations conçues par des experts internes certifiés</p>
                    </div>
                  </li>
                  
                  <li class="feature-item-modern">
                    <div class="check-icon-modern">
                      <i class="bi bi-check2"></i>
                    </div>
                    <div class="feature-text-modern">
                      <h5>Flexibilité Totale</h5>
                      <p>Apprenez à votre rythme, où et quand vous voulez</p>
                    </div>
                  </li>
                  
                  <li class="feature-item-modern">
                    <div class="check-icon-modern">
                      <i class="bi bi-check2"></i>
                    </div>
                    <div class="feature-text-modern">
                      <h5>Support Personnalisé</h5>
                      <p>Accompagnement dédié tout au long de votre parcours</p>
                    </div>
                  </li>
                  
                  <li class="feature-item-modern">
                    <div class="check-icon-modern">
                      <i class="bi bi-check2"></i>
                    </div>
                    <div class="feature-text-modern">
                      <h5>Communauté Active</h5>
                      <p>Échangez et apprenez avec vos collègues</p>
                    </div>
                  </li>
                  
                  <li class="feature-item-modern">
                    <div class="check-icon-modern">
                      <i class="bi bi-check2"></i>
                    </div>
                    <div class="feature-text-modern">
                      <h5>Impact Mesurable</h5>
                      <p>Résultats concrets sur votre évolution professionnelle</p>
                    </div>
                  </li>
                </ul>
                
                <router-link to="/employe/profil" class="btn-elegant-primary">
                  <i class="bi bi-person-circle"></i>
                  <span>Découvrir mon parcours</span>
                  <i class="bi bi-arrow-right"></i>
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Témoignages OPTIMISÉS -->
    <section class="testimonials-section-modern">
      <div class="container">
        <div class="section-header-modern">
          
          <h2 class="section-title-elegant">Ce que disent nos employés</h2>
          <p class="section-subtitle-elegant">Découvrez les expériences de vos collègues</p>
        </div>

        <div class="row g-4">
          <div class="col-md-4" v-for="testimonial in testimonials" :key="testimonial.id">
            <div class="testimonial-card-modern">
              <div class="testimonial-header-modern">
                <div class="author-avatar-modern" :style="{ background: getAvatarGradient(testimonial.id) }">
                  {{ getInitials(testimonial.name) }}
                </div>
                <div class="author-info-modern">
                  <h5>{{ testimonial.name }}</h5>
                  <p>{{ testimonial.position }}</p>
                </div>
                <div class="rating-modern">
                  <i v-for="n in 5" :key="n" 
                     class="bi bi-star-fill" 
                     :class="{ 'filled': n <= testimonial.rating }"></i>
                </div>
              </div>
              <div class="testimonial-content-modern">
                <p>"{{ testimonial.content }}"</p>
              </div>
              <div class="testimonial-footer-modern">
                <span class="course-taken-modern">
                  <i class="bi bi-book me-1"></i>
                  {{ testimonial.course }}
                </span>
                <span class="date-modern">{{ testimonial.date }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="main-footer-modern">
      <div class="container">
        <div class="row">
          <div class="col-lg-4">
            <div class="footer-brand-modern">
              <img src="@/assets/logoStir.png" alt="STIR Logo" class="footer-logo">
              <p class="mt-3">
                Plateforme de formation interne dédiée au développement 
                continu des compétences de nos employés.
              </p>
              <div class="social-links-modern">
                <a href="#" class="social-link-modern"><i class="bi bi-facebook"></i></a>
                <a href="#" class="social-link-modern"><i class="bi bi-linkedin"></i></a>
                <a href="#" class="social-link-modern"><i class="bi bi-twitter"></i></a>
                <a href="#" class="social-link-modern"><i class="bi bi-instagram"></i></a>
              </div>
            </div>
          </div>
          
          <div class="col-lg-2 col-md-6">
            <h5 class="footer-title-modern">Navigation</h5>
            <ul class="footer-links-modern">
              <li><router-link to="/employe">Accueil</router-link></li>
              <li><router-link to="/employe/formations">Formations</router-link></li>
              <li><router-link to="/employe/mes-inscriptions">Mes inscriptions</router-link></li>
              <li><router-link to="/employe/calendrier">Calendrier</router-link></li>
              <li><router-link to="/employe/profil">Mon profil</router-link></li>
            </ul>
          </div>
          
          <div class="col-lg-3 col-md-6">
            <h5 class="footer-title-modern">Ressources</h5>
            <ul class="footer-links-modern">
              <li><router-link to="/employe/aide">Centre d'aide</router-link></li>
              <li><router-link to="/employe/faq">FAQ</router-link></li>
              <li><router-link to="/employe/blog">Blog</router-link></li>
              <li><router-link to="/employe/documents">Documents</router-link></li>
              <li><router-link to="/employe/politique">Politique de confidentialité</router-link></li>
            </ul>
          </div>
          
          <div class="col-lg-3">
            <h5 class="footer-title-modern">Contact</h5>
            <ul class="contact-info-modern">
              <li>
                <div class="contact-icon-modern">
                  <i class="bi bi-geo-alt"></i>
                </div>
                <span>Siège Social STIR, Tunis, Tunisie</span>
              </li>
              <li>
                <div class="contact-icon-modern">
                  <i class="bi bi-telephone"></i>
                </div>
                <span>+216 70 000 000</span>
              </li>
              <li>
                <div class="contact-icon-modern">
                  <i class="bi bi-envelope"></i>
                </div>
                <span>formation@stir.com</span>
              </li>
              <li>
                <div class="contact-icon-modern">
                  <i class="bi bi-clock"></i>
                </div>
                <span>Lun - Ven: 8h00 - 17h00</span>
              </li>
            </ul>
          </div>
        </div>
        
        <div class="footer-bottom-modern">
          <div class="row align-items-center">
            <div class="col-md-6">
              <p class="mb-0">&copy; {{ currentYear }} STIR Formation Interne. Tous droits réservés.</p>
            </div>
            <div class="col-md-6 text-end">
              <p class="mb-0">Développé avec <i class="bi bi-heart-fill text-primary"></i> pour nos employés</p>
            </div>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script>
import { ref, computed } from 'vue'

export default {
  name: 'EmployeHome',
  
  setup() {
    const currentYear = computed(() => new Date().getFullYear())
    
    const testimonials = ref([
      {
        id: 1,
        name: 'Amira Chatti',
        position: 'Chef de Projet',
        rating: 5,
        content: 'Les formations STIR ont transformé ma manière de gérer les projets. Très pratique et adapté à nos besoins.',
        course: 'Gestion de Projet Agile',
        date: 'Jan 2024'
      },
      {
        id: 2,
        name: 'Karim Ben Salem',
        position: 'Data Analyst',
        rating: 4,
        content: 'Excellent contenu sur l\'analyse de données. J\'ai pu appliquer directement les concepts dans mon travail.',
        course: 'Data Science Fundamentals',
        date: 'Déc 2023'
      },
      {
        id: 3,
        name: 'Sara Jlassi',
        position: 'Responsable RH',
        rating: 5,
        content: 'Plateforme intuitive avec un excellent support. Les formations soft skills sont particulièrement utiles.',
        course: 'Communication Efficace',
        date: 'Nov 2023'
      }
    ])
    
    const getAvatarGradient = (id) => {
      const gradients = [
        'linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%)',
        'linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%)',
        'linear-gradient(135deg, #10b981 0%, #34d399 100%)'
      ];
      return gradients[(id - 1) % gradients.length];
    }
    
    const getInitials = (name) => {
      return name.split(' ').map(n => n[0]).join('').toUpperCase()
    }
    
    return {
      currentYear,
      testimonials,
      getAvatarGradient,
      getInitials
    }
  }
}
</script>

<style scoped>
.employe-home {
  font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
}

/* Hero Section */
.hero-section {
  background-image: url('@/assets/fond.jpg');
  color: white;
  padding: 120px 0 80px;
  position: relative;
  overflow: hidden;
  margin-top: 0%;
  
  /* Propriétés pour l'image de fond */
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  min-height: 90vh;
  width: 100%;
}

/* Parallaxe uniquement sur desktop */
@media (min-width: 769px) {
  .hero-section {
    background-attachment: fixed;
  }
}

.hero-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(
    to bottom,
    rgba(43, 50, 63, 0.85) 0%,
    rgba(39, 54, 76, 0.75) 50%,
    rgba(38, 50, 76, 0.85) 100%
  );
  z-index: 1;
}

.hero-section > * {
  position: relative;
  z-index: 2;
}

.hero-content {
  position: relative;
  z-index: 2;
}

.hero-title {
  font-size: 3.5rem;
  font-weight: 800;
  line-height: 1.2;
  margin-bottom: 1.5rem;
}

.highlight {
  color: #4ade80;
  position: relative;
}

.highlight::after {
  content: '';
  position: absolute;
  bottom: 5px;
  left: 0;
  width: 100%;
  height: 8px;
  background: rgba(74, 222, 128, 0.3);
  z-index: -1;
}

.hero-subtitle {
  font-size: 1.2rem;
  opacity: 0.9;
  margin-bottom: 2rem;
  max-width: 600px;
}

.hero-buttons {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.hero-buttons .btn {
  padding: 12px 30px;
  border-radius: 50px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.hero-buttons .btn-primary {
  background: #4ade80;
  border: none;
}

.hero-buttons .btn-primary:hover {
  background: #22c55e;
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(74, 222, 128, 0.3);
}

.hero-buttons .btn-outline-light:hover {
  transform: translateY(-3px);
}

.hero-stats {
  display: flex;
  gap: 3rem;
  margin-top: 3rem;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stat-item i {
  font-size: 2.5rem;
  color: #4ade80;
}

.stat-item h4 {
  font-size: 2rem;
  margin: 0;
  font-weight: 700;
}

.stat-item p {
  margin: 0;
  opacity: 0.8;
  font-size: 0.9rem;
}

.hero-image {
  animation: float 6s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-20px); }
}

/* About Section */
.about-section-modern {
  padding: 70px 0;
  background: linear-gradient(180deg,#9fc2dc 0%, #d4e4e8 50%, #f0f9ff 100%);
  position: relative;
  overflow: hidden;
  margin-bottom: 0%;
}

.about-section-modern::before {
  content: '';
  position: absolute;
  top: 0;
  right: 0;
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, rgba(64, 169, 234, 0.05) 0%, transparent 70%);
  border-radius: 50%;
}

/* En-tête Section */
.section-header-modern {
  text-align: center;
  margin-bottom: 60px;
  position: relative;
}

.section-badge-elegant {
  display: inline-block;
  padding: 10px 24px;
  background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
  color: #0369a1;
  border-radius: 30px;
  font-size: 14px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 20px;
  box-shadow: 0 4px 14px rgba(64, 169, 234, 0.15);
}

.section-badge-elegant.blue {
  background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
  color: white;
  box-shadow: 0 4px 14px rgba(59, 130, 246, 0.15);
}

.section-title-elegant {
  font-size: 2.5rem;
  font-weight: 800;
  color: #1e293b;
  margin-bottom: 20px;
  letter-spacing: -0.5px;
}

.section-subtitle-elegant {
  font-size: 1.1rem;
  color: #64748b;
  max-width: 700px;
  margin: 0 auto;
  line-height: 1.7;
}

/* ========================================
   CARTES DE FONCTIONNALITÉS ÉLÉGANTES
   ======================================== */
.feature-card-elegant {
  background: white;
  padding: 40px 32px;
  border-radius: 24px;
  height: 100%;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid #e2e8f0;
  position: relative;
  overflow: hidden;
}

.feature-card-elegant::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 4px;
  background: linear-gradient(90deg, transparent, #40a9ea, transparent);
  transform: scaleX(0);
  transition: transform 0.4s ease;
}

.feature-card-elegant:hover::before {
  transform: scaleX(1);
}

.feature-card-elegant:hover {
  transform: translateY(-16px);
  box-shadow: 0 24px 48px rgba(0, 0, 0, 0.12);
  border-color: #40a9ea;
}

/* Icônes Modernes avec Gradients */
.feature-icon-modern {
  width: 90px;
  height: 90px;
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 40px;
  color: white;
  margin-bottom: 28px;
  position: relative;
  transition: all 0.4s ease;
}

.feature-card-elegant:hover .feature-icon-modern {
  transform: scale(1.1) rotate(-5deg);
}

.purple-gradient {
  background: linear-gradient(135deg, #a78bfa 0%, #8b5cf6 100%);
  box-shadow: 0 8px 24px rgba(139, 92, 246, 0.3);
}

.blue-gradient {
  background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
  box-shadow: 0 8px 24px rgba(59, 130, 246, 0.3);
}

.green-gradient {
  background: linear-gradient(135deg, #34d399 0%, #10b981 100%);
  box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3);
}

.icon-glow {
  position: absolute;
  width: 100%;
  height: 100%;
  border-radius: 20px;
  opacity: 0;
  transition: opacity 0.4s ease;
}

.feature-card-elegant:hover .icon-glow {
  opacity: 0.3;
  animation: pulse-glow 2s infinite;
}

@keyframes pulse-glow {
  0%, 100% { transform: scale(1); opacity: 0.3; }
  50% { transform: scale(1.2); opacity: 0.1; }
}

.icon-glow.purple {
  background: radial-gradient(circle, #a78bfa, transparent);
}

.icon-glow.blue {
  background: radial-gradient(circle, #60a5fa, transparent);
}

.icon-glow.green {
  background: radial-gradient(circle, #34d399, transparent);
}

.feature-title-elegant {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 16px;
  line-height: 1.3;
}

.feature-desc-elegant {
  color: #64748b;
  line-height: 1.8;
  margin-bottom: 24px;
  font-size: 15px;
}

/* ========================================
   SECTION POURQUOI STIR
   ======================================== */
.why-stir-section {
  margin-top: 80px;
  padding: 60px 0;
}

/* Image avec cartes flottantes */
.about-image-modern {
  position: relative;
  height: 500px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.image-wrapper {
  position: relative;
  width: 100%;
  height: 100%;
}

.main-illustration {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 280px;
  height: 280px;
  background: linear-gradient(135deg, #40a9ea 0%, #2d8de8 100%);
  border-radius: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 120px;
  color: white;
  box-shadow: 0 20px 60px rgba(64, 169, 234, 0.3);
  animation: float-main 6s ease-in-out infinite;
}

@keyframes float-main {
  0%, 100% { transform: translate(-50%, -50%) translateY(0); }
  50% { transform: translate(-50%, -50%) translateY(-20px); }
}

.floating-card {
  position: absolute;
  background: white;
  padding: 24px 20px;
  border-radius: 20px;
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
  text-align: center;
  min-width: 140px;
  transition: all 0.3s ease;
  animation: float-card 6s ease-in-out infinite;
}

.floating-card:hover {
  transform: scale(1.1);
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.18);
}

.floating-card i {
  font-size: 32px;
  color: #40a9ea;
  margin-bottom: 8px;
}

.floating-card span {
  display: block;
  font-size: 2rem;
  font-weight: 800;
  color: #1e293b;
  margin-bottom: 4px;
}

.floating-card p {
  margin: 0;
  font-size: 13px;
  color: #64748b;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.card-1 {
  top: 10%;
  right: 0;
  animation-delay: 0s;
}

.card-2 {
  top: 50%;
  left: 0;
  transform: translateY(-50%);
  animation-delay: 1s;
}

.card-3 {
  bottom: 10%;
  right: 5%;
  animation-delay: 2s;
}

@keyframes float-card {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-15px); }
}

/* Contenu About */
.about-content-modern {
  padding-left: 20px;
}

.about-title-elegant {
  font-size: 2.2rem;
  font-weight: 800;
  color: #1e293b;
  margin-bottom: 16px;
  line-height: 1.2;
}

.gradient-text-elegant {
  background: linear-gradient(135deg, #40a9ea 0%, #2d8de8 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.about-intro-elegant {
  font-size: 1.1rem;
  color: #64748b;
  margin-bottom: 32px;
  line-height: 1.7;
}

/* Liste de fonctionnalités moderne */
.features-list-modern {
  list-style: none;
  padding: 0;
  margin: 0 0 32px 0;
}

.feature-item-modern {
  display: flex;
  gap: 16px;
  padding: 20px 0;
  border-bottom: 1px solid #e2e8f0;
  transition: all 0.3s ease;
}

.feature-item-modern:last-child {
  border-bottom: none;
}

.feature-item-modern:hover {
  padding-left: 8px;
  background: linear-gradient(90deg, rgba(64, 169, 234, 0.03), transparent);
}

.check-icon-modern {
  width: 32px;
  height: 32px;
  background: linear-gradient(135deg, #40a9ea 0%, #2d8de8 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: white;
  font-size: 16px;
  font-weight: 700;
  box-shadow: 0 4px 12px rgba(64, 169, 234, 0.3);
}

.feature-text-modern h5 {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 6px 0;
}

.feature-text-modern p {
  font-size: 15px;
  color: #64748b;
  margin: 0;
  line-height: 1.6;
}

/* Bouton Élégant */
.btn-elegant-primary {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  padding: 16px 32px;
  background: linear-gradient(135deg, #40a9ea 0%, #2d8de8 100%);
  color: white;
  border-radius: 14px;
  text-decoration: none;
  font-weight: 600;
  font-size: 16px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 8px 24px rgba(64, 169, 234, 0.3);
}

.btn-elegant-primary:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 32px rgba(64, 169, 234, 0.4);
  color: white;
}

.btn-elegant-primary i:last-child {
  transition: transform 0.3s ease;
}

.btn-elegant-primary:hover i:last-child {
  transform: translateX(4px);
}

/* ========================================
   SECTION TÉMOIGNAGES OPTIMISÉE
   ======================================== */
.testimonials-section-modern {
  padding: 100px 0;
  margin-top:0%;
  background: linear-gradient(180deg, #f0f9ff 0%, #ffffff 100%);
  position: relative;
  overflow: hidden;
}

.testimonial-card-modern {
  background: white;
  padding: 32px;
  border-radius: 24px;
  height: 100%;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid #e2e8f0;
  position: relative;
  overflow: hidden;
}

.testimonial-card-modern:hover {
  transform: translateY(-12px);
  box-shadow: 0 24px 48px rgba(0, 0, 0, 0.12);
  border-color: #40a9ea;
}

.testimonial-card-modern::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 4px;
  background: linear-gradient(90deg, transparent, #40a9ea, transparent);
  transform: scaleX(0);
  transition: transform 0.4s ease;
}

.testimonial-card-modern:hover::before {
  transform: scaleX(1);
}

.testimonial-header-modern {
  display: flex;
  align-items: center;
  margin-bottom: 1.5rem;
  gap: 1rem;
}

.author-avatar-modern {
  width: 70px;
  height: 70px;
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  font-size: 1.5rem;
  flex-shrink: 0;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.author-info-modern h5 {
  margin: 0;
  color: #1e293b;
  font-weight: 700;
  font-size: 1.1rem;
}

.author-info-modern p {
  margin: 0;
  color: #64748b;
  font-size: 0.9rem;
}

.rating-modern {
  margin-left: auto;
  display: flex;
  gap: 2px;
}

.rating-modern i {
  font-size: 1rem;
  color: #e2e8f0;
}

.rating-modern i.filled {
  color: #fbbf24;
}

.testimonial-content-modern p {
  color: #475569;
  font-style: italic;
  line-height: 1.7;
  margin-bottom: 1.5rem;
  font-size: 1rem;
  position: relative;
  padding-left: 20px;
}

.testimonial-content-modern p::before {
  content: '"';
  position: absolute;
  left: 0;
  top: -10px;
  font-size: 3rem;
  color: #e2e8f0;
  font-family: Georgia, serif;
}

.testimonial-footer-modern {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 1rem;
  border-top: 1px solid #e2e8f0;
  font-size: 0.9rem;
}

.course-taken-modern {
  color: #64748b;
  display: flex;
  align-items: center;
}

.date-modern {
  color: #94a3b8;
  font-size: 0.85rem;
}

/* ========================================
   FOOTER MODERNISÉ
   ======================================== */
.main-footer-modern {
  background: #0f172a;
  color: white;
  padding: 80px 0 30px;
}

.footer-brand-modern {
  margin-bottom: 2rem;
}

.footer-logo {
  height: 50px;
  margin-bottom: 1rem;
}

.footer-brand-modern p {
  color: #94a3b8;
  line-height: 1.6;
  margin-bottom: 1.5rem;
}

.social-links-modern {
  display: flex;
  gap: 1rem;
}

.social-link-modern {
  width: 44px;
  height: 44px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  text-decoration: none;
  transition: all 0.3s ease;
}

.social-link-modern:hover {
  background: linear-gradient(135deg, #40a9ea 0%, #2d8de8 100%);
  transform: translateY(-3px);
}

.footer-title-modern {
  color: white;
  margin-bottom: 1.5rem;
  font-size: 1.1rem;
  font-weight: 700;
}

.footer-links-modern {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer-links-modern li {
  margin-bottom: 0.75rem;
}

.footer-links-modern a {
  color: #94a3b8;
  text-decoration: none;
  transition: all 0.3s ease;
}

.footer-links-modern a:hover {
  color: #60a5fa;
  padding-left: 5px;
}

.contact-info-modern {
  list-style: none;
  padding: 0;
  margin: 0;
}

.contact-info-modern li {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 1rem;
  color: #94a3b8;
}

.contact-icon-modern {
  width: 32px;
  height: 32px;
  background: rgba(59, 130, 246, 0.1);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #60a5fa;
  flex-shrink: 0;
}

.footer-bottom-modern {
  margin-top: 3rem;
  padding-top: 2rem;
  border-top: 1px solid #334155;
  color: #94a3b8;
}

.text-primary {
  color: #60a5fa !important;
}

/* ========================================
   RESPONSIVE
   ======================================== */
@media (max-width: 992px) {
  .hero-title {
    font-size: 2.5rem;
  }
  
  .hero-stats {
    flex-direction: column;
    gap: 1.5rem;
  }
  
  .section-title-elegant {
    font-size: 2.2rem;
  }
  
  .about-image-modern {
    height: 400px;
    margin-bottom: 40px;
  }
  
  .main-illustration {
    width: 220px;
    height: 220px;
    font-size: 90px;
  }
  
  .floating-card {
    min-width: 110px;
    padding: 16px 12px;
  }
  
  .floating-card i {
    font-size: 24px;
  }
  
  .floating-card span {
    font-size: 1.5rem;
  }
}

@media (max-width: 768px) {
  .hero-section {
    padding: 80px 0 60px;
    min-height: auto;
  }
  
  .hero-buttons {
    flex-direction: column;
  }
  
  .hero-buttons .btn {
    width: 100%;
  }
  
  .about-section-modern {
    padding: 80px 0;
  }
  
  .section-header-modern {
    margin-bottom: 60px;
  }
  
  .section-title-elegant {
    font-size: 1.8rem;
  }
  
  .about-title-elegant {
    font-size: 2rem;
  }
  
  .about-content-modern {
    padding-left: 0;
    margin-top: 40px;
  }
  
  .testimonial-card-modern {
    padding: 24px;
  }
  
  .author-avatar-modern {
    width: 60px;
    height: 60px;
    font-size: 1.2rem;
  }
  
  .footer-bottom-modern {
    text-align: center;
  }
  
  .footer-bottom-modern .text-end {
    text-align: center !important;
    margin-top: 1rem;
  }
}

@media (max-width: 576px) {
  .hero-title {
    font-size: 2rem;
  }
  
  .section-title-elegant {
    font-size: 1.8rem;
  }
  
  .hero-subtitle {
    font-size: 1rem;
  }
  
  .feature-card-elegant,
  .testimonial-card-modern {
    padding: 20px;
  }
  
  .feature-icon-modern {
    width: 70px;
    height: 70px;
    font-size: 32px;
  }
}
</style>