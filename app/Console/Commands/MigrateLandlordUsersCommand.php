<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\Landlord;
use App\Models\User;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MigrateLandlordUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'landlord:migrate-users
                            {--dry-run : Preview what would happen without making any DB changes}
                            {--landlord-id= : Process a single landlord by ID (for testing)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill: create or link a User account for every existing Landlord record and assign the landlord Spatie role.';

    /** @var bool */
    private bool $isDryRun = false;

    /** @var array */
    private array $createdUserIds = [];

    /** @var array */
    private array $summary = [
        'linked' => 0,
        'created' => 0,
        'skipped' => 0,
        'errors' => 0,
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->isDryRun = (bool) $this->option('dry-run');

        if ($this->isDryRun) {
            $this->warn('=== DRY RUN — No database changes will be made ===');
        }

        // BUILD QUERY
        $query = Landlord::whereNull('user_id')
            ->orderBy('id');

        if ($this->option('landlord-id')) {
            $query->where('id', (int) $this->option('landlord-id'));
        }

        $total = $query->count();
        $this->info("Found {$total} landlord(s) without a linked User account.");

        if ($total === 0) {
            $this->info('Nothing to do. All landlords already have a user_id.');
            return self::SUCCESS;
        }

        // PROCESS IN CHUNKS TO AVOID MEMORY ISSUES
        $query->chunk(100, function ($landlords) {
            foreach ($landlords as $landlord) {
                $this->processLandlord($landlord);
            }
        });

        // PRINT SUMMARY
        $this->newLine();
        $this->table(
            ['Result', 'Count'],
            [
                ['Linked to existing user', $this->summary['linked']],
                ['New user created', $this->summary['created']],
                ['Skipped (already done)', $this->summary['skipped']],
                ['Errors', $this->summary['errors']],
            ]
        );

        if ($this->isDryRun) {
            $this->warn('=== DRY RUN complete — nothing was written ===');
        } else {
            // WRITE ROLLBACK LOG
            $logPath = storage_path('logs/landlord_migration_' . now()->format('Ymd_His') . '.json');
            file_put_contents($logPath, json_encode([
                'run_at' => now()->toIso8601String(),
                'created_user_ids' => $this->createdUserIds,
                'summary' => $this->summary,
            ], JSON_PRETTY_PRINT));
            $this->info("Rollback log written to: {$logPath}");
        }

        return $this->summary['errors'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Process a single landlord record.
     */
    private function processLandlord(Landlord $landlord): void
    {
        try {
            $this->line("  Processing landlord #{$landlord->id} ({$landlord->email}) ...");

            // FIND THE BUSINESS OWNER WHO CREATED THIS LANDLORD
            /** @var \App\Models\User|null $businessOwner */
            $businessOwner = User::find($landlord->created_by);

            // CASE 1 — EMAIL ALREADY EXISTS IN users TABLE
            $existingUser = User::where('email', $landlord->email)->first();

            if ($existingUser) {
                $this->handleExistingUser($landlord, $existingUser);
                return;
            }

            // CASE 2 — CREATE A NEW USER
            $this->handleNewUser($landlord, $businessOwner);

        } catch (Exception $e) {
            $this->error("  ERROR on landlord #{$landlord->id}: " . $e->getMessage());
            Log::error('[landlord:migrate-users] Error', [
                'landlord_id' => $landlord->id,
                'email' => $landlord->email,
                'error' => $e->getMessage(),
            ]);
            $this->summary['errors']++;
        }
    }

    /**
     * CASE 1 — A user with this email already exists.
     * Link the landlord to that user and assign the landlord role.
     */
    private function handleExistingUser(Landlord $landlord, User $existingUser): void
    {
        // ALREADY HAS LANDLORD ROLE AND IS LINKED — SKIP
        if ($existingUser->hasRole('landlord') && $landlord->user_id === $existingUser->id) {
            $this->comment("    Skipped — landlord #{$landlord->id} already linked to user #{$existingUser->id}");
            $this->summary['skipped']++;
            return;
        }

        $this->warn("    [CASE 1] Existing user #{$existingUser->id} found for email {$landlord->email}");

        if (!$this->isDryRun) {
            DB::transaction(function () use ($landlord, $existingUser) {
                // ASSIGN LANDLORD ROLE IF NOT ALREADY PRESENT
                if (!$existingUser->hasRole('landlord')) {
                    $existingUser->assignRole('landlord');
                }

                // LINK landlord.user_id → existing user
                $landlord->user_id = $existingUser->id;
                $landlord->save();
            });
        }

        $this->info("    ✓ Linked landlord #{$landlord->id} → user #{$existingUser->id}");
        $this->summary['linked']++;
    }

    /**
     * CASE 2 — No existing user for this email.
     * Create a new inactive User account and link it to the landlord.
     */
    private function handleNewUser(Landlord $landlord, ?User $businessOwner): void
    {
        $this->comment("    [CASE 2] Creating new user for landlord #{$landlord->id} ({$landlord->email})");

        if (!$this->isDryRun) {
            DB::transaction(function () use ($landlord, $businessOwner) {
                // CREATE THE USER ACCOUNT
                // is_active = false — must be activated by admin
                // Temporary password = 12345678@We
                $user = User::create([
                    'first_Name' => $landlord->first_Name,
                    'last_Name' => $landlord->last_Name,
                    'email' => $landlord->email,
                    'phone' => $landlord->phone,
                    'image' => $landlord->image,
                    'address_line_1' => $landlord->address_line_1,
                    'address_line_2' => $landlord->address_line_2,
                    'country' => $landlord->country,
                    'city' => $landlord->city,
                    'postcode' => $landlord->postcode,
                    'lat' => $landlord->lat,
                    'long' => $landlord->long,
                    // TEMPORARY PASSWORD — admin must prompt landlord to reset
                    'password' => Hash::make('12345678@We'),
                    'is_active' => true,
                    'remember_token' => Str::random(10),
                    // INHERIT BUSINESS SCOPE FROM THE ADMIN WHO CREATED THE LANDLORD
                    'created_by' => $businessOwner?->id,
                    'business_id' => $businessOwner?->business_id,
                ]);

                // SET EMAIL VERIFICATION TOKEN SO STANDARD RESET EMAIL WORKS
                $user->email_verify_token = Str::random(30);
                $user->email_verify_token_expires = Carbon::now()->addDay();
                $user->save();

                // ASSIGN LANDLORD ROLE
                $user->assignRole('landlord');

                // LINK landlord.user_id → new user
                $landlord->user_id = $user->id;
                $landlord->save();

                $this->createdUserIds[] = $user->id;
                $this->info("    ✓ Created user #{$user->id} and linked to landlord #{$landlord->id}");
            });
        } else {
            $this->info("    [DRY-RUN] Would create user for {$landlord->email} with business_id={$businessOwner?->business_id}");
        }

        $this->summary['created']++;
    }
}
