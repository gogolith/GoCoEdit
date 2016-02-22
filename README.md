GoCoEditServer-Connector
==============

PHP version of the GoCoEdit Server-Connector. 
It connects the app with your own Http-Server used 
for the ftp part. 

**This is an optional Add-On for the iOS App, only for an edge case use to connect to a FTP-Server over port 80. The GoCoEdit App can connect by default to serval Servers (FTP, SFTP, DROPBOX, GOOGLE DRIVE) without this connector**

All Server connections are stored in your App 
(temporary on your own server only if you use this connector).

GoCoEdit - Code and Text Editor for iOS
-------------

The App is available at the Apple AppStore:  
https://itunes.apple.com/app/gocoedit-remote-code-editor/id869346854?l=de&ls=1&mt=8


Install instructions 
-------------

1. Upload the gocoeditserver.php to a folder on your Server or clone this repo  
`git clone https://github.com/gogolith/gocoeditserver.git`
2. Chmod the tmp folder and give gocoeditserver.php write rights to it  
`chmod 777 tmp`
3. Access gocoeditserver.php in your Browser  
(example: https://yourserver.de/gocoeditserver/gocoeditserver.php)
4. Notice the "Connector ID/Code"  
(it will also stored in tmp/config_admin.php under connectorid)
5. Enter the "Connector ID/Code" in your GoCoEdit App
6. Create a new remote within the iOS App and select "REMOTE PHP TO FTP" and use it


Limitations 
-------------

Compared to the supported "native" connections (ftp,sftp,dropbox,google drive) in GoCoEdit.

- Connector works only with the default ftp port 21
- Some file actions are not enabled
- Works only with UTF-8 encoded files
- You need full access to your server to install the connector 
- Webserver with php interpreter required - like nginx or apache


Support
-------------

**Questions:**

Twitter: http://twitter.com/planetdine  
E-Mail: develop@gogolith.de  
Web: http://gocoedit.com  
Docs: http://gocoedit.com/docs (still in progress)  

**Report bugs and issues:**

https://github.com/gogolith/gocoeditserver/issues



License
-------------
Copyright © 2016 Christoph Gogolin

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
