<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\DefenceSession;
use App\Models\Project;
use App\Models\User;
use App\Models\SessionAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function index()
    {
        $reports = Report::with('generatedBy:id,name')
            ->latest()
            ->paginate(10);

        return view('admin.reports.index', compact('reports'));
    }

    public function create()
    {
        $reportTypes = [
            'defence_sessions' => 'Defence Sessions Report',
            'projects' => 'Projects Report', 
            'users' => 'Users Report',
            'evaluations' => 'Evaluations Report',
            'supervisor_workload' => 'Supervisor Workload Report',
        ];

        return view('admin.reports.create', compact('reportTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:defence_sessions,projects,users,evaluations,supervisor_workload'],
            'format' => ['required', 'string', 'in:pdf,excel,csv'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'status_filter' => ['nullable', 'string'],
            'role_filter' => ['nullable', 'string'],
        ]);

        $parameters = [
            'date_from' => $validated['date_from'] ?? null,
            'date_to' => $validated['date_to'] ?? null,
            'status_filter' => $validated['status_filter'] ??  null,
            'role_filter' => $validated['role_filter'] ?? null,
        ];

        $report = Report::create([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'format' => $validated['format'],
            'parameters' => $parameters,
            'generated_by' => auth()->id(),
            'status' => 'pending',
        ]);

        // Generate report immediately (you could also queue this)
        $this->generateReport($report);

        return redirect()->route('admin.reports.index')
            ->with('success', 'Report generation started.  You will be able to download it once completed.');
    }

    public function download(Report $report)
    {
        if ($report->status !== 'completed' || !$report->file_path) {
            return back()->withErrors(['error' => 'Report is not available for download.']);
        }

        $filePath = storage_path('app/' . $report->file_path);
        
        if (!file_exists($filePath)) {
            return back()->withErrors(['error' => 'Report file not found.']);
        }

        return response()->download($filePath, $report->title .  '.' . $report->format);
    }

    public function destroy(Report $report)
    {
        // Delete file if exists
        if ($report->file_path && Storage::exists($report->file_path)) {
            Storage::delete($report->file_path);
        }

        $report->delete();

        return redirect()->route('admin.reports.index')
            ->with('success', 'Report deleted successfully.');
    }

    private function generateReport(Report $report)
    {
        try {
            $report->update(['status' => 'generating']);

            $data = $this->getReportData($report);
            $filePath = $this->createReportFile($report, $data);

            $report->update([
                'status' => 'completed',
                'file_path' => $filePath,
                'generated_at' => now(),
            ]);
        } catch (\Exception $e) {
            $report->update(['status' => 'failed']);
            Log::error('Report generation failed:  ' . $e->getMessage());
        }
    }

    private function getReportData(Report $report): array
    {
        $params = $report->parameters;

        return match($report->type) {
            'defence_sessions' => $this->getDefenceSessionsData($params),
            'projects' => $this->getProjectsData($params),
            'users' => $this->getUsersData($params),
            'evaluations' => $this->getEvaluationsData($params),
            'supervisor_workload' => $this->getSupervisorWorkloadData($params),
            default => []
        };
    }

    private function getDefenceSessionsData(array $params): array
    {
        $query = DefenceSession::with(['project:id,title', 'committee:id,name', 'assignments.evaluator:id,name']);

        if ($params['date_from']) {
            $query->whereDate('scheduled_at', '>=', $params['date_from']);
        }
        if ($params['date_to']) {
            $query->whereDate('scheduled_at', '<=', $params['date_to']);
        }
        if ($params['status_filter']) {
            $query->where('status', $params['status_filter']);
        }

        return $query->get()->map(function ($session) {
            return [
                'ID' => $session->id,
                'Project' => $session->project?->title ?? 'N/A',
                'Committee' => $session->committee?->name ?? 'N/A',
                'Scheduled At' => $session->scheduled_at->format('Y-m-d H:i'),
                'Status' => ucfirst($session->status),
                'Evaluators' => $session->assignments->pluck('evaluator.name')->implode(', '),
                'Evaluations Complete' => $session->assignments->whereNotNull('submitted_at')->count().'/'.$session->assignments->count(),
            ];
        })->toArray();
    }

    private function getProjectsData(array $params): array
    {
        $query = Project::with(['student:id,name', 'supervisor:id,name']);

        if ($params['date_from']) {
            $query->whereDate('created_at', '>=', $params['date_from']);
        }
        if ($params['date_to']) {
            $query->whereDate('created_at', '<=', $params['date_to']);
        }
        if ($params['status_filter']) {
            $query->where('status', $params['status_filter']);
        }

        return $query->get()->map(function ($project) {
            return [
                'ID' => $project->id,
                'Title' => $project->title,
                'Student' => $project->student?->name ?? 'N/A',
                'Supervisor' => $project->supervisor?->name ?? 'N/A',
                'Status' => ucfirst($project->status),
                'Created At' => $project->created_at->format('Y-m-d'),
                'Updated At' => $project->updated_at->format('Y-m-d'),
            ];
        })->toArray();
    }

    // private function getUsersData(array $params): array
    // {
    //     $query = User::with('supervisorProfile:user_id,available_slots');

    //     if ($params['date_from']) {
    //         $query->whereDate('created_at', '>=', $params['date_from']);
    //     }
    //     if ($params['date_to']) {
    //         $query->whereDate('created_at', '<=', $params['date_to']);
    //     }
    //     if ($params['role_filter']) {
    //         $query->where('role', $params['role_filter']);
    //     }

    //     return $query->get()->map(function ($user) {
    //         return [
    //             'ID' => $user->id,
    //             'Name' => $user->name,
    //             'Email' => $user->email,
    //             'Role' => ucfirst($user->role),
    //             'Status' => ucfirst($user->status ??  'active'),
    //             'Available Slots' => $user->role === 'supervisor' ? ($user->supervisorProfile?->available_slots ?? 0) : 'N/A',
    //             'Joined At' => $user->created_at->format('Y-m-d'),
    //         ];
    //     })->toArray();
    // }

    private function getUsersData(array $params): array
    {
        $query = User::with('supervisorProfile:user_id,available_slots');

        if ($params['date_from']) {
            $query->whereDate('created_at', '>=', $params['date_from']);
        }
        if ($params['date_to']) {
            $query->whereDate('created_at', '<=', $params['date_to']);
        }
        if ($params['role_filter']) {
            $query->where('role', $params['role_filter']);
        }

        // Get all evaluator user IDs
        $evaluatorIds = SessionAssignment::distinct()->pluck('user_id')->toArray();

        return $query->get()->map(function ($user) use ($evaluatorIds) {
            // Determine if user is an evaluator
            $isEvaluator = in_array($user->id, $evaluatorIds);
            
            // Build role string
            $roleString = ucfirst($user->role);
            if ($user->role === 'supervisor' && $isEvaluator) {
                $roleString .= ' + Evaluator';
            }

            return [
                'ID' => $user->id,
                'Name' => $user->name,
                'Email' => $user->email,
                'Role' => $roleString, // Modified to include evaluator info
                'Status' => ucfirst($user->status ??  'active'),
                'Available Slots' => $user->role === 'supervisor' ? ($user->supervisorProfile?->available_slots ?? 0) : 'N/A',
                'Joined At' => $user->created_at->format('Y-m-d'),
            ];
        })->toArray();
    }

    private function getEvaluationsData(array $params): array
    {
        $query = SessionAssignment::with([
            'session.project:id,title',
            'evaluator:id,name'
        ])->whereNotNull('submitted_at');

        if ($params['date_from']) {
            $query->whereDate('submitted_at', '>=', $params['date_from']);
        }
        if ($params['date_to']) {
            $query->whereDate('submitted_at', '<=', $params['date_to']);
        }

        return $query->get()->map(function ($assignment) {
            return [
                'Assignment ID' => $assignment->id,
                'Project' => $assignment->session?->project?->title ?? 'N/A',
                'Evaluator' => $assignment->evaluator?->name ?? 'N/A',
                'Total Score' => $assignment->total_score ?? 0,
                'Submitted At' => $assignment->submitted_at->format('Y-m-d H:i'),
                'Session Date' => $assignment->session?->scheduled_at->format('Y-m-d') ?? 'N/A',
            ];
        })->toArray();
    }

    private function getSupervisorWorkloadData(array $params): array
    {
        $supervisors = User::where('role', 'supervisor')
            ->with(['supervisorProfile:user_id,available_slots'])
            ->withCount(['supervisedProjects'])
            ->get();

        return $supervisors->map(function ($supervisor) {
            $availableSlots = $supervisor->supervisorProfile?->available_slots ??  0;
            $usedSlots = $supervisor->supervised_projects_count;
            
            return [
                'Supervisor' => $supervisor->name,
                'Email' => $supervisor->email,
                'Available Slots' => $availableSlots,
                'Used Slots' => $usedSlots,
                'Remaining Slots' => max(0, $availableSlots - $usedSlots),
                'Utilization %' => $availableSlots > 0 ?  round(($usedSlots / $availableSlots) * 100, 1) : 0,
            ];
        })->toArray();
    }

    private function createReportFile(Report $report, array $data): string
    {
        $fileName = Str::slug($report->title) . '_' . now()->format('Y_m_d_His') . '.' . $report->format;
        $filePath = 'reports/' . $fileName;

        switch ($report->format) {
            case 'csv':
                $this->createCsvFile($filePath, $data);
                break;
            case 'excel':
                $this->createExcelFile($filePath, $data);
                break;
            case 'pdf':
                $this->createPdfFile($filePath, $data, $report);
                break;
        }

        return $filePath;
    }

    private function createCsvFile(string $filePath, array $data): void
    {
        if (empty($data)) {
            Storage::put($filePath, 'No data available');
            return;
        }

        $csv = fopen(storage_path('app/' . $filePath), 'w');
        
        // Write headers
        fputcsv($csv, array_keys($data[0]));
        
        // Write data
        foreach ($data as $row) {
            fputcsv($csv, $row);
        }
        
        fclose($csv);
    }

    private function createExcelFile(string $filePath, array $data): void
    {
        // For now, create as CSV (you can enhance with PhpSpreadsheet later)
        $this->createCsvFile($filePath, $data);
    }

    private function createPdfFile(string $filePath, array $data, Report $report): void
    {
        // Simple HTML to PDF conversion (you can enhance with DomPDF/mPDF later)
        $html = $this->generateHtmlReport($data, $report);
        Storage::put($filePath, $html);
    }

    private function generateHtmlReport(array $data, Report $report): string
    {
        if (empty($data)) {
            return '<html><body><h1>' . $report->title . '</h1><p>No data available</p></body></html>';
        }

        $html = '<html><head><style>
            table { width: 100%; border-collapse:  collapse; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color:  #f2f2f2; }
        </style></head><body>';
        
        $html .= '<h1>' . $report->title . '</h1>';
        $html .= '<p>Generated on:  ' . now()->format('Y-m-d H:i: s') . '</p>';
        $html .= '<table><thead><tr>';
        
        // Headers
        foreach (array_keys($data[0]) as $header) {
            $html .= '<th>' . $header . '</th>';
        }
        $html .= '</tr></thead><tbody>';
        
        // Data
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $value) {
                $html .= '<td>' . htmlspecialchars($value) . '</td>';
            }
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table></body></html>';
        
        return $html;
    }
}