<?php

namespace App\Http\Controllers;

use App\Models\Result;
use App\Services\StatementOfResultService;
use Symfony\Component\HttpFoundation\Response;

class StatementOfResultController extends Controller
{
    /**
     * Stream the official Statement of Result PDF.
     */
    public function download(Result $result, StatementOfResultService $service): Response
    {
        $this->authorize('view', $result);

        return $service->download($result);
    }
}
