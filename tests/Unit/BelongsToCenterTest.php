<?php

namespace Tests\Unit;

use App\Models\Center;
use App\Models\User;
use App\Traits\BelongsToCenter;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TestScopedItem extends Model
{
    use BelongsToCenter;

    protected $table = 'test_scoped_items';
    protected $fillable = ['name', 'center_id'];
}

class BelongsToCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('test_scoped_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('center_id')->nullable()->constrained('centers')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function test_belongs_to_center_auto_scopes_and_assigns_center_id(): void
    {
        $this->seed(RoleSeeder::class);

        $center1 = Center::create([
            'name' => 'Center 1',
            'phone' => '+201000000001',
            'specialty' => 'Therapy',
            'city' => 'Cairo',
        ]);
        $center2 = Center::create([
            'name' => 'Center 2',
            'phone' => '+201000000002',
            'specialty' => 'Therapy',
            'city' => 'Alexandria',
        ]);

        $user1 = User::create([
            'name' => 'User 1',
            'phone' => '+201000000010',
            'email' => 'user1@center1.com',
            'password' => bcrypt('password'),
            'center_id' => $center1->id,
        ]);
        $user1->assignRole('admin');

        $user2 = User::create([
            'name' => 'User 2',
            'phone' => '+201000000020',
            'email' => 'user2@center2.com',
            'password' => bcrypt('password'),
            'center_id' => $center2->id,
        ]);
        $user2->assignRole('admin');

        // Act as User 1
        $this->actingAs($user1);
        $item1 = TestScopedItem::create(['name' => 'Item for Center 1']);

        $this->assertEquals($center1->id, $item1->center_id);

        // Act as User 2
        $this->actingAs($user2);
        $item2 = TestScopedItem::create(['name' => 'Item for Center 2']);

        $this->assertEquals($center2->id, $item2->center_id);

        // User 2 query should only see Item 2
        $itemsUser2 = TestScopedItem::all();
        $this->assertCount(1, $itemsUser2);
        $this->assertEquals('Item for Center 2', $itemsUser2->first()->name);

        // Act as Landlord (Global access)
        $landlord = User::create([
            'name' => 'Landlord User',
            'phone' => '+201000000030',
            'email' => 'landlord@massar.com',
            'password' => bcrypt('password'),
            'center_id' => null,
        ]);
        $landlord->assignRole('landlord');

        $this->actingAs($landlord);
        $allItems = TestScopedItem::all();
        $this->assertCount(2, $allItems);
    }
}
