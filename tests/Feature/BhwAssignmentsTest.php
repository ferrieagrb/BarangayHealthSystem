<?php
namespace Tests\Feature;

use App\Http\Controllers\AdminSettings;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BhwAssignmentsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('users', function (Blueprint $t) { $t->id(); $t->string('role'); $t->softDeletes(); });
        Schema::create('purok_subgroups', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('user_id')->nullable(); $t->timestamps(); $t->softDeletes(); });
        Schema::create('purok_subgroup_user', function (Blueprint $t) { $t->unsignedBigInteger('purok_subgroup_id'); $t->unsignedBigInteger('user_id'); $t->timestamps(); });
        DB::table('users')->insert([['id'=>1,'role'=>'bhw'],['id'=>2,'role'=>'bhw'],['id'=>3,'role'=>'bhw'],['id'=>4,'role'=>'citizen']]);
        DB::table('purok_subgroups')->insert([['id'=>1,'user_id'=>3],['id'=>2,'user_id'=>1]]);
    }

    public function test_bulk_selection_preserves_other_workers_and_supports_shared_coverage(): void
    {
        app(AdminSettings::class)->updateBhwAssignments(Request::create('/', 'PUT', ['assignments'=>[
            1=>['present'=>1,'subgroup_ids'=>[1,2]], 2=>['present'=>1,'subgroup_ids'=>[1]],
        ]]));
        $this->assertEqualsCanonicalizing([1,2,3], DB::table('purok_subgroup_user')->where('purok_subgroup_id',1)->pluck('user_id')->all());
        app(AdminSettings::class)->updateBhwAssignments(Request::create('/', 'PUT', ['assignments'=>[1=>['present'=>1]]]));
        $this->assertEqualsCanonicalizing([2,3], DB::table('purok_subgroup_user')->where('purok_subgroup_id',1)->pluck('user_id')->all());
        $this->assertNull(DB::table('purok_subgroups')->where('id',2)->value('user_id'));
    }

    public function test_non_bhw_selection_is_rejected_without_changes(): void
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(AdminSettings::class)->updateBhwAssignments(Request::create('/', 'PUT', ['assignments'=>[4=>['present'=>1,'subgroup_ids'=>[1]]]]));
    }
}
