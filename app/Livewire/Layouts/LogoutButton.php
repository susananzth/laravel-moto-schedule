<?php

declare(strict_types=1);

namespace App\Livewire\Layouts;

use App\Modules\Auth\Actions\LogoutAction;
use App\Modules\Auth\DTOs\LogoutDTO;
use Livewire\Component;

final class LogoutButton extends Component
{
    public function __construct(
        ?LogoutAction $logoutAction = null
    ) {
        // Allow Livewire instantiation without parameters during tests. Resolve via container when not provided.
        $this->logoutAction = $logoutAction ?? app()->make(LogoutAction::class);
    }

    public function logout(): void
    {
        // Execute logout via new Auth module
        $this->logoutAction->execute(new LogoutDTO());

        // Redirect using navigate for SPA-like experience
        $this->redirect('/', navigate: true);
    }

    public function render()
    {
        return view('livewire.layouts.logout-button');
    }
}
