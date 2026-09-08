<?php

declare(strict_types=1);

use Filament\Panel;
use Gowa\Filament\GowaPlugin;
use Gowa\Filament\Pages\GowaConversationsPage;
use Gowa\Filament\Pages\GowaMessagingPage;
use Gowa\Filament\Resources\GowaInstanceResource;
use Gowa\Filament\Widgets\GowaDeviceStatusWidget;

it('has correct plugin id', function () {
    $plugin = GowaPlugin::make();

    expect($plugin->getId())->toBe('gowa-filament');
});

it('can instantiate plugin via make static method', function () {
    $plugin = GowaPlugin::make();

    expect($plugin)->toBeInstanceOf(GowaPlugin::class);
});

it('registers its enabled components with the panel', function () {
    $panel = Panel::make()
        ->id('gowa-enabled-components')
        ->plugin(new GowaPlugin());

    expect($panel->getResources())->toContain(GowaInstanceResource::class)
        ->and($panel->getPages())->toContain(GowaMessagingPage::class)
        ->and($panel->getPages())->toContain(GowaConversationsPage::class)
        ->and($panel->getWidgets())->toContain(GowaDeviceStatusWidget::class);
});

it('does not register components disabled through fluent configuration', function () {
    $plugin = (new GowaPlugin())
        ->instanceResource(false)
        ->messagingPage(false)
        ->conversationsPage(false)
        ->deviceStatusWidget(false);

    $panel = Panel::make()
        ->id('gowa-disabled-components')
        ->plugin($plugin);

    expect($plugin->hasInstanceResource())->toBeFalse()
        ->and($plugin->hasMessagingPage())->toBeFalse()
        ->and($plugin->hasConversationsPage())->toBeFalse()
        ->and($plugin->hasDeviceStatusWidget())->toBeFalse()
        ->and($panel->getResources())->not->toContain(GowaInstanceResource::class)
        ->and($panel->getPages())->not->toContain(GowaMessagingPage::class)
        ->and($panel->getPages())->not->toContain(GowaConversationsPage::class)
        ->and($panel->getWidgets())->not->toContain(GowaDeviceStatusWidget::class);
});

it('supports custom authorization callbacks for conversations and messaging', function () {
    $plugin = GowaPlugin::make()
        ->authorizeConversationsUsing(fn() => false)
        ->authorizeMessagingUsing(fn() => true);

    expect($plugin->getConversationsAuthorizationCallback())->not->toBeNull()
        ->and($plugin->getMessagingAuthorizationCallback())->not->toBeNull()
        ->and(call_user_func($plugin->getConversationsAuthorizationCallback()))->toBeFalse()
        ->and(call_user_func($plugin->getMessagingAuthorizationCallback()))->toBeTrue();
});

it('fails closed when authorization callback throws an exception', function () {
    $plugin = filament()->getPlugin('gowa-filament');
    $plugin->authorizeConversationsUsing(fn() => throw new \RuntimeException('Auth service unavailable'));
    $plugin->authorizeMessagingUsing(fn() => throw new \RuntimeException('Auth service unavailable'));

    expect(GowaConversationsPage::canAccess())->toBeFalse()
        ->and(GowaMessagingPage::canAccess())->toBeFalse();
});
