<?php
    include "component/componentDocType.php";
    include 'template/ini.php';
    include 'template/chekEtat.php';
    session_start();
    
    // Vérifier si l'utilisateur est connecté et a une commande active
    if (isset($_SESSION['user_id'])) {
        verifierEtatCommande($_SESSION['user_id']);
    }
?>
<body>
    <?php
        include 'component/componentNavConnected.php'; 
    ?>
    <section class="sectionArticle" id="sectionArticle">
        <div class="areaArticle">
            <?php
                include "component/componentArticle.php";
            ?>
        </div>
    </section>

    <?php
        include 'component/componentPanier.php';
    ?>

    <?php
        include 'component/componentPaiement.php';
    ?>

    <?php
        include 'component/componentProfile.php';
    ?>

    <?php
        include 'component/componentVoirArticleOverlay.php';
    ?>

    <?php
        include 'component/componentVoirCommandeOverlay.php';
    ?>

    <script>
        document.querySelectorAll('.article').forEach(article => {
            article.addEventListener('click', function() {
                const overlay = document.getElementById('voirArticleOverlay');
                const imgSrc = this.querySelector('img').src;
                const nom = this.querySelector('h2').textContent;
                const prix = this.querySelector('h3').textContent;
                const idProduit = this.getAttribute('data-id'); // Récupérer l'ID du produit

                overlay.querySelector('.voir-article-img img').src = imgSrc;
                overlay.querySelector('.voir-article-nom').textContent = nom;
                overlay.querySelector('.voir-article-prix').textContent = 'Prix : ' + prix;
                overlay.querySelector('#produitId').value = idProduit; // Définir l'ID dans le champ caché
                overlay.style.display = 'flex';
            });
        });

        document.getElementById('closeVoirArticle').addEventListener('click', function() {
            document.getElementById('voirArticleOverlay').style.display = 'none';
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Masquer la notification au chargement et toggle au clic
            const notifSection = document.querySelector('.notification');
            const notifIcon = document.querySelector('.pannier-notif a img');
            if (notifSection) {
                notifSection.style.display = 'none';
            }
            if (notifIcon) {
                notifIcon.parentElement.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (notifSection) {
                        notifSection.style.display = (notifSection.style.display === 'none' || notifSection.style.display === '') ? 'flex' : 'none';
                    }
                });
            }

            // Fonctions de navigation entre sections
            function afficherSection(sectionAfficher, sectionsACacher) {
                // Afficher la section demandée
                const sectionAAfficher = document.getElementById(sectionAfficher) || document.querySelector('.' + sectionAfficher);
                if (sectionAAfficher) {
                    sectionAAfficher.style.display = 'block';
                }
                
                // Cacher toutes les autres sections
                sectionsACacher.forEach(function(sectionId) {
                    const section = document.getElementById(sectionId) || document.querySelector('.' + sectionId);
                    if (section) {
                        section.style.display = 'none';
                    }
                });
            }

            function afficherPaiement() {
                afficherSection('sectionPaiement', ['sectionPanier', 'sectionArticle', 'sectionProfile']);
            }

            function afficherPanier() {
                afficherSection('sectionPanier', ['sectionArticle', 'sectionPaiement', 'sectionProfile']);
            }

            function afficherArticles() {
                afficherSection('sectionArticle', ['sectionPanier', 'sectionPaiement', 'sectionProfile']);
            }

            function afficherProfile() {
                afficherSection('sectionProfile', ['sectionPanier', 'sectionPaiement', 'sectionArticle']);
            }

            // Événements pour les boutons
            const boutonValiderPanier = document.querySelector('.bouton-valider');
            if (boutonValiderPanier) {
                boutonValiderPanier.addEventListener('click', function(e) {
                    e.preventDefault();
                    afficherPaiement();
                });
            }

            const boutonValiderPaiement = document.querySelector('.bouton-valider-paiement');
            if (boutonValiderPaiement) {
                boutonValiderPaiement.addEventListener('click', function(e) {
                    e.preventDefault();
                    afficherArticles();
                });
            }

            const boutonAnnulerPaiement = document.querySelector('.bouton-annuler');
            if (boutonAnnulerPaiement) {
                boutonAnnulerPaiement.addEventListener('click', function(e) {
                    e.preventDefault();
                    afficherArticles();
                });
            }

            const boutonRetourPanier = document.querySelector('.bouton-retour');
            if (boutonRetourPanier) {
                boutonRetourPanier.addEventListener('click', function(e) {
                    e.preventDefault();
                    afficherArticles();
                });
            }

            const lienPanier = document.querySelector('.pannier');
            if (lienPanier) {
                lienPanier.addEventListener('click', function(e) {
                    e.preventDefault();
                    afficherPanier();
                });
            }

            const logoLien = document.querySelector('.logo a');
            if (logoLien) {
                logoLien.addEventListener('click', function(e) {
                    e.preventDefault();
                    afficherArticles();
                });
            }

            // Événement pour afficher le profile
            const lienProfile = document.querySelector('.profile');
            if (lienProfile) {
                lienProfile.addEventListener('click', function(e) {
                    e.preventDefault();
                    afficherProfile();
                });
            }

            // Gestion de l'overlay pour voir le détail d'une commande
            const voirCommandeOverlay = document.getElementById('voirCommandeOverlay');
            const closeVoirCommande = document.getElementById('closeVoirCommande');
            const voirCommandeBtns = document.querySelectorAll('.voir-commande-btn');
            
            voirCommandeBtns.forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    // Ici, on pourrait récupérer les données spécifiques à la commande
                    // et les injecter dans l'overlay
                    if (voirCommandeOverlay) {
                        voirCommandeOverlay.classList.add('active');
                    }
                });
            });
            
            if (closeVoirCommande && voirCommandeOverlay) {
                closeVoirCommande.addEventListener('click', function() {
                    voirCommandeOverlay.classList.remove('active');
                });
            }

            // Fermer l'overlay en cliquant en dehors de la popup
            if (voirCommandeOverlay) {
                voirCommandeOverlay.addEventListener('click', function(e) {
                    if (e.target === voirCommandeOverlay) {
                        voirCommandeOverlay.classList.remove('active');
                    }
                });
            }
        });
    </script>
</body>
</html>