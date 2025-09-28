<div class="voir-commande-overlay" id="voirCommandeOverlay">
    <section class="voir-commande">
        <div class="voir-commande-header">
            <h2>Détail de la commande</h2>
            <button id="closeVoirCommande" class="close-btn">✕</button>
        </div>
        <div class="voir-commande-content">
            <div class="commande-info">
                <div class="info-row">
                    <span class="label">N° de commande :</span>
                    <span class="value" id="numeroCommande">500500500</span>
                </div>
                <div class="info-row">
                    <span class="label">Date :</span>
                    <span class="value" id="dateCommande">19/09/2025 - 14:30</span>
                </div>
                <div class="info-row">
                    <span class="label">Statut :</span>
                    <span class="value statut-badge" id="statutCommande">En livraison</span>
                </div>
                <div class="info-row">
                    <span class="label">Type :</span>
                    <span class="value" id="typeCommande">À emporter</span>
                </div>
            </div>
            
            <div class="commande-articles">
                <h3>Articles commandés :</h3>
                <div class="articles-liste">
                    <div class="article-item">
                        <img src="image/pizza.jpg" alt="Pizza" class="article-img">
                        <div class="article-details">
                            <div class="article-nom">Pizza Margherita</div>
                            <div class="article-description">Sauce tomate, mozzarella, basilic frais</div>
                            <div class="article-quantite-prix">
                                <span class="quantite">Quantité: 2</span>
                                <span class="prix-unitaire">15€ × 2 = 30€</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="article-item">
                        <img src="image/pizza.jpg" alt="Pizza" class="article-img">
                        <div class="article-details">
                            <div class="article-nom">Pizza Pepperoni</div>
                            <div class="article-description">Sauce tomate, mozzarella, pepperoni</div>
                            <div class="article-quantite-prix">
                                <span class="quantite">Quantité: 1</span>
                                <span class="prix-unitaire">18€ × 1 = 18€</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="commande-total">
                <div class="total-ligne">
                    <span class="total-label">Sous-total :</span>
                    <span class="total-value">48€</span>
                </div>
                <div class="total-ligne">
                    <span class="total-label">TVA (10%) :</span>
                    <span class="total-value">4.80€</span>
                </div>
                <div class="total-ligne total-final">
                    <span class="total-label">Total TTC :</span>
                    <span class="total-value">52.80€</span>
                </div>
            </div>
        </div>
    </section>
</div>