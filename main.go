package main

import (
	"fmt"
	"image"
	"io"
	"log"
	"net/http"
	"net/url"
	"os"

	"example.com/image-proxy/myimage"
	"github.com/sunshineplan/imgconv"
)

// TODO: key & salt for signing

// http server
// quality, width, height, convert

// sha?
// cache - fs &/ memory => hash map[hash of filename] = base64 || with max size for fs & memory

// wordpress shortcode für bilder? mit js für dynamische größen
const foo = "http://localhost:5555/images?url={x}&w=500&h=500&q=80"

func main() {
	mux := http.NewServeMux()
	mux.HandleFunc("GET /image/", func(w http.ResponseWriter, r *http.Request) {
		fmt.Fprint(w, "got image\n")

		myUrl, _ := url.Parse(foo)
		params, _ := url.ParseQuery(myUrl.RawQuery)
		fmt.Println(params)

		// TODO: use params for image options
		// work on image with options
		// maybe save on disk?

		// urlString := r.URL.String()

		// fmt.Println("%s", urlString)

	})

	opts := myimage.Options{
		Width:   200,
		Height:  200,
		Quality: 80,
	}
	resizeNQualityImage("./example/images/4.png", opts)

	fmt.Println("Image downloaded successfully")

	mux.HandleFunc("/task/{id}/", func(w http.ResponseWriter, r *http.Request) {
		id := r.PathValue("id")
		fmt.Fprintf(w, "handling task with id=%v\n", id)
	})

	http.ListenAndServe("localhost:8090", mux)
}

func downloadImage(path string) {

	// imageUrl := "https://example.com/image.jpg"

	// Send HTTP GET request
	response, err := http.Get(path)
	if err != nil {
		fmt.Println("Error fetching image:", err)
		return
	}
	defer response.Body.Close()

	// Create the file where the image will be saved
	file, err := os.Create("image.jpg")
	if err != nil {
		fmt.Println("Error creating file:", err)
		return
	}
	defer file.Close()

	// Copy the response body to the file
	_, err = io.Copy(file, response.Body)
	if err != nil {
		fmt.Println("Error saving image:", err)
		return
	}
}

func resizeNQualityImage(srcPath string, opt myimage.Options) {
	src, err := imgconv.Open("./example/images/4.png")
	var mark image.Image
	if err != nil {
		log.Fatalf("failed to open image: %v", err)
	}

	if opt.Width != 0 {
		mark = imgconv.Resize(src, &imgconv.ResizeOption{Width: int(opt.Width)})
	}

	if opt.Height != 0 {
		mark = imgconv.Resize(src, &imgconv.ResizeOption{Height: int(opt.Height)})

	}

	if opt.Quality != 0 {
		imgconv.Quality(opt.Quality)
	}

	// resizedImage := imaging.Resize(src, 300, 200, imaging.Lanczos)

	// Save the resized image to a file
	err = imgconv.Save("output.jpg", mark, &imgconv.FormatOption{Format: imgconv.PNG})
	if err != nil {
		panic(err)
	}

	// Resize the image to width = 200px preserving the aspect ratio.

	// Add random watermark set opacity = 128.
	// dst := imgconv.Watermark(src, &imgconv.WatermarkOption{Mark: mark, Opacity: 128, Random: true})

	// Write the resulting image as TIFF.
	// err = imgconv.Write(io.Discard, mark, &imgconv.FormatOption{Format: imgconv.PNG})
	// if err != nil {
	// 	log.Fatalf("failed to write image: %v", err)
	// }
}
