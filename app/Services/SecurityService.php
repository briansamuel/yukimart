<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SecurityService
{
    protected $cachePrefix = 'security:';
    protected $maxLoginAttempts = 5;
    protected $lockoutDuration = 900; // 15 minutes

    /**
     * Validate and sanitize user input
     */
    public function sanitizeInput(array $data, array $rules = []): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Basic XSS protection
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                
                // Remove potentially dangerous characters
                $value = preg_replace('/[<>"\']/', '', $value);
                
                // Trim whitespace
                $value = trim($value);
                
                // Apply specific rules if provided
                if (isset($rules[$key])) {
                    $value = $this->applyRule($value, $rules[$key]);
                }
            }
            
            $sanitized[$key] = $value;
        }

        return $sanitized;
    }

    /**
     * Apply specific sanitization rule
     */
    protected function applyRule(string $value, string $rule): string
    {
        switch ($rule) {
            case 'email':
                return filter_var($value, FILTER_SANITIZE_EMAIL);
            
            case 'phone':
                return preg_replace('/[^0-9+\-\s]/', '', $value);
            
            case 'alphanumeric':
                return preg_replace('/[^a-zA-Z0-9]/', '', $value);
            
            case 'numeric':
                return preg_replace('/[^0-9.]/', '', $value);
            
            case 'url':
                return filter_var($value, FILTER_SANITIZE_URL);
            
            default:
                return $value;
        }
    }

    /**
     * Check for suspicious activity patterns
     */
    public function detectSuspiciousActivity(Request $request): array
    {
        $suspiciousPatterns = [];
        $userAgent = $request->userAgent();
        $ip = $request->ip();
        $url = $request->fullUrl();

        // Check for SQL injection patterns
        $sqlPatterns = [
            '/union\s+select/i',
            '/drop\s+table/i',
            '/insert\s+into/i',
            '/delete\s+from/i',
            '/update\s+set/i',
            '/or\s+1\s*=\s*1/i',
            '/and\s+1\s*=\s*1/i',
            '/\'\s*or\s*\'/i'
        ];

        foreach ($sqlPatterns as $pattern) {
            if (preg_match($pattern, $url) || preg_match($pattern, json_encode($request->all()))) {
                $suspiciousPatterns[] = [
                    'type' => 'sql_injection',
                    'pattern' => $pattern,
                    'severity' => 'high'
                ];
            }
        }

        // Check for XSS patterns
        $xssPatterns = [
            '/<script/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe/i',
            '/<object/i',
            '/<embed/i'
        ];

        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, json_encode($request->all()))) {
                $suspiciousPatterns[] = [
                    'type' => 'xss_attempt',
                    'pattern' => $pattern,
                    'severity' => 'high'
                ];
            }
        }

        // Check for suspicious user agents
        $suspiciousAgents = [
            '/bot/i',
            '/crawler/i',
            '/spider/i',
            '/scanner/i',
            '/sqlmap/i',
            '/nikto/i'
        ];

        foreach ($suspiciousAgents as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                $suspiciousPatterns[] = [
                    'type' => 'suspicious_user_agent',
                    'pattern' => $pattern,
                    'severity' => 'medium'
                ];
            }
        }

        // Check request frequency
        $requestCount = $this->getRequestCount($ip);
        if ($requestCount > 100) { // More than 100 requests per minute
            $suspiciousPatterns[] = [
                'type' => 'high_request_frequency',
                'count' => $requestCount,
                'severity' => 'medium'
            ];
        }

        return $suspiciousPatterns;
    }

    /**
     * Rate limiting for login attempts
     */
    public function checkLoginAttempts(string $identifier): bool
    {
        $key = $this->cachePrefix . 'login_attempts:' . $identifier;
        $attempts = Cache::get($key, 0);

        return $attempts < $this->maxLoginAttempts;
    }

    /**
     * Record failed login attempt
     */
    public function recordFailedLogin(string $identifier, Request $request): void
    {
        $key = $this->cachePrefix . 'login_attempts:' . $identifier;
        $attempts = Cache::get($key, 0) + 1;
        
        Cache::put($key, $attempts, now()->addSeconds($this->lockoutDuration));

        // Log security event
        Log::warning('Failed login attempt', [
            'identifier' => $identifier,
            'attempts' => $attempts,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString()
        ]);

        // If max attempts reached, log security alert
        if ($attempts >= $this->maxLoginAttempts) {
            Log::alert('Account locked due to multiple failed login attempts', [
                'identifier' => $identifier,
                'ip' => $request->ip(),
                'lockout_duration' => $this->lockoutDuration
            ]);
        }
    }

    /**
     * Clear login attempts on successful login
     */
    public function clearLoginAttempts(string $identifier): void
    {
        $key = $this->cachePrefix . 'login_attempts:' . $identifier;
        Cache::forget($key);
    }

    /**
     * Validate password strength
     */
    public function validatePasswordStrength(string $password): array
    {
        $score = 0;
        $feedback = [];

        // Length check
        if (strlen($password) >= 8) {
            $score += 2;
        } else {
            $feedback[] = 'Password should be at least 8 characters long';
        }

        // Uppercase check
        if (preg_match('/[A-Z]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Password should contain at least one uppercase letter';
        }

        // Lowercase check
        if (preg_match('/[a-z]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Password should contain at least one lowercase letter';
        }

        // Number check
        if (preg_match('/[0-9]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Password should contain at least one number';
        }

        // Special character check
        if (preg_match('/[^a-zA-Z0-9]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Password should contain at least one special character';
        }

        // Common password check
        if ($this->isCommonPassword($password)) {
            $score -= 2;
            $feedback[] = 'Password is too common, please choose a more unique password';
        }

        $strength = 'weak';
        if ($score >= 5) {
            $strength = 'strong';
        } elseif ($score >= 3) {
            $strength = 'medium';
        }

        return [
            'score' => max(0, $score),
            'strength' => $strength,
            'feedback' => $feedback,
            'is_valid' => $score >= 3
        ];
    }

    /**
     * Check if password is commonly used
     */
    protected function isCommonPassword(string $password): bool
    {
        $commonPasswords = [
            'password', '123456', '123456789', 'qwerty', 'abc123',
            'password123', 'admin', 'letmein', 'welcome', 'monkey',
            'dragon', 'master', 'shadow', 'football', 'baseball'
        ];

        return in_array(strtolower($password), $commonPasswords);
    }

    /**
     * Generate secure token
     */
    public function generateSecureToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Validate file upload security
     */
    public function validateFileUpload($file): array
    {
        $issues = [];

        // Check file size
        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($file->getSize() > $maxSize) {
            $issues[] = 'File size exceeds maximum allowed size';
        }

        // Check file extension
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (!in_array($extension, $allowedExtensions)) {
            $issues[] = 'File type not allowed';
        }

        // Check MIME type
        $allowedMimes = [
            'image/jpeg', 'image/png', 'image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];

        if (!in_array($file->getMimeType(), $allowedMimes)) {
            $issues[] = 'Invalid file MIME type';
        }

        // Check for executable files
        $dangerousExtensions = ['php', 'exe', 'bat', 'cmd', 'com', 'scr', 'vbs', 'js'];
        if (in_array($extension, $dangerousExtensions)) {
            $issues[] = 'Executable files are not allowed';
        }

        return [
            'is_safe' => empty($issues),
            'issues' => $issues
        ];
    }

    /**
     * Get request count for IP
     */
    protected function getRequestCount(string $ip): int
    {
        $key = $this->cachePrefix . 'requests:' . $ip;
        $count = Cache::get($key, 0);
        
        Cache::put($key, $count + 1, now()->addMinute());
        
        return $count;
    }

    /**
     * Log security event
     */
    public function logSecurityEvent(string $event, array $data = []): void
    {
        Log::channel('security')->info($event, array_merge($data, [
            'timestamp' => now()->toISOString(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'user_id' => auth()->id()
        ]));
    }

    /**
     * Check for CSRF token validity
     */
    public function validateCsrfToken(Request $request): bool
    {
        $token = $request->header('X-CSRF-TOKEN') ?: $request->input('_token');
        $sessionToken = $request->session()->token();

        return hash_equals($sessionToken, $token);
    }

    /**
     * Generate security headers
     */
    public function getSecurityHeaders(): array
    {
        return [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'X-XSS-Protection' => '1; mode=block',
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
            'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';",
            'Referrer-Policy' => 'strict-origin-when-cross-origin'
        ];
    }

    /**
     * Encrypt sensitive data
     */
    public function encryptSensitiveData(string $data): string
    {
        return encrypt($data);
    }

    /**
     * Decrypt sensitive data
     */
    public function decryptSensitiveData(string $encryptedData): string
    {
        try {
            return decrypt($encryptedData);
        } catch (\Exception $e) {
            Log::error('Failed to decrypt data: ' . $e->getMessage());
            return '';
        }
    }
}
