<?php

use App\Filament\Pages\ManageAboutCompany;
use App\Models\AboutCompany;
use App\Models\User;
use App\Support\OrgChart;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * @param  array<int, array<string, mixed>>  $tree
 * @return array<int, string>
 */
function orgNames(array $tree): array
{
    return collect($tree)->map(fn (array $node): string => $node['name'])->all();
}

function orgNode(string $id, string $name, ?string $parent = null, string $placement = 'below', int $order = 0): array
{
    return ['id' => $id, 'name' => $name, 'position' => "Jabatan {$name}", 'parent_id' => $parent, 'placement' => $placement, 'sort_order' => $order];
}

test('people are nested under who they report to, in sort order', function () {
    $tree = OrgChart::tree(['nodes' => [
        orgNode('anak-b', 'Anak B', 'bos', order: 2),
        orgNode('bos', 'Bos'),
        orgNode('anak-a', 'Anak A', 'bos', order: 1),
        orgNode('cucu', 'Cucu', 'anak-a'),
    ]]);

    expect(orgNames($tree))->toBe(['Bos'])
        ->and(orgNames($tree[0]['children']))->toBe(['Anak A', 'Anak B'])
        ->and(orgNames($tree[0]['children'][0]['children']))->toBe(['Cucu'])
        ->and($tree[0]['depth'])->toBe(0)
        ->and($tree[0]['children'][0]['children'][0]['depth'])->toBe(2);
});

test('side placement shows a card beside its manager without adding a level', function () {
    $tree = OrgChart::tree(['nodes' => [
        orgNode('coo', 'COO'),
        orgNode('keuangan', 'Keuangan', 'coo', 'side'),
        orgNode('tim', 'Tim', 'coo'),
    ]]);

    expect(orgNames($tree[0]['side']))->toBe(['Keuangan'])
        ->and(orgNames($tree[0]['children']))->toBe(['Tim']);
});

test('someone reporting to a side card is attached to that card\'s manager instead', function () {
    $tree = OrgChart::tree(['nodes' => [
        orgNode('coo', 'COO'),
        orgNode('keuangan', 'Keuangan', 'coo', 'side'),
        orgNode('staf', 'Staf Keuangan', 'keuangan'),
    ]]);

    expect(orgNames($tree[0]['side']))->toBe(['Keuangan'])
        ->and(orgNames($tree[0]['children']))->toBe(['Staf Keuangan']);
});

test('unknown managers become roots, unnamed people are ignored and reporting loops cannot hang the page', function () {
    $tree = OrgChart::tree(['nodes' => [
        orgNode('a', 'Yatim', 'tidak-ada'),
        orgNode('b', 'Tanpa Nama Dihapus', null),
        ['id' => 'c', 'name' => '', 'position' => 'Kosong', 'parent_id' => null],
        orgNode('loop-1', 'Loop Satu', 'loop-2'),
        orgNode('loop-2', 'Loop Dua', 'loop-1'),
        orgNode('self', 'Diri Sendiri', 'self'),
    ]]);

    expect(orgNames($tree))->toBe(['Yatim', 'Tanpa Nama Dihapus', 'Diri Sendiri']);
    expect(OrgChart::tree(null))->toBe([])
        ->and(OrgChart::tree(['nodes' => []]))->toBe([]);
});

test('the seeded chart has the full structure of the company chart', function () {
    $this->seed(DatabaseSeeder::class);

    $tree = AboutCompany::current()->org_tree;
    $director = $tree[0]['children'][0];
    $coo = $director['children'][0];

    expect($tree)->toHaveCount(1)
        ->and($tree[0]['position'])->toBe('Commissioner')
        ->and($director['position'])->toBe('Director')
        ->and($coo['position'])->toBe('Chief Operating Officer')
        ->and(orgNames($coo['side']))->toBe(['Khansa Wijayanti'])
        ->and(orgNames($coo['children']))->toBe(['Moh. Arifin Tris Hidayanto', 'Tazam Wibowo', 'Eko Haryanto', 'Setyo Yanuar Prayitno'])
        ->and(collect($coo['children'][0]['children'])->pluck('name')->all())->toBe(['Koirul Anam', 'Budiarto', 'Laode Iqmal Aztamar'])
        ->and(collect($coo['children'][0]['children'][1]['children'][0]['children'])->pluck('name')->all())->toBe(['Rio Sandi', 'Oki Lukman']);
});

test('the about page hides the chart card when there are no people', function () {
    $this->seed(DatabaseSeeder::class);
    AboutCompany::current()->update(['org_chart' => ['chart_revision_tag' => 'KOSONG', 'nodes' => []]]);

    $this->get(route('about'))->assertOk()->assertDontSee('id="org-tree"', false);
});

test('names and positions from the cms are escaped in the chart', function () {
    $this->seed(DatabaseSeeder::class);
    AboutCompany::current()->update(['org_chart' => ['chart_revision_tag' => 'X', 'nodes' => [
        ['id' => 'x', 'name' => '<script>alert(1)</script>', 'position' => '<b>Bos</b>', 'parent_id' => null, 'placement' => 'below', 'sort_order' => 1],
    ]]]);

    $this->get(route('about'))->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
});

test('an admin can edit the organization chart people through the about company form', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = User::factory()->create(['email' => 'admin_orgchart@example.com']);

    Livewire\Livewire::actingAs($admin)
        ->test(ManageAboutCompany::class)
        ->assertSuccessful()
        ->set('data.org_chart.nodes', [
            'item-1' => ['id' => 'bos', 'name' => 'Bos Baru', 'position' => 'Direktur', 'parent_id' => null, 'placement' => 'below', 'sort_order' => 1],
            'item-2' => ['id' => 'staf', 'name' => 'Staf Baru', 'position' => 'Staf', 'parent_id' => 'bos', 'placement' => 'side', 'sort_order' => 2],
        ])
        ->call('save')
        ->assertHasNoErrors();

    $tree = AboutCompany::current()->org_tree;

    expect(orgNames($tree))->toBe(['Bos Baru'])
        ->and(orgNames($tree[0]['side']))->toBe(['Staf Baru']);
});
