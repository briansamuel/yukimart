<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProductImportService;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProductImportController extends Controller
{
    protected $importService;

    public function __construct(ProductImportService $importService)
    {
        $this->importService = $importService;
    }

    /**
     * Display the import page
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get categories for mapping
        $categories = Category::orderBy('category_name')->get();

        return view('admin.products.import.index', compact('categories'));
    }

    /**
     * Upload and preview import file
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        try {
            // Validate file upload
            $validator = Validator::make($request->all(), [
                'import_file' => 'required|file|mimes:xlsx,xls,csv|max:102400', // 100MB max
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => __('product.invalid_file_format'),
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('import_file');
            
            // Store file temporarily
            $filePath = $file->store('temp/imports', 'local');
            $originalName = $file->getClientOriginalName();
            $fileExtension = $file->getClientOriginalExtension();

            // Parse file and get preview data
            $previewData = $this->importService->parseFilePreview($filePath, $fileExtension);

            // Store file info in session for later use
            session([
                'import_file_path' => $filePath,
                'import_file_name' => $originalName,
                'import_file_extension' => $fileExtension,
            ]);

            Log::info('Import file uploaded successfully', [
                'user_id' => Auth::id(),
                'file_name' => $originalName,
                'file_size' => $file->getSize(),
                'rows_count' => count($previewData['data']),
            ]);

            return response()->json([
                'success' => true,
                'message' => __('product.file_uploaded_successfully'),
                'data' => $previewData
            ]);

        } catch (\Exception $e) {
            Log::error('Import file upload failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('product.file_upload_failed') . ': ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Test upload endpoint for debugging
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testUpload(Request $request)
    {
        try {
            Log::info('Test upload started', [
                'user_id' => Auth::id(),
                'request_size' => $request->header('Content-Length'),
                'files' => $request->hasFile('import_file') ? 'File present' : 'No file',
            ]);

            if (!$request->hasFile('import_file')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No file uploaded',
                    'debug' => [
                        'post_data' => $request->all(),
                        'files' => $_FILES,
                        'php_config' => [
                            'upload_max_filesize' => ini_get('upload_max_filesize'),
                            'post_max_size' => ini_get('post_max_size'),
                            'max_execution_time' => ini_get('max_execution_time'),
                            'memory_limit' => ini_get('memory_limit'),
                        ]
                    ]
                ], 400);
            }

            $file = $request->file('import_file');

            return response()->json([
                'success' => true,
                'message' => 'Test upload successful',
                'debug' => [
                    'file_info' => [
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'type' => $file->getClientMimeType(),
                        'extension' => $file->getClientOriginalExtension(),
                        'error' => $file->getError(),
                    ],
                    'php_config' => [
                        'upload_max_filesize' => ini_get('upload_max_filesize'),
                        'post_max_size' => ini_get('post_max_size'),
                        'max_execution_time' => ini_get('max_execution_time'),
                        'memory_limit' => ini_get('memory_limit'),
                    ],
                    'server_info' => [
                        'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                        'php_version' => PHP_VERSION,
                        'sapi' => php_sapi_name(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Test upload failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Test upload failed: ' . $e->getMessage(),
                'debug' => [
                    'php_config' => [
                        'upload_max_filesize' => ini_get('upload_max_filesize'),
                        'post_max_size' => ini_get('post_max_size'),
                        'max_execution_time' => ini_get('max_execution_time'),
                        'memory_limit' => ini_get('memory_limit'),
                    ]
                ]
            ], 500);
        }
    }

    /**
     * Get available product fields for mapping
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFields()
    {
        try {
            $fields = $this->importService->getAvailableFields();

            return response()->json([
                'success' => true,
                'data' => $fields
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get product fields', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('product.failed_to_get_fields'),
                'data' => []
            ], 500);
        }
    }

    /**
     * Process the import with column mapping
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function process(Request $request)
    {
        try {
            // Validate mapping data
            $validator = Validator::make($request->all(), [
                'column_mapping' => 'required|array',
                'import_options' => 'array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => __('product.invalid_mapping_data'),
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get file info from session
            $filePath = session('import_file_path');
            $fileExtension = session('import_file_extension');

            if (!$filePath || !Storage::disk('local')->exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => __('product.import_file_not_found'),
                ], 404);
            }

            // Process import
            $result = $this->importService->processImport(
                $filePath,
                $fileExtension,
                $request->input('column_mapping'),
                $request->input('import_options', [])
            );

            // Clean up temporary file
            Storage::disk('local')->delete($filePath);
            session()->forget(['import_file_path', 'import_file_name', 'import_file_extension']);

            Log::info('Product import completed', [
                'user_id' => Auth::id(),
                'result' => $result,
            ]);

            return response()->json([
                'success' => true,
                'message' => __('product.import_completed_successfully'),
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Product import failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('product.import_failed') . ': ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Download import template
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadTemplate()
    {
        try {
            $templatePath = $this->importService->generateTemplate();

            return response()->download($templatePath, 'product_import_template.xlsx')->deleteFileAfterSend();

        } catch (\Exception $e) {
            Log::error('Failed to generate import template', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', __('product.failed_to_generate_template'));
        }
    }

    /**
     * Get import history
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 10);

            $history = $this->importService->getImportHistory($page, $limit);

            return response()->json([
                'success' => true,
                'data' => $history
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get import history', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('product.failed_to_get_history'),
                'data' => []
            ], 500);
        }
    }

    /**
     * Validate import data before processing
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateImport(Request $request)
    {
        try {
            // Get file info from session
            $filePath = session('import_file_path');
            $fileExtension = session('import_file_extension');

            if (!$filePath || !Storage::disk('local')->exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => __('product.import_file_not_found'),
                ], 404);
            }

            // Validate import data
            $validation = $this->importService->validateImportData(
                $filePath,
                $fileExtension,
                $request->input('column_mapping', [])
            );

            return response()->json([
                'success' => true,
                'data' => $validation
            ]);

        } catch (\Exception $e) {
            Log::error('Import validation failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('product.validation_failed') . ': ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Get detailed preview of uploaded file
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function preview(Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 50);

            // Get file info from session
            $filePath = session('import_file_path');
            $fileExtension = session('import_file_extension');

            if (!$filePath || !Storage::disk('local')->exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => __('product.import_file_not_found'),
                ], 404);
            }

            // Get detailed preview data
            $previewData = $this->importService->getDetailedPreview($filePath, $fileExtension, $page, $limit);

            return response()->json([
                'success' => true,
                'data' => $previewData
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get file preview', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('product.failed_to_get_preview') . ': ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Get file statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFileStats()
    {
        try {
            // Get file info from session
            $filePath = session('import_file_path');
            $fileExtension = session('import_file_extension');
            $fileName = session('import_file_name');

            if (!$filePath || !Storage::disk('local')->exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => __('product.import_file_not_found'),
                ], 404);
            }

            // Get file statistics
            $stats = $this->importService->getFileStatistics($filePath, $fileExtension, $fileName);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get file statistics', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('product.failed_to_get_stats') . ': ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Clear import session data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearSession()
    {
        try {
            // Clean up temporary file if exists
            $filePath = session('import_file_path');
            if ($filePath && Storage::disk('local')->exists($filePath)) {
                Storage::disk('local')->delete($filePath);
            }

            // Clear session data
            session()->forget(['import_file_path', 'import_file_name', 'import_file_extension']);

            return response()->json([
                'success' => true,
                'message' => __('product.session_cleared')
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to clear import session', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('product.failed_to_clear_session'),
            ], 500);
        }
    }
}
