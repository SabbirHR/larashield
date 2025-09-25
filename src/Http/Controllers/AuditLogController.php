<?php

namespace Larashield\Http\Controllers;

use Illuminate\Routing\Controller;
use OwenIt\Auditing\Models\Audit;
use Illuminate\Http\Request;
use Sabbir\ResponseBuilder\Services\ResourceService;
use Sabbir\ResponseBuilder\Traits\ResponseHelperTrait;
use Sabbir\ResponseBuilder\Facades\ResponseBuilder;

class AuditLogController extends Controller
{
    use ResponseHelperTrait;

    protected $resourceService;

    public function __construct(ResourceService $resourceService)
    {
        $this->middleware('permission:read_audit_log'); // Adjust permission as needed
        $this->resourceService = $resourceService;
        $this->resourceService->setValue(request(), new Audit);
    }

    /**
     * List all audit logs with optional filtering
     */
    public function index()
    {
        return $this->resourceService->index();
    }
}
