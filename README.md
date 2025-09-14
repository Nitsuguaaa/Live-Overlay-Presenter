# Live Overlay Presenter Plugin for OBS Studio
**A Powerpoint-like presentation slides for your overlays!**

![alt text][logo]

[logo]: https://github.com/Nitsuguaaa/Lower-thirds-OBS-Plugin/blob/master/lower-thirds-example.png "Plugin Preview"
I made this OBS Plugin because OBS Studio does not full on support image presentations. Usually if you want an image presentation, you would need to add an image slideshow, which is usually fine but I want to see what the **images are, add custom animation, update real time, and navigate anywhere instead of just what's the next slide and the previous one.**
This plugin solves that problem by creating a local PHP server that reads all your images in `lower-thirds` and simulating a presentation. This plugin is used if you have a corresponding lower thirds (transparent background images) **in addition** to your 
actual presentation.
> [!CAUTION]
> This plugin is **NOT** efficient nor clean. Further improvements are being made so do use this at your own risk.
>
> If you want to look under the hood then goodluck soldier :worried:

## Features and Notes
- Do not change the `lower-thirds` directory name it will cause errors
- Images are relative to the size you give (If the browser size is 1920x1080 then all images must be 1920x1080 even though it's in a clear background)
- Supports [jpg, jpeg, png, gif, subdirectories(lower-thirds/subdirectory)]
- Supports 4 animations [fade, pop, slide left, slide right]

## Installation
1. Download as a ZIP folder and extract
2. Open OBS studio and on your scene add a browser source
3. Set size to your preference then on the URL add `http://localhost:8000/index.php` and check `Refresh browser when scene becomes active` then click `ok`
4. Open `Docks` -> `Custom Browser Docks...`, add a new dock with your preferred `name` and `URL` to `http://localhost:8000/control-panel.php` then click `Apply` then `Close`
5. Add all your images and folders inside `lower-thirds`
6. Run `start-php-server.bat`
7. Either Restart OBS Studio or click retry in the custom dock (if shown, if not click `Docks` -> `your-dock-name`) and change scene to reload the source
8. Enjoy!
## To-Do
The current version needs to be rewritten as a whole. What's being used right now is more of a proof of concept rather than production use so use the plugin at your own risk.
- [ ] Separatation of concerns (not just control-panel.php and index.php)
- [ ] Rewrite AJAX requests (it's a mess)
- [ ] Add more animation controls
- [ ] Add OBS key binding
