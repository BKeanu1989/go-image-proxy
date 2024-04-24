# Go Image Proxy 4 All

# sources for go education

    - anthony gg - yt -> daily 
    - learning go - book -> daily
    - 100 go mistakes - book  

## Todo
    - Server
    - Image Conversion & Co
    - make file
    - libvips??
---
should work on own server

---

    - Implement a proxy layer in Golang.
    - Modify the proxy code to intercept the actual response data and return the resized data.
    - Process or save the resized image in S3 parallely without blocking the response.


base 64 / buffer

---
### snippets
```go
myUrl, _ := url.Parse(urlStr)
params, _ := url.ParseQuery(myUrl.RawQuery)
fmt.Println(params)
```


```tip
The imgproxy URL in this example is not signed but signing URLs is especially important when using encrypted source URLs to prevent a padding oracle attack.
```
