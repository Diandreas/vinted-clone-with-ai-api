# Intégration NotchPay - Système de Paiement par Produit

## Vue d'ensemble

Ce système permet aux utilisateurs de payer les frais de publication de leurs produits via NotchPay (Mobile Money) et d'activer automatiquement leurs produits après paiement réussi.

## Configuration

### Variables d'environnement

Ajoutez ces variables dans votre fichier `.env` :

```env
# NotchPay Configuration
NOTCHPAY_BASE_URL=https://api.notchpay.co
NOTCHPAY_PUBLIC_KEY=your_notchpay_public_key_here
NOTCHPAY_SECRET_KEY=your_notchpay_secret_key_here
NOTCHPAY_CURRENCY=XAF
NOTCHPAY_SANDBOX=false
NOTCHPAY_WEBHOOK_SECRET=your_webhook_secret_here
NOTCHPAY_CALLBACK_URL="${APP_URL}/payment/callback"
```

### Migration

Exécutez la migration pour créer la table `payments` :

```bash
php artisan migrate
```

## Fonctionnement

### 1. Création d'un produit

Quand un utilisateur crée un produit :
- Le produit est créé avec le statut `pending_payment`
- Les frais de publication sont calculés (5% du prix de vente)

### 2. Paiement

L'utilisateur peut payer via :
- **Interface produit** : Bouton "💳 Payer pour activer" sur la page du produit
- **API** : Endpoint `/api/v1/notchpay/initialize`

### 3. Processus de paiement

1. **Initialisation** : L'utilisateur clique sur "Payer pour activer"
2. **Modal de paiement** : Affiche les détails du produit et les frais
3. **Redirection NotchPay** : L'utilisateur est redirigé vers la page de paiement NotchPay
4. **Paiement Mobile Money** : L'utilisateur paie via MTN Mobile Money, Orange Money, etc.
5. **Callback** : NotchPay redirige vers `/payment/callback` après paiement
6. **Activation automatique** : Le produit est automatiquement activé

### 4. Webhook (optionnel)

Pour une mise à jour en temps réel, configurez le webhook NotchPay vers :
```
POST /api/v1/notchpay/webhook
```

## API Endpoints

### Initialiser un paiement

```http
POST /api/v1/notchpay/initialize
Content-Type: application/json
X-XSRF-TOKEN: {csrf_token}

{
  "product_id": 123,
  "amount": 1500,
  "email": "user@example.com"
}
```

**Réponse :**
```json
{
  "success": true,
  "authorization_url": "https://checkout.notchpay.co/..."
}
```

### Callback de paiement

```http
GET /payment/callback?status=complete&reference=prod_123_abc123
```

## Composants Frontend

### NotchPayButton

Composant Vue.js pour gérer les paiements :

```vue
<NotchPayButton 
  :product="product"
  :amount="listingFee"
  @success="onPaymentSuccess"
  @error="onPaymentError"
/>
```

### Modal de paiement

Le modal affiche :
- Image et nom du produit
- Prix de vente
- Frais de publication (5%)
- Bouton de paiement

## Modèles de données

### Payment

```php
class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'currency',
        'transaction_id',
        'status',
        'payment_method',
        'metadata',
        'processed_at',
    ];
}
```

### Product

Le produit a un statut `pending_payment` jusqu'au paiement réussi.

## Gestion des erreurs

### Erreurs courantes

1. **Produit non trouvé** : Vérifiez que l'ID du produit existe
2. **Non autorisé** : L'utilisateur doit être le propriétaire du produit
3. **Statut invalide** : Le produit doit être en statut `pending_payment`
4. **Erreur NotchPay** : Vérifiez la configuration de l'API

### Logs

Tous les événements sont loggés avec des détails complets :
- Initialisation du paiement
- Réponse de l'API NotchPay
- Création de l'enregistrement de paiement
- Callback et webhook
- Activation du produit

## Sécurité

### Vérifications

- **Authentification** : L'utilisateur doit être connecté
- **Propriété** : L'utilisateur doit être le propriétaire du produit
- **Statut** : Le produit doit être en attente de paiement
- **Signature webhook** : Vérification de la signature NotchPay

### CSRF Protection

Tous les appels API incluent le token CSRF Laravel.

## Tests

### Test manuel

1. Créez un produit (il sera en statut `pending_payment`)
2. Allez sur la page du produit
3. Cliquez sur "💳 Payer pour activer"
4. Vérifiez que le modal s'affiche
5. Testez le processus de paiement

### Test API

```bash
# Test d'initialisation de paiement
curl -X POST /api/v1/notchpay/initialize \
  -H "Content-Type: application/json" \
  -H "X-XSRF-TOKEN: {token}" \
  -d '{"product_id": 1, "amount": 1000, "email": "test@example.com"}'
```

## Déploiement

### Production

1. **Désactivez le mode sandbox** : `NOTCHPAY_SANDBOX=false`
2. **Configurez le webhook** : URL de production
3. **Vérifiez les clés** : Clés de production NotchPay
4. **Testez le callback** : Vérifiez la redirection

### Monitoring

- Surveillez les logs de paiement
- Vérifiez les webhooks NotchPay
- Surveillez les erreurs d'API

## Support

Pour toute question ou problème :
1. Vérifiez les logs Laravel
2. Testez l'API NotchPay directement
3. Vérifiez la configuration des variables d'environnement
4. Consultez la documentation NotchPay
