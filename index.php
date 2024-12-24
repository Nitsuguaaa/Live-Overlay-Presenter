<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Lower Thirds Browser Source</title>
</head>

<body>
    <div id="main-content">
        <img src="" id="lower-thirds-placeholder">
    </div>

    <script>
        var bc = new BroadcastChannel('obs-lower-thirds-channel');
        var isHidden = false;

        bc.onmessage = function(event) {
            var message = event.data.split('|');

            switch (message[0]) {
                case "lower-third":
                    var image = message[1];
                    isHidden = false;
                    applyAnimation(image, message[2]);
                    break;
                case "current-slide":
                    var image = message[1];
                    isHidden = false;
                    applyAnimation(image, message[2]);
                    break;
                case "hide-slide":
                    var image = message[1];
                    applyAnimation(image, message[2]);
                    break;
            }
        };

        function applyAnimation(image, selectedAnimation) {
            const imageElement = document.getElementById('lower-thirds-placeholder');
            if (imageElement) {
                
                
                if(isHidden === true) { return; }

                imageElement.src = './lower-thirds/' + image; //this is making sure it is the src
                imageElement.style.display = 'block'; // Ensure the image is visible
                // Run the animation
                imageElement.classList.add('animating', selectedAnimation);

                // Listens if the animation is finshed
                imageElement.addEventListener('animationend', function handleAnimationEnd() {
                    imageElement.classList.remove('animating', selectedAnimation);

                    if (selectedAnimation.split('-')[1] === "hide") {
                        // After the hide animation ends, clear the src and hide the image
                        isHidden = true;
                        imageElement.src = ''; // Clear src correctly to avoid 404 errors
                    }

                    // Remove this listener to avoid duplicates
                    imageElement.removeEventListener('animationend', handleAnimationEnd);
                });
            }
        }
    </script>
</body>

</html>