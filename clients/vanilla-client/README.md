```js
let controller = new AbortController();
setTimeout(() => controller.abort(), 1000);

try {
  let response = await fetch('http://localhost:8090/health', {
    signal: controller.signal,
    mode: 'no-cors'
  });
} catch(err) {
  if (err.name == 'AbortError') { // handle abort()
    alert("Aborted!");
  } else {
    throw err;
  }
}
```

# how to handle responsiveness?

# What about base64 encoded images?
in general +30% file size compared to png / jpeg


---
what data is needed?

- width, height, format, quality, original_url
- srcset?


