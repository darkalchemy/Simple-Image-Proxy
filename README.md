# Simple-Image-Proxy

The purpose of this image proxy is to reduce the exposure of the requesting sites url, when hot-linking images, by using the image proxy to make the http request, store the image and return the image from the proxy in an http response.

This could also be used to serve images that are using http links on your https site. Thereby not opening up a security hole.  

This image proxy will store an optimized for web version of the original image and can return that image in any dimensions that are included with the http request. 

# How To:
#### get the code

```
git clone https://github.com/darkalchemy/Simple-Image-Proxy.git image-proxy
```

#### set ownership

```
chown -R www-data:www-data image-proxy
```

#### install dependancies

```
cd image-proxy
composer install
```

#### set webroot to path image-proxy/public

#### edit settings.php

```
replace the uid and key with values given to the requsting site. These values must match.
```


### Usage by the requesting site

##### add class with composer
```
composer require blocktrail/cryptojs-aes-php
```

#### use class in site
```
use Blocktrail\CryptoJSAES\CryptoJSAES;
```

#### to return image without modification

```
$encrypted = CryptoJSAES::encrypt($url, $key);
return 'http://image_proxy_url/image?' . base64_encode($encrypted . '&uid=' . $uid);
```

#### to return image and resize the image
```
$encrypted = CryptoJSAES::encrypt("$url&width={$width}&height={$height}", $key);
return 'http://image_proxy_url/image?' . base64_encode($encrypted . '&uid=' . $uid);
```

The $uid and $key can be anything, so long as they are agreed upon by both the requesting site and the image proxy.
