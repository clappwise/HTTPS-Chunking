<?php
// Define the directory where you want to save the video chunks
$uploadDirectory = 'videos/';

// Ensure the 'chunk' key is present in the POST data
if (!isset($_FILES['chunk']['name'])) {
    die('Invalid request');
}

$chunk = $_FILES['chunk'];
$chunkSize = (int) $_SERVER['HTTP_CONTENT_LENGTH'];

// Ensure the chunk is not empty
if ($chunkSize === 0) {
    die('Empty chunk');
}

// Get the index and total chunks from HTTP headers
$chunkIndex = (int) $_SERVER['HTTP_X_CHUNK_INDEX'];
$totalChunks = (int) $_SERVER['HTTP_X_CHUNK_TOTAL'];

// Create the target file name
$targetFileName = $uploadDirectory . basename($_SERVER['HTTP_X_FILE_NAME']);

// Append the chunk to the target file
if ($chunkIndex === 0) {
    // If this is the first chunk, create a new file
    $fileHandle = fopen($targetFileName, 'wb');
} else {
    // If not the first chunk, append to the existing file
    $fileHandle = fopen($targetFileName, 'ab');
}

if (!$fileHandle) {
    die('Unable to open the target file');
}

// Read and write the chunk
if ($chunk && $fileHandle) {
    while (!feof($chunk)) {
        fwrite($fileHandle, fread($chunk, 8192));
    }
}

fclose($fileHandle);

// Check if all chunks have been received
if ($chunkIndex === $totalChunks - 1) {
    echo 'Upload completed successfully';
} else {
    echo 'Chunk uploaded';
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Video Upload</title>
    <link rel="stylesheet" type="text/css" href="path/to/dropzone.min.css">
</head>
<body>
       <form action="" method="post" enctype="multipart/form-data">
     
    <div>
    <input type='file' name='file' class="form-control" /><br>
    </div>
    
    </form>

  <script>
        // Function to upload video in chunks
        function uploadVideoChunked() {
            const fileInput = document.querySelector('input[type="file"]');
            const file = fileInput.files[0];
            const chunkSize = 1024 * 1024; // 1MB chunks

            // Calculate the total number of chunks
            const totalChunks = Math.ceil(file.size / chunkSize);
            let currentChunk = 0;

            // Function to send a chunk of the file to the server
            function sendChunk() {
                const start = currentChunk * chunkSize;
                const end = Math.min(start + chunkSize, file.size);
                const chunk = file.slice(start, end);

                // Create a FormData object and append the chunk
                const formData = new FormData();
                formData.append('chunk', chunk);

                // Send the chunk to the server using AJAX
                $.ajax({
                    url: 'main.php', // Replace with your server endpoint
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        console.log(`Uploaded chunk ${currentChunk + 1} of ${totalChunks}`);
                        currentChunk++;

                        // Continue uploading the next chunk
                        if (currentChunk < totalChunks) {
                            sendChunk();
                        } else {
                            console.log('Upload complete');
                        }
                    }
                });
            }

            // Start uploading the first chunk
            sendChunk();
        }
    </script>

</body>
</html>



 
