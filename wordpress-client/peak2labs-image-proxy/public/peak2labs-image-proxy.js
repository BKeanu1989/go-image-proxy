console.log("peak2labs js workingd");

let controller = new AbortController();
setTimeout(() => controller.abort(), 10000);

(async function () {
  try {
    // let response = await fetch("http://localhost:8090/health", {
    let response = await fetch("http://localhost:3000", {
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
