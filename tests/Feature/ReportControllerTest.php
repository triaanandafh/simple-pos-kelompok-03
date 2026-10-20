<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_report_filters_transactions_inclusive_of_the_selected_end_date(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $this->createTransaction($admin, 75, '2026-09-30 23:59:59');
        $this->createTransaction($admin, 100, '2026-10-01 00:00:00');
        $this->createTransaction($admin, 200, '2026-10-03 23:59:59');
        $this->createTransaction($admin, 300, '2026-10-04 00:00:00');

        $this->get(route('reports.index', ['from' => '2026-10-01', 'to' => '2026-10-03']))
            ->assertOk()
            ->assertSee('Rp 300')
            ->assertSee('2 transaksi')
            ->assertSee('value="2026-10-01"', false)
            ->assertSee('value="2026-10-03"', false);
    }

    public function test_report_defaults_to_the_current_month_through_the_end_of_today(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $this->createTransaction($admin, 75, now()->startOfMonth()->subSecond()->toDateTimeString());
        $this->createTransaction($admin, 100, now()->startOfMonth()->toDateTimeString());
        $this->createTransaction($admin, 200, now()->endOfDay()->toDateTimeString());
        $this->createTransaction($admin, 300, now()->endOfDay()->addSecond()->toDateTimeString());

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Rp 300')
            ->assertSee('2 transaksi')
            ->assertSee('value="'.now()->startOfMonth()->toDateString().'"', false)
            ->assertSee('value="'.now()->toDateString().'"', false);
    }

    public function test_non_admin_cannot_access_the_sales_report(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);

        $this->actingAs($cashier)
            ->get(route('reports.index'))
            ->assertForbidden();
    }

    private function createTransaction(User $user, int $total, string $createdAt): void
    {
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'total' => $total,
        ]);

        DB::table('transactions')
            ->where('id', $transaction->id)
            ->update(['created_at' => $createdAt]);
    }
}
