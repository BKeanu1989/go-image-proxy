# Go Image Proxy 4 All
With the help of cloudflare tunnnel it might be possible to host a service for almost free on the internet. The cost are usually roughly 60 - 100€. 

Serve a "native" version as well.




# sources for go education

    - anthony gg - yt -> daily 
    - learning go - book -> daily
    - 100 go mistakes - book  

## Todo
    - Server
    - Image Conversion & Co
    - make file
    - libvips?? & or be - docker will solve it
    - Docker image
    - React & Vue library
    - htmx components?
    - Cloudflare Tunnel with Raspberry Pi
    - Cache Memory Size auto clean
    - How to build url?
    - URL Signing

## Done

## Known Issues


---
    - Implement a proxy layer in Golang.
    - Modify the proxy code to intercept the actual response data and return the resized data.
    - Process or save the resized image in S3 parallely without blocking the response.


```tip
The imgproxy URL in this example is not signed but signing URLs is especially important when using encrypted source URLs to prevent a padding oracle attack.
```


---
TODO: 

- dynamic image formats -> client for wp, react, vue, htmx, vanilla js
- what is 
- ~~caching redislike?~~ - solved via go-cache - see in repo
- logger
- go routine?
- read user agent?
  
---
DONE:

- only width changes -> grow/shrink height accordingly | preserving aspect ratio

---


dont grow bigger than "root" image

---
# Components Todo:
- max width and height(?)
- fallback
