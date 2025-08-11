<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour le système d'administration
    |
    */

    // Rôles disponibles
    'roles' => [
        'user' => [
            'name' => 'Utilisateur',
            'permissions' => [
                'dashboard:view',
                'profile:manage',
                'products:create',
                'products:edit_own',
                'orders:view_own',
                'messages:send',
            ]
        ],
        'admin' => [
            'name' => 'Administrateur',
            'permissions' => [
                'dashboard:view',
                'users:manage',
                'products:moderate',
                'lives:moderate',
                'orders:view',
                'analytics:view',
                'settings:manage',
                'categories:manage',
                'brands:manage',
                'reports:manage',
            ]
        ],
        'manager' => [
            'name' => 'Manager',
            'permissions' => [
                'dashboard:view',
                'users:view',
                'products:moderate',
                'lives:moderate',
                'orders:view',
                'analytics:view',
                'reports:manage',
            ]
        ],
        'analyst' => [
            'name' => 'Analyste',
            'permissions' => [
                'dashboard:view',
                'analytics:view',
                'reports:view',
            ]
        ],
        'moderator' => [
            'name' => 'Modérateur',
            'permissions' => [
                'dashboard:view',
                'products:moderate',
                'lives:moderate',
                'reports:manage',
            ]
        ],
    ],

    // Permissions disponibles
    'permissions' => [
        'dashboard:view' => 'Voir le tableau de bord',
        'users:manage' => 'Gérer les utilisateurs',
        'users:view' => 'Voir les utilisateurs',
        'products:moderate' => 'Modérer les produits',
        'lives:moderate' => 'Modérer les lives',
        'orders:view' => 'Voir les commandes',
        'analytics:view' => 'Voir les analytics',
        'settings:manage' => 'Gérer les paramètres',
        'categories:manage' => 'Gérer les catégories',
        'brands:manage' => 'Gérer les marques',
        'reports:manage' => 'Gérer les signalements',
        'reports:view' => 'Voir les signalements',
        'profile:manage' => 'Gérer son profil',
        'products:create' => 'Créer des produits',
        'products:edit_own' => 'Modifier ses produits',
        'orders:view_own' => 'Voir ses commandes',
        'messages:send' => 'Envoyer des messages',
    ],

    // Paramètres par défaut
    'defaults' => [
        'pagination' => [
            'per_page' => 15,
            'max_per_page' => 100,
        ],
        'search' => [
            'min_length' => 2,
            'max_results' => 50,
        ],
        'uploads' => [
            'max_file_size' => 10240, // 10MB
            'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'max_files' => 10,
        ],
        'notifications' => [
            'email' => true,
            'push' => true,
            'sms' => false,
        ],
    ],

    // Paramètres de sécurité
    'security' => [
        'password_min_length' => 8,
        'password_require_special' => true,
        'session_timeout' => 120, // minutes
        'max_login_attempts' => 5,
        'lockout_duration' => 15, // minutes
        'require_email_verification' => true,
        'require_phone_verification' => false,
    ],

    // Paramètres de modération
    'moderation' => [
        'auto_approve_products' => false,
        'auto_approve_lives' => false,
        'require_approval_for_new_users' => false,
        'max_reports_before_auto_review' => 3,
        'content_filter_enabled' => true,
        'spam_protection' => true,
    ],

    // Paramètres d'analytics
    'analytics' => [
        'track_user_behavior' => true,
        'track_product_views' => true,
        'track_search_queries' => true,
        'retention_days' => 90,
        'export_formats' => ['csv', 'xlsx', 'json'],
    ],

    // Paramètres de notification
    'notifications' => [
        'admin_dashboard' => [
            'new_users' => true,
            'pending_products' => true,
            'new_reports' => true,
            'system_alerts' => true,
        ],
        'email_templates' => [
            'welcome' => 'emails.admin.welcome',
            'user_verified' => 'emails.admin.user_verified',
            'product_approved' => 'emails.admin.product_approved',
            'product_rejected' => 'emails.admin.product_rejected',
        ],
    ],

    // Paramètres de maintenance
    'maintenance' => [
        'enabled' => false,
        'allowed_ips' => [],
        'message' => 'Site en maintenance. Merci de revenir plus tard.',
        'retry_after' => 300, // 5 minutes
    ],

    // Paramètres de cache
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 heure
        'tags' => [
            'users',
            'products',
            'categories',
            'analytics',
        ],
    ],

    // Paramètres de logs
    'logging' => [
        'admin_actions' => true,
        'user_actions' => false,
        'system_events' => true,
        'retention_days' => 365,
        'channels' => ['daily', 'slack'],
    ],

    // Paramètres de backup
    'backup' => [
        'enabled' => true,
        'frequency' => 'daily',
        'retention_days' => 30,
        'compress' => true,
        'notify_on_failure' => true,
    ],

    // Paramètres de performance
    'performance' => [
        'query_cache' => true,
        'image_optimization' => true,
        'cdn_enabled' => false,
        'compression' => true,
        'minification' => true,
    ],
];
