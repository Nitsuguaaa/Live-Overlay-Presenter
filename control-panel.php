<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');

    $inputJson = file_get_contents('php://input');
    $input = json_decode($inputJson, true);

    $dir = isset($input['directory']) ? $input['directory'] : '';

    $basePath = realpath('lower-thirds');  // base directory absolute path
    $requestedPath = realpath($basePath . DIRECTORY_SEPARATOR . $dir);

    if (
        $requestedPath !== false
        && str_starts_with($requestedPath, $basePath)
        && is_dir($requestedPath)
    ) {

        $files = scandir($requestedPath);

        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $webPath = 'lower-thirds/' . ($dir ? $dir . '/' : '');
        ob_start();

        // Add subdirectory buttons
        foreach ($files as $child) {
            if ($child == '.' || $child == '..') continue;
            if (is_dir($requestedPath . DIRECTORY_SEPARATOR . $child)) {
                echo "
        <button type='button' class='sub-directory-button' onclick='sendData(" . json_encode($child) . ")'>
        <p style='margin: 0px; text-align:left;padding-bottom:2px;color:white'>" . htmlspecialchars($child) . "</p>
        </button>
        ";
            }
        }

        // Then image files
        $imageFiles = array_filter($files, function ($file) use ($imageExtensions, $requestedPath) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            return in_array($ext, $imageExtensions) && $file !== '.' && $file !== '..' && is_file($requestedPath . DIRECTORY_SEPARATOR . $file);
        });

        display_images($webPath, $imageFiles);

        $html = ob_get_clean();

        echo json_encode([
            'status' => 'success',
            'html' => $html,
            'received' => $input
        ]);
    }

    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Lower Thirds Control Panel</title>
</head>

