<section class="section-paiement" id="sectionPaiement" style="display: none;">
    <div class="conteneur-paiement">
        <h2>Paiement</h2>
        <div class="formulaire-paiement">
            <div class="champ-paiement">
                <label for="carte">Numéro de carte bancaire :</label>
                <input type="text" id="carte" name="carte" placeholder="1234 5678 9012 3456">
            </div>
            <div class="champs-inline">
                <div class="champ-ccv">
                    <label for="ccv">CCV :</label>
                    <input type="text" id="ccv" name="ccv" maxlength="3">
                </div>
                <div class="champ-date">
                    <label for="date">Date :</label>
                    <input type="text" id="date" name="date" placeholder="MM/AA" maxlength="5">
                </div>
                <div class="montant">
                    <span>Montant : 52.80€</span>
                </div>
            </div>
            <div class="boutons-paiement">
                <button class="bouton-annuler">Annuler</button>
                <button class="bouton-valider-paiement">Valider</button>
            </div>
        </div>
    </div>
</section>