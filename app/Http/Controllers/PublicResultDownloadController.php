<?php

namespace App\Http\Controllers;

use App\Enums\ResultStatus;
use App\Models\Result;
use App\Services\SettingsService;
use App\Services\StatementOfResultService;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PublicResultDownloadController extends Controller
{
    public function __invoke(Result $result, StatementOfResultService $service): Response
    {
        $currentSession = app(SettingsService::class)->get('current_session')
            ?? now()->format('Y').'/'.(now()->format('Y') + 1);

        if ($result->status !== ResultStatus::Published || $result->session !== $currentSession) {
            throw new NotFoundHttpException('Result not found.');
        }

        return $service->download($result, stream: false);
    }
}
