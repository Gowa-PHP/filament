<?php

declare(strict_types=1);

namespace Gowa\Filament\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\Facades\Filament;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Panel;
use Filament\Support\SupportServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Gowa\Filament\GowaFilamentServiceProvider;
use Gowa\Filament\GowaPlugin;
use Gowa\Laravel\GowaServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Livewire\Features\SupportTesting\SupportTesting;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        if (class_exists(SupportTesting::class) && app()->bound('livewire')) {
            app('livewire')->componentHook(SupportTesting::class);
        }

        $panel = Panel::make()
            ->default()
            ->id('admin')
            ->path('admin')
            ->plugin(GowaPlugin::make());

        Filament::setCurrentPanel($panel);
        Filament::registerPanel($panel);

        $this->setUpDatabase($this->app);
    }

    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            BladeIconsServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            SupportServiceProvider::class,
            FormsServiceProvider::class,
            ActionsServiceProvider::class,
            NotificationsServiceProvider::class,
            WidgetsServiceProvider::class,
            FilamentServiceProvider::class,
            GowaServiceProvider::class,
            GowaFilamentServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('app.key', 'base64:6Cu4Ayw3hT7g1W6hS2k3l4m5n6o7p8q9r0s1t2u3v4w=');
        config()->set('session.driver', 'array');

        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        config()->set('gowa.base_url', 'https://gowa-api.test');
        config()->set('gowa.auth.username', 'admin');
        config()->set('gowa.auth.password', 'secret');
        config()->set('gowa.webhook.secret', 'test-secret');
    }

    protected function setUpDatabase($app): void
    {
        $app['db']->connection()->getSchemaBuilder()->create('gowa_instances', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->unique();
            $table->string('name')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('status')->default('close');
            $table->json('meta')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->timestamps();
        });

        $app['db']->connection()->getSchemaBuilder()->create('gowa_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instance_id')->constrained('gowa_instances')->cascadeOnDelete();
            $table->string('contact_jid');
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->unique(['instance_id', 'contact_jid']);
        });

        $app['db']->connection()->getSchemaBuilder()->create('gowa_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instance_id')->constrained('gowa_instances')->cascadeOnDelete();
            $table->foreignId('conversation_id')->nullable()->constrained('gowa_conversations')->nullOnDelete();
            $table->string('message_id')->index();
            $table->string('direction')->default('inbound');
            $table->string('status')->default('pending');
            $table->string('type')->default('text');
            $table->text('body')->nullable();
            $table->string('media_url')->nullable();
            $table->string('media_mime')->nullable();
            $table->string('reply_to')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }
}
