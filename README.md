# Go Image Proxy 4 All
With the help of cloudflare tunnnel it might be possible to host a service for almost free on the internet. The cost are usually roughly 60 - 100€. 

Serve a "native" version as well.

---
https://github.com/h2non/bimg

---


# sources for go education

    - anthony gg - yt -> daily 
    - learning go - book -> daily
    - 100 go mistakes - book  

## Todo
    - Webp
    - make file
    - libvips?? & or be - docker will solve it
    - Docker image
    - React & Vue library
    - htmx components?
    - Cloudflare Tunnel with Raspberry Pi
    - Cache Memory Size auto clean
    - URL Signing
    - link rel=preconnect
    - http2
    - check port -> use another if necessary
    - base64 encoded images / go process put into media library of wp?
    - preferred data usage 
        - This is clever because remember that we have the media attribute on the <source> element. So, we can instruct browsers to use certain images when working with <picture>, a la: <source srcset="small.jpg" media="(prefers-reduced-data: reduce)" />.
    - wenn html antwort (htmx) -> alt text übernehmen

    - read img src and replace/edit in load event? is that early enough? -> blocking script in header with window.addEventListener...
    - what about output buffering?
    - service worker - for proxying requests - in general, without changing db / global, 
        - possibility for removing service worker - if images are not working
        - can i use: https://caniuse.com/?search=service%20worker - good support

    - data-src in js

## Done
    - How to build url?
    - Server
    - Image Conversion & Co

## Known Issues
- Quality only available for jpeg

---
    - Implement a proxy layer in Golang.
    - Modify the proxy code to intercept the actual response data and return the resized data.
    - Process or save the resized image in S3 parallely without blocking the response.


```tip
The imgproxy URL in this example is not signed but signing URLs is especially important when using encrypted source URLs to prevent a padding oracle attack.
```

---
## Building
```
GOOS=linux GOARCH=amd64 go build -o bin/app-amd64-linux app.go # 64-bit
```
-> 
```
# linux
set GOOS=linux
set GOARCH=amd64

go build -o bin/app-amd64-linux

# windows
set GOOS=windows
set GOARCH=amd64

go build -o bin/app-amd64.exe 

```
# ./app-amd64-linux
```txt
/bin/sh: 8: ./app-amd64-linux: Exec format error
```

https://stackoverflow.com/questions/36279253/go-compiled-binary-wont-run-in-an-alpine-docker-container-on-ubuntu-host

-
https://www.digitalocean.com/community/tutorials/how-to-build-go-executables-for-multiple-platforms-on-ubuntu-20-04
linux/386
linux/amd64
linux/arm
linux/arm64
linux/mips
linux/mips64
linux/mips64le
linux/mipsle
linux/ppc64
linux/ppc64le
linux/riscv64
linux/s390x

uname -a
Linux a056130fca28 6.6.12-linuxkit #1 SMP PREEMPT_DYNAMIC Fri Jan 19 12:50:23 UTC 2024 x86_64 GNU/Linux

dpkg --print-architecture
amd64

file ./app-amd64-linux
./app-amd64-linux: PE32+ executable (console) x86-64, for **MS Windows**

use command line with admin and set goos again

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


---
## Go memory usage with maps
While using maps in Go, we need to understand some important characteristics of how a map grows and shrinks. Does really maps shrink?

Let’s see with a example, consider a scenario while implementing a map as follows

m := make(map[int][128]byte)
Each value of m is an array of 128 bytes. We will do the following:

Allocate an empty map.
Add 1 million elements.
Remove all the elements, and run a Garbage Collection (GC).
After each step, print the size of the heap (using a printMemAlloc utility function). This shows us how this example behaves memory-wise:

```go
package main

import (
 "fmt"
 "runtime"
)

func main() {
 n := 1000000
 m := make(map[int][128]byte)
 printMemAlloc()

 for i := 0; i < n; i++ { 
  m[i] = [128]byte{}
 }
 printMemAlloc()

 for i := 0; i < n; i++ { 
  delete(m, i)
 }

 runtime.GC()
 printMemAlloc()
 runtime.KeepAlive(m)
}

func printMemAlloc() {
 var m runtime.MemStats
 runtime.ReadMemStats(&m)
 fmt.Printf("%d KB\n", m.Alloc/1024)
}
```