<body>
    <div id="controls-container">
        <button onclick="refresh_slides()" id="refresh-slide" class="controls">Refresh Slides</button>
        <div id="animation-container">
            <input type="radio" name="animation-radio-group" id="animation-fade"><label for="animation-fade">Fade</label>
            <input type="radio" name="animation-radio-group" id="animation-pop"><label for="animation-pop">Pop</label>
            <input type="radio" name="animation-radio-group" id="animation-slideLeft"><label for="animation-slideLeft">Slide Left</label>
            <input type="radio" name="animation-radio-group" id="animation-slideRight"><label for="animation-slideRight">Slide Right</label>
        </div>
        <div id="slide-controller-container">
            <div id="scc-left" class="scc-panel">
                <button onclick="prev_slide()" class="controls change-slide"><span id="change-slide-txt">
                        < </span></button>
                <button onclick="next_slide()" class="controls change-slide"><span id="change-slide-txt"> > </span></button>
            </div>
            <div id="scc-right" class="scc-panel">
                <button onclick="show_slide()" class="controls view-slide" id="show-slide-button">Show Slide</button>
                <button onclick="hide_slide()" class="controls view-slide">Hide Slide</button>
            </div>


        </div>

    </div>

    <div id="slides-container">
        <?php
        $imageDirectory = 'lower-thirds/';
        $files = scandir($imageDirectory);
        foreach ($files as $child) {
            if ($child == '.' || $child == '..') {
                continue;
            }

            if (is_dir($imageDirectory . $child)) {
                echo "
                <button type='button' class='sub-directory-button' onclick='sendData(" . json_encode($child) . ")'>
                <p style='margin: 0px; text-align:left;padding-bottom:2px;color:white'>" . htmlspecialchars($child) . "</p>
                </button>
                ";
            }
        }

        $imageFiles = array_filter($files, function ($file) use ($imageDirectory) {
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $fileExtension = pathinfo($file, PATHINFO_EXTENSION);
            return in_array(strtolower($fileExtension), $imageExtensions) && $file !== '.' && $file !== '..';
        });

        // Filter only image files (jpg, jpeg, png, gif)
        function display_images($imageDirectory, $imageFiles)
        {
            $imageFiles = array_values($imageFiles);

            foreach ($imageFiles as $image) {
                // Build the relative web path
                $webPath = $imageDirectory . rawurlencode($image);

                echo '<button type="button" class="lower-third-button" data-image="' . htmlspecialchars($webPath) . '">
                <p style="margin: 0px; text-align:left;padding-bottom:5px;color:white">' . htmlspecialchars($image) . '</p>
                <img src="' . htmlspecialchars($webPath) . '" alt="' . htmlspecialchars($image) . '" id="lower-third-image" />
              </button>';
            }
        }
        display_images($imageDirectory, $imageFiles)
        ?>
    </div>
    <div id="response" style="white-space: pre-wrap; color: white;"></div>

    <script>
        let currentDir = '';
        var imageFiles = <?php echo json_encode($imageFiles); ?>; // Get PHP image list as a JavaScript array
        var currentSlide;
        var selectedAnimation = 'fade'; // Default animation

        document.querySelectorAll('input[name="animation-radio-group"]').forEach(radio => {
            radio.addEventListener('change', function() {
                selectedAnimation = this.id.split('-')[1]; // Get animation type from the radio button's ID
            });
        });

        document.querySelectorAll('.lower-third-button').forEach(button => { //this detects if a image button is clicked
            button.addEventListener('click', function() {
                // Remove the "current-slide" class from all buttons
                document.querySelectorAll('.lower-third-button').forEach(b => {
                    b.classList.remove('current-slide');
                });

                // Add the "current-slide" class to the clicked button
                button.classList.add('current-slide');

                var image = button.getAttribute('data-image');
                currentSlide = image; // Track the current slide
                send_to_scene('lower-third', image + "|" + selectedAnimation);
            });
        });

        //might need later, changing AJAX request
        /*document.querySelectorAll('.sub-directory-button').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('dir-input').value = button.getAttribute("data-dir");
                document.getElementById('dir-form').submit();
                
                document.getElementById('slides-container').innerHTML = "";
            });
        });*/

        function sendData(dir) {
            if (dir === '..') {
                // Go up one directory level
                if (currentDir) {
                    // Remove trailing slash if exists
                    currentDir = currentDir.replace(/\/$/, '');
                    // Remove last folder from path
                    currentDir = currentDir.substring(0, currentDir.lastIndexOf('/'));
                }
            } else {
                // Append new directory
                currentDir = currentDir ? currentDir + '/' + dir : dir;
            }

            fetch("control-panel.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        directory: currentDir
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Add up button if not root
                        let upButtonHTML = '';
                        if (currentDir) {
                            upButtonHTML = `<button type="button" onclick="sendData('..')" class="sub-directory-button">
                            <p style="margin:0; color:white;">⬆ Up</p>
                          </button>`;
                        }

                        document.getElementById('slides-container').innerHTML = upButtonHTML + data.html;

                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = data.html;
                        imageFiles = Array.from(tempDiv.querySelectorAll('.lower-third-button')).map(b => b.getAttribute('data-image'));

                        setupLowerThirdButtons();
                    } else {
                        console.error('Error:', data.message);
                    }
                })
                .catch(err => console.error('Fetch error:', err));
        }

        function setupLowerThirdButtons() {
            document.querySelectorAll('.lower-third-button').forEach(button => {
                button.removeEventListener('click', handleLowerThirdClick); // remove previous to avoid duplicates
                button.addEventListener('click', handleLowerThirdClick);
            });
        }

        function handleLowerThirdClick(event) {
            // Remove the "current-slide" class from all buttons
            document.querySelectorAll('.lower-third-button').forEach(b => b.classList.remove('current-slide'));

            // Add the "current-slide" class to the clicked button
            this.classList.add('current-slide');

            var image = this.getAttribute('data-image');
            currentSlide = image; // Track the current slide
            send_to_scene('lower-third', image + "|" + selectedAnimation);
        }

        // Show the current slide
        function show_slide() {
            if (currentSlide == null) {
                console.log('There is no current slide.');
                return;
            }
            send_to_scene('current-slide', currentSlide + "|" + selectedAnimation); // Send message to index.php
        }

        // Hide current slide
        function hide_slide() {
            if (currentSlide != null) {
                send_to_scene('hide-slide' + "|" + currentSlide + "|" + selectedAnimation + "-hide");
            } else {
                console.log('Slide is already hidden.');
            }
        }

        // Previous slide function
        function prev_slide() {
            if (currentSlide == null) {
                console.log('No current slide selected.');
                return;
            }
            var currentIndex = imageFiles.indexOf(currentSlide);
            if (currentIndex > 0) {
                currentSlide = imageFiles[currentIndex - 1]; // Set previous slide as current
                updateActiveSlide();
                send_to_scene('current-slide', currentSlide + "|" + selectedAnimation); // Send message to index.php
            } else {
                console.log('You are already at the first slide.');
            }
        }

        // Next slide function
        function next_slide() {
            if (currentSlide == null) {
                console.log('No current slide selected.');
                return;
            }
            var currentIndex = imageFiles.indexOf(currentSlide);
            if (currentIndex < imageFiles.length - 1) {
                currentSlide = imageFiles[currentIndex + 1]; // Set next slide as current
                updateActiveSlide();
                send_to_scene('current-slide', currentSlide + "|" + selectedAnimation); // Send message to index.php
            } else {
                console.log('You are already at the last slide.');
            }
        }

        function refresh_slides() {
            location.reload();
        }

        function updateActiveSlide() {
            // Remove the "current-slide" class from all buttons
            document.querySelectorAll('.lower-third-button').forEach(button => {
                button.classList.remove('current-slide');
            });

            // Find the button corresponding to the current slide
            var buttonToActivate = document.querySelector(`.lower-third-button[data-image="${currentSlide}"]`);

            // Add the "current-slide" class to the new active button
            if (buttonToActivate) {
                buttonToActivate.classList.add('current-slide');
            }
        }

        // Sends the data back to scene
        function send_to_scene(type, data) {
            var bc = new BroadcastChannel('obs-lower-thirds-channel');
            bc.postMessage(type + "|" + data);
        }

        setupLowerThirdButtons();
    </script>
</body>

</html>