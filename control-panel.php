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
                <button onclick="prev_slide()" class="controls change-slide"><span id="change-slide-txt"> < </span></button>
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
        $imageDirectory = './lower-thirds/';
        $files = scandir($imageDirectory);

        // Filter only image files (jpg, jpeg, png, gif)
        $imageFiles = array_filter($files, function ($file) use ($imageDirectory) {
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $fileExtension = pathinfo($file, PATHINFO_EXTENSION);
            return in_array(strtolower($fileExtension), $imageExtensions) && $file !== '.' && $file !== '..';
        });
        $imageFiles = array_values($imageFiles);

        foreach ($imageFiles as $image) {
            echo '<button class="lower-third-button" data-image="' . $image . '">
                    <img src="' . $imageDirectory . $image . '" alt="' . $image . '" id="lower-third-image" />
                  </button>';
        }
        ?>
    </div>

    <script>
        var imageFiles = <?php echo json_encode($imageFiles); ?>; // Get PHP image list as a JavaScript array
        var currentSlide;
        var selectedAnimation = 'fade'; // Default animation

        document.querySelectorAll('input[name="animation-radio-group"]').forEach(radio => {
            radio.addEventListener('change', function() {
                selectedAnimation = this.id.split('-')[1]; // Get animation type from the radio button's ID
            });
        });

        document.querySelectorAll('.lower-third-button').forEach(button => {
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
    </script>
</body>

</html>