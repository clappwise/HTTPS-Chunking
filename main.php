<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['chunk'])) {
        $chunk = $_POST['chunk']; // Received chunk
    } else {
        $chunk = "";
    }
    
    if (isset($_POST['chunkNumber'])) {
        $chunkNumber = $_POST['chunkNumber']; // Chunk number
    } else {
        $chunkNumber = "";
    }
    
    if (isset($_POST['totalChunks'])) {
        $totalChunks = $_POST['totalChunks']; // Total number of chunks
    } else {
        $totalChunks = "";
    }
    
    if (isset($_POST['uniqueKey'])) {
        $uniqueKey = $_POST['uniqueKey']; // Identifier for the file
    } else {
        $uniqueKey = "";
    }
    
    // Retrieve the file type (MIME type) sent from the client
    if (isset($_POST['fileType'])) {
        $fileType = $_POST['fileType'];
    } else {
        $fileType = "application/octet-stream"; // Default to a generic binary type
    }
    
    $tempUploadPath = 'temp_upload_directory/' . $uniqueKey; // Temporary folder
    $finalUploadPath = 'final_upload_directory/' . $uniqueKey; // Permanent folder
    
    // Create the permanent folder if it doesn't exist
    if (!file_exists($finalUploadPath)) {
        mkdir($finalUploadPath, 0777, true);
    }
    
    if ($chunkNumber == $totalChunks) {
    // All chunks received, assemble the file
     // Determine the correct MIME type based on the file extension
    $mimeTypes = [
        'txt' => 'text/plain',
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
        'mp4' => 'video/mp4', // MIME type for video files
        'zip' => 'application/zip', // MIME type for zip files
        // Add more mappings as needed
    ];
    
    $fileExtension = $filetype;  // Replace with the actual file extension
    if (isset($mimeTypes[$fileExtension])) {
        $mimeType = $mimeTypes[$fileExtension];
        header('Content-Type: ' . $mimeType);
    }
    
    $outputFile = fopen("$finalUploadPath/finalfile.ext", 'w');

    for ($i = 1; $i <= $totalChunks; $i++) {
        $chunkData = file_get_contents("$tempUploadPath/$i");
        fwrite($outputFile, $chunkData);
    }

    fclose($outputFile);

   
    
    // Send the assembled file to the client
    readfile("$finalUploadPath/finalfile.ext");
    
    // Clean up temporary chunks
    for ($i = 1; $i <= $totalChunks; $i++) {
        unlink("$tempUploadPath/$i");
    }


        
        // File is now fully assembled and saved in $finalUploadPath/finalfile.ext
        echo json_encode(['message' => 'Upload Complete']);
    } else {
        file_put_contents("$tempUploadPath/$chunkNumber", $chunk);
        echo json_encode(['message' => 'Chunk Received']);
    }
}
?>
