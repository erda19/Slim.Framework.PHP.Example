# Slim.Framework.PHP.Example

Example Slim Framework :
Middleware, Route

Setting Nginx Web Server:

server {
    listen 8881;
    server_name slimAPI.com;
    index index.php;
    #error_log /path/to/slim.api.error.log;
    #access_log /path/to/slim.api.access.log;
    access_log  "D:/PHPProject/Api.Slim.Framework/access.log";
    error_log  "D:/PHPProject/Api.Slim.Framework/error.log";
    root "D:/PHPProject/Api.Slim.Framework/src/public";
    location / {
        try_files $uri /index.php$is_args$args;
    }
    location ~ \.php {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param SCRIPT_NAME $fastcgi_script_name;
        fastcgi_index index.php;
		fastcgi_read_timeout 1020;
        fastcgi_pass 127.0.0.1:9000;
	}
}