We allocate an empty map, add 1 million elements, remove 1 million elements, and then run a GC. We also make sure to keep a reference to the map using runtime.KeepAlive so that the map isn’t collected as well. This will produce a output as below.

//Output
93 KB
464105 KB //After adding 1 million elements
300425 KB //After deleting 1 million elements
At first, the heap size is minimum. Then it grows significantly after having added 1 million elements to the map. But if we expected the heap size to decrease after removing all the elements, this isn’t how maps work in Go. In the end, even though the GC has collected all the elements, the heap size is still 300 MB. So the memory shrunk, but not as we might have expected.

A map provides an unordered collection of key-value pairs in which all the keys are distinct. In Go, a map is based on the hash table data structure: an array where each element is a pointer to a bucket of key-value pairs, as shown in the figure.


Each bucket is a fixed-size array of eight elements. In the case of an insertion into a bucket that is already full (a bucket overflow), Go creates another bucket of eight elements and links the previous one to it.


A Go map is a pointer to a runtime.hmap struct. This struct contains multiple fields, including a B field, giving the number of buckets in the map:
```go
type hmap struct {
    B uint8 // log_2 of # of buckets
            // (can hold up to loadFactor * 2^B items)
    // ...
}
```
After adding 1 million elements, the value of B equals 18, which means 2 pow 18 = 262,144 buckets. When we remove 1 million elements, what’s the value of B? Still 18. Hence, the map still contains the same number of buckets.

The reason is that the number of buckets in a map cannot shrink. Therefore, removing elements from a map doesn’t impact the number of existing buckets; it just zeroes the slots in the buckets. A map can only grow and have more buckets; it never shrinks.

What are the solutions if we don’t want to manually restart our service to clean the amount of memory consumed by the map? One solution could be to re-create a copy of the current map at a regular pace. For example, every hour, we can build a new map, copy all the elements, and release the previous one. The main drawback of this option is that following the copy and until the next garbage collection, we may consume twice the current memory for a short period.

Another solution would be to change the map type to store an array pointer: map[int]*[128]byte. It doesn’t solve the fact that we will have a significant number of buckets; however, each bucket entry will reserve the size of a pointer for the value instead of 128 bytes (8 bytes on 64-bit systems and 4 bytes on 32-bit systems).

If a key or a value is over 128 bytes, Go won’t store it directly in the map bucket. Instead, Go stores a pointer to reference the key or the value.

As we have seen, adding n elements to a map and then deleting all the elements means keeping the same number of buckets in memory. So, we must remember that because a Go map can only grow in size, so does its memory consumption. There is no automated strategy to shrink it. If this leads to high memory consumption, we can try different options such as forcing Go to re-create the map or using pointers to check if it can be optimized.

If you like this post, please do like and add your comments and follow my page for more go related blogs.

https://medium.com/@quicktechlearn/go-an-evolving-language-in-this-modern-era-b5d4e58dd838
https://medium.com/@quicktechlearn/how-slices-affect-the-performance-of-your-code-in-go-how-can-they-be-mitigated-9e6f3d90e2e6
https://medium.com/@quicktechlearn/how-to-sync-goroutines-in-go-how-to-use-wait-groups-7fef6741950



---
docker notes
https://www.youtube.com/watch?v=DM65_JyGxCo

docker network create NAME_OF_NETWORK --subnet 192.168.92.0/24


in docker-compose file
```yml
... # (webservices)
    website:
        ports:
            - "3000:8080"
        restart: always
        networks:
            NAME_OF_NETWORK:
                ipv4_address: 192.168.92.21
    ports
networks:
    NAME_OF_NETWORK:
        ipam:
            driver: default
            config:
                - subnet: "192.168.92.0/24"
```
---
# cli tool

## imagify
- converts image 
- sets
    - quality
    - width
    - height
        - aspect ratio
    


---
# installing docker on rp4 for zero trust cloudlfare

 curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg



 InRelease: The following signatures couldn't be verified because the public key is not available: https://download.docker.com/linux/raspbian bookworm 

