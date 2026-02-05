<?php

namespace App\Modules\Test\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TestPathPerformance extends Command
{
    protected $signature = 'test:path-perf {userId=200000}';
    protected $description = 'Test performance of path query for a specific user';

    public function handle()
    {
        $userId = $this->argument('userId');
        $user = User::find($userId);

        if (!$user) {
            $this->error("User {$userId} not found.");
            return;
        }

        $path = $user->path;
        $this->info("Path length: " . strlen($path));

        // Postgres specific EXPLAIN ANALYZE
        // Note: 'path' column has a GIN index (gin_trgm_ops) which supports LIKE '%...%' 
        // but standard B-Tree is usually better for prefix LIKE '...%' if the text is not too long.
        // However, for very long text, B-Tree might fail or be inefficient.
        // Let's see what the planner does.
        
        try {
            $this->info("--- Test 1: GIN Index (LIKE) ---");
            $query = "EXPLAIN ANALYZE SELECT count(*) FROM users WHERE path LIKE ?";
            $results = DB::select($query, [$path . '%']);
            
            foreach ($results as $row) {
                $plan = $row->{'QUERY PLAN'} ?? array_values((array)$row)[0];
                $this->line($plan);
            }

            $this->info("\n--- Test 2: Recursive CTE (p_id) ---");
            // Standard Recursive Query for all descendants
            $recursiveQuery = "
                EXPLAIN ANALYZE
                WITH RECURSIVE subordinates AS (
                    SELECT id FROM users WHERE id = ?
                    UNION ALL
                    SELECT u.id FROM users u
                    INNER JOIN subordinates s ON u.p_id = s.id
                )
                SELECT count(*) FROM subordinates
            ";
            
            $resultsRecursive = DB::select($recursiveQuery, [$userId]);
            
            foreach ($resultsRecursive as $row) {
                $plan = $row->{'QUERY PLAN'} ?? array_values((array)$row)[0];
                $this->line($plan);
            }

            $this->info("\n--- Test 3: Trait Implementation (getDescendantIds) ---");
            $start = microtime(true);
            $ids = User::getDescendantIds($userId);
            $count = count($ids);
            $duration = (microtime(true) - $start) * 1000;
            
            $this->info("Fetched {$count} descendants IDs in {$duration} ms");

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}
