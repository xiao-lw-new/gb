<?php

namespace App\Modules\Test\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GenerateTestData extends Command
{
    protected $signature = 'test:generate-mock-data {--root=1} {--levels=1000}';
    protected $description = 'Generate mock users with specific structure: ~1000 levels, 100k+ users, testing 5-25 direct referrals';

    public function handle()
    {
        $rootId = $this->option('root');
        $targetLevels = (int)$this->option('levels');
        
        $rootUser = User::find($rootId);
        if (!$rootUser) {
            $this->error("Root user not found. Please ensure user ID {$rootId} exists.");
            return;
        }

        $currentTotal = User::count();
        $this->info("Start generating mock data...");
        $this->info("Target Levels: {$targetLevels}");
        $this->info("Initial User Count: {$currentTotal}");

        $now = Carbon::now();
        $bar = $this->output->createProgressBar($targetLevels);
        $bar->start();

        // Current backbone node
        $currentBackboneId = $rootUser->id;
        $currentBackbonePath = $rootUser->path ?: '|' . $rootUser->id . '|';

        // Direct referral counts to test
        $testCounts = [5, 10, 15, 20, 25];
        // Filler count to reach ~120k users (Sum(testCounts)=75. Need ~45 more for 120 total per level)
        $fillerCount = 45;

        for ($level = 1; $level <= $targetLevels; $level++) {
            // 1. Create parents for this level (Next Backbone + Test Parents + Filler Parent)
            // We need to insert them one by one to get IDs for paths
            
            // A. Next Backbone
            $nextBackboneId = $this->createNode($currentBackboneId, $currentBackbonePath, 'Backbone L'.$level, $now);
            $nextBackbonePath = $currentBackbonePath . $nextBackboneId . '|';

            // B. Test Parents (Nodes that will have 5, 10... children)
            $testParentIds = [];
            foreach ($testCounts as $count) {
                $pId = $this->createNode($currentBackboneId, $currentBackbonePath, "TestParent {$count} L{$level}", $now);
                $testParentIds[$pId] = $count; // Map ID -> num children
            }

            // C. Filler Parent
            $fillerParentId = $this->createNode($currentBackboneId, $currentBackbonePath, "Filler L{$level}", $now);

            // 2. Bulk insert children (Leaves)
            // We collect all children to be inserted in batches
            $childrenToInsert = [];

            // Add children for Test Parents
            foreach ($testParentIds as $pId => $count) {
                $pPath = $currentBackbonePath . $pId . '|';
                $childrenToInsert = array_merge($childrenToInsert, $this->prepareChildren($pId, $pPath, $count, 'TestLeaf', $now));
            }

            // Add children for Filler Parent
            $fPath = $currentBackbonePath . $fillerParentId . '|';
            $childrenToInsert = array_merge($childrenToInsert, $this->prepareChildren($fillerParentId, $fPath, $fillerCount, 'FillerLeaf', $now));

            // Execute Bulk Insert
            if (!empty($childrenToInsert)) {
                foreach (array_chunk($childrenToInsert, 500) as $chunk) {
                    // Temporarily fill path with a unique placeholder to avoid unique constraint violation if empty string is unique
                    // Or better, just insert and let them be empty if unique constraint allows empty strings?
                    // The error says: Key (md5(path))=(d41d8cd98f00b204e9800998ecf8427e) already exists.
                    // d41d8cd98f00b204e9800998ecf8427e is MD5 of empty string.
                    // So we cannot insert multiple empty paths.
                    // Solution: We must give them a temporary unique path or insert 1 by 1.
                    // Or we can use a temporary unique path like "TEMP_{uuid}"
                    
                    // Let's modify the chunk data to have unique temp paths
                    $chunkWithTempPaths = [];
                    foreach ($chunk as $row) {
                        $row['path'] = 'TEMP_' . Str::random(20); 
                        $chunkWithTempPaths[] = $row;
                    }
                    
                    DB::table('users')->insert($chunkWithTempPaths);
                }
                
                // Update paths for all newly inserted children
                // We identify them by p_id and "TEMP_" prefix
                // 1. Update children of Test Parents
                foreach ($testParentIds as $pId => $count) {
                    $pPath = $currentBackbonePath . $pId . '|';
                    $this->updateChildrenPaths($pId, $pPath);
                }
                // 2. Update children of Filler Parent
                $fPath = $currentBackbonePath . $fillerParentId . '|';
                $this->updateChildrenPaths($fillerParentId, $fPath);
            }

            // Move to next backbone
            $currentBackboneId = $nextBackboneId;
            $currentBackbonePath = $nextBackbonePath;

            $bar->advance();
        }

        $bar->finish();
        $this->info("\nSuccess! Final count: " . User::count());
    }

    private function updateChildrenPaths($parentId, $parentPath)
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite' || $driver === 'pgsql') {
            DB::update("UPDATE users SET path = ? || id || '|' WHERE p_id = ? AND path LIKE 'TEMP_%'", [$parentPath, $parentId]);
        } else {
            // MySQL and others
            DB::update("UPDATE users SET path = CONCAT(?, id, '|') WHERE p_id = ? AND path LIKE 'TEMP_%'", [$parentPath, $parentId]);
        }
    }

    private function createNode($parentId, $parentPath, $remark, $now)
    {
        $address = '0x' . Str::random(40);
        $id = DB::table('users')->insertGetId([
            'address'    => $address,
            'name'       => substr($address, 0, 8),
            'p_id'       => $parentId,
            'path'       => '', // Set temporarily empty or update after? 
            // Actually we can compute path BEFORE if we knew ID, but we don't.
            // So we insert, then update path? Or we just assume path = parentPath + ID + |.
            // But we need ID first.
            'active'     => 1, // Active users for testing
            'status'     => 1,
            'remark'     => $remark,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Update path
        $path = $parentPath . $id . '|';
        DB::table('users')->where('id', $id)->update(['path' => $path]);

        return $id;
    }

    private function prepareChildren($parentId, $parentPath, $count, $remarkPrefix, $now)
    {
        $data = [];
        for ($i = 0; $i < $count; $i++) {
            $address = '0x' . Str::random(40);
            // We can't know ID yet, so path update must be done via loop if we need accurate path for leaves.
            // BUT: Do leaves need accurate paths immediately? Yes, usually.
            // However, bulk insert doesn't return IDs easily in all drivers.
            // If we don't need to chain off these leaves, maybe we can skip path or fix it later?
            // Requirement doesn't strict about leaf paths, but system might depend on it.
            // Let's generate IDs? No, auto-inc.
            // We can guess IDs if no other writes? Risky.
            // Alternative: Insert one by one? 120 inserts per level * 1000 = 120k queries. 
            // SQLite/MySQL can handle 120k simple inserts reasonably fast (minutes).
            // Let's try to optimize: 
            // If we leave path empty for leaves, can we update them in bulk?
            // "UPDATE users SET path = CONCAT(?, id, '|') WHERE p_id = ?" 
            // Yes! efficient update after insert.
            
            $data[] = [
                'address'    => $address,
                'name'       => substr($address, 0, 8),
                'p_id'       => $parentId,
                'path'       => '', // Will update in bulk
                'active'     => 1,
                'status'     => 1,
                'remark'     => $remarkPrefix,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        return $data;
    }
}
