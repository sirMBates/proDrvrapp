<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;

class ProfileImageService {
    private DriverProfileService $profileService;
    private UserRepository $userRepository;

    public function __construct() {
        $this->profileService = new DriverProfileService();
        $this->userRepository = new UserRepository();
    }

    public function uploadImage(int $driverId, array $file): array {
        $driverInformation = $this->profileService->driverProfile((int) $driverId);
        $operatorBasicInfo = [
            $driverInformation['firstName'],
            $driverInformation['lastName'],
            $driverInformation['operatorid']
        ];
        $firstInitial = $operatorBasicInfo[0][0];

        $uploadBase = base_path('storage/uploads/profile-pictures/');
        // Create a directory for the user if it doesn't exist
        $uploadDir = $uploadBase . $firstInitial . $operatorBasicInfo[1] . '-' . $operatorBasicInfo[2] . '/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Create a unique filename using the driver's ID and current timestamp
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = strtolower($operatorBasicInfo[2] . '_' . time() . '.' . $extension);
        $filePath = $uploadDir . $filename;

        //move_uploaded_file($file['tmp_name'], $filePath);

        // Store **application-relative storage Key** in database for frontend
        $storedPath = 'profile-pictures/' . $firstInitial . $operatorBasicInfo[1] . '-' . $operatorBasicInfo[2] . '/' . $filename;

        // Move the uploaded file to the server directory
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            return ([
                'status' => 'error',
                'message' => 'Error uploading the image.'
            ]);
            exit();
        }

        // Update the database with the path to the profile picture
        $updated = $this->userRepository->updateProfilePicture($driverId, $storedPath);

        if (!$updated) {
            return [
                'status' => 'error',
                'message' => 'Upload failed!'
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Photo successfully uploaded!'
        ];
    }
}
?>