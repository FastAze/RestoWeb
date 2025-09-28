<section class="section-profile" id="sectionProfile" style="display: none;">
    <div class="conteneur-profile">
        <div class="profile-header">
            <div class="profile-avatar">
                <div class="avatar-circle">
                    <div class="bat"></div>
                </div>
            </div>
            <h2><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'nom utilisateur' ?></h2>
        </div>
        <div class="profile-content">
            <table class="commandes-table">
                <thead>
                    <tr>
                        <th>ID commande</th>
                        <th>Voir le panier</th>
                        <th>Prix (TTC)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>500500500</td>
                        <td><button class="voir-commande-btn">Voir la commande</button></td>
                        <td>52.80€</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>