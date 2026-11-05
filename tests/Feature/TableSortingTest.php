<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Services\CompanyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TableSortingTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_service_sorts_by_whitelisted_field_and_direction(): void
    {
        Company::create(['code' => 'B', 'name' => 'Beta', 'status' => 'active']);
        Company::create(['code' => 'A', 'name' => 'Alpha', 'status' => 'active']);
        Company::create(['code' => 'C', 'name' => 'Charlie', 'status' => 'active']);

        $service = app(CompanyService::class);

        $asc = $service->getFiltered(sortField: 'name', sortDir: 'asc');
        $this->assertSame(['Alpha', 'Beta', 'Charlie'], $asc->pluck('name')->all());

        $desc = $service->getFiltered(sortField: 'name', sortDir: 'desc');
        $this->assertSame(['Charlie', 'Beta', 'Alpha'], $desc->pluck('name')->all());
    }

    public function test_unknown_sort_field_falls_back_to_default(): void
    {
        Company::create(['code' => 'B', 'name' => 'Beta', 'status' => 'active']);
        Company::create(['code' => 'A', 'name' => 'Alpha', 'status' => 'active']);

        $result = app(CompanyService::class)->getFiltered(sortField: 'malicious_column', sortDir: 'asc');

        $this->assertSame(['Alpha', 'Beta'], $result->pluck('name')->all());
    }

    public function test_null_values_sorted_first_on_asc_and_last_on_desc(): void
    {
        Company::create(['code' => 'N', 'name' => 'No Npwp', 'status' => 'active', 'npwp' => null]);
        Company::create(['code' => 'Z', 'name' => 'Zeta', 'status' => 'active', 'npwp' => '999999999999999']);
        Company::create(['code' => 'A', 'name' => 'Alpha', 'status' => 'active', 'npwp' => '111111111111111']);

        $service = app(CompanyService::class);

        $asc = $service->getFiltered(sortField: 'npwp', sortDir: 'asc');
        $this->assertSame('No Npwp', $asc->first()->name);
        $this->assertSame('Zeta', $asc->last()->name);

        $desc = $service->getFiltered(sortField: 'npwp', sortDir: 'desc');
        $this->assertSame('Zeta', $desc->first()->name);
        $this->assertSame('No Npwp', $desc->last()->name);
    }

    public function test_company_service_sorts_by_users_count(): void
    {
        $empty = Company::create(['code' => 'E', 'name' => 'Empty Co', 'status' => 'active']);
        $full = Company::create(['code' => 'F', 'name' => 'Full Co', 'status' => 'active']);
        User::factory()->count(3)->create(['company_id' => $full->id]);

        $result = app(CompanyService::class)->getFiltered(sortField: 'users_count', sortDir: 'desc');

        $this->assertSame('Full Co', $result->first()->name);
        $this->assertSame('Empty Co', $result->last()->name);
    }
}
