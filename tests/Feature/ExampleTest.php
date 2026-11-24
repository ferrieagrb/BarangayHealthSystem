<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id(); $table->string('key'); $table->text('value')->nullable();
        });
        Schema::create('events', function (Blueprint $table) {
            $table->id(); $table->string('title'); $table->date('start');
            $table->timestamps(); $table->softDeletes();
        });
        Schema::create('announcements', function (Blueprint $table) {
            $table->id(); $table->string('title'); $table->text('description');
            $table->string('category'); $table->timestamps(); $table->softDeletes();
        });
        Schema::create('announcement_images', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('announcement_id');
            $table->string('image_path'); $table->integer('sort_order')->default(0);
        });
        Schema::create('page_views', function (Blueprint $table) {
            $table->id(); $table->string('ip_address')->nullable(); $table->text('url')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); $table->integer('load_time')->nullable();
            $table->string('browser')->nullable(); $table->timestamps();
        });

        $response = $this->get('/');

        $response->assertStatus(200);
        $this->assertDatabaseCount('page_views', 1);
    }
}