```txt
W: GPG error: https://download.docker.com/linux/raspbian bookworm InRelease: The following signatures couldn't be verified because the public key is not available: NO_PUBKEY 7EA0A9C3F273FCD8
E: The repository 'https://download.docker.com/linux/raspbian bookworm InRelease' is not signed.
N: Updating from such a repository can't be done securely, and is therefore disabled by default.
N: See apt-secure(8) manpage for repository creation and user configuration details.
```

source:
https://stackoverflow.com/questions/60137344/docker-how-to-solve-the-public-key-error-in-ubuntu-while-installing-docker

sudo apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys 7EA0A9C3F273FCD8
sudo apt-key adv --keyserver keyserver.ubuntu.com --recv 7EA0A9C3F273FCD8

----
afer reinstalling ubuntu os on rp4 -> same error

docker run cloudflare/cloudflared:latest tunnel --no-autoupdate run --token YOURTOKENGOESHERE


--- 
connector is active

but getting error now:
```txt
2024-05-09T13:03:15Z ERR Request failed error="Unable to reach the origin service. The service may be down or it may not be responding to traffic from cloudflared: dial tcp [::1]:8080: connect: connection refused" connIndex=2 dest=http://image.kevin-fechner.site/ 
event=0 ip=198.41.192.77 type=http
```

https://community.cloudflare.com/t/tunnel-unable-to-reach-the-origin-service/450596

```
You need to replace localhost with host.docker.internal.
```
2024-05-09T13:09:21Z ERR Request failed error="Unable to reach the origin service. The service may be down or it may not be responding to traffic from cloudflared: dial tcp: lookup host.docker.internal on 192.168.0.1:53: no such host" connIndex=2 dest=http://image.kevin-fechner.site/ event=0 ip=198.41.192.77 type=http

-> apparently, the go service needs to be in a docker compose setup, with cloudflare tunnel image


https://keestalkstech.com/2023/01/expose-docker-compose-app-with-a-secure-cloudflare-tunnel/

```yaml
version: "3"
services:
  web:
    image: nginx:latest
    volumes:
      - ./index.html:/usr/share/nginx/html/index.html
    restart: always
    container_name: web

  tunnel:
    image: cloudflare/cloudflared:latest
    command: tunnel --no-autoupdate run
    env_file: tunnel.env
    restart: always
    container_name: tunnel
    depends_on:
      - web

```

----
# Docker

When packaging your application into a Dockerfile and configuring it to listen on a specific IP address, using `0.0.0.0` is generally a good practice. This setting makes your application listen on all available network interfaces, including the loopback interface (localhost). It's particularly useful in Docker environments because it allows your containerized application to accept connections from outside the container, such as from other containers within the same Docker network or from the host machine itself.

However, if you specifically want to bind your application to a certain IP address within the Docker network, you can do so by specifying the desired IP address in your Dockerfile or during runtime. Here's how you can handle both scenarios:

### Binding to All Interfaces (`0.0.0.0`)

If you're okay with your application listening on all interfaces, you typically don't need to specify an IP address in your Dockerfile. Instead, you configure your application to listen on `0.0.0.0`. This is common for web servers and applications that need to be accessible from outside the container.

Example for a Node.js Express app:

```Dockerfile
FROM node:14
WORKDIR /usr/src/app
COPY package*.json./
RUN npm install
COPY..
EXPOSE 8080
CMD ["node", "server.js"]
```

In `server.js`, you'd set up your server to listen on `0.0.0.0`:

```javascript
const express = require('express');
const app = express();
const port = 8080;

app.listen(port, '0.0.0.0', () => {
  console.log(`Server running on port ${port}`);
});
```

### Binding to a Specific IP Address

If you need to bind your application to a specific IP address within the Docker network, you can either specify this in your application's configuration or use Docker's networking features to assign a static IP to your container.

To assign a static IP to a container, you can use the `--ip` flag when connecting a container to a network:

```bash
docker network connect --ip 10.10.36.122 my_network my_container
```

Or, if you're defining your network in a `docker-compose.yml` file, you can specify the `ipv4_address` for your service:

