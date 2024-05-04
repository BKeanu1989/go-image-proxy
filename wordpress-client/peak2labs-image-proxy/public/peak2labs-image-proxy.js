console.log("peak2labs js workingd");

(async function () {
  try {
    let controller = new AbortController();
    setTimeout(() => controller.abort(), 10000);
    // let response = await fetch("http://localhost:8090/health", {
    let response = await fetch("http://localhost:8080", {
      signal: controller.signal,
      mode: "no-cors",
    });
    console.log(response);
  } catch (err) {
    if (err.name == "AbortError") {
      // handle abort()
      alert("Aborted!");
    } else {
      throw err;
    }
  }
})();
