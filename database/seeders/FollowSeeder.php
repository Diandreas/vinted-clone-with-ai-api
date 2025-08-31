<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Follow;
use Illuminate\Support\Facades\DB;

class FollowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vérifier si la table follows existe
        if (!DB::getSchemaBuilder()->hasTable('follows')) {
            $this->command->error('Table "follows" does not exist!');
            return;
        }

        // Récupérer quelques utilisateurs existants
        $users = User::take(5)->get();
        
        if ($users->count() < 2) {
            $this->command->error('Need at least 2 users to create follows!');
            return;
        }

        $this->command->info('Creating test follows...');

        // Créer des relations de follow de test
        $followsCreated = 0;
        
        foreach ($users as $index => $user) {
            // Chaque utilisateur suit le suivant (créer un cycle)
            $followingUser = $users[($index + 1) % $users->count()];
            
            // Éviter qu'un utilisateur se suive lui-même
            if ($user->id !== $followingUser->id) {
                // Vérifier si la relation existe déjà
                $existingFollow = DB::table('follows')
                    ->where('follower_id', $user->id)
                    ->where('following_id', $followingUser->id)
                    ->first();
                
                if (!$existingFollow) {
                    DB::table('follows')->insert([
                        'follower_id' => $user->id,
                        'following_id' => $followingUser->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $followsCreated++;
                    
                    $this->command->info("User {$user->name} (ID: {$user->id}) now follows {$followingUser->name} (ID: {$followingUser->id})");
                } else {
                    $this->command->info("Follow relationship already exists between {$user->name} and {$followingUser->name}");
                }
            }
        }

        $this->command->info("Created {$followsCreated} new follow relationships.");
        
        // Afficher un résumé des relations
        $this->command->info("\nFollow relationships summary:");
        $follows = DB::table('follows')->get();
        
        foreach ($follows as $follow) {
            $follower = User::find($follow->follower_id);
            $following = User::find($follow->following_id);
            
            if ($follower && $following) {
                $this->command->info("- {$follower->name} follows {$following->name}");
            }
        }
        
        $this->command->info("\nTotal follows in database: " . $follows->count());
    }
}



