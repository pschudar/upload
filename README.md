# upload
I used similar code in several projects. This exact code runs on my local backup server @ home 
to allow me to back up my phone and tablet data while watching .. or better yet, ignoring the television.

This code uses the Bootstrap 4 framework because it looks good right out of the box, is easy to work with, and
lets a person focus on functionality, and not so much the look and feel of things.
https://getbootstrap.com/docs/4.1/getting-started/introduction/

This code makes use of the bootstrap filestyle 2 library. This allows one to fully customize the browse files button.
https://markusslima.github.io/bootstrap-filestyle/

While bootstrap 4 makes things look pretty darned good in my opinion on any platform, I prefer my old fashioned desktop.
This code is optimized for a large viewport. It works well on small devices too, but it's not as aesthetically pleasing on
smaller screens. Someone with knowledge of PHP and jQuery could easily modify it for their own purposes. 

NOTE: While careful thought and planning went into the initial design of the upload script on the PHP side of things as well
as the jQuery side, this code has all security turned off, essentially. What I mean by this is with this default set up, ANY
and ALL file types are accepted. .js files, .php files, image files, video files, .exe files, you name it.
One could easily up the security by simply reading the comments in the code, though.

I have the security so low because I run this on my localhost backup server, which as 0 access to the internet. If your plan 
is to use this code in a script, definitely up the security level to your liking (I.E accept only the file types you need).

upload.js (assets\js\upload.js) is only lightly commented. It's written as an extension for jQuery. It makes use of the
FileReader object to generate a 'preview' of the file (such as a movie or image file). The larger the file, the longer it will
take for the preview to load.

The script as a whole allows the person visiting the page backup.php to upload any file type (unless you lock it down further, which
can be done very easily). If you see a file in the FileList that you didn't intend to upload, you do have the option to remove that
particular file with the click of a button.
