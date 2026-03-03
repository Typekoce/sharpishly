<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\VideoUploadModel;
use App\Services\VideoOptimizerService;

class SocialUploadController
{
    public function index(): void
    {
        // Simple upload form (see view below)
        echo file_get_contents(dirname(__DIR__, 2) . '/views/social-upload/index.html');
    }

    public function upload(): void
    {
        if (empty($_FILES['video']['tmp_name'])) {
            echo "No file uploaded";
            return;
        }

        $model = new VideoUploadModel();
        $optimizer = new VideoOptimizerService();

        $originalPath = 'php/uploads/' . time() . '_' . $_FILES['video']['name'];
        move_uploaded_file($_FILES['video']['tmp_name'], $originalPath);

        $uploadId = $model->createUpload($originalPath, $_FILES['video']['name']);

        $platforms = $_POST['platforms'] ?? []; // array of selected platforms

        foreach ($platforms as $platform) {
            $custom = [
                'title'       => $_POST["title_$platform"] ?? "My Video on " . ucfirst($platform),
                'description' => $_POST["desc_$platform"] ?? "",
                'hashtags'    => $_POST["hashtags_$platform"] ?? "#Sharpishly",
            ];

            $postId = $model->addPlatformPost($uploadId, $platform, $custom);

            // Queue for background processing
            file_put_contents('php/queue.txt', json_encode([
                'post_id' => $postId,
                'platform' => $platform,
                'original_path' => $originalPath,
            ]) . "\n", FILE_APPEND);
        }

        echo "<h2>Video queued for " . count($platforms) . " platforms!</h2>";
        echo "<a href='/php/social-upload/status'>View Status</a>";
    }
}