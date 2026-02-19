<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer mon compte – RIKEAA</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8f9fa;
            color: #333;
            line-height: 1.7;
        }

        header {
            background: #1a1a2e;
            color: #fff;
            padding: 32px 24px;
            text-align: center;
        }

        header h1 { font-size: 2rem; font-weight: 700; }
        header p { margin-top: 8px; font-size: 0.95rem; color: #a0aec0; }

        .container {
            max-width: 720px;
            margin: 0 auto;
            padding: 48px 24px 80px;
        }

        .warning-box {
            background: #fff5f5;
            border-left: 4px solid #e63946;
            border-radius: 8px;
            padding: 20px 24px;
            margin-bottom: 32px;
        }

        .warning-box p { font-size: 0.95rem; color: #555; }

        section {
            background: #fff;
            border-radius: 10px;
            padding: 28px 32px;
            margin-bottom: 20px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }

        section h2 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .steps { list-style: none; padding: 0; counter-reset: step; }

        .steps li {
            counter-increment: step;
            display: flex;
            gap: 14px;
            align-items: flex-start;
            margin-bottom: 14px;
        }

        .steps li::before {
            content: counter(step);
            background: #e63946;
            color: #fff;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .steps li p { font-size: 0.95rem; color: #444; padding-top: 4px; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
            font-size: 0.9rem;
        }

        th {
            background: #1a1a2e;
            color: #fff;
            text-align: left;
            padding: 10px 14px;
        }

        td {
            padding: 10px 14px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        tr:nth-child(even) td { background: #fafafa; }

        .badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-red { background: #fee2e2; color: #b91c1c; }
        .badge-yellow { background: #fef9c3; color: #92400e; }

        .email-box {
            background: #1a1a2e;
            color: #fff;
            border-radius: 10px;
            padding: 28px 32px;
            margin-bottom: 20px;
            text-align: center;
        }

        .email-box p { color: #a0aec0; margin-bottom: 16px; font-size: 0.95rem; }

        .email-box a {
            display: inline-block;
            background: #e63946;
            color: #fff;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1rem;
        }

        .email-box a:hover { background: #c1121f; }

        .note { font-size: 0.85rem; color: #6b7280; margin-top: 12px; }

        .update-note {
            text-align: center;
            font-size: 0.85rem;
            color: #9ca3af;
            padding-top: 20px;
        }

        @media (max-width: 600px) {
            header h1 { font-size: 1.5rem; }
            section { padding: 20px 18px; }
            .container { padding: 28px 16px 60px; }
        }
    </style>
</head>
<body>

<header>
    <h1>RIKEAA</h1>
    <p>Suppression de compte et des données associées</p>
</header>

<div class="container">

    <div class="warning-box">
        <p>
            <strong>Attention :</strong> La suppression de votre compte est <strong>définitive et irréversible</strong>.
            Vos annonces, messages, avis et solde de portefeuille seront perdus. Assurez-vous d'avoir retiré
            votre solde avant de faire votre demande.
        </p>
    </div>

    <!-- Méthode 1 : depuis l'app -->
    <section>
        <h2>Option 1 — Supprimer depuis l'application</h2>
        <ol class="steps">
            <li><p>Ouvrez l'application <strong>RIKEAA</strong> et connectez-vous à votre compte.</p></li>
            <li><p>Allez dans <strong>Profil</strong> (icône en bas à droite).</p></li>
            <li><p>Appuyez sur <strong>Paramètres</strong> (icône engrenage).</p></li>
            <li><p>Faites défiler jusqu'à la section <strong>Compte</strong>.</p></li>
            <li><p>Appuyez sur <strong>« Supprimer mon compte »</strong> et confirmez votre choix.</p></li>
        </ol>
    </section>

    <!-- Méthode 2 : par email -->
    <section>
        <h2>Option 2 — Demande par e-mail</h2>
        <p style="font-size:0.95rem; color:#444; margin-bottom:14px;">
            Si vous ne pouvez plus accéder à votre compte, envoyez-nous un e-mail depuis l'adresse
            associée à votre compte RIKEAA avec l'objet <strong>« Suppression de compte »</strong>.
        </p>
    </section>

    <div class="email-box">
        <p>Envoyer votre demande de suppression à :</p>
        <a href="mailto:hello@rikeaa.com?subject=Suppression%20de%20compte%20RIKEAA&body=Bonjour%2C%0A%0AJe%20souhaite%20supprimer%20mon%20compte%20RIKEAA%20ainsi%20que%20toutes%20les%20donn%C3%A9es%20associ%C3%A9es.%0A%0ANom%20d'utilisateur%20%3A%20%0AE-mail%20du%20compte%20%3A%20%0A%0AMerci.">
            Envoyer la demande par e-mail
        </a>
        <p class="note" style="color:#a0aec0; margin-top:14px;">
            Nous traiterons votre demande dans un délai de <strong style="color:#fff;">30 jours</strong>.
        </p>
    </div>

    <!-- Données supprimées / conservées -->
    <section>
        <h2>Ce qui est supprimé et ce qui est conservé</h2>
        <table>
            <thead>
                <tr>
                    <th>Type de données</th>
                    <th>Statut</th>
                    <th>Durée de conservation</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Profil (nom, photo, bio)</td>
                    <td><span class="badge badge-red">Supprimé</span></td>
                    <td>Immédiatement</td>
                </tr>
                <tr>
                    <td>Annonces et produits</td>
                    <td><span class="badge badge-red">Supprimé</span></td>
                    <td>Sous 30 jours</td>
                </tr>
                <tr>
                    <td>Messages privés</td>
                    <td><span class="badge badge-red">Supprimé</span></td>
                    <td>Sous 90 jours</td>
                </tr>
                <tr>
                    <td>Favoris, abonnements, avis</td>
                    <td><span class="badge badge-red">Supprimé</span></td>
                    <td>Sous 30 jours</td>
                </tr>
                <tr>
                    <td>Données de paiement / transactions</td>
                    <td><span class="badge badge-yellow">Conservé</span></td>
                    <td>7 ans (obligation légale comptable)</td>
                </tr>
                <tr>
                    <td>Données de signalement / litiges</td>
                    <td><span class="badge badge-yellow">Conservé</span></td>
                    <td>Durée du litige + 1 an</td>
                </tr>
            </tbody>
        </table>
        <p class="note" style="margin-top:14px;">
            Les données conservées après suppression sont uniquement utilisées pour respecter nos obligations
            légales et ne servent à aucune finalité commerciale.
        </p>
    </section>

    <p class="update-note">
        &copy; {{ date('Y') }} RIKEAA &mdash; <a href="/privacy-policy" style="color:#6b7280;">Politique de confidentialité</a>
    </p>

</div>
</body>
</html>
