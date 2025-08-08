<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create firebase directory in storage/app
        $firebaseDir = storage_path('app/firebase');
        
        if (!File::exists($firebaseDir)) {
            File::makeDirectory($firebaseDir, 0755, true);
            
            // Create .gitignore to exclude service account files
            $gitignoreContent = "# Firebase Service Account files\n*.json\n!.gitkeep\n";
            File::put($firebaseDir . '/.gitignore', $gitignoreContent);
            
            // Create .gitkeep to ensure directory is tracked
            File::put($firebaseDir . '/.gitkeep', '');
            
            echo "Created firebase directory: {$firebaseDir}\n";
        }
        
        // Create example service account file
        $examplePath = $firebaseDir . '/service-account.example.json';
        if (!File::exists($examplePath)) {
            $exampleContent = [
                "type" => "service_account",
                "project_id" => "saas-techcura",
                "private_key_id" => "your_private_key_id_here",
                "private_key" => "-----BEGIN PRIVATE KEY-----\nyour_private_key_here\n-----END PRIVATE KEY-----\n",
                "client_email" => "firebase-adminsdk-xxxxx@saas-techcura.iam.gserviceaccount.com",
                "client_id" => "your_client_id_here",
                "auth_uri" => "https://accounts.google.com/o/oauth2/auth",
                "token_uri" => "https://oauth2.googleapis.com/token",
                "auth_provider_x509_cert_url" => "https://www.googleapis.com/oauth2/v1/certs",
                "client_x509_cert_url" => "https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-xxxxx%40saas-techcura.iam.gserviceaccount.com",
                "universe_domain" => "googleapis.com"
            ];
            
            File::put($examplePath, json_encode($exampleContent, JSON_PRETTY_PRINT));
            echo "Created example service account file: {$examplePath}\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't remove the directory in down migration to prevent data loss
        // Users should manually remove if needed
    }
};
