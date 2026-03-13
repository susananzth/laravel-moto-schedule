<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Actions\LogoutAction;
use App\Modules\Auth\DTOs\LogoutDTO;
use Illuminate\Http\Request;

/**
 * HTTP controller for logout route.
 * Uses the centralized LogoutAction from Auth module.
 */
final class LogoutController extends Controller
{
    public function __construct(
        private LogoutAction $logoutAction
    ) {}

    /**
     * Execute user logout.
     */
    public function __invoke(Request $request)
    {
        // pass authenticated user id to DTO so action can log or audit if needed
        $user = $request->user();
        $this->logoutAction->execute(new LogoutDTO($user?->id ?? 0));

        return redirect('/');
    }
}
