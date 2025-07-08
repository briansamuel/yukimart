<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AuditLogController extends Controller
{
    /**
     * Display audit logs page
     */
    public function index()
    {
        $users = User::select('id', 'name')->orderBy('name')->get();
        $actions = AuditLog::distinct()->pluck('action')->sort()->values();
        $modelTypes = AuditLog::distinct()->pluck('model_type')->sort()->values();
        
        return view('admin.audit-logs.index', compact('users', 'actions', 'modelTypes'));
    }

    /**
     * Get audit logs data for DataTables
     */
    public function getData(Request $request)
    {
        try {
            $query = AuditLog::with('user')
                ->orderBy('created_at', 'desc');

            // Apply search
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query->where(function($q) use ($search) {
                    $q->where('action', 'like', "%{$search}%")
                      ->orWhere('model_type', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('ip_address', 'like', "%{$search}%")
                      ->orWhereHas('user', function($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%");
                      });
                });
            }

            // Apply filters
            if ($request->has('user_id') && $request->user_id !== '') {
                $query->where('user_id', $request->user_id);
            }

            if ($request->has('action') && $request->action !== '') {
                $query->where('action', $request->action);
            }

            if ($request->has('model_type') && $request->model_type !== '') {
                $query->where('model_type', $request->model_type);
            }

            if ($request->has('date_from') && $request->date_from !== '') {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to !== '') {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Get total count before pagination
            $totalRecords = $query->count();

            // Apply pagination
            $start = $request->start ?? 0;
            $length = $request->length ?? 25;
            $logs = $query->skip($start)->take($length)->get();

            $data = $logs->map(function($log) {
                return [
                    'id' => $log->id,
                    'user_name' => $log->user ? $log->user->name : 'System',
                    'action' => $log->action,
                    'action_display' => $log->action_display,
                    'action_icon' => $log->action_icon,
                    'model_type' => $log->model_type,
                    'model_display' => $log->model_display,
                    'model_id' => $log->model_id,
                    'description' => $log->description,
                    'ip_address' => $log->ip_address,
                    'created_at' => $log->created_at->format('d/m/Y H:i:s'),
                    'time_ago' => $log->time_ago,
                    'has_changes' => !empty($log->old_values) || !empty($log->new_values),
                ];
            });

            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Lỗi khi tải dữ liệu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show audit log details
     */
    public function show(AuditLog $auditLog)
    {
        $auditLog->load('user');
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $auditLog->id,
                'user' => $auditLog->user ? [
                    'id' => $auditLog->user->id,
                    'name' => $auditLog->user->name,
                    'email' => $auditLog->user->email,
                ] : null,
                'action' => $auditLog->action,
                'action_display' => $auditLog->action_display,
                'action_icon' => $auditLog->action_icon,
                'model_type' => $auditLog->model_type,
                'model_display' => $auditLog->model_display,
                'model_id' => $auditLog->model_id,
                'old_values' => $auditLog->old_values,
                'new_values' => $auditLog->new_values,
                'changes_summary' => $auditLog->changes_summary,
                'ip_address' => $auditLog->ip_address,
                'user_agent' => $auditLog->user_agent,
                'url' => $auditLog->url,
                'method' => $auditLog->method,
                'description' => $auditLog->description,
                'tags' => $auditLog->tags,
                'created_at' => $auditLog->created_at->format('d/m/Y H:i:s'),
                'time_ago' => $auditLog->time_ago,
            ]
        ]);
    }

    /**
     * Get audit log statistics
     */
    public function getStatistics(Request $request)
    {
        try {
            $filters = [
                'date_from' => $request->date_from ? Carbon::parse($request->date_from) : Carbon::now()->subDays(30),
                'date_to' => $request->date_to ? Carbon::parse($request->date_to) : Carbon::now(),
                'user_id' => $request->user_id,
                'action' => $request->action,
                'model_type' => $request->model_type,
            ];

            $statistics = AuditLog::getStatistics($filters);

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải thống kê: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export audit logs
     */
    public function export(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'format' => 'required|in:csv,xlsx,json',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'user_id' => 'nullable|exists:users,id',
            'action' => 'nullable|string',
            'model_type' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $query = AuditLog::with('user');

            // Apply filters
            if ($request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            if ($request->user_id) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->action) {
                $query->where('action', $request->action);
            }

            if ($request->model_type) {
                $query->where('model_type', $request->model_type);
            }

            $logs = $query->orderBy('created_at', 'desc')->get();

            $filename = 'audit_logs_' . date('Y-m-d_H-i-s') . '.' . $request->format;

            switch ($request->format) {
                case 'csv':
                    return $this->exportToCsv($logs, $filename);
                case 'xlsx':
                    return $this->exportToExcel($logs, $filename);
                case 'json':
                    return $this->exportToJson($logs, $filename);
                default:
                    throw new \Exception('Định dạng không được hỗ trợ');
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xuất dữ liệu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clean old audit logs
     */
    public function cleanup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'days' => 'required|integer|min:1|max:365',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $deletedCount = AuditLog::cleanOldLogs($request->days);

            // Log the cleanup action
            AuditLog::logAction('cleanup', null, "Dọn dẹp {$deletedCount} log cũ hơn {$request->days} ngày");

            return response()->json([
                'success' => true,
                'message' => "Đã xóa {$deletedCount} log cũ thành công",
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi dọn dẹp log: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available filters
     */
    public function getFilters()
    {
        try {
            $users = User::select('id', 'name')
                ->orderBy('name')
                ->get()
                ->map(function($user) {
                    return [
                        'id' => $user->id,
                        'text' => $user->name
                    ];
                });

            $actions = AuditLog::distinct()
                ->pluck('action')
                ->sort()
                ->values()
                ->map(function($action) {
                    return [
                        'id' => $action,
                        'text' => ucfirst($action)
                    ];
                });

            $modelTypes = AuditLog::distinct()
                ->pluck('model_type')
                ->sort()
                ->values()
                ->map(function($modelType) {
                    return [
                        'id' => $modelType,
                        'text' => class_basename($modelType)
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'users' => $users,
                    'actions' => $actions,
                    'model_types' => $modelTypes,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải bộ lọc: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export to CSV
     */
    private function exportToCsv($logs, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");
            
            // Headers
            fputcsv($file, [
                'ID', 'Người dùng', 'Hành động', 'Model', 'Model ID', 
                'Mô tả', 'IP Address', 'Thời gian'
            ]);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user ? $log->user->name : 'System',
                    $log->action_display,
                    $log->model_display,
                    $log->model_id,
                    $log->description,
                    $log->ip_address,
                    $log->created_at->format('d/m/Y H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export to JSON
     */
    private function exportToJson($logs, $filename)
    {
        $data = $logs->map(function($log) {
            return [
                'id' => $log->id,
                'user' => $log->user ? $log->user->name : 'System',
                'action' => $log->action,
                'model_type' => $log->model_type,
                'model_id' => $log->model_id,
                'old_values' => $log->old_values,
                'new_values' => $log->new_values,
                'description' => $log->description,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'url' => $log->url,
                'method' => $log->method,
                'created_at' => $log->created_at->toISOString(),
            ];
        });

        return response()->json($data)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Export to Excel (placeholder - would need Laravel Excel package)
     */
    private function exportToExcel($logs, $filename)
    {
        // This would require Laravel Excel package
        // For now, fallback to CSV
        return $this->exportToCsv($logs, str_replace('.xlsx', '.csv', $filename));
    }
}
