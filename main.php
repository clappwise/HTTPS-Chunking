<?php


$chunk = $_POST['chunk']; // Received chunk
$chunkNumber = $_POST['chunkNumber']; // Chunk number
$totalChunks = $_POST['totalChunks']; // Total number of chunks
$uniqueKey = $_POST['uniqueKey']; // Identifier for the file

$uploadPath = 'your_upload_directory/' . $uniqueKey;

// Save the chunk
file_put_contents("$uploadPath/$chunkNumber", $chunk);

// Check if all chunks have been received
if ($chunkNumber == $totalChunks) {
    // All chunks received, assemble the file
    $outputFile = 'final_upload_directory/' . $uniqueKey;

    for ($i = 1; $i <= $totalChunks; $i++) {
        $chunkData = file_get_contents("$uploadPath/$i", FILE_BINARY);
        file_put_contents($outputFile, $chunkData, FILE_APPEND);
    }

    // Clean up temporary chunks
    for ($i = 1; $i <= $totalChunks; $i++) {
        unlink("$uploadPath/$i");
    }

    // File is now fully assembled and saved in $outputFile
        }

? >

<!DOCTYPE html>
<html>
<head>
    <title>Chunked File Upload</title>
</head>
<body>
    <input type="file" id="fileInput">
    <button id="uploadButton">Upload</button>
    <progress id="progressBar" max="100" value="0"></progress>
    <div id="status"></div>

    <script>
        const fileInput = document.getElementById('fileInput');
        const uploadButton = document.getElementById('uploadButton');
        const progressBar = document.getElementById('progressBar');
        const status = document.getElementById('status');

        uploadButton.addEventListener('click', () => {
            const file = fileInput.files[0];
            if (file) {
                const chunkSize = 1024 * 1024; // 1MB chunks (adjust as needed)
                const totalChunks = Math.ceil(file.size / chunkSize);
                let currentChunk = 0;

                function uploadChunk() {
                    const start = currentChunk * chunkSize;
                    const end = Math.min(start + chunkSize, file.size);
                    const chunk = file.slice(start, end);

                    const formData = new FormData();
                    formData.append('chunk', chunk);
                    formData.append('chunkNumber', currentChunk + 1);
                    formData.append('totalChunks', totalChunks);
                    formData.append('uniqueKey', 'your_unique_key'); // Replace with a unique identifier

                    fetch('upload.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        currentChunk++;

                        const progress = (currentChunk / totalChunks) * 100;
                        progressBar.value = progress;
                        status.textContent = `Uploading... ${progress.toFixed(2)}%`;

                        if (currentChunk < totalChunks) {
                            uploadChunk();
                        } else {
                            progressBar.value = 100;
                            status.textContent = 'Upload Complete';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        status.textContent = 'Upload Failed';
                    });
                }

                uploadChunk();
            }
        });
    </script>
</body>
</html>
