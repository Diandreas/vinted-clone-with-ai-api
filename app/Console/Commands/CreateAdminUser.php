<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create-user 
                            {--name= : Nom de l\'utilisateur}
                            {--email= : Email de l\'utilisateur}
                            {--username= : Nom d\'utilisateur}
                            {--password= : Mot de passe}
                            {--role=admin : Rôle de l\'utilisateur (admin, manager, analyst, moderator)}
                            {--interactive : Mode interactif}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Créer un nouvel utilisateur administrateur';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Création d\'un nouvel utilisateur administrateur');
        $this->newLine();

        // Mode interactif ou paramètres en ligne de commande
        if ($this->option('interactive') || !$this->hasRequiredOptions()) {
            $this->interactiveMode();
        } else {
            $this->commandLineMode();
        }
    }

    /**
     * Vérifier si toutes les options requises sont présentes
     */
    private function hasRequiredOptions(): bool
    {
        return $this->option('name') && 
               $this->option('email') && 
               $this->option('username') && 
               $this->option('password');
    }

    /**
     * Mode interactif
     */
    private function interactiveMode(): void
    {
        $name = $this->ask('Nom complet de l\'utilisateur');
        $email = $this->ask('Adresse email');
        $username = $this->ask('Nom d\'utilisateur');
        $password = $this->secret('Mot de passe');
        $passwordConfirm = $this->secret('Confirmer le mot de passe');
        $role = $this->choice(
            'Rôle de l\'utilisateur',
            ['admin', 'manager', 'analyst', 'moderator'],
            'admin'
        );

        // Validation
        if ($password !== $passwordConfirm) {
            $this->error('Les mots de passe ne correspondent pas');
            return;
        }

        $this->createUser($name, $email, $username, $password, $role);
    }

    /**
     * Mode ligne de commande
     */
    private function commandLineMode(): void
    {
        $name = $this->option('name');
        $email = $this->option('email');
        $username = $this->option('username');
        $password = $this->option('password');
        $role = $this->option('role');

        $this->createUser($name, $email, $username, $password, $role);
    }

    /**
     * Créer l'utilisateur
     */
    private function createUser(string $name, string $email, string $username, string $password, string $role): void
    {
        // Validation des données
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'username' => $username,
            'password' => $password,
            'role' => $role,
        ], [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,manager,analyst,moderator',
        ]);

        if ($validator->fails()) {
            $this->error('Erreurs de validation :');
            foreach ($validator->errors()->all() as $error) {
                $this->error("- $error");
            }
            return;
        }

        // Vérifier si l'utilisateur existe déjà
        if (User::where('email', $email)->exists()) {
            $this->error('Un utilisateur avec cet email existe déjà');
            return;
        }

        if (User::where('username', $username)->exists()) {
            $this->error('Un utilisateur avec ce nom d\'utilisateur existe déjà');
            return;
        }

        try {
            // Créer l'utilisateur
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'username' => $username,
                'password' => Hash::make($password),
                'role' => $role,
                'is_admin' => in_array($role, ['admin', 'manager']),
                'is_verified' => true,
                'email_verified_at' => now(),
                'permissions' => $this->getDefaultPermissions($role),
            ]);

            $this->info("✅ Utilisateur créé avec succès !");
            $this->newLine();
            
            $this->table(
                ['Champ', 'Valeur'],
                [
                    ['ID', $user->id],
                    ['Nom', $user->name],
                    ['Email', $user->email],
                    ['Username', $user->username],
                    ['Rôle', $user->role],
                    ['Admin', $user->is_admin ? 'Oui' : 'Non'],
                    ['Vérifié', $user->is_verified ? 'Oui' : 'Non'],
                ]
            );

            $this->newLine();
            $this->info("L'utilisateur peut maintenant se connecter avec :");
            $this->line("Email: $email");
            $this->line("Mot de passe: [mot de passe saisi]");

        } catch (\Exception $e) {
            $this->error('Erreur lors de la création de l\'utilisateur : ' . $e->getMessage());
        }
    }

    /**
     * Obtenir les permissions par défaut pour un rôle
     */
    private function getDefaultPermissions(string $role): array
    {
        $permissions = config("admin.roles.$role.permissions", []);
        
        if (empty($permissions)) {
            // Permissions par défaut si la configuration n'est pas trouvée
            switch ($role) {
                case 'admin':
                    $permissions = [
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
                    ];
                    break;
                case 'manager':
                    $permissions = [
                        'dashboard:view',
                        'users:view',
                        'products:moderate',
                        'lives:moderate',
                        'orders:view',
                        'analytics:view',
                        'reports:manage',
                    ];
                    break;
                case 'analyst':
                    $permissions = [
                        'dashboard:view',
                        'analytics:view',
                        'reports:view',
                    ];
                    break;
                case 'moderator':
                    $permissions = [
                        'dashboard:view',
                        'products:moderate',
                        'lives:moderate',
                        'reports:manage',
                    ];
                    break;
                default:
                    $permissions = ['dashboard:view'];
            }
        }

        return $permissions;
    }
}