```yaml
version: '3'
services:
  my_service:
    image: my_image
    networks:
      my_network:
        ipv4_address: 10.10.36.122
networks:
  my_network:
    driver: bridge
```

In your application, you would then configure it to listen on the specific IP address you've assigned to it within the Docker network.

Remember, the choice between binding to `0.0.0.0` and a specific IP address depends on your application's requirements and how you intend to access it within your Docker environment.

Citations:
[1] https://docs.docker.com/network/#:~:text=By%20default%2C%20the%20container%20gets,default%20subnet%20mask%20and%20gateway.
[2] https://docs.docker.com/network/network-tutorial-standalone/
[3] https://www.freecodecamp.org/news/how-to-get-a-docker-container-ip-address-explained-with-examples/
[4] https://stackoverflow.com/questions/17157721/how-to-get-a-docker-containers-ip-address-from-the-host
[5] https://docs.docker.com/reference/cli/docker/network/connect/
[6] https://www.reddit.com/r/docker/comments/1916gof/how_to_use_static_ips_to_reach_container/
[7] https://serverfault.com/questions/958367/how-do-i-give-a-docker-container-its-own-routable-ip-on-the-original-network
[8] https://www.baeldung.com/ops/docker-assign-static-ip-container
[9] https://kodekloud.com/blog/get-docker-container-ip/
[10] https://www.reddit.com/r/docker/comments/r9k4y0/how_do_i_run_a_container_with_an_ip_on_my_host/


---
host.docker.internal

---
https://docs.docker.com/language/golang/build-images/

---
https://bitbysystems.com/cloudflare-tunnels-docker/

---
### Debugging responsive images for avada:


- https://local.kevin-fechner.site/image/?url=https://sks.mokka-webdesign.com/wp-content/uploads/2023/01/ac23012.png&f=jpeg&q=80&w=800 - not working 
- https://sks.mokka-webdesign.com/wp-content/uploads/2023/01/ac23012.png - working


- https://local.kevin-fechner.site/image/?url=https://sks.mokka-webdesign.com/wp-content/uploads/2023/01/ac23012-400x300.png&f=jpeg&q=80&w=400 - working


--> local
- http://localhost:4444/image/?url=https://sks.mokka-webdesign.com/wp-content/uploads/2023/01/ac23012-400x300.png&f=jpeg&q=80&w=400 - ?
<!-- - https://sks.mokka-webdesign.com/wp-content/uploads/2023/01/ac23012.png - ? -->
- http://localhost:4444/image/?url=https://sks.mokka-webdesign.com/wp-content/uploads/2023/01/ac23012.png&f=jpeg&q=80&w=800 - ? 



<span class=" fusion-imageframe imageframe-none imageframe-2 peak2-f-png" style="border:10px solid #ffffff;"><img decoding="async" width="800" height="600" alt="Alu Verbundplatten" title="av23012" src="https://local.kevin-fechner.site/image/?url=https://sks.mokka-webdesign.com/wp-content/uploads/2023/01/av23012.png&amp;f=png&amp;q=80" class="img-responsive wp-image-32058" srcset="https://local.kevin-fechner.site/image/?url=https://sks.mokka-webdesign.com/wp-content/uploads/2023/01/av23012-200x150.png&amp;f=png&amp;q=80&amp;w=200 200w, https://local.kevin-fechner.site/image/?url=https://sks.mokka-webdesign.com/wp-content/uploads/2023/01/av23012-400x300.png&amp;f=png&amp;q=80&amp;w=400 400w, https://local.kevin-fechner.site/image/?url=https://sks.mokka-webdesign.com/wp-content/uploads/2023/01/av23012-600x450.png&amp;f=png&amp;q=80&amp;w=600 600w, https://local.kevin-fechner.site/image/?url=https://sks.mokka-webdesign.com/wp-content/uploads/2023/01/av23012.png&amp;f=png&amp;q=80&amp;w=800 800w, " sizes="(max-width: 800px) 100vw, 400px"></span>


---
http://localhost:4444/image?url=https://sks.mokka-webdesign.com/wp-content/uploads/2023/01/av23012.png&amp;f=png&amp;q=80